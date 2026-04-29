<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderStatusHistory extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'note',
        'changed_by',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /** Accesseurs pratiques pour récupérer les enums typés. */
    public function getFromStatusEnumAttribute(): ?OrderStatus
    {
        return $this->from_status ? OrderStatus::tryFrom($this->from_status) : null;
    }

    public function getToStatusEnumAttribute(): OrderStatus
    {
        return OrderStatus::from($this->to_status);
    }

    public function getLabelAttribute(): string
    {
        $to = $this->to_status_enum->label();
        return $this->from_status_enum
            ? "{$this->from_status_enum->label()} → {$to}"
            : $to;
    }
}
