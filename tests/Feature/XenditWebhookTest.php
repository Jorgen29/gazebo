<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class XenditWebhookTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.callback_token' => 'test-callback-token']);
    }

    private function payment(string $status = Payment::STATUS_PENDING): Payment
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        return $inquiry->payments()->create([
            'payment_session_id' => 'ps_webhook',
            'status' => $status,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);
    }

    public function test_a_completed_webhook_marks_the_payment_paid(): void
    {
        $payment = $this->payment();

        $this->postJson('/api/webhooks/xendit/payment-sessions', [
            'event' => 'payment_session.completed',
            'data' => [
                'payment_session_id' => 'ps_webhook',
                'status' => 'COMPLETED',
                'payment_id' => 'pay_123',
                'paid_at' => '2026-11-17T10:30:00+08:00',
            ],
        ], ['x-callback-token' => 'test-callback-token'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $payment->refresh();

        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertSame('pay_123', $payment->payment_id);
        $this->assertSame('payment_session.completed', $payment->payload['last_event']);
    }

    public function test_an_expired_webhook_marks_the_payment_expired(): void
    {
        $payment = $this->payment();

        $this->postJson('/api/webhooks/xendit/payment-sessions', [
            'event' => 'payment_session.expired',
            'data' => ['payment_session_id' => 'ps_webhook', 'status' => 'EXPIRED'],
        ], ['x-callback-token' => 'test-callback-token'])->assertOk();

        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);
    }

    public function test_an_invalid_callback_token_is_rejected_without_changes(): void
    {
        $payment = $this->payment();

        $this->postJson('/api/webhooks/xendit/payment-sessions', [
            'event' => 'payment_session.completed',
            'data' => ['payment_session_id' => 'ps_webhook', 'status' => 'COMPLETED'],
        ], ['x-callback-token' => 'wrong-token'])->assertStatus(401);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_a_missing_payment_session_id_is_rejected(): void
    {
        $this->postJson('/api/webhooks/xendit/payment-sessions', [
            'event' => 'payment_session.completed',
            'data' => ['status' => 'COMPLETED'],
        ], ['x-callback-token' => 'test-callback-token'])->assertStatus(400);
    }

    public function test_an_unknown_session_is_acknowledged_without_creating_payments(): void
    {
        $this->postJson('/api/webhooks/xendit/payment-sessions', [
            'event' => 'payment_session.completed',
            'data' => ['payment_session_id' => 'ps_unknown', 'status' => 'COMPLETED'],
        ], ['x-callback-token' => 'test-callback-token'])
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertDatabaseCount('payments', 0);
    }

    public function test_duplicate_delivery_is_idempotent(): void
    {
        $payment = $this->payment();
        $payload = [
            'event' => 'payment_session.completed',
            'data' => [
                'payment_session_id' => 'ps_webhook',
                'status' => 'COMPLETED',
                'paid_at' => '2026-11-17T10:30:00+08:00',
            ],
        ];

        $this->postJson('/api/webhooks/xendit/payment-sessions', $payload, ['x-callback-token' => 'test-callback-token'])->assertOk();
        $paidAt = $payment->fresh()->paid_at;

        $this->postJson('/api/webhooks/xendit/payment-sessions', $payload, ['x-callback-token' => 'test-callback-token'])->assertOk();

        $this->assertTrue($payment->fresh()->paid_at->equalTo($paidAt));
        $this->assertSame(1, $payment->inquiry->payments()->count());
    }

    public function test_a_failure_event_is_logged_but_does_not_fail_the_payment(): void
    {
        $payment = $this->payment();

        $this->postJson('/api/webhooks/xendit/payment-sessions', [
            'event' => 'payment.failure',
            'data' => [
                'payment_session_id' => 'ps_webhook',
                'status' => 'FAILED',
            ],
        ], ['x-callback-token' => 'test-callback-token'])
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }
}
