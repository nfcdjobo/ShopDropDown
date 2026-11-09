<?php

namespace App\Models;

use App\Services\ProductMediaStorage;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductImage extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'product_id',
        'path',
        'disk',
        'alt_text',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // Le fichier physique n'est supprimé qu'à la suppression DÉFINITIVE de la ligne :
        // une suppression douce reste restaurable avec son image.
        static::deleting(function (ProductImage $image) {
            if ($image->isForceDeleting()) {
                app(ProductMediaStorage::class)->delete($image->disk, $image->path);
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute(): ?string
    {
        return app(ProductMediaStorage::class)->url($this->disk, $this->path);
    }
}
