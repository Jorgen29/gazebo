<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_FAILED = 'failed';
    public const STATUS_SUPERSEDED = 'superseded';

    protected $fillable = [
        'inquiry_id',
        'amount',
        'currency',
        'gateway',
        'gateway_invoice_id',
        'invoice_url',
        'status',
        'payment_method',
        'paid_at',
        'expires_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function inquiry(): BelongsTo
    {
        return $this->belongsTo(Inquiry::class);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isCurrent(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}