<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Venue;
use Carbon\Carbon;
use InvalidArgumentException;

class InquiryAmountCalculator
{
    /**
     * Return the full booking amount as a 2-decimal string.
     */
    public function calculate(Inquiry $inquiry, ?Venue $venue = null): string
    {
        $venue ??= Venue::query()->find($inquiry->venue_id);

        if (! $venue) {
            throw new InvalidArgumentException('The inquiry does not reference an existing venue.');
        }

        $rate = (float) $venue->price_per_hour;

        if ($rate <= 0) {
            throw new InvalidArgumentException('The venue has no valid hourly rate.');
        }

        $date = $inquiry->booking_date instanceof \DateTimeInterface
            ? Carbon::instance($inquiry->booking_date)->toDateString()
            : (string) $inquiry->booking_date;

        $start = Carbon::parse($date.' '.$inquiry->start_time);
        $end = Carbon::parse($date.' '.$inquiry->end_time);

        // An end time earlier than the start time belongs to the next day.
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $hours = ($end->getTimestamp() - $start->getTimestamp()) / 3600;

        if ($hours <= 0) {
            throw new InvalidArgumentException('The booking duration must be greater than zero.');
        }

        $amount = round($rate * $hours, 2);

        if ($amount <= 0) {
            throw new InvalidArgumentException('The booking amount must be greater than zero.');
        }

        return number_format($amount, 2, '.', '');
    }
}
