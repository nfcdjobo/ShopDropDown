<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductItemRequest;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductProof;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * CRUD admin des produits.
 *
 * Gère deux types vendables seuls : 'simple' et 'package'.
 * Les sous-produits de package (type = 'item') sont managés via les méthodes
 * storeItem / destroyItem qui s'attachent à un package parent — ils n'apparaissent
 * jamais dans le listing principal.
 */
class ProductController extends Controller
{
    private const IMAGE_DISK = 'public';
    private const IMAGE_DIR  = 'products';
    private const PROOF_DIR  = 'products/proofs';

    /**
     * Champs présents dans le formulaire mais qui ne doivent jamais être passés
     * directement à Product::create() / ::update() — ils sont traités à part.
     */
    private const EXCLUDED_FROM_MASS_ASSIGNMENT = [
        'gallery',
        'delete_images',
        'proofs',
        'proof_captions',
        'proof_alt_texts',
        'delete_proofs',
        'existing_proofs',
    ];

    /*
    |--------------------------------------------------------------------------
    | CRUD principal — produits vendables (simple + package)
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View|RedirectResponse
    {
        try {
            $validated = $request->validate([
                'q'           => ['nullable', 'string', 'max:100'],
                'category_id' => ['nullable', 'string'],
                'type'        => ['nullable', 'in:simple,package'],
                'status'      => ['nullable', 'in:active,inactive,featured,low_stock'],
            ]);

            $query = Product::query()
                ->sellable()
                ->with('category', 'images')
                ->latest();

            if ($q = $validated['q'] ?? null) {
                $query->search($q);
            }

            if ($cat = $validated['category_id'] ?? null) {
                $query->where('category_id', $cat);
            }

            if ($type = $validated['type'] ?? null) {
                $query->where('type', $type);
            }

            match ($validated['status'] ?? null) {
                'active'    => $query->where('is_active', true),
                'inactive'  => $query->where('is_active', false),
                'featured'  => $query->featured(),
                'low_stock' => $query->where('track_stock', true)
                                     ->whereColumn('stock', '<=', 'low_stock_threshold'),
                default     => null,
            };

            return view('admin.products.index', [
                'products'   => $query->paginate(20)->withQueryString(),
                'categories' => Category::active()->ordered()->get(),
                'filters'    => $validated,
            ]);
        } catch (Throwable $e) {
            Log::error('Erreur listing produits', [
                'exception' => $e->getMessage(),
                'user_id'   => $request->user()?->id,
            ]);

            return back()->with('error', "Impossible de charger la liste des produits.");
        }
    }

    public function create(): View|RedirectResponse
    {
        try {
            return view('admin.products.create', [
                'product' => new Product([
                    'type'        => Product::TYPE_SIMPLE,
                    'is_active'   => true,
                    'track_stock' => true,
                ]),
                'categories' => Category::active()->ordered()->get(),
            ]);
        } catch (Throwable $e) {
            Log::error('Erreur affichage formulaire création produit', [
                'exception' => $e->getMessage(),
            ]);

            return redirect()->route('admin.products.index')
                ->with('error', "Impossible d'ouvrir le formulaire de création.");
        }
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(self::EXCLUDED_FROM_MASS_ASSIGNMENT);

        // Garde-fou : ce contrôleur ne crée jamais de sous-produit.
        unset($data['parent_id'], $data['package_role'], $data['package_sort_order']);
        if (($data['type'] ?? null) === Product::TYPE_ITEM) {
            $data['type'] = Product::TYPE_SIMPLE;
        }

        $uploadedPaths = []; // Fichiers à supprimer si rollback

        DB::beginTransaction();

        try {
            if ($featured = $request->file('featured_image')) {
                $path = $featured->store(self::IMAGE_DIR, self::IMAGE_DISK);
                $uploadedPaths[] = $path;
                $data['featured_image'] = $path;
            }

            $product = Product::create($data);

            $galleryPaths  = $this->syncGalleryImages($product, $request->file('gallery', []));
            $uploadedPaths = array_merge($uploadedPaths, $galleryPaths);

            $proofPaths = $this->syncProofs(
                $product,
                $request->file('proofs', []),
                (array) $request->input('proof_captions', []),
                (array) $request->input('proof_alt_texts', []),
            );
            $uploadedPaths = array_merge($uploadedPaths, $proofPaths);

            DB::commit();

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', "Produit « {$product->name} » créé.");
        } catch (Throwable $e) {
            DB::rollBack();

            $this->cleanupUploadedFiles($uploadedPaths);

            Log::error('Erreur création produit', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'user_id'   => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', "Une erreur est survenue lors de la création du produit. Veuillez réessayer.");
        }
    }

    public function show(Product $product): RedirectResponse
    {
        return redirect()->route('admin.products.edit', $product);
    }

    public function edit(Product $product): View|RedirectResponse
    {
        try {
            $product->load([
                'images',
                'proofs',
                'category',
                'items.images',
                'reviews',
                'faqs',
            ]);

            return view('admin.products.edit', [
                'product'    => $product,
                'categories' => Category::active()->ordered()->get(),
            ]);
        } catch (Throwable $e) {
            Log::error('Erreur affichage édition produit', [
                'exception'  => $e->getMessage(),
                'product_id' => $product->id ?? null,
            ]);

            return redirect()->route('admin.products.index')
                ->with('error', "Impossible d'ouvrir la fiche produit.");
        }
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except(self::EXCLUDED_FROM_MASS_ASSIGNMENT);

        // Empêche un changement involontaire de la hiérarchie via ce endpoint.
        unset($data['parent_id'], $data['package_role'], $data['package_sort_order']);
        if (($data['type'] ?? null) === Product::TYPE_ITEM) {
            unset($data['type']);
        }

        $uploadedPaths  = []; // Nouveaux fichiers — à supprimer si rollback
        $pendingDeletes = []; // Anciens fichiers — à supprimer seulement si commit réussi

        DB::beginTransaction();

        try {
            if ($featured = $request->file('featured_image')) {
                if ($product->featured_image) {
                    $pendingDeletes[] = $product->featured_image;
                }
                $path = $featured->store(self::IMAGE_DIR, self::IMAGE_DISK);
                $uploadedPaths[] = $path;
                $data['featured_image'] = $path;
            }

            $product->update($data);

            // ─── Galerie ─────────────────────────────────────────────────
            if ($idsToDelete = $request->input('delete_images', [])) {
                $product->images()->whereIn('id', $idsToDelete)->get()
                    ->each->delete();
            }

            $galleryPaths  = $this->syncGalleryImages($product, $request->file('gallery', []));
            $uploadedPaths = array_merge($uploadedPaths, $galleryPaths);

            // ─── Preuves visuelles ───────────────────────────────────────
            if ($proofIdsToDelete = $request->input('delete_proofs', [])) {
                $product->proofs()->whereIn('id', $proofIdsToDelete)->get()
                    ->each->delete();
            }

            $this->updateExistingProofs(
                $product,
                (array) $request->input('existing_proofs', []),
            );

            $proofPaths = $this->syncProofs(
                $product,
                $request->file('proofs', []),
                (array) $request->input('proof_captions', []),
                (array) $request->input('proof_alt_texts', []),
            );
            $uploadedPaths = array_merge($uploadedPaths, $proofPaths);

            DB::commit();

            // Commit OK : on peut supprimer les anciens fichiers physiquement.
            $this->cleanupUploadedFiles($pendingDeletes);

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Produit mis à jour.');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->cleanupUploadedFiles($uploadedPaths);

            Log::error('Erreur mise à jour produit', [
                'exception'  => $e->getMessage(),
                'trace'      => $e->getTraceAsString(),
                'product_id' => $product->id,
                'user_id'    => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', "Une erreur est survenue lors de la mise à jour du produit.");
        }
    }

    public function destroy(Product $product): RedirectResponse
    {
        DB::beginTransaction();

        try {
            // Soft delete — la cascade des items se fait via la FK cascadeOnDelete()
            // au moment du forceDelete uniquement.
            $product->delete();

            DB::commit();

            return redirect()
                ->route('admin.products.index')
                ->with('success', "Produit « {$product->name} » archivé.");
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Erreur suppression produit', [
                'exception'  => $e->getMessage(),
                'product_id' => $product->id,
                'user_id'    => $this->getUserId(),
            ]);

            return back()->with('error', "Impossible d'archiver ce produit.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Sous-produits d'un package
    |--------------------------------------------------------------------------
    */

