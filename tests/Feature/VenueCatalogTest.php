<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class VenueCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function makeVenue(array $overrides = []): Venue
    {
        return Venue::create(array_merge([
            'title' => 'Test Venue',
            'description' => 'A database backed venue',
            'capacity' => 100,
            'price_per_hour' => 3000,
            'image' => 'venues/test.jpg',
            'showcase_images' => [],
            'features' => ['Garden view'],
            'is_active' => true,
        ], $overrides));
    }

    private function makeInquiry(Venue $venue, array $overrides = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => '2026-10-10',
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'full_name' => 'Test Guest',
            'email' => 'guest@example.com',
            'email_contact' => '09170000000',
            'status' => 'approved',
        ], $overrides));
    }

    public function test_venues_get_a_unique_slug_and_keep_it_when_renamed(): void
    {
        $first = $this->makeVenue(['title' => 'Sunset Pavilion']);
        $second = $this->makeVenue(['title' => 'Sunset Pavilion']);

        $this->assertSame('sunset-pavilion', $first->slug);
        $this->assertSame('sunset-pavilion-2', $second->slug);

        $second->update(['title' => 'Renamed Pavilion']);

        $this->assertSame('sunset-pavilion-2', $second->fresh()->slug);
    }

    public function test_admin_created_venue_receives_a_slug_without_a_form_field(): void
    {
        $admin = User::factory()->create(['username' => 'admin']);

        $this->actingAs($admin, 'web')
            ->post('/admin/venues', [
                'title' => 'Grand New Hall',
                'description' => 'Created from the admin panel',
                'capacity' => 250,
                'price_per_hour' => 5000,
                'is_active' => '1',
            ])
            ->assertRedirect();

        $venue = Venue::firstOrFail();

        $this->assertSame('grand-new-hall', $venue->slug);
    }

    public function test_booking_page_defaults_to_first_active_venue_and_today(): void
    {
        $this->makeVenue(['title' => 'Beta Hall']);
        $this->makeVenue(['title' => 'Alpha Hall']);
        $this->makeVenue(['title' => 'Hidden Hall', 'is_active' => false]);

        $response = $this->get('/book');

        $response->assertOk();
        $response->assertSee('Alpha Hall');
        $response->assertDontSee('Beta Hall');
        $response->assertDontSee('Hidden Hall');
        $response->assertSee(now()->toDateString());
    }

    public function test_booking_page_resolves_a_venue_by_slug(): void
    {
        $venue = $this->makeVenue(['title' => 'Garden Pavilion']);

        $response = $this->get('/book?space=' . $venue->slug);

        $response->assertOk();
        $response->assertSee('Garden Pavilion');
    }

    public function test_booking_page_rejects_unknown_and_inactive_venues(): void
    {
        $inactive = $this->makeVenue(['title' => 'Closed Hall', 'is_active' => false]);

        $this->get('/book?space=does-not-exist')->assertNotFound();
        $this->get('/book?space=' . $inactive->id)->assertNotFound();
        $this->get('/book?space=' . $inactive->slug)->assertNotFound();
    }

    public function test_booking_page_is_not_found_when_no_active_venue_exists(): void
    {
        $this->makeVenue(['title' => 'Closed Hall', 'is_active' => false]);

        $this->get('/book')->assertNotFound();
    }

    public function test_availability_only_includes_approved_inquiries_for_the_resolved_venue(): void
    {
        $venue = $this->makeVenue(['title' => 'Calendar Hall']);
        $other = $this->makeVenue(['title' => 'Other Hall']);

        $this->makeInquiry($venue, ['full_name' => 'Approved Guest']);
        $this->makeInquiry($venue, ['full_name' => 'Pending Guest', 'status' => 'pending']);
        $this->makeInquiry($other, ['full_name' => 'Other Venue Guest']);

        $response = $this->get('/book?space=' . $venue->slug);

        $response->assertOk();
        $response->assertSee('Approved Guest');
        $response->assertDontSee('Pending Guest');
        $response->assertDontSee('Other Venue Guest');
    }

    public function test_inquiry_submission_uses_the_stored_venue_title(): void
    {
        Mail::fake();

        $venue = $this->makeVenue(['title' => 'Canonical Hall']);

        $this->post('/book/submit', [
            'venue_id' => (string) $venue->id,
            'venue_title' => 'Forged Title',
            'booking_date' => '2026-10-20',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'full_name' => 'Real Client',
            'email' => 'real@example.com',
            'email_contact' => '09170000001',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inquiries', [
            'venue_id' => (string) $venue->id,
            'venue_title' => 'Canonical Hall',
            'email' => 'real@example.com',
        ]);
    }

    public function test_inquiry_submission_rejects_unknown_or_inactive_venues(): void
    {
        Mail::fake();

        $inactive = $this->makeVenue(['title' => 'Closed Hall', 'is_active' => false]);

        $payload = [
            'venue_title' => 'Anything',
            'booking_date' => '2026-10-20',
            'start_time' => '09:00',
            'end_time' => '12:00',
            'full_name' => 'Forged Client',
            'email' => 'forged@example.com',
            'email_contact' => '09170000002',
        ];

        $this->post('/book/submit', $payload + ['venue_id' => '999999'])
            ->assertSessionHasErrors('venue_id');

        $this->post('/book/submit', $payload + ['venue_id' => (string) $inactive->id])
            ->assertSessionHasErrors('venue_id');

        $this->assertDatabaseCount('inquiries', 0);
        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_legacy_inquiry_route_is_no_longer_registered(): void
    {
        $this->get('/inquiry')->assertNotFound();
        $this->get('/inquire')->assertOk();
    }

    public function test_booking_form_posts_the_resolved_venue_id(): void
    {
        $venue = $this->makeVenue(['title' => 'Form Hall']);

        $response = $this->get('/book?space=' . $venue->slug);

        $response->assertOk();
        $response->assertSee('name="venue_id"', false);
        $response->assertSee('Form Hall', false);
    }
}
