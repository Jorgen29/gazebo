<?php

namespace Tests\Feature;

use App\Mail\RefundSucceededMail;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class XenditRefundWebhookTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.callback_token' => 'test-callback-token']);
    }

    private function refund(string $status = Refund::STATUS_PENDING): Refund
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        $payment = $inquiry->payments()->create([
            'payment_session_id' => 'ps_webhook_refund',
            'status' => Payment::STATUS_PAID,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'payment_request_id' => 'pr_webhook_refund',
            'paid_at' => '2026-11-16T09:00:00+08:00',
        ]);

        return $payment->refunds()->create([
            'refund_id' => 'rf_webhook',
            'reference_id' => 'refund-webhook-reference',
            'payment_request_id' => 'pr_webhook_refund',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => $status,
            'payload' => [],
        ]);
    }

    public function test_a_successful_refund_webhook_marks_the_refund_succeeded_and_the_payment_refunded(): void
    {
        Mail::fake();
        $refund = $this->refund();
        $paidAt = $refund->payment->paid_at;

        $this->postJson('/api/webhooks/xendit/refunds', [
            'event' => 'refund.succeeded',
            'data' => [
                'id' => 'rf_webhook',
                'status' => 'SUCCEEDED',
                'updated' => '2026-11-18T11:00:00+08:00',
            ],
        ], ['x-callback-token' => 'test-callback-token'])
            ->assertOk()
            ->assertJson(['status' => 'ok']);

        $refund->refresh();
        $payment = $refund->payment;

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->status);
        $this->assertSame('refund.succeeded', $refund->payload['last_event']);
        $this->assertSame(Payment::STATUS_REFUNDED, $payment->status);
        $this->assertTrue($payment->paid_at->equalTo($paidAt));
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_a_failed_refund_webhook_keeps_the_payment_paid_and_stores_the_failure_code(): void
    {
        Mail::fake();
        $refund = $this->refund();

        $this->postJson('/api/webhooks/xendit/refunds', [
            'event' => 'refund.failed',
            'data' => [
                'id' => 'rf_webhook',
                'status' => 'FAILED',
                'failure_code' => 'INSUFFICIENT_BALANCE',
            ],
        ], ['x-callback-token' => 'test-callback-token'])->assertOk();

        $refund->refresh();

        $this->assertSame(Refund::STATUS_FAILED, $refund->status);
        $this->assertSame('INSUFFICIENT_BALANCE', $refund->failure_code);
        $this->assertSame(Payment::STATUS_PAID, $refund->payment->status);
        Mail::assertNothingQueued();
    }

    public function test_a_cancelled_refund_webhook_keeps_the_payment_paid(): void
    {
        Mail::fake();
        $refund = $this->refund();

        $this->postJson('/api/webhooks/xendit/refunds', [
            'event' => 'refund.cancelled',
            'data' => ['id' => 'rf_webhook', 'status' => 'CANCELLED'],
        ], ['x-callback-token' => 'test-callback-token'])->assertOk();

        $refund->refresh();

        $this->assertSame(Refund::STATUS_CANCELLED, $refund->status);
        $this->assertSame(Payment::STATUS_PAID, $refund->payment->status);
        Mail::assertNothingQueued();
    }

    public function test_the_event_name_supplies_the_status_when_the_payload_omits_it(): void
    {
        Mail::fake();
        $refund = $this->refund();

        $this->postJson('/api/webhooks/xendit/refunds', [
            'event' => 'refund.succeeded',
            'data' => ['id' => 'rf_webhook'],
        ], ['x-callback-token' => 'test-callback-token'])->assertOk();

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->fresh()->status);
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_a_duplicate_successful_webhook_is_idempotent(): void
    {
        Mail::fake();
        $refund = $this->refund();
        $payload = [
            'event' => 'refund.succeeded',
            'data' => ['id' => 'rf_webhook', 'status' => 'SUCCEEDED'],
        ];

        $this->postJson('/api/webhooks/xendit/refunds', $payload, ['x-callback-token' => 'test-callback-token'])->assertOk();
        $this->postJson('/api/webhooks/xendit/refunds', $payload, ['x-callback-token' => 'test-callback-token'])->assertOk();

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->fresh()->status);
        $this->assertSame(Payment::STATUS_REFUNDED, $refund->payment->status);
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_a_missing_refund_id_is_rejected_with_400(): void
    {
        $this->postJson('/api/webhooks/xendit/refunds', [
            'event' => 'refund.succeeded',
            'data' => ['status' => 'SUCCEEDED'],
        ], ['x-callback-token' => 'test-callback-token'])
            ->assertStatus(400)
            ->assertJson(['message' => 'Missing refund id.']);

        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_an_unknown_refund_id_is_acknowledged_without_changes(): void
    {
        $refund = $this->refund();

        $this->postJson('/api/webhooks/xendit/refunds', [
            'event' => 'refund.succeeded',
            'data' => ['id' => 'rf_unknown', 'status' => 'SUCCEEDED'],
        ], ['x-callback-token' => 'test-callback-token'])
            ->assertOk()
            ->assertJson(['status' => 'ignored']);

        $this->assertSame(Refund::STATUS_PENDING, $refund->fresh()->status);
        $this->assertSame(Payment::STATUS_PAID, $refund->payment->status);
    }

    public function test_invalid_and_missing_callback_tokens_are_rejected_without_changes(): void
    {
        $refund = $this->refund();

        foreach ([['x-callback-token' => 'wrong-token'], []] as $headers) {
            $this->postJson('/api/webhooks/xendit/refunds', [
                'event' => 'refund.succeeded',
                'data' => ['id' => 'rf_webhook', 'status' => 'SUCCEEDED'],
            ], $headers)->assertStatus(401);
        }

        $this->assertSame(Refund::STATUS_PENDING, $refund->fresh()->status);
        $this->assertSame(Payment::STATUS_PAID, $refund->payment->status);
    }
}
