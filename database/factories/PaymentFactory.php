<?php

namespace Database\Factories;

use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'inquiry_id' => Inquiry::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'currency' => 'PHP',
            'gateway' => 'xendit',
            'gateway_invoice_id' => 'inv_' . fake()->unique()->uuid(),
            'invoice_url' => fake()->url(),
            'status' => Payment::STATUS_PENDING,
            'payment_method' => null,
            'paid_at' => null,
            'expires_at' => now()->addDay(),
            'payload' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_PAID,
            'paid_at' => now(),
            'payment_method' => 'EWALLET',
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_EXPIRED,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => Payment::STATUS_FAILED,
        ]);
    }
}