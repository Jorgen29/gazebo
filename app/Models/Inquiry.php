<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
