<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PushSubscription extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'subscribable_id',
        'subscribable_type',
        'endpoint',
        'public_key',
        'auth_token',
        'content_encoding',
    ];

    public function subscribable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function findByEndpoint(string $endpoint): ?self
    {
        return static::where('endpoint', $endpoint)->first();
    }
}