    public function storeItem(StoreProductItemRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->isPackage(), 404, 'Seul un package peut recevoir des sous-produits.');

        if ($request->input('package_role') === Product::ROLE_PRINCIPAL
            && $product->principalItem()->exists()) {
            return back()->with('error', 'Ce package a déjà un produit principal.');
        }

        $data = $request->safe()->except(['gallery']);
        $data['type']               = Product::TYPE_ITEM;
        $data['parent_id']          = $product->id;
        $data['category_id']        = $product->category_id;
        $data['is_active']          = true;
        $data['track_stock']        = false;
        $data['price']              = null;
        $data['package_sort_order'] = $data['package_sort_order']
            ?? ((int) $product->items()->max('package_sort_order') + 1);

        $uploadedPaths = [];

        DB::beginTransaction();

        try {
            if ($featured = $request->file('featured_image')) {
                $path = $featured->store(self::IMAGE_DIR, self::IMAGE_DISK);
                $uploadedPaths[] = $path;
                $data['featured_image'] = $path;
            }

            $item = Product::create($data);

            $galleryPaths  = $this->syncGalleryImages($item, $request->file('gallery', []));
            $uploadedPaths = array_merge($uploadedPaths, $galleryPaths);

            DB::commit();

            return redirect()
                ->route('admin.products.edit', $product)
                ->with('success', 'Sous-produit ajouté au package.');
        } catch (Throwable $e) {
            DB::rollBack();

            $this->cleanupUploadedFiles($uploadedPaths);

            Log::error('Erreur ajout sous-produit', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'parent_id' => $product->id,
                'user_id'   => $request->user()?->id,
            ]);

