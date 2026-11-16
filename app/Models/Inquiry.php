<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
        'booking_reference',
    ];

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Current payment is the latest active (pending) invoice for this inquiry.
     * Expired/failed/superseded attempts are not current.
     */
    public function currentPayment(): ?Payment
    {
        return $this->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->latest('id')
            ->first();
    }

    public function latestCurrentPaidPayment(): ?Payment
    {
        // "Latest current payment is paid" for approval gate means the most recent
        // invoice attempt for the inquiry is paid (and thus the active/current one).
        $latest = $this->payments()->latest('id')->first();

        return $latest && $latest->isPaid() ? $latest : null;
    }

    public static function generateBookingReference(?self $inquiry = null): string
    {
        $referenceId = $inquiry?->id ?? ((int) self::query()->max('id') + 1);
        $reference = 'R' . str_pad((string) $referenceId, 6, '0', STR_PAD_LEFT);

        while (self::query()
            ->when($inquiry?->exists, fn($query) => $query->whereKeyNot($inquiry->getKey()))
            ->where('booking_reference', $reference)
            ->exists()
        ) {
            $referenceId++;
            $reference = 'R' . str_pad((string) $referenceId, 6, '0', STR_PAD_LEFT);
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
}
