<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Avis client curaté par l'administrateur.
 *
 * Ce ne sont pas des UGC : c'est l'admin qui saisit l'identité fictive
 * et le contenu de l'avis pour rassurer les visiteurs sur la fiche produit.
 * Limite : Product::MAX_REVIEWS (3) par produit, appliquée côté Form Request.
 */
class ProductReview extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'product_id',
        'author_name',
        'author_location',
        'author_avatar',
        'rating',
        'title',
        'comment',
        'reviewed_on',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'rating'      => 'integer',
            'sort_order'  => 'integer',
            'is_active'   => 'boolean',
            'reviewed_on' => 'date',
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
        return $query->orderBy('sort_order')->orderByDesc('reviewed_on');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    /**
     * URL publique de l'avatar (si fourni).
     */
    public function getAuthorAvatarUrlAttribute(): ?string
    {
        return $this->author_avatar
            ? Storage::disk('public')->url($this->author_avatar)
            : null;
    }

    /**
     * Initiales de l'auteur, pour fallback quand il n'y a pas d'avatar.
     * Ex : « Aïcha K. » → « AK »
     */
    public function getAuthorInitialsAttribute(): string
    {
        $name = trim((string) $this->author_name);
        if ($name === '') {
            return '?';
        }

        $parts = preg_split('/\s+/', $name) ?: [];
        $first = Str::of($parts[0] ?? '')->substr(0, 1)->upper()->toString();
        $last  = Str::of($parts[1] ?? '')->substr(0, 1)->upper()->toString();
        $init  = $first . $last;

        return $init !== '' ? $init : Str::upper(Str::substr($name, 0, 1));
    }

    /**
     * Date d'affichage formatée en français court.
     * Ex : « 14 mars 2025 ». Retombe sur created_at si reviewed_on est vide.
     */
    public function getFormattedReviewedOnAttribute(): string
    {
        $date = $this->reviewed_on ?? $this->created_at;

        return $date
            ? $date->locale('fr')->isoFormat('D MMMM YYYY')
            : '';
    }

    /**
     * Nombre d'étoiles pleines (1-5), borné pour l'affichage.
     */
    public function getStarsAttribute(): int
    {
        return max(0, min(5, (int) $this->rating));
    }

    /*
    |--------------------------------------------------------------------------
    | Hooks
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        // Si reviewed_on est vide à la création, on prend la date du jour.
        // Cela donne un comportement par défaut sain quand l'admin oublie le champ.
        static::creating(function (ProductReview $review) {
            if (empty($review->reviewed_on)) {
                $review->reviewed_on = now()->toDateString();
            }

            if ($review->is_active === null) {
                $review->is_active = true;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Suppression — nettoyage de l'avatar
    |--------------------------------------------------------------------------
    */

    protected static function boot()
    {
        parent::boot();

        static::deleting(function (ProductReview $review) {
            if ($review->author_avatar) {
                Storage::disk('public')->delete($review->author_avatar);
            }
        });
    }
}
