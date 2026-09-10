<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Notification;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

    public function test_client_submission_and_payment_upload_create_notification_records(): void
    {
        $venue = Venue::create([
            'title' => 'Notification Venue',
            'description' => 'Tracks booking and payment notifications',
            'capacity' => 160,
            'price_per_hour' => 4200,
            'image' => 'venues/notification.jpg',
            'showcase_images' => ['venues/showcase/notification-1.jpg'],
            'features' => ['Sound system'],
            'is_active' => true,
        ]);

        $this->post('/book/submit', [
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => '2026-09-30',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'full_name' => 'New Client',
            'email' => 'newclient@example.com',
            'email_contact' => '09170000005',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'type' => 'booking_request',
            'email' => 'newclient@example.com',
        ]);

        $inquiry = Inquiry::first();

        $this->post('/admin/inquiries/' . $inquiry->id . '/upload-proof', [
            'payment_proof' => UploadedFile::fake()->image('payment-proof.jpg', 1200, 900),
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'inquiry_id' => $inquiry->id,
            'type' => 'payment_received',
        ]);
    }

    public function test_venue_update_replaces_cover_and_removed_showcase_images(): void
    {
        $admin = \App\Models\User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin, 'web');

        $venue = Venue::create([
            'title' => 'Replace Images Venue',
            'description' => 'Old gallery content',
            'capacity' => 180,
            'price_per_hour' => 4000,
            'image' => 'venues/old-cover.jpg',
            'showcase_images' => ['venues/showcase/old-1.jpg', 'venues/showcase/old-2.jpg'],
            'features' => ['Wi-Fi'],
            'is_active' => true,
        ]);

        Storage::fake('public');
        Storage::disk('public')->put('venues/showcase/old-1.jpg', 'old-1');
        Storage::disk('public')->put('venues/showcase/old-2.jpg', 'old-2');
        Storage::disk('public')->put('venues/old-cover.jpg', 'old-cover');

        $response = $this->put('/admin/venues/' . $venue->id, [
            'title' => 'Replace Images Venue',
            'price_per_hour' => 4200,
            'capacity' => 180,
            'is_active' => '1',
            'description' => 'Updated venue content',
            'features' => ['Wi-Fi', 'Stage'],
            'image' => UploadedFile::fake()->image('new-cover.jpg', 1200, 800),
            'showcase_images' => [
                UploadedFile::fake()->image('new-showcase-1.jpg', 1200, 800),
            ],
            'removed_showcase_images' => 'venues/showcase/old-1.jpg',
        ]);

        $response->assertRedirect();

        $venue->refresh();
        $this->assertNotSame('venues/old-cover.jpg', $venue->image);
        $this->assertCount(1, $venue->showcase_images ?? []);
        $this->assertStringContainsString('new-showcase-1', $venue->showcase_images[0]);
    }

    public function test_admin_can_mark_all_notifications_as_read(): void
    {
        $admin = \App\Models\User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin, 'web');

        $inquiry = Inquiry::create([
            'venue_id' => '2',
            'venue_title' => 'Read All Venue',
            'booking_date' => '2026-10-01',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'full_name' => 'Read All Guest',
            'email' => 'readall@example.com',
            'email_contact' => '09170000006',
            'status' => 'pending',
        ]);

        Notification::create([
            'inquiry_id' => $inquiry->id,
            'type' => 'booking_request',
            'email' => $inquiry->email,
            'subject' => 'New inquiry',
            'message' => 'New inquiry message',
        ]);

        Notification::create([
            'inquiry_id' => $inquiry->id,
            'type' => 'payment_received',
            'email' => $inquiry->email,
            'subject' => 'Payment received',
            'message' => 'Payment received message',
        ]);

        $this->post('/admin/notifications/mark-all-read')
            ->assertRedirect('/admin/inquiries');

        $this->assertEquals(2, Notification::query()->whereNotNull('read_at')->count());
    }

    public function test_clicking_a_notification_redirects_to_the_matching_inquiry_and_marks_it_read(): void
    {
        $admin = \App\Models\User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($admin, 'web');

        $inquiry = Inquiry::create([
            'venue_id' => '3',
            'venue_title' => 'Highlight Venue',
            'booking_date' => '2026-10-02',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
            'full_name' => 'Highlight Guest',
            'email' => 'highlight@example.com',
            'email_contact' => '09170000007',
            'status' => 'pending',
        ]);

        $notification = Notification::create([
            'inquiry_id' => $inquiry->id,
            'type' => 'booking_request',
            'email' => $inquiry->email,
            'subject' => 'Inquiry update',
            'message' => 'A notification was created.',
        ]);

        $this->get('/admin/notifications/' . $notification->id . '/open')
            ->assertRedirect('/admin/inquiries?highlight_id=' . $inquiry->id);

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
