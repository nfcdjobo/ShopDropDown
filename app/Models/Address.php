<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'label',
        'full_name',
        'phone',
        'country',
        'city',
        'commune',
        'address_line_1',
        'address_line_2',
        'postal_code',
        'delivery_notes',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        // La première adresse créée devient automatiquement celle par défaut.
        static::creating(function (Address $address) {
            $hasAny = static::where('user_id', $address->user_id)->exists();
            if (!$hasAny) {
                $address->is_default = true;
            }
        });

        // Si on marque une adresse comme "par défaut", les autres perdent ce flag.
        static::saving(function (Address $address) {
            if ($address->is_default && $address->user_id) {
                static::where('user_id', $address->user_id)
                    ->when($address->exists, fn ($q) => $q->where('id', '!=', $address->id))
                    ->update(['is_default' => false]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address_line_1,
            $this->address_line_2,
            $this->commune,
            $this->city,
            $this->country,
        ])->filter()->implode(', ');
    }
}
