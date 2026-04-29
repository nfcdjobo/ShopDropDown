<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;


    protected $fillable = [
        'order_number',
        'user_id',
        'address_id',
        'shipping_full_name',
        'shipping_phone',
        'shipping_email',
        'shipping_country',
        'shipping_city',
        'shipping_address',
        'shipping_notes',
        'subtotal',
        'shipping_fee',
        'discount',
        'tax',
        'total',
        'currency',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'paid_at',
        'admin_notes',
        'assigned_to',
        'confirmed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'       => 'decimal:2',
            'shipping_fee'   => 'decimal:2',
            'discount'       => 'decimal:2',
            'tax'            => 'decimal:2',
            'total'          => 'decimal:2',
            'status'         => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'payment_method' => PaymentMethod::class,
            'paid_at'        => 'datetime',
            'confirmed_at'   => 'datetime',
            'processing_at'  => 'datetime',
            'shipped_at'     => 'datetime',
            'delivered_at'   => 'datetime',
            'cancelled_at'   => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Génère un numéro de commande séquentiel au format SD-YYYY-00001.
     * Utilise un verrou de table pour éviter les collisions en concurrence.
     */
    public static function generateOrderNumber(): string
    {
        return DB::transaction(function () {
            $year = date('Y');
            $last = static::where('order_number', 'like', "SD-{$year}-%")
                ->lockForUpdate()
                ->orderByDesc('id')
                ->value('order_number');

            $next = $last ? ((int) substr($last, -5)) + 1 : 1;
            return sprintf('SD-%s-%05d', $year, $next);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->latest();
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeStatus(Builder $query, OrderStatus $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', OrderStatus::Pending);
    }

    /** Commandes en cours (non finalisées) — vue principale du back-office. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            OrderStatus::Pending,
            OrderStatus::Confirmed,
            OrderStatus::Processing,
            OrderStatus::Shipped,
        ]);
    }

    public function scopeFinalized(Builder $query): Builder
    {
        return $query->whereIn('status', [
            OrderStatus::Delivered,
            OrderStatus::Cancelled,
            OrderStatus::Refunded,
        ]);
    }

    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        return $query->where(function (Builder $q) use ($like) {
            $q->where('order_number', 'like', $like)
              ->orWhere('shipping_full_name', 'like', $like)
              ->orWhere('shipping_phone', 'like', $like)
              ->orWhere('shipping_email', 'like', $like);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedTotalAttribute(): string
    {
        return number_format((float) $this->total, 0, ',', ' ') . ' ' . $this->currency;
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    /*
    |--------------------------------------------------------------------------
    | Machine à états — transitions de statut
    |--------------------------------------------------------------------------
    */

    /**
     * Transition générique. Vérifie que le changement est autorisé, horodate
     * l'étape, journalise l'historique, puis persiste. Retourne false si la
     * transition n'est pas valide — le contrôleur peut alors afficher un 422.
     */
    public function transitionTo(OrderStatus $newStatus, ?User $user = null, ?string $note = null): bool
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            return false;
        }

        return DB::transaction(function () use ($newStatus, $user, $note) {
            $oldStatus = $this->status;
            $this->status = $newStatus;

            // Horodatage automatique de l'étape clé.
            match ($newStatus) {
                OrderStatus::Confirmed  => $this->confirmed_at  = now(),
                OrderStatus::Processing => $this->processing_at = now(),
                OrderStatus::Shipped    => $this->shipped_at    = now(),
                OrderStatus::Delivered  => $this->delivered_at  = now(),
                OrderStatus::Cancelled  => $this->cancelled_at  = now(),
                default                 => null,
            };

            $this->save();

            $this->statusHistories()->create([
                'from_status' => $oldStatus->value,
                'to_status'   => $newStatus->value,
                'note'        => $note,
                'changed_by'  => $user?->id,
            ]);

            return true;
        });
    }

    public function confirm(?User $user = null, ?string $note = null): bool
    {
        return $this->transitionTo(OrderStatus::Confirmed, $user, $note);
    }

    public function markAsProcessing(?User $user = null, ?string $note = null): bool
    {
        return $this->transitionTo(OrderStatus::Processing, $user, $note);
    }

    public function markAsShipped(?User $user = null, ?string $note = null): bool
    {
        return $this->transitionTo(OrderStatus::Shipped, $user, $note);
    }

    /**
     * Livraison : transition + règlement automatique si paiement à la livraison.
     */
    public function markAsDelivered(?User $user = null, ?string $note = null): bool
    {
        $ok = $this->transitionTo(OrderStatus::Delivered, $user, $note);

        if ($ok
            && $this->payment_method === PaymentMethod::CashOnDelivery
            && $this->payment_status !== PaymentStatus::Paid) {
            $this->update([
                'payment_status' => PaymentStatus::Paid,
                'paid_at'        => now(),
            ]);
        }

        return $ok;
    }

    /**
     * Annule la commande et réinjecte le stock.
     */
    public function cancel(?User $user = null, ?string $reason = null): bool
    {
        return DB::transaction(function () use ($user, $reason) {
            if (!$this->transitionTo(OrderStatus::Cancelled, $user, $reason)) {
                return false;
            }

            $this->update(['cancellation_reason' => $reason]);

            // Remise en stock pour chaque ligne liée à un produit encore existant.
            $this->loadMissing('items.product');
            foreach ($this->items as $item) {
                $item->product?->incrementStock($item->quantity);
            }

            return true;
        });
    }

    public function assignTo(User $user): void
    {
        $this->update(['assigned_to' => $user->id]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isPaid(): bool
    {
        return $this->payment_status === PaymentStatus::Paid;
    }

    public function canBeCancelled(): bool
    {
        return $this->status->canTransitionTo(OrderStatus::Cancelled);
    }

    /** Route-model binding par numéro de commande. */
    public function getRouteKeyName(): string
    {
        return 'order_number';
    }
}
