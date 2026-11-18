<?php

namespace Tests\Unit;

use App\Models\Inquiry;
use App\Models\Venue;
use App\Services\InquiryAmountCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class InquiryAmountCalculatorTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    private function calculate(Inquiry $inquiry): string
    {
        return (new InquiryAmountCalculator())->calculate($inquiry);
    }

    public function test_it_multiplies_the_hourly_rate_by_a_whole_number_of_hours(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue(['price_per_hour' => 3000]), [
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
        ]);

        $this->assertSame('9000.00', $this->calculate($inquiry));
    }

    public function test_it_handles_fractional_hours(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue(['price_per_hour' => 3000]), [
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
        ]);

        $this->assertSame('4500.00', $this->calculate($inquiry));
    }

    public function test_it_rolls_an_overnight_range_to_the_next_day(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue(['price_per_hour' => 3000]), [
            'start_time' => '22:00:00',
            'end_time' => '02:00:00',
        ]);

        $this->assertSame('12000.00', $this->calculate($inquiry));
    }

    public function test_it_rejects_zero_duration(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue(), [
            'start_time' => '09:00:00',
            'end_time' => '09:00:00',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->calculate($inquiry);
    }

    public function test_it_rejects_a_venue_without_a_rate(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue(['price_per_hour' => 0]));

        $this->expectException(InvalidArgumentException::class);

        $this->calculate($inquiry);
    }

    public function test_it_rejects_a_missing_venue(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());
        $inquiry->venue_id = '999999';
        $inquiry->save();

        $this->expectException(InvalidArgumentException::class);

        $this->calculate($inquiry);
    }
}
