<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Question fréquente curatée par l'administrateur, attachée à un produit
 * 'simple' ou 'package'. Limite : Product::MAX_FAQS (3) par produit,
 * appliquée côté Form Request.
 */
class ProductFaq extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'product_faqs';

    protected $fillable = [
        'product_id',
        'question',
        'answer',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active'  => 'boolean',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /*
    |--------------------------------------------------------------------------
    | Hooks
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (ProductFaq $faq) {
            if ($faq->is_active === null) {
                $faq->is_active = true;
            }

            // Auto-incrémente sort_order pour les FAQs créées sans valeur explicite,
            // ce qui garantit un ordre d'affichage stable et prévisible.
            if ($faq->sort_order === null && $faq->product_id) {
                $faq->sort_order = (int) static::where('product_id', $faq->product_id)
                    ->max('sort_order') + 1;
            }
        });
    }
}
