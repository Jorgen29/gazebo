<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicVenueListTest extends TestCase
{
    use RefreshDatabase;

    public function test_inquire_page_only_shows_active_venues_from_the_database(): void
    {
        Venue::create([
            'title' => 'Sunset Pavilion',
            'description' => 'Beautiful sunset venue',
            'capacity' => 180,
            'price_per_hour' => 4500,
            'image' => 'venues/sunset.jpg',
            'showcase_images' => ['venues/showcase/1.jpg'],
            'features' => ['Garden view', 'Air conditioning'],
            'is_active' => true,
        ]);

        Venue::create([
            'title' => 'Hidden Venue',
            'description' => 'This should stay hidden',
            'capacity' => 50,
            'price_per_hour' => 1500,
            'image' => 'venues/hidden.jpg',
            'showcase_images' => ['venues/showcase/2.jpg'],
            'features' => ['Hidden feature'],
            'is_active' => false,
        ]);

        $response = $this->get('/inquire');

        $response->assertOk();
        $response->assertSee('Sunset Pavilion');
        $response->assertDontSee('Hidden Venue');
    }

    public function test_booking_page_uses_the_selected_database_venue_record(): void
    {
        $venue = Venue::create([
            'title' => 'Database Venue',
            'description' => 'Live venue from the database',
            'capacity' => 220,
            'price_per_hour' => 6500,
            'image' => 'venues/live.jpg',
            'showcase_images' => ['venues/showcase/live-1.jpg'],
            'features' => ['Air conditioning', 'Stage'],
            'is_active' => true,
        ]);

        $response = $this->get('/book?space=' . $venue->id . '&date=2026-09-15');

        $response->assertOk();
        $response->assertSee('Database Venue');
        $response->assertSee('Live venue from the database');
    }

    public function test_selected_date_schedule_only_shows_approved_inquiries(): void
    {
        $venue = Venue::create([
            'title' => 'Approved-Only Venue',
            'description' => 'It should only list approved bookings',
            'capacity' => 120,
            'price_per_hour' => 3500,
            'image' => 'venues/approved-only.jpg',
            'showcase_images' => ['venues/showcase/approved-1.jpg'],
            'features' => ['Outdoor setup'],
            'is_active' => true,
        ]);

        \App\Models\Inquiry::create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => '2026-09-15',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'full_name' => 'Approved Guest',
            'email' => 'approved@example.com',
            'email_contact' => '09170000000',
            'status' => 'approved',
        ]);

        \App\Models\Inquiry::create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => '2026-09-15',
            'start_time' => '13:00:00',
            'end_time' => '15:00:00',
            'full_name' => 'Pending Guest',
            'email' => 'pending@example.com',
            'email_contact' => '09170000001',
            'status' => 'pending',
        ]);

        $response = $this->get('/book?space=' . $venue->id . '&date=2026-09-15');

        $response->assertOk();
        $response->assertSee('Approved Guest');
        $response->assertDontSee('Pending Guest');
    }

    public function test_inquire_page_calendar_reflects_approved_inquiries_from_database(): void
    {
        $venue = Venue::create([
            'title' => 'Calendar Venue',
            'description' => 'Should show in the modal calendar',
            'capacity' => 200,
            'price_per_hour' => 5200,
            'image' => 'venues/calendar.jpg',
            'showcase_images' => ['venues/showcase/calendar-1.jpg'],
            'features' => ['Stage'],
            'is_active' => true,
        ]);

        \App\Models\Inquiry::create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => '2026-09-15',
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'full_name' => 'Calendar Guest',
            'email' => 'calendar@example.com',
            'email_contact' => '09170000002',
            'status' => 'approved',
        ]);

        $response = $this->get('/inquire');

        $response->assertOk();
        $response->assertSee('Calendar Guest');
        $response->assertSee('2026-09-15');
    }

    public function test_payment_reference_is_generated_in_r_format(): void
    {
        $reference = \App\Models\Inquiry::generateBookingReference();

        $this->assertMatchesRegularExpression('/^R\d+$/', $reference);
        $this->assertNotSame('R', $reference);
        $this->assertSame(0, \App\Models\Inquiry::where('booking_reference', $reference)->count());
    }

    public function test_cancelled_status_is_valid_for_inquiries(): void
    {
        $inquiry = \App\Models\Inquiry::create([
            'venue_id' => '1',
            'venue_title' => 'Cancelled Venue',
            'booking_date' => '2026-09-20',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'full_name' => 'Cancel Guest',
            'email' => 'cancel@example.com',
            'email_contact' => '09170000003',
            'status' => 'pending',
        ]);

        $inquiry->update(['status' => 'cancelled']);

        $this->assertSame('cancelled', $inquiry->fresh()->status);
    }

    public function test_status_updates_send_customer_notifications(): void
    {
        Mail::fake();

        $admin = \App\Models\User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin, 'web');

        $inquiry = Inquiry::create([
            'venue_id' => '2',
            'venue_title' => 'Status Email Venue',
            'booking_date' => '2026-09-25',
            'start_time' => '10:00:00',
            'end_time' => '14:00:00',
            'full_name' => 'Status Email Guest',
            'email' => 'status@example.com',
            'email_contact' => '09170000004',
            'status' => 'pending',
        ]);

        $this->patch("/admin/inquiries/{$inquiry->id}/status", ['status' => 'approved'])
            ->assertRedirect();

        Mail::assertSent(\App\Mail\InquiryStatusNotification::class, function ($mail) use ($inquiry) {
            return $mail->hasTo($inquiry->email)
                && str_contains(strtolower($mail->subject), 'approved');
        });
    }
}
