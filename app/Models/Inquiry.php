<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Inquiry extends Model
{
    use HasFactory;

    protected $fillable = [
        'venue_id',
        'venue_title',
        'booking_date',
        'start_time',
        'end_time',
        'full_name',
        'email',
        'email_contact',
        'status',
        'payment_proof',
        'payment_requested_at',
        'booking_reference',
    ];

    protected $casts = [
        'payment_requested_at' => 'datetime',
        'booking_date' => 'date',
    ];

    public static function generateBookingReference(?self $inquiry = null): string
    {
        $referenceId = $inquiry?->id ?? ((int) self::query()->max('id') + 1);
        $reference = 'R'.str_pad((string) $referenceId, 6, '0', STR_PAD_LEFT);

        while (self::query()
            ->when($inquiry?->exists, fn ($query) => $query->whereKeyNot($inquiry->getKey()))
            ->where('booking_reference', $reference)
            ->exists()
        ) {
            $referenceId++;
            $reference = 'R'.str_pad((string) $referenceId, 6, '0', STR_PAD_LEFT);
        }

        return $reference;
    }

    public function getBookingReference(): string
    {
        if (empty($this->booking_reference)) {
            $this->booking_reference = self::generateBookingReference($this);
            $this->saveQuietly();
        }

        return $this->booking_reference;
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function latestPaymentRecord(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function latestPayment(): ?Payment
    {
        return $this->payments()->latest('id')->first();
    }

    public function latestPaidPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', Payment::STATUS_PAID)
            ->latest('id')
            ->first();
    }

    public function currentPendingPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->latest('id')
            ->first();
    }

    /**
     * Constrain inquiries whose latest payment is paid.
     */
    public function scopeWhereLatestPaymentPaid(Builder $query): Builder
    {
        return $query->whereHas('payments', function (Builder $payments): void {
            $payments->where('status', Payment::STATUS_PAID)
                ->whereNotExists(function ($newer): void {
                    $newer->selectRaw('1')
                        ->from('payments as newer_payments')
                        ->whereColumn('newer_payments.inquiry_id', 'payments.inquiry_id')
                        ->whereColumn('newer_payments.id', '>', 'payments.id');
                });
        });
    }
}
