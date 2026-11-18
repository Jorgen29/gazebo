<?php

namespace App\Services;

use App\Models\Inquiry;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use InvalidArgumentException;

class PaymentSessionWindow
{
    /**
     * Resolve the session expiry as the earlier of the configured window and the event start.
     *
     * The event must be at least one hour away so Xendit's ten-minute minimum is always met.
     */
    public function resolve(Inquiry $inquiry, ?CarbonInterface $now = null): Carbon
    {
        $now = $now ? Carbon::instance($now) : now();

        $date = $inquiry->booking_date instanceof \DateTimeInterface
            ? Carbon::instance($inquiry->booking_date)->toDateString()
            : (string) $inquiry->booking_date;

        $eventStart = Carbon::parse($date . ' ' . $inquiry->start_time);

        if ($eventStart->lessThanOrEqualTo($now)) {
            throw new InvalidArgumentException('The event has already started.');
        }

        if ($eventStart->lessThan($now->copy()->addHour())) {
            throw new InvalidArgumentException('The event starts in less than one hour.');
        }

        $expiry = $now->copy()->addHours((int) config('services.xendit.session_expiry_hours', 24));

        return $expiry->lessThan($eventStart) ? $expiry : $eventStart->copy();
    }
}
