<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELED = 'canceled';
    public const STATUS_SUPERSEDED = 'superseded';
    public const STATUS_REFUNDED = 'refunded';

    protected $fillable = [
        'inquiry_id',
        'payment_session_id',
        'reference_id',
        'status',
        'amount',
        'currency',
        'payment_link_url',
        'payment_request_id',
        'payment_id',
        'payment_method',
        'paid_at',
        'expires_at',
        'payload',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expires_at' => 'datetime',
        'payload' => 'array',
    ];

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    public function latestRefundRecord(): HasOne
    {
        return $this->hasOne(Refund::class)->latestOfMany();
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isRefunded(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }
}
