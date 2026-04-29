<?php

namespace App\Http\Controllers;

use App\Actions\Cart\GetOrCreateCartAction;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion du panier. Fonctionne indifféremment pour visiteurs invités
 * (panier lié à session_id) et utilisateurs connectés (panier lié à user_id).
 * Toutes les méthodes retournent soit une redirection (navigation classique)
 * soit du JSON (appels AJAX depuis la landing page, pour une UX réactive).
 */
class CartController extends Controller
{
    public function __construct(
        private readonly GetOrCreateCartAction $getCart,
    ) {}

    /**
     * GET /panier — affiche le contenu du panier.
     */
    public function index(Request $request): View
    {
        $cart = $this->getCart->handle($request)->load('items.product.images');

        return view('cart.index', compact('cart'));
    }

    /**
     * POST /panier — ajoute un produit au panier.
     * Retourne du JSON si la requête est AJAX, sinon redirige.
     */
    public function store(AddToCartRequest $request): RedirectResponse|JsonResponse
    {
        $product = Product::findOrFail($request->validated('product_id'));
        $quantity = $request->quantity();

        if (!$product->canBeOrdered($quantity)) {
            return $this->respond(
                $request,
                success: false,
                message: "Produit indisponible dans la quantité demandée.",
                status: 422,
            );
        }

        $cart = $this->getCart->handle($request);
        $cart->addProduct($product, $quantity);
        $cart->refresh()->load('items');

        return $this->respond(
            $request,
            success: true,
            message: 'Produit ajouté au panier.',
            data: [
                'items_count'        => $cart->items_count,
                'subtotal'           => $cart->subtotal,
                'formatted_subtotal' => $cart->formatted_subtotal,
            ],
        );
    }

    /**
     * PATCH /panier/{item} — modifie la quantité d'une ligne.
     * Supprime la ligne si quantity = 0.
     */
    public function update(UpdateCartItemRequest $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $cart = $this->getCart->handle($request);

        // Sécurité : l'item doit appartenir au panier courant.
        abort_unless($item->cart_id === $cart->id, 403);

        $quantity = (int) $request->validated('quantity');

        if ($quantity === 0) {
            $item->delete();
        } else {
            $product = $item->product;
            if ($product && !$product->canBeOrdered($quantity)) {
                return $this->respond(
                    $request,
                    success: false,
                    message: "Stock insuffisant pour « {$product->name} ».",
                    status: 422,
                );
            }
            $item->update(['quantity' => $quantity]);
        }

        $cart->refresh()->load('items');

        return $this->respond(
            $request,
            success: true,
            message: 'Panier mis à jour.',
            data: [
                'items_count'        => $cart->items_count,
                'subtotal'           => $cart->subtotal,
                'formatted_subtotal' => $cart->formatted_subtotal,
            ],
        );
    }

    /**
     * DELETE /panier/{item} — retire une ligne du panier.
     */
    public function destroy(Request $request, CartItem $item): RedirectResponse|JsonResponse
    {
        $cart = $this->getCart->handle($request);
        abort_unless($item->cart_id === $cart->id, 403);

        $item->delete();
        $cart->refresh()->load('items');

        return $this->respond(
            $request,
            success: true,
            message: 'Article retiré du panier.',
            data: [
                'items_count'        => $cart->items_count,
                'subtotal'           => $cart->subtotal,
                'formatted_subtotal' => $cart->formatted_subtotal,
            ],
        );
    }

    /**
     * DELETE /panier — vide complètement le panier.
     */
    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $cart = $this->getCart->handle($request);
        $cart->clear();

        return $this->respond(
            $request,
            success: true,
            message: 'Panier vidé.',
            data: ['items_count' => 0, 'subtotal' => 0, 'formatted_subtotal' => '0 FCFA'],
        );
    }

    /**
     * GET /panier/resume — mini-résumé JSON (pour le badge du panier en header).
     */
    public function summary(Request $request): JsonResponse
    {
        $cart = $this->getCart->handle($request)->load('items');

        return response()->json([
            'items_count'        => $cart->items_count,
            'subtotal'           => $cart->subtotal,
            'formatted_subtotal' => $cart->formatted_subtotal,
            'is_empty'           => $cart->is_empty,
        ]);
    }

    /**
     * Helper : réponse JSON ou redirection selon que la requête soit AJAX ou non.
     */
    private function respond(
        Request $request,
        bool $success,
        string $message,
        array $data = [],
        int $status = 200,
    ): RedirectResponse|JsonResponse {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => $success,
                'message' => $message,
                ...$data,
            ], $status);
        }

        return $success
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }
}
