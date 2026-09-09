<?php

namespace Tests\Feature;

use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
