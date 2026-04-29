<?php

namespace App\Http\Controllers;

use App\Actions\Cart\GetOrCreateCartAction;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Landing page : affiche le(s) produit(s) mis en avant.
     */
    public function index(Request $request, GetOrCreateCartAction $getCart): View
    {
        $cart = $getCart->handle($request)->load('items.product');

        $featuredProducts = Product::query()
            ->active()
            ->inStock()
            ->sellable()
            ->with(['images', 'category'])
            ->latest()
            ->take(8)
            ->get();

        $heroProduct = $featuredProducts->first() ?? Product::query()
            ->active()
            ->inStock()
            ->sellable()
            ->with('images')
            ->latest()
            ->first();

        return view('landing.index', [
            'cart'             => $cart,
            'heroProduct'      => $heroProduct,
            'featuredProducts' => $featuredProducts,
        ]);
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
