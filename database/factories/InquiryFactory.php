<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Inquiry>
 */
class InquiryFactory extends Factory
{
    protected $model = Inquiry::class;

    public function definition(): array
    {
        $start = fake()->time('H:i:s');
        $end = date('H:i:s', strtotime($start . ' +3 hours'));

        return [
            'venue_id' => (string) fake()->numberBetween(1, 50),
            'venue_title' => fake()->words(3, true),
            'booking_date' => fake()->date('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
            'full_name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'email_contact' => fake()->phoneNumber(),
            'status' => 'pending',
            'booking_reference' => null,
        ];
    }
}