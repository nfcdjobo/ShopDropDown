<?php

namespace App\Models;

use App\Services\ProductMediaStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /*
    |--------------------------------------------------------------------------
    | Constantes
    |--------------------------------------------------------------------------
    */

    /** Limites métier (contenu marketing curaté par l'admin) */
    public const MAX_REVIEWS = 3;
    public const MAX_FAQS    = 3;

    /** Types de produit */
    public const TYPE_SIMPLE  = 'simple';
    public const TYPE_PACKAGE = 'package';
    public const TYPE_ITEM    = 'item';

    /** Rôles dans un package */
    public const ROLE_PRINCIPAL = 'principal';
    public const ROLE_BONUS     = 'bonus';

    /*
    |--------------------------------------------------------------------------
    | Schéma
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'category_id',
        'type',
        'parent_id',
        'package_role',
        'package_sort_order',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'featured_image',
        'featured_image_disk',
        'video_url',
        'price',
        'compare_price',
        'cost_price',
        'stock',
        'track_stock',
        'low_stock_threshold',
        'availability_min_days',
        'availability_max_days',
        'availability_label',
        'is_active',
        'is_featured',
        'attributes',
        'meta_title',
        'meta_description',
        'views_count',
        'orders_count',
        'rating_value',
        'rating_count',
        'offer_duration_hours'
    ];

    protected function casts(): array
    {
        return [
            'price'                 => 'decimal:2',
            'compare_price'         => 'decimal:2',
            'cost_price'            => 'decimal:2',
            'stock'                 => 'integer',
            'low_stock_threshold'   => 'integer',
            'track_stock'           => 'boolean',
            'is_active'             => 'boolean',
            'is_featured'           => 'boolean',
            'attributes'            => 'array',
            'views_count'           => 'integer',
            'orders_count'          => 'integer',
            'package_sort_order'    => 'integer',
            'rating_value'          => 'decimal:1',
            'rating_count'          => 'integer',
            'availability_min_days' => 'integer',
            'availability_max_days' => 'integer',
             'offer_duration_hours' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Hooks & génération
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->type)) {
                $product->type = self::TYPE_SIMPLE;
            }
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
            if (empty($product->sku)) {
                $product->sku = static::generateSku();
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'produit';
        $slug = $base;
        $i    = 1;
        while (static::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->withTrashed()
            ->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }
        return $slug;
    }

    public static function generateSku(): string
    {
        do {
            $sku = 'SD-' . strtoupper(Str::random(8));
        } while (static::where('sku', $sku)->withTrashed()->exists());
        return $sku;
    }

    /*
    |--------------------------------------------------------------------------
    | Relations
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /** Preuves visuelles (captures d'écran de résultats / témoignages) */
    public function proofs(): HasMany
    {
        return $this->hasMany(ProductProof::class)->orderBy('sort_order');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Package parent (uniquement renseigné si type = item) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'parent_id');
    }

    /** Tous les sous-produits d'un package, ordonnés */
    public function items(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id')
            ->orderBy('package_sort_order');
    }

    /** Le sous-produit principal du package */
    public function principalItem(): HasOne
    {
        return $this->hasOne(Product::class, 'parent_id')
            ->where('package_role', self::ROLE_PRINCIPAL)
            ->orderBy('package_sort_order');
    }

    /** Les bonus du package */
    public function bonusItems(): HasMany
    {
        return $this->hasMany(Product::class, 'parent_id')
            ->where('package_role', self::ROLE_BONUS)
            ->orderBy('package_sort_order');
    }

    /** Avis curatés actifs (limite MAX_REVIEWS appliquée à la création) */
    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    /** FAQs actives (limite MAX_FAQS appliquée à la création) */
    public function faqs(): HasMany
    {
        return $this->hasMany(ProductFaq::class)
            ->where('is_active', true)
            ->orderBy('sort_order');
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

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where(function (Builder $q) {
            $q->where('track_stock', false)->orWhere('stock', '>', 0);
        });
    }

    public function scopeOnSale(Builder $query): Builder
    {
        return $query->whereColumn('compare_price', '>', 'price')
                     ->whereNotNull('compare_price');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        $like = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $term) . '%';
        return $query->where(function (Builder $q) use ($like) {
            $q->where('name', 'like', $like)
              ->orWhere('sku', 'like', $like)
              ->orWhere('short_description', 'like', $like);
        });
    }

    /** Produits vendables seuls : exclut les sous-produits de package */
    public function scopeSellable(Builder $query): Builder
    {
        return $query->whereIn('type', [self::TYPE_SIMPLE, self::TYPE_PACKAGE]);
    }

    public function scopeSimples(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SIMPLE);
    }

    public function scopePackages(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PACKAGE);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers de type
    |--------------------------------------------------------------------------
    */

    public function isSimple(): bool  { return $this->type === self::TYPE_SIMPLE; }
    public function isPackage(): bool { return $this->type === self::TYPE_PACKAGE; }
    public function isItem(): bool    { return $this->type === self::TYPE_ITEM; }
    public function isSellable(): bool { return !$this->isItem(); }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Prix & promotion
    |--------------------------------------------------------------------------
    */

    public function getOnSaleAttribute(): bool
    {
        return $this->compare_price !== null
            && $this->price !== null
            && $this->compare_price > $this->price;
    }

    public function getDiscountPercentAttribute(): ?int
    {
        if (!$this->on_sale) {
            return null;
        }
        return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
    }

    public function getFormattedPriceAttribute(): string
    {
        if ($this->price === null) {
            return '—';
        }
        return number_format((float) $this->price, 0, ',', ' ') . ' FCFA';
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Stock
    |--------------------------------------------------------------------------
    */

    public function getIsInStockAttribute(): bool
    {
        if ($this->isItem()) {
            return false; // un sous-produit n'est jamais commandé seul
        }
        return !$this->track_stock || $this->stock > 0;
    }

    public function getIsLowStockAttribute(): bool
    {
        return !$this->isItem()
            && $this->track_stock
            && $this->stock > 0
            && $this->stock <= $this->low_stock_threshold;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Image
    |--------------------------------------------------------------------------
    */

    // public function getFeaturedImageUrlAttribute(): ?string
    // {
    //     return $this->featured_image
    //         ? Storage::url($this->featured_image)
    //         : null;
    // }

    public function getFeaturedImageUrlAttribute(): ?string
{
    return app(ProductMediaStorage::class)->url($this->featured_image_disk, $this->featured_image);
}

    /*
    |--------------------------------------------------------------------------
    | Accessors — Offre limitée (compteur d'urgence)
    |--------------------------------------------------------------------------
    */

    public function getHasOfferAttribute(): bool
    {
        // Une offre n'est affichée que s'il y a aussi une promotion réelle —
        // sinon le compteur n'a rien à urger.
        return $this->offer_duration_hours !== null
            && $this->offer_duration_hours > 0
            && $this->on_sale;
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Évaluation (étoiles)
    |--------------------------------------------------------------------------
    */

    public function getHasRatingAttribute(): bool
    {
        return $this->rating_value !== null && $this->rating_count > 0;
    }

    public function getFormattedRatingAttribute(): string
    {
        return $this->rating_value !== null
            ? number_format((float) $this->rating_value, 1, ',', '')
            : '—';
    }

    /**
     * Renvoie [pleines, demie, vides] pour l'affichage des étoiles.
     * Ex : 4.7 → [4, 1, 0] ; 3.0 → [3, 0, 2]
     */
    public function getStarsBreakdownAttribute(): array
    {
        if ($this->rating_value === null) {
            return [0, 0, 5];
        }

        $value = max(0, min(5, (float) $this->rating_value));
        $full  = (int) floor($value);
        $half  = ($value - $full) >= 0.5 ? 1 : 0;
        $empty = 5 - $full - $half;

        return [$full, $half, $empty];
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Preuves visuelles
    |--------------------------------------------------------------------------
    */

    public function getHasProofsAttribute(): bool
    {
        return $this->proofs()->exists();
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors — Délai de disponibilité
    |--------------------------------------------------------------------------
    */

    /**
     * Texte affichable du délai annoncé au client.
     *
     * Ordre de priorité :
     *   1. availability_label (override marketing libre)
     *   2. intervalle min/max  → « Livraison sous X à Y jours »
     *   3. valeur unique       → « Livraison sous X jour(s) »
     *   4. null                → rien à afficher
     *
     * Pour un 'item', on délègue au package parent si disponible.
     */
    public function getAvailabilityTextAttribute(): ?string
    {
        if ($this->isItem() && $this->parent) {
            return $this->parent->availability_text;
        }

        if (!empty($this->availability_label)) {
            return $this->availability_label;
        }

        $min = $this->availability_min_days;
        $max = $this->availability_max_days;

        // Aucun délai renseigné
        if ($min === null && $max === null) {
            return null;
        }

        // Plage : min < max
        if ($min !== null && $max !== null && $min !== $max) {
            return "Livraison sous {$min} à {$max} jours";
        }

        // Valeur unique (min = max, ou un seul des deux renseigné)
        $days = $min ?? $max;

        if ($days === 0) {
            return 'Disponible immédiatement';
        }

        if ($days === 1) {
            return 'Livraison sous 1 jour';
        }

        return "Livraison sous {$days} jours";
    }

    public function getHasAvailabilityAttribute(): bool
    {
        return $this->availability_text !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Logique métier
    |--------------------------------------------------------------------------
    */

    public function canBeOrdered(int $quantity = 1): bool
    {
        if ($this->isItem() || !$this->is_active) {
            return false;
        }
        if (!$this->track_stock) {
            return true;
        }
        return $this->stock >= $quantity;
    }

    public function decrementStock(int $quantity): void
    {
        if (!$this->isItem() && $this->track_stock) {
            $this->decrement('stock', $quantity);
        }
    }

    public function incrementStock(int $quantity): void
    {
        if (!$this->isItem() && $this->track_stock) {
            $this->increment('stock', $quantity);
        }
    }

    public function incrementViews(): void
    {
        $this->incrementQuietly('views_count');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }




    /**
     * Classe les produits disponibles avant les ruptures.
     * Miroir exact de scopeInStock() : disponible = stock non suivi OU stock > 0.
     */
    public function scopeOrderInStockFirst(Builder $query): Builder
    {
        return $query->orderByRaw('CASE WHEN track_stock = ? OR stock > 0 THEN 0 ELSE 1 END', [false]);
    }

    /** Vrai si le produit est en rupture (miroir de scopeInStock). */
    public function getIsSoldOutAttribute(): bool
    {
        return $this->track_stock && ! ($this->stock > 0);
    }
}
