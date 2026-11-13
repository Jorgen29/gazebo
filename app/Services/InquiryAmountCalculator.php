<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Venue;
use Carbon\Carbon;

class InquiryAmountCalculator
{
    public function calculate(Inquiry $inquiry): string
    {
        $venue = Venue::query()->find($inquiry->venue_id);
        $rate = $venue ? (float) $venue->price_per_hour : 0.0;

        $hours = 0.0;
        if ($inquiry->start_time && $inquiry->end_time) {
            $baseDate = $inquiry->booking_date ? Carbon::parse($inquiry->booking_date)->toDateString() : now()->toDateString();
            $start = Carbon::parse($baseDate . ' ' . $inquiry->start_time);
            $end = Carbon::parse($baseDate . ' ' . $inquiry->end_time);
            $seconds = $start->diffInSeconds($end, false);
            $hours = max($seconds, 0) / 3600;
        }

        $amount = $rate * $hours;

        return number_format($amount, 2, '.', '');
    }
}