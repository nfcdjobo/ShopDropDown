<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Tableau de bord administrateur : KPIs, commandes récentes, alertes stock.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // KPIs principaux
        $stats = [
            'orders_today'      => Order::whereDate('created_at', $today)->count(),
            'orders_pending'    => Order::where('status', OrderStatus::Pending)->count(),
            'orders_active'     => Order::active()->count(),
            'orders_month'      => Order::where('created_at', '>=', $monthStart)->count(),
            'revenue_month'     => (float) Order::where('created_at', '>=', $monthStart)
                ->where('payment_status', PaymentStatus::Paid)
                ->sum('total'),
            'revenue_pending'   => (float) Order::active()->sum('total'),
            'customers_total'   => User::customers()->count(),
            'customers_new_7d'  => User::customers()
                ->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        // Commandes récentes pour la vue "activité"
        $recentOrders = Order::with(['user', 'items'])
            ->latest()
            ->take(10)
            ->get();

        // Commandes à traiter (non attribuées en priorité)
        $pendingOrders = Order::active()
            ->unassigned()
            ->with('user')
            ->latest()
            ->take(5)
            ->get();

        // Alertes stock faible (pour réapprovisionnement)
        $lowStockProducts = Product::active()
            ->where('track_stock', true)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock')
            ->take(8)
            ->get();

        // Série pour le graphique des 14 derniers jours (commandes / jour)
        $chartData = Order::query()
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        return view('admin.dashboard', [
            'stats'            => $stats,
            'recentOrders'     => $recentOrders,
            'pendingOrders'    => $pendingOrders,
            'lowStockProducts' => $lowStockProducts,
            'chartData'        => $chartData,
        ]);
    }
}
