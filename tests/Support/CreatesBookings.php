<?php

namespace Tests\Support;

use App\Models\Inquiry;
use App\Models\User;
use App\Models\Venue;

trait CreatesBookings
{
    protected function makeVenue(array $overrides = []): Venue
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

    protected function makeInquiry(Venue $venue, array $overrides = []): Inquiry
    {
        return Inquiry::create(array_merge([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => now()->addDays(7)->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '12:00:00',
            'full_name' => 'Test Guest',
            'email' => 'guest@example.com',
            'email_contact' => '09170000000',
            'status' => 'pending',
        ], $overrides));
    }

    protected function makeAdmin(): User
    {
        return User::factory()->create([
            'email' => 'admin@example.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);
    }
}
