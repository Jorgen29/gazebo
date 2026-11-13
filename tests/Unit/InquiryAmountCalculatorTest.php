<?php

namespace Tests\Unit;

use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Venue;
use App\Services\InquiryAmountCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryAmountCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_amount_from_venue_rate_and_duration(): void
    {
        $venue = Venue::create([
            'title' => 'Calc Venue',
            'description' => null,
            'capacity' => 10,
            'price_per_hour' => 1500.00,
            'image' => null,
            'showcase_images' => [],
            'features' => [],
            'is_active' => true,
        ]);

        $inquiry = Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'start_time' => '09:00:00',
            'end_time' => '12:30:00',
        ]);

        $amount = (new InquiryAmountCalculator())->calculate($inquiry);

        $this->assertSame('5250.00', $amount);
    }

    public function test_payment_stores_snapshotted_amount_from_calculation(): void
    {
        $venue = Venue::create([
            'title' => 'Snapshot Venue',
            'description' => null,
            'capacity' => 10,
            'price_per_hour' => 2000.00,
            'image' => null,
            'showcase_images' => [],
            'features' => [],
            'is_active' => true,
        ]);

        $inquiry = Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
        ]);

        $snapshot = (new InquiryAmountCalculator())->calculate($inquiry);
        $payment = Payment::factory()->create([
            'inquiry_id' => $inquiry->id,
            'amount' => $snapshot,
        ]);

        // Change venue rate after invoice creation; stored payment amount must not change.
        $venue->update(['price_per_hour' => 9999.00]);

        $this->assertSame('4000.00', (string) $payment->fresh()->amount);
        $this->assertNotSame((new InquiryAmountCalculator())->calculate($inquiry->fresh()), (string) $payment->fresh()->amount);
    }
}