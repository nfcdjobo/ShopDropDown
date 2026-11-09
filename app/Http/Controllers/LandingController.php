<?php

namespace App\Http\Controllers;

use App\Actions\Cart\GetOrCreateCartAction;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LandingController extends Controller
{
    /** Nombre de produits chargés à chaque « Voir plus ». */
    private const PER_PAGE = 15;

    /** Sélections disponibles via ?selection=… (clé => libellé). */
    private const SELECTIONS = [
        'promos'     => 'Offres du moment',
        'nouveautes' => 'Nouveautés',
        'favoris'    => 'Coups de cœur',
    ];

    /**
     * Accueil façon marketplace : recherche, catégories, carrousel, bandes de produits
     * (offres, nouveautés, coups de cœur) puis catalogue paginé.
     *
     * Paramètres d'URL (tous optionnels) :
     *   ?q=…            recherche dans le nom et la description courte
     *   ?categorie=ID   filtre par catégorie
     *   ?selection=…    promos | nouveautes | favoris
     *
     * Dès qu'un filtre est actif, la page affiche uniquement les résultats (et n'est
     * pas indexée). Requête JSON/AJAX (« Voir plus ») : renvoie seulement les cartes suivantes.
     */
    public function index(Request $request, GetOrCreateCartAction $getCart): View|JsonResponse
    {
        // ─── Filtres (entrées normalisées : jamais de tableau ni de texte démesuré) ───
        $q = is_string($request->query('q')) ? trim(Str::limit($request->query('q'), 100, '')) : '';

        $selectionInput = $request->query('selection');
        $selection = is_string($selectionInput) && array_key_exists($selectionInput, self::SELECTIONS)
            ? $selectionInput
            : null;

        // Catégories contenant au moins un produit publié
        $categories = Category::query()
            ->whereIn('id', Product::query()->active()->sellable()->whereNotNull('category_id')->select('category_id'))
            ->orderBy('name')
            ->get();

        $categoryInput  = $request->query('categorie');
        $activeCategory = is_string($categoryInput) ? $categories->firstWhere('id', $categoryInput) : null;

        $isFiltered = $q !== '' || $activeCategory !== null || $selection !== null;

        // ─── Catalogue paginé ───
        $products = Product::query()
            ->active()
            ->sellable()
            ->with(['images', 'category'])
            ->when($q !== '', function (Builder $query) use ($q) {
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], mb_strtolower($q)) . '%';

                $query->where(fn (Builder $w) => $w
                    ->whereRaw('LOWER(name) LIKE ?', [$like])
                    ->orWhereRaw('LOWER(short_description) LIKE ?', [$like]));
            })
            ->when($activeCategory, fn (Builder $query) => $query->where('category_id', $activeCategory->id))
            ->when($selection, fn (Builder $query) => $this->applySelection($query, $selection))
            ->orderInStockFirst()
            ->latest()
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'html'  => view('landing.partials.product-cards', [
                    'products' => $products,
                    'animate'  => false,
                ])->render(),
                'next'  => $products->nextPageUrl(),
                'total' => $products->total(),
            ]);
        }

        $cart = $getCart->handle($request)->load('items.product');

        // ─── Bandes de la page d'accueil (inutiles quand on filtre) ───
        $heroProducts = $deals = $newArrivals = $favorites = collect();

        if (! $isFiltered) {
            $available = fn (): Builder => Product::query()
                ->active()->inStock()->sellable()
                ->with(['images', 'category']);

            $heroProducts = $available()->orderByDesc('is_featured')->latest()->take(4)->get();
            $deals        = $this->applySelection($available(), 'promos')->latest()->take(12)->get();
            $newArrivals  = $this->applySelection($available(), 'nouveautes')->latest()->take(12)->get();
            $favorites    = $this->applySelection($available(), 'favoris')->latest()->take(12)->get();
        }

        return view('landing.index', [
            'cart'           => $cart,
            'products'       => $products,
            'categories'     => $categories,
            'activeCategory' => $activeCategory,
            'q'              => $q,
            'selection'      => $selection,
            'selectionLabel' => $selection ? self::SELECTIONS[$selection] : null,
            'isFiltered'     => $isFiltered,
            'heroProducts'   => $heroProducts,
            'deals'          => $deals,
            'newArrivals'    => $newArrivals,
            'favorites'      => $favorites,
        ]);
    }

    /**
     * Applique une sélection éditoriale à une requête de produits.
     */
    private function applySelection(Builder $query, string $selection): Builder
    {
        return match ($selection) {
            'promos'     => $query->whereNotNull('compare_price')->whereColumn('compare_price', '>', 'price'),
            'nouveautes' => $query->where('created_at', '>=', now()->subDays(30)),
            'favoris'    => $query->where('is_featured', true),
        };
    }

    /**
     * Page détail d'un produit (route-model binding via slug).
     *
     * Eager-loading exhaustif : la vue affiche images, preuves, avis, FAQs,
     * et — si c'est un package — les sous-produits avec leurs images.
     */
    public function show(Request $request, Product $product, GetOrCreateCartAction $getCart): View
    {
        abort_unless($product->is_active, 404);

        // Empêche d'ouvrir directement un sous-produit via son slug — il n'a
        // pas vocation à exister seul sur la boutique.
        abort_if($product->isItem(), 404);

        $product->incrementViews();

        $product->load([
            'images',
            'category',
            'proofs',
            'reviews',
            'faqs',
            'items' => fn ($q) => $q->orderBy('package_sort_order'),
            'items.images',
        ]);

        $cart = $getCart->handle($request)->load('items.product');

        // Produits liés : uniquement vendables seuls (jamais de sous-produit).
        $relatedProducts = Product::query()
            ->active()
            ->inStock()
            ->sellable()
            ->where('id', '!=', $product->id)
            ->when(
                $product->category_id,
                fn ($q) => $q->where('category_id', $product->category_id)
            )
            ->with(['images', 'category'])
            ->take(4)
            ->get();

        // ─── Date de livraison estimée ───────────────────────────────────
        // Calculée côté serveur pour rester stable au refresh (sinon le
        // compteur se réinitialise à chaque page-load et perd toute crédibilité).
        // Cutoff métier : passé 18h, l'expédition est considérée comme
        // partant le lendemain.
        $deliveryEstimate = null;
        if ($product->availability_min_days !== null) {
            $base = now();
            if ($base->hour >= 18) {
                $base = $base->addDay()->startOfDay();
            }
            $deliveryEstimate = $base
                ->copy()
                ->addDays($product->availability_min_days)
                ->setTime(18, 0); // arrivée prévue en fin de journée ouvrée
        }


        // dd($product, $relatedProducts);
        return view('landing.show', [
            'product'          => $product,
            'cart'             => $cart,
            'relatedProducts'  => $relatedProducts,
            'deliveryEstimate' => $deliveryEstimate,
        ]);
    }
}
