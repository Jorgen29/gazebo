<?php

namespace Tests\Feature;

use App\Contracts\XenditGateway;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XenditWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGatewayReturning(string $status, array $extra = []): void
    {
        $gateway = new class($status, $extra) implements XenditGateway {
            public function __construct(private string $status, private array $extra) {}
            public function createInvoice(array $data): array { return []; }
            public function getInvoice(string $invoiceId): array
            {
                return array_merge([
                    'id' => $invoiceId,
                    'invoice_url' => 'https://checkout.test/'.$invoiceId,
                    'status' => $this->status,
                    'paid_at' => $this->status === 'PAID' ? '2026-11-12T03:00:00Z' : null,
                    'payment_method' => $this->status === 'PAID' ? 'EWALLET' : null,
                    'expires_at' => null,
                    'raw' => ['id' => $invoiceId, 'status' => $this->status],
                ], $this->extra);
            }
            public function expireInvoice(string $invoiceId): void {}
        };
        $this->app->instance(XenditGateway::class, $gateway);
    }

    private function makePendingPayment(): Payment
    {
        $venue = Venue::create([
            'title' => 'V', 'description' => null, 'capacity' => 10, 'price_per_hour' => 1000,
            'image' => null, 'showcase_images' => [], 'features' => [], 'is_active' => true,
        ]);
        $inquiry = Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'start_time' => '10:00:00', 'end_time' => '11:00:00',
        ]);
        return Payment::factory()->create([
            'inquiry_id' => $inquiry->id,
            'gateway_invoice_id' => 'inv_webhook_1',
            'status' => Payment::STATUS_PENDING,
            'paid_at' => null,
            'payment_method' => null,
        ]);
    }

    public function test_valid_paid_callback_updates_payment_idempotently(): void
    {
        config(['services.xendit.callback_token' => 'tok_test']);
        $this->fakeGatewayReturning('PAID');
        $payment = $this->makePendingPayment();

        $payload = ['id' => 'inv_webhook_1', 'status' => 'PAID'];

        $this->postJson('/api/webhooks/xendit', $payload, ['x-callback-token' => 'tok_test'])
            ->assertOk();

        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertSame('EWALLET', $payment->payment_method);

        // Duplicate callback must not change final state or create side effects
        $paidAt = $payment->paid_at;
        $this->postJson('/api/webhooks/xendit', $payload, ['x-callback-token' => 'tok_test'])
            ->assertOk();
        $payment->refresh();
        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertEquals($paidAt->timestamp, $payment->paid_at->timestamp);
        $this->assertSame('EWALLET', $payment->payment_method);
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_invalid_callback_token_is_rejected(): void
    {
        config(['services.xendit.callback_token' => 'tok_test']);
        $this->fakeGatewayReturning('PAID');
        $payment = $this->makePendingPayment();

        $this->postJson('/api/webhooks/xendit', ['id' => 'inv_webhook_1', 'status' => 'PAID'], ['x-callback-token' => 'wrong'])
            ->assertStatus(401);

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_expired_callback_marks_payment_expired_and_keeps_inquiry_pending(): void
    {
        config(['services.xendit.callback_token' => 'tok_test']);
        $this->fakeGatewayReturning('EXPIRED');
        $payment = $this->makePendingPayment();

        $this->postJson('/api/webhooks/xendit', ['id' => 'inv_webhook_1', 'status' => 'EXPIRED'], ['x-callback-token' => 'tok_test'])
            ->assertOk();

        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);
        $this->assertSame('pending', $payment->inquiry->fresh()->status);
    }
}