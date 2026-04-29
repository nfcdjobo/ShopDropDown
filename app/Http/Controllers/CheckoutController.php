<?php

namespace App\Http\Controllers;

use App\Actions\Cart\GetOrCreateCartAction;
use App\Actions\Checkout\PlaceOrderAction;
use App\Exceptions\CheckoutException;
use App\Http\Requests\Checkout\GuestCheckoutRequest;
use App\Http\Requests\Checkout\LoginCheckoutRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Orchestration du checkout :
 *
 *   GET  /checkout           -> page unique qui montre le panier + le form
 *                               (3 modes visibles en onglets : invité, login, inscription)
 *   POST /checkout/guest     -> validation + création user auto + commande
 *   POST /checkout/login     -> login in-place puis redirection vers processAuthenticated
 *   POST /checkout           -> pour user déjà connecté : validation commande directe
 *   GET  /checkout/merci/... -> page de succès
 */
class CheckoutController extends Controller
{
    public function __construct(
        private readonly GetOrCreateCartAction $getCart,
        private readonly PlaceOrderAction $placeOrder,
    ) {}

    /**
     * GET /checkout — page unique avec panier toujours visible.
     * Si le panier est vide, on renvoie sur la home.
     */
    public function index(Request $request): View|RedirectResponse
    {

        $cart = $this->getCart->handle($request)->load('items.product.images');

        // if ($cart->is_empty) {
        //     return redirect()->route('home')
        //         ->with('warning', 'Votre panier est vide.');
        // }

        return view('checkout.index', [
            'cart' => $cart,
            'user' => $request->user(),
        ]);
    }

    /**
     * POST /checkout/guest — checkout pour visiteur non connecté.
     * Crée le compte + la commande + envoie les notifications.
     */
    public function storeAsGuest(GuestCheckoutRequest $request): RedirectResponse
    {

        if ($request->user()) {
            // L'utilisateur s'est connecté entre-temps : on bascule sur le flow connecté.
            return redirect()->route('checkout.index');
        }

        $cart = $this->getCart->handle($request);

        if ($cart->is_empty) {
            return redirect()->route('home')->with('error', 'Votre panier est vide.');
        }


        try {
            $result = $this->placeOrder->handleGuestCheckout(
                $cart,
                $request->shippingData(),
                $request,
            );
        } catch (CheckoutException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        // Connexion automatique du nouvel utilisateur pour le rediriger sur son espace.
        Auth::login($result['user']);
        $request->session()->regenerate();

        return redirect()
            ->route('customer.orders.show', $result['order']->order_number)
            ->with('order_success', [
                'order_number'      => $result['order']->order_number,
                'is_new_user'       => true,
                'password_emailed'  => true, // flag pour afficher "votre mot de passe a été envoyé par email"
            ]);
    }

    /**
     * POST /checkout/login — login depuis la page checkout.
     * L'event Login déclenche MergeGuestCartOnLogin → le panier invité
     * est fusionné avec celui du user avant qu'on arrive ici.
     */
    public function loginAsExistingCustomer(LoginCheckoutRequest $request): RedirectResponse
    {
        // Rate limiting anti-brute-force
        $throttleKey = 'checkout-login:' . strtolower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => "Trop de tentatives. Réessayez dans {$seconds} secondes.",
            ]);
        }

        if (!Auth::attempt($request->credentials(), $request->remember())) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => 'Identifiants incorrects.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        // Retour sur la page checkout : le panier a été fusionné, l'utilisateur
        // voit maintenant l'écran "confirmez votre commande" (mode connecté).
        return redirect()->route('checkout.index')
            ->with('success', 'Vous êtes connecté. Vérifiez votre commande avant de valider.');
    }

    /**
     * POST /checkout — validation finale pour utilisateur connecté.
     * Le formulaire envoie les infos de livraison (qui peuvent être pré-remplies
     * avec le profil ou une adresse enregistrée).
     */
    public function storeAsAuthenticated(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user, 401);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'phone'     => ['required', 'string', 'regex:/^[\d\s\-\+\(\)]{8,20}$/'],
            'email'     => ['required', 'email:rfc', 'max:150'],
            'city'      => ['required', 'string', 'max:80'],
            'address'   => ['required', 'string', 'max:255'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ]);

        $cart = $this->getCart->handle($request);

        if ($cart->is_empty) {
            return redirect()->route('home')->with('error', 'Votre panier est vide.');
        }

        try {
            $result = $this->placeOrder->handleAuthenticatedCheckout(
                $cart,
                $user,
                $validated,
                $request,
            );
        } catch (CheckoutException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('customer.orders.show', $result['order']->order_number)
            ->with('order_success', [
                'order_number'     => $result['order']->order_number,
                'is_new_user'      => false,
                'password_emailed' => false,
            ]);
    }
}
