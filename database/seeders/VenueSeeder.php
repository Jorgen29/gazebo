<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    /**
     * Seed the venue catalogue so a fresh install has bookable spaces.
     */
    public function run(): void
    {
        $venues = [
            [
                'title' => 'The Grand Hall',
                'description' => 'Flagship indoor ballroom with a full stage, banquet seating, and climate control.',
                'capacity' => 300,
                'price_per_hour' => 6500,
                'image' => 'https://images.unsplash.com/photo-1519167758481-83f550bb49b3?auto=format&fit=crop&w=1200&q=80',
                'showcase_images' => [
                    'https://images.unsplash.com/photo-1464366400600-7168b8af9bc3?auto=format&fit=crop&w=1200&q=80',
                ],
                'features' => ['Air conditioning', 'Stage and sound system', 'Banquet seating'],
                'is_active' => true,
            ],
            [
                'title' => 'The Garden Pavilion',
                'description' => 'Open-air garden pavilion for ceremonies, receptions, and intimate celebrations.',
                'capacity' => 150,
                'price_per_hour' => 4200,
                'image' => 'https://images.unsplash.com/photo-1545232979-fbf59202c396?auto=format&fit=crop&w=1200&q=80',
                'showcase_images' => [],
                'features' => ['Garden setting', 'Covered seating', 'Photo areas'],
                'is_active' => true,
            ],
        ];

        foreach ($venues as $venue) {
            Venue::query()->updateOrCreate(
                ['slug' => \Illuminate\Support\Str::slug($venue['title'])],
                $venue
            );
        }
    }
}
