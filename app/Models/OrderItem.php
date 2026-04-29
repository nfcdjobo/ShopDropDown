<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'product_sku',
        'product_image',
        'product_options',
        'quantity',
        'unit_price',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'integer',
            'unit_price'      => 'decimal:2',
            'total'           => 'decimal:2',
            'product_options' => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Recalcule systématiquement le total à partir de quantity × unit_price
        // pour éviter toute incohérence si un appelant oublie de le faire.
        static::saving(function (OrderItem $item) {
            $item->total = $item->quantity * (float) $item->unit_price;
        });
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Construit le payload d'un OrderItem à partir d'un CartItem (snapshot du
     * produit au moment de la commande). À utiliser dans le service de checkout.
     */
    public static function fromCartItem(CartItem $cartItem): array
    {
        $product = $cartItem->product;

        return [
            'product_id'      => $product?->id,
            'product_name'    => $product?->name ?? 'Produit supprimé',
            'product_sku'     => $product?->sku,
            'product_image'   => $product?->featured_image,
            'product_options' => $cartItem->options,
            'quantity'        => $cartItem->quantity,
            'unit_price'      => $cartItem->unit_price,
            'total'           => $cartItem->quantity * (float) $cartItem->unit_price,
        ];
    }
}