            return back()
                ->withInput()
                ->with('error', "Impossible d'ajouter le sous-produit.");
        }
    }

    public function destroyItem(Product $product, Product $item): RedirectResponse
    {
        abort_unless(
            $product->isPackage() && $item->isItem() && $item->parent_id === $product->id,
            404,
            'Sous-produit introuvable dans ce package.'
        );

        DB::beginTransaction();

        try {
            // On garde une trace des fichiers à supprimer après commit
            $filesToDelete = [];
            if ($item->featured_image) {
                $filesToDelete[] = $item->featured_image;
            }

            foreach ($item->images as $img) {
                $img->delete(); // ProductImage gère son propre fichier
            }
            $item->forceDelete();

            DB::commit();

            // Suppression physique du featured_image après commit
            $this->cleanupUploadedFiles($filesToDelete);

            return back()->with('success', 'Sous-produit retiré.');
        } catch (Throwable $e) {
            DB::rollBack();

            Log::error('Erreur suppression sous-produit', [
                'exception'  => $e->getMessage(),
                'parent_id'  => $product->id,
                'item_id'    => $item->id,
                'user_id'    => $this->getUserId(),
            ]);

            return back()->with('error', "Impossible de retirer ce sous-produit.");
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers privés — Galerie
    |--------------------------------------------------------------------------
    */

    /**
     * Crée les ProductImage pour chaque fichier de galerie et retourne les
     * paths créés (utile pour cleanup en cas de rollback).
     *
     * @param  array<int, UploadedFile|null>  $files
     * @return array<int, string>
     */
    private function syncGalleryImages(Product $product, array $files): array
    {
        if (empty($files)) {
            return [];
        }

        $uploadedPaths = [];
        $startOrder    = (int) $product->images()->max('sort_order') + 1;

        foreach ($files as $i => $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store(self::IMAGE_DIR . '/gallery', self::IMAGE_DISK);
            $uploadedPaths[] = $path;

            ProductImage::create([
                'product_id' => $product->id,
                'path'       => $path,
                'sort_order' => $startOrder + $i,
            ]);
        }

        return $uploadedPaths;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers privés — Preuves visuelles
    |--------------------------------------------------------------------------
    */

    /**
     * Crée les ProductProof pour chaque fichier soumis. Les légendes et alt_text
     * sont alignés par index sur le tableau de fichiers.
     *
     * @param  array<int, UploadedFile|null>  $files
     * @param  array<int, string|null>        $captions
     * @param  array<int, string|null>        $altTexts
     * @return array<int, string>  Paths uploadés (pour rollback éventuel)
     */
    private function syncProofs(
        Product $product,
        array $files,
        array $captions = [],
        array $altTexts = [],
    ): array {
        if (empty($files)) {
            return [];
        }

        $uploadedPaths = [];
        $startOrder    = (int) $product->proofs()->max('sort_order') + 1;

        foreach ($files as $i => $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store(self::PROOF_DIR, self::IMAGE_DISK);
            $uploadedPaths[] = $path;

            ProductProof::create([
                'product_id' => $product->id,
                'path'       => $path,
                'caption'    => $captions[$i] ?? null,
                'alt_text'   => $altTexts[$i] ?? null,
                'sort_order' => $startOrder + $i,
            ]);
        }

        return $uploadedPaths;
    }

    /**
     * Met à jour les métadonnées (caption, alt_text, sort_order) des preuves
     * existantes dont l'UUID est passé en clé du tableau.
     *
     * Format attendu :
     *   [
     *     '01HF...uuid' => ['caption' => '...', 'alt_text' => '...', 'sort_order' => 2],
     *     ...
     *   ]
     *
     * Ne fait rien si la preuve n'appartient pas au produit (sécurité).
     *
     * @param  array<string, array<string, mixed>>  $existing
     */
    private function updateExistingProofs(Product $product, array $existing): void
    {
        if (empty($existing)) {
            return;
        }

        // Récupère uniquement les preuves qui appartiennent vraiment à ce produit
        // — empêche un attaquant de modifier les preuves d'un autre produit
        // en injectant des UUIDs inconnus dans le formulaire.
        $owned = $product->proofs()
            ->whereIn('id', array_keys($existing))
            ->get()
            ->keyBy('id');

        foreach ($existing as $id => $attrs) {
            $proof = $owned->get($id);
            if (!$proof) {
                continue;
            }

            $update = [];

            if (array_key_exists('caption', $attrs)) {
                $update['caption'] = $attrs['caption'] !== '' ? $attrs['caption'] : null;
            }
            if (array_key_exists('alt_text', $attrs)) {
                $update['alt_text'] = $attrs['alt_text'] !== '' ? $attrs['alt_text'] : null;
            }
            if (isset($attrs['sort_order']) && $attrs['sort_order'] !== '') {
                $update['sort_order'] = (int) $attrs['sort_order'];
            }

            if (!empty($update)) {
                $proof->update($update);
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers privés — Fichiers
    |--------------------------------------------------------------------------
    */

    /**
     * Supprime les fichiers du disque, en silenciant les erreurs individuelles
     * (un fichier déjà absent ne doit pas casser le flux).
     *
     * @param  array<int, string>  $paths
     */
    private function cleanupUploadedFiles(array $paths): void
    {
        foreach ($paths as $path) {
            if (!$path) {
                continue;
            }

            try {
                Storage::disk(self::IMAGE_DISK)->delete($path);
            } catch (Throwable $e) {
                Log::warning("Impossible de supprimer le fichier orphelin", [
                    'path'  => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    private function getUserId(): ?string
    {
        return request()->user()?->id;
    }
}
