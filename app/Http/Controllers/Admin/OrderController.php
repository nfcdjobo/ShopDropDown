<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderStatusChangedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestion des commandes côté admin.
 *
 * Les commandes ne sont jamais créées manuellement depuis l'admin : elles
 * proviennent du checkout. On désactive donc create/store.
 *
 * update() gère les transitions de statut (confirm, ship, deliver, cancel),
 * l'attribution à un agent, et les notes internes, via un champ `action`.
 */
class OrderController extends Controller
{
    /**
     * GET /admin/orders — liste paginée avec filtres.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status'       => ['nullable', 'string'],
            'assigned_to'  => ['nullable', 'string'], // 'me', 'unassigned', ou UUID
            'q'            => ['nullable', 'string', 'max:100'],
            'from'         => ['nullable', 'date'],
            'to'           => ['nullable', 'date'],
        ]);

        $query = Order::query()
            ->with(['user', 'assignedTo', 'items'])
            ->latest();

        if ($status = $validated['status'] ?? null) {
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'finalized') {
                $query->finalized();
            } elseif ($enum = OrderStatus::tryFrom($status)) {
                $query->status($enum);
            }
        }

        if ($assigned = $validated['assigned_to'] ?? null) {
            match ($assigned) {
                'me'         => $query->where('assigned_to', $request->user()->id),
                'unassigned' => $query->unassigned(),
                default      => $query->where('assigned_to', $assigned),
            };
        }

        if ($q = $validated['q'] ?? null) {
            $query->search($q);
        }

        if ($from = $validated['from'] ?? null) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $validated['to'] ?? null) {
            $query->whereDate('created_at', '<=', $to);
        }

        $orders = $query->paginate(20)->withQueryString();

        // Comptage par statut pour les onglets du header
        $counts = [
            'all'        => Order::count(),
            'pending'    => Order::where('status', OrderStatus::Pending)->count(),
            'confirmed'  => Order::where('status', OrderStatus::Confirmed)->count(),
            'processing' => Order::where('status', OrderStatus::Processing)->count(),
            'shipped'    => Order::where('status', OrderStatus::Shipped)->count(),
            'delivered'  => Order::where('status', OrderStatus::Delivered)->count(),
            'cancelled'  => Order::where('status', OrderStatus::Cancelled)->count(),
        ];


        return view('admin.orders.index', [
            'orders'  => $orders,
            'filters' => $validated,
            'counts'  => $counts,
            'admins'  => User::admins()->active()->orderBy('first_name')->get(),
        ]);
    }

    /**
     * Create/store désactivés : on ne crée pas de commande depuis l'admin.
     */
    public function create(): never { abort(404); }
    public function store(Request $request): never { abort(404); }

    /**
     * GET /admin/orders/{order} — détail complet avec timeline.
     */
    public function show(Order $order): View
    {
        $order->load([
            'items.product',
            'statusHistories.changedBy',
            'user',
            'assignedTo',
            'address',
        ]);

        return view('admin.orders.show', [
            'order'           => $order,
            'admins'          => User::admins()->active()->orderBy('first_name')->get(),
            'allowedStatuses' => $this->allowedNextStatuses($order),
        ]);
    }

    /**
     * GET /admin/orders/{order}/edit — formulaire d'édition (notes, attribution).
     * Note : on préfère traiter ça dans show(), mais on expose la route par
     * cohérence avec Route::resource.
     */
    public function edit(Order $order): RedirectResponse
    {
        return redirect()->route('admin.orders.show', $order);
    }

    /**
     * PATCH /admin/orders/{order} — dispatch selon `action`.
     *
     * Actions supportées :
     *  - confirm | process | ship | deliver | cancel : transition de statut
     *  - assign     : attribuer la commande à un admin (assigned_to)
     *  - notes      : mettre à jour admin_notes
     */
    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {

        $data   = $request->validated();
        $action = $data['action'];
        $admin  = $request->user();
        $note   = $data['note'] ?? null;

        
        $ok = match ($action) {
            'confirm' => $order->confirm($admin, $note),
            'process' => $order->markAsProcessing($admin, $note),
            'ship'    => $order->markAsShipped($admin, $note),
            'deliver' => $order->markAsDelivered($admin, $note),
            'cancel'  => $order->cancel($admin, $data['cancellation_reason'] ?? $note),
            'assign'  => $this->handleAssign($order, $data),
            'notes'   => $order->update(['admin_notes' => $data['admin_notes'] ?? null]),
        };


        if ($ok === false) {
            return back()->with('error', 'Transition non autorisée depuis le statut actuel.');
        }

        // Notifier le client à chaque transition de statut (pas pour assign/notes).
        if (in_array($action, ['confirm', 'process', 'ship', 'deliver', 'cancel'], true)
            && $order->user) {
            $order->user->notify(new OrderStatusChangedNotification(
                $order->fresh(),
                $order->getOriginal('status') instanceof OrderStatus
                    ? $order->getOriginal('status')
                    : OrderStatus::tryFrom((string) $order->getOriginal('status')),
            ));
        }

        return back()->with('success', $this->successMessage($action));
    }


    /**
     * DELETE /admin/orders/{order} — soft delete.
     * Réservé au super_admin (à protéger via policy si besoin).
     */
    public function destroy(Request $request, Order $order): RedirectResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', "Commande {$order->order_number} archivée.");
    }

    /* --------------------------------------------------------------------
     | Helpers internes
     | --------------------------------------------------------------------*/

    private function handleAssign(Order $order, array $data): bool
    {
        $adminId = $data['assigned_to'] ?? null;

        if (!$adminId) {
            $order->update(['assigned_to' => null]);
            return true;
        }

        $admin = User::admins()->active()->whereKey($adminId)->first();
        if (!$admin) {
            return false;
        }

        $order->assignTo($admin);
        return true;
    }

    /** Les statuts vers lesquels on peut transitionner depuis l'état courant. */
    private function allowedNextStatuses(Order $order): array
    {
        return collect(OrderStatus::cases())
            ->filter(fn (OrderStatus $s) => $order->status->canTransitionTo($s))
            ->values()
            ->all();
    }

    private function successMessage(string $action): string
    {
        return match ($action) {
            'confirm' => 'Commande confirmée.',
            'process' => 'Commande passée en préparation.',
            'ship'    => 'Commande marquée comme expédiée.',
            'deliver' => 'Commande marquée comme livrée.',
            'cancel'  => 'Commande annulée et stock remis.',
            'assign'  => 'Commande attribuée.',
            'notes'   => 'Notes mises à jour.',
        };
    }
}
