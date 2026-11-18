<?php

namespace Tests\Unit;

use App\Models\Inquiry;
use App\Services\PaymentSessionWindow;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class PaymentSessionWindowTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function resolve(Inquiry $inquiry): Carbon
    {
        return (new PaymentSessionWindow())->resolve($inquiry);
    }

    public function test_it_refuses_an_event_that_has_already_started(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));

        $inquiry = $this->makeInquiry($this->makeVenue(), [
            'booking_date' => '2026-11-17',
            'start_time' => '08:00:00',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->resolve($inquiry);
    }

    public function test_it_refuses_an_event_less_than_one_hour_away(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));

        $inquiry = $this->makeInquiry($this->makeVenue(), [
            'booking_date' => '2026-11-17',
            'start_time' => '09:30:00',
        ]);

        $this->expectException(InvalidArgumentException::class);

        $this->resolve($inquiry);
    }

    public function test_expiry_is_the_event_start_when_it_is_inside_the_window(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));

        $inquiry = $this->makeInquiry($this->makeVenue(), [
            'booking_date' => '2026-11-17',
            'start_time' => '12:00:00',
        ]);

        $expiry = $this->resolve($inquiry);

        $this->assertTrue($expiry->equalTo(Carbon::parse('2026-11-17 12:00:00')));
    }

    public function test_expiry_is_the_configured_window_when_the_event_is_further_away(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));

        $inquiry = $this->makeInquiry($this->makeVenue(), [
            'booking_date' => '2026-11-20',
            'start_time' => '12:00:00',
        ]);

        $expiry = $this->resolve($inquiry);

        $this->assertTrue($expiry->equalTo(Carbon::parse('2026-11-18 09:00:00')));
    }

    public function test_an_event_exactly_one_hour_away_is_allowed_and_meets_the_gateway_minimum(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));

        $inquiry = $this->makeInquiry($this->makeVenue(), [
            'booking_date' => '2026-11-17',
            'start_time' => '10:00:00',
        ]);

        $expiry = $this->resolve($inquiry);

        $this->assertTrue($expiry->greaterThanOrEqualTo(Carbon::now()->addMinutes(10)));
    }
}
