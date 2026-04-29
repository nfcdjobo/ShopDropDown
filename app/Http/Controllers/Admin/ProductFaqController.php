<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductFaqRequest;
use App\Models\Product;
use App\Models\ProductFaq;
use Illuminate\Http\RedirectResponse;

/**
 * Gestion des questions fréquentes curatées par l'administrateur.
 */
class ProductFaqController extends Controller
{
    public function store(StoreProductFaqRequest $request, Product $product): RedirectResponse
    {
        if ($product->faqs()->count() >= Product::MAX_FAQS) {
            return back()->with('error', 'Limite de questions atteinte.');
        }

        $data = $request->validated();
        $data['product_id'] = $product->id;

        // sort_order auto-incrémenté également côté contrôleur (le hook du modèle
        // le fait aussi, mais on garantit ici qu'il sera explicitement passé)
        if (empty($data['sort_order'])) {
            $data['sort_order'] = (int) $product->faqs()->max('sort_order') + 1;
        }

        ProductFaq::create($data);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Question publiée avec succès.');
    }

    public function destroy(Product $product, ProductFaq $faq): RedirectResponse
    {
        abort_unless($faq->product_id === $product->id, 404);

        $faq->delete();

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('success', 'Question supprimée.');
    }
}
