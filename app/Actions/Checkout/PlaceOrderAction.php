<?php

namespace App\Actions\Checkout;

use App\Exceptions\CheckoutException;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Notifications\OrderConfirmationNotification;
use App\Notifications\WelcomeWithDefaultPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PlaceOrderAction
{
    public function __construct(
        private readonly CreateGuestUserAction $createGuestUser,
    ) {}

    /**
     * Checkout invité :
     *   1. Création du compte client (mot de passe par défaut généré)
     *   2. Rattachement du panier à ce nouveau user
     *   3. Création de la commande + lignes (snapshots produit)
     *   4. Décrément du stock
     *   5. Vidage du panier
     *   6. Notifications : email + push aux admins, email de bienvenue au client
     *
     * @return array{order: Order, user: User, plain_password: string, is_new_user: bool}
     */
    public function handleGuestCheckout(Cart $cart, array $guestData, Request $request): array
    {
        /** @var array{user: User, order: Order, plainPassword: string} $result */
        $result = DB::transaction(function () use ($cart, $guestData, $request) {
            [$user, $plainPassword] = $this->createGuestUser->handle($guestData);

            $cart->update([
                'user_id'    => $user->id,
                'session_id' => null,
            ]);

            $order = $this->createOrderFromCart($cart, $user, $guestData, $request);

            return compact('user', 'order', 'plainPassword');
        });

        // Les trois variables sont typées : User, Order, string — plus de null.
        $this->dispatchNotifications($result['order'], $result['user'], $result['plainPassword'], isNewUser: true);

        return [
            'order'          => $result['order'],
            'user'           => $result['user'],
            'plain_password' => $result['plainPassword'],
            'is_new_user'    => true,
        ];
    }


    /**
     * Checkout pour utilisateur déjà connecté.
     *
     * @return array{order: Order, user: User, plain_password: null, is_new_user: false}
     */
    public function handleAuthenticatedCheckout(Cart $cart, User $user, array $shippingData, Request $request): array
    {
        /** @var Order $order */
        $order = DB::transaction(
            fn() => $this->createOrderFromCart($cart, $user, $shippingData, $request)
        );

        $this->dispatchNotifications($order, $user, plainPassword: null, isNewUser: false);

        return [
            'order'          => $order,
            'user'           => $user,
            'plain_password' => null,
            'is_new_user'    => false,
        ];
    }

    /**
     * Routine commune de création de la commande à partir du panier.
     * Toujours appelée DANS une transaction DB.
     */
    private function createOrderFromCart(Cart $cart, User $user, array $shipping, Request $request): Order
    {
        $cart->loadMissing('items.product');

        if ($cart->items->isEmpty()) {
            throw new CheckoutException('Votre panier est vide.');
        }

        // Vérification de disponibilité avec verrou pour éviter la survente
        // si deux checkouts partent en parallèle sur le dernier article.
        foreach ($cart->items as $item) {
            $product = $item->product()->lockForUpdate()->first();

            if (!$product || !$product->canBeOrdered($item->quantity)) {
                throw new CheckoutException(
                    "Le produit « {$item->product?->name} » n'est plus disponible dans la quantité demandée."
                );
            }
        }

        $subtotal    = (float) $cart->subtotal;
        $shippingFee = 0.0; // À remplacer plus tard par un ShippingCalculator dédié
        $discount    = 0.0;
        $tax         = 0.0;
        $total       = $subtotal + $shippingFee + $tax - $discount;

        // Création de la commande (statut 'pending' par défaut via le modèle)
        $order = Order::create([
            'user_id'            => $user->id,

            // Snapshots livraison
            'shipping_full_name' => $shipping['full_name'] ?? $user->full_name,
            'shipping_phone'     => $shipping['phone']     ?? $user->phone,
            'shipping_email'     => $shipping['email']     ?? $user->email,
            'shipping_country'   => $shipping['country']   ?? 'CI',
            'shipping_city'      => $shipping['city']      ?? '',
            'shipping_address'   => $shipping['address']   ?? '',
            'shipping_notes'     => $shipping['notes']     ?? null,

            // Montants
            'subtotal'           => $subtotal,
            'shipping_fee'       => $shippingFee,
            'discount'           => $discount,
            'tax'                => $tax,
            'total'              => $total,
            'currency'           => 'XOF',

            // Contexte technique
            'ip_address'         => $request->ip(),
            'user_agent'         => $request->userAgent(),
        ]);

        // Lignes de commande + décrément stock
        foreach ($cart->items as $cartItem) {
            $order->items()->create(OrderItem::fromCartItem($cartItem));

            if ($cartItem->product) {
                $cartItem->product->decrementStock($cartItem->quantity);
                $cartItem->product->increment('orders_count', $cartItem->quantity);
            }
        }

        // Panier vidé uniquement après succès de toute la chaîne
        $cart->clear();

        return $order->fresh(['items', 'user']);
    }

    /**
     * Envoi des notifications post-commande.
     * Appelée hors transaction : ne peut pas être annulée par un rollback.
     *
     * Les notifications implémentent ShouldQueue → file d'attente, non-bloquant.
     */
    private function dispatchNotifications(Order $order, User $user, ?string $plainPassword, bool $isNewUser): void
    {
        // --- Client ---
        if ($isNewUser && $plainPassword !== null) {
            // Nouveau client : bienvenue + confirmation + identifiants provisoires
            $user->notify(new WelcomeWithDefaultPasswordNotification($order, $plainPassword));
        } else {
            // Client existant : confirmation commande uniquement
            $user->notify(new OrderConfirmationNotification($order));
        }

        // --- Admins (mail + in-app database + web push si abonnés) ---
        $admins = User::admins()->active()->get();
        if ($admins->isNotEmpty()) {
            Notification::send($admins, new NewOrderNotification($order));
        }
    }
}
