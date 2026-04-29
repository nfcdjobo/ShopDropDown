<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductReviewRequest;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\RedirectResponse;

/**
 * Gestion des avis curatés par l'administrateur.
 *
 * Pas de méthode update() : un avis fictif est saisi tel quel ; pour le modifier,
 * l'admin le supprime et le recrée. Cela évite la complexité d'un formulaire
 * d'édition supplémentaire pour un cas d'usage rare.
 */
class ProductReviewController extends Controller
{
    public function store(StoreProductReviewRequest $request, Product $product): RedirectResponse
    {
        // Filet de sécurité : la limite est déjà vérifiée par le Form Request,
        // mais on protège quand même contre les conditions de course en cas de
        // double soumission depuis le modal.
        if ($product->reviews()->count() >= Product::MAX_REVIEWS) {
            return back()->with('error', 'Limite d\'avis atteinte.');
        }

        $data = $request->validated();
        $data['product_id'] = $product->id;

        // Auto-incrémente sort_order si non fourni, pour un ordre d'affichage stable
        if (empty($data['sort_order'])) {
            $data['sort_order'] = (int) $product->reviews()->max('sort_order') + 1;
        }

        ProductReview::create($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Avis publié avec succès.');
    }

    public function destroy(Product $product, ProductReview $review): RedirectResponse
    {
        // Vérifie que l'avis appartient bien au produit (protection contre IDOR)
        abort_unless($review->product_id === $product->id, 404);

        $review->delete(); // Le hook deleting du modèle nettoie l'avatar physique

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Avis supprimé.');
    }
}
