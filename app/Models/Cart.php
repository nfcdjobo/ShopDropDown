<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'session_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
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

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeForGuest(Builder $query, string $sessionId): Builder
    {
        return $query->whereNull('user_id')->where('session_id', $sessionId);
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /*
    |--------------------------------------------------------------------------
    | Totaux (calculs à la volée depuis les items)
    |--------------------------------------------------------------------------
    */

    public function getSubtotalAttribute(): float
    {
        return (float) $this->items->sum(fn (CartItem $item) => $item->quantity * (float) $item->unit_price);
    }

    public function getItemsCountAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getIsEmptyAttribute(): bool
    {
        return $this->items->isEmpty();
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', ' ') . ' FCFA';
    }

    /*
    |--------------------------------------------------------------------------
    | Opérations panier
    |--------------------------------------------------------------------------
    */

    /**
     * Ajoute un produit au panier, ou incrémente la quantité s'il y est déjà.
     * Le prix est remis au prix courant du produit (protection contre un prix obsolète).
     */
    public function addProduct(Product $product, int $quantity = 1): CartItem
    {
        return DB::transaction(function () use ($product, $quantity) {
            /** @var CartItem|null $item */
            $item = $this->items()->where('product_id', $product->id)->lockForUpdate()->first();

            if ($item) {
                $item->quantity  += $quantity;
                $item->unit_price = $product->price;
                $item->save();
                return $item;
            }

            return $this->items()->create([
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'unit_price' => $product->price,
            ]);
        });
    }

    public function updateQuantity(Product $product, int $quantity): ?CartItem
    {
        $item = $this->items()->where('product_id', $product->id)->first();
        if (!$item) {
            return null;
        }

        if ($quantity <= 0) {
            $item->delete();
            return null;
        }

        $item->update(['quantity' => $quantity]);
        return $item;
    }

    public function removeProduct(Product $product): void
    {
        $this->items()->where('product_id', $product->id)->delete();
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    /**
     * Fusionne un panier invité dans ce panier (appelé après login/inscription).
     * Les quantités s'additionnent pour les produits déjà présents.
     */
    public function mergeFrom(Cart $guestCart): void
    {
        if ($guestCart->is($this)) {
            return;
        }

        DB::transaction(function () use ($guestCart) {
            foreach ($guestCart->items as $guestItem) {
                $existing = $this->items()->where('product_id', $guestItem->product_id)->first();

                if ($existing) {
                    $existing->increment('quantity', $guestItem->quantity);
                } else {
                    $this->items()->create([
                        'product_id' => $guestItem->product_id,
                        'quantity'   => $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                        'options'    => $guestItem->options,
                    ]);
                }
            }
            $guestCart->delete();
        });
    }
}
