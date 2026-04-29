<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des clients côté admin.
 *
 * index()       → liste paginée avec recherche, filtres, tri
 * show()        → fiche client : stats d'activité + commandes récentes
 * toggleActive() → active/désactive le compte
 * resetPassword() → génère un nouveau MDP provisoire (affiché dans le flash)
 */
class UserController extends Controller
{
    /**
     * GET /admin/users
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q'      => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive,new'],
            'sort'   => ['nullable', 'in:latest,orders,spent'],
        ]);

        $query = User::customers()
            ->withCount('orders')
            ->withSum('orders as total_spent', 'total')
            ->latest();

        if ($q = $validated['q'] ?? null) {
            $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $q) . '%';
            $query->where(function ($sub) use ($like) {
                $sub->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        match ($validated['status'] ?? null) {
            'active'   => $query->where('is_active', true),
            'inactive' => $query->where('is_active', false),
            'new'      => $query->where('created_at', '>=', now()->subDays(7)),
            default    => null,
        };

        match ($validated['sort'] ?? 'latest') {
            'orders' => $query->orderByDesc('orders_count'),
            'spent'  => $query->orderByDesc('total_spent'),
            default  => $query->latest(),
        };

        $customers = $query->paginate(25)->withQueryString();

        $counts = [
            'all'      => User::customers()->count(),

            'active'   => User::customers()->where('is_active', true)->count(),
            'inactive' => User::customers()->where('is_active', false)->count(),
            'new'      => User::customers()->where('created_at', '>=', now()->subDays(7))->count(),
        ];

        return view('admin.users.index', [
            'customers' => $customers,
            'filters'   => $validated,
            'counts'    => $counts,
        ]);
    }




    /**
     * GET /admin/users/{user}
     */
    public function show(User $user): View
    {
        abort_unless($user->isCustomer(), 404);

        $user->load(['orders' => fn ($q) => $q->with('items')->latest(), 'addresses']);

        $orders = $user->orders;

        $stats = [
            'total_orders'     => $orders->count(),
            'active_orders'    => $orders->whereIn('status', [
                OrderStatus::Pending,
                OrderStatus::Confirmed,
                OrderStatus::Processing,
                OrderStatus::Shipped,
            ])->count(),
            'delivered_orders' => $orders->where('status', OrderStatus::Delivered)->count(),
            'cancelled_orders' => $orders->where('status', OrderStatus::Cancelled)->count(),
            'total_spent'      => (float) $orders->sum('total'),
            'avg_order_value'  => $orders->count() > 0 ? (float) $orders->avg('total') : 0.0,
            'last_order_at'    => $orders->first()?->created_at, // déjà triées par latest
            'by_status'        => $orders
                ->groupBy(fn ($o) => $o->status->value)
                ->map->count(),
        ];

        return view('admin.users.show', [
            'customer'     => $user,
            'stats'        => $stats,
            'recentOrders' => $orders->take(5),
        ]);
    }

    /**
     * POST /admin/users/{user}/toggle-active
     */
    public function toggleActive(User $user): RedirectResponse
    {
        abort_unless($user->isCustomer(), 404);

        $user->update(['is_active' => !$user->is_active]);

        return back()->with(
            'success',
            'Compte de ' . $user->full_name . ' ' . ($user->is_active ? 'activé' : 'désactivé') . '.'
        );
    }

    /**
     * POST /admin/users/{user}/reset-password
     * Génère un nouveau mot de passe provisoire et l'affiche en flash.
     * L'admin le communique au client par téléphone ou email manuel.
     *
     * TODO : créer une ResetPasswordByAdminNotification pour l'envoyer par email
     *        automatiquement quand le flux email sera validé.
     */
    public function resetPassword(User $user): RedirectResponse
    {
        abort_unless($user->isCustomer(), 404);

        $plain = User::generateDefaultPassword();

        $user->forceFill([
            'password'             => $plain,   // cast 'hashed' → bcrypt auto
            'must_change_password' => true,
        ])->save();

        return back()->with(
            'password_reset',
            // Flash spécial : affiché dans une modale/bannière dédiée dans la vue show
            ['name' => $user->full_name, 'email' => $user->email, 'password' => $plain]
        );
    }
}
