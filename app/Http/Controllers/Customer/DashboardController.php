<?php

namespace App\Http\Controllers\Customer;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Espace client : dashboard, liste des commandes, détail.
 * Route-model binding sur Order par order_number (défini dans le modèle).
 */
class DashboardController extends Controller
{
    /**
     * GET /mon-compte
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $activeOrders = $user->orders()
            ->active()
            ->latest()
            ->take(5)
            ->get();

        $recentOrders = $user->orders()
            ->latest()
            ->take(10)
            ->get();

        $stats = [
            'total_orders'     => $user->orders()->count(),
            'active_orders'    => $user->orders()->active()->count(),
            'delivered_orders' => $user->orders()->where('status', OrderStatus::Delivered)->count(),
        ];

        // Si le profil n'est pas complété, on le signale au layout
        // (le layout affichera une bannière "complétez vos informations").
        return view('customer.dashboard', [
            'user'                   => $user,
            'activeOrders'           => $activeOrders,
            'recentOrders'           => $recentOrders,
            'stats'                  => $stats,
            'needsProfileCompletion' => $user->needsProfileCompletion(),
        ]);
    }

    /**
     * GET /mes-commandes — liste paginée des commandes.
     */
    public function orders(Request $request): View
    {
        $orders = $request->user()
            ->orders()
            ->with('items')
            ->latest()
            ->paginate(15);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * GET /mes-commandes/{order:order_number}
     * Affiche le détail d'une commande et sa timeline de statuts.
     */
    public function showOrder(Request $request, Order $order): View
    {
        // Sécurité : une commande ne se voit que par son propriétaire.
        abort_unless($order->user_id === $request->user()->id, 403);

        $order->load(['items.product', 'statusHistories.changedBy']);

        return view('customer.orders.show', compact('order'));
    }
}
