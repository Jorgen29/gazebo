<?php

namespace Tests\Feature;

use App\Contracts\XenditGateway;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PaymentExpiryFailureTest extends TestCase
{
    use RefreshDatabase;

    private function setupInquiryWithPendingPayment(string $invoiceId): array
    {
        $venue = Venue::create([
            'title' => 'V', 'description' => null, 'capacity' => 10, 'price_per_hour' => 1000,
            'image' => null, 'showcase_images' => [], 'features' => [], 'is_active' => true,
        ]);
        $inquiry = Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'start_time' => '10:00:00', 'end_time' => '11:00:00',
            'status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'inquiry_id' => $inquiry->id,
            'gateway_invoice_id' => $invoiceId,
            'status' => Payment::STATUS_PENDING,
        ]);

        return [$inquiry, $payment, $venue];
    }

    private function gatewayReturning(string $status): void
    {
        $gateway = new class($status) implements XenditGateway {
            public function __construct(private string $status) {}
            public function createInvoice(array $data): array
            {
                return [
                    'id' => 'inv_new',
                    'invoice_url' => 'https://checkout.test/inv_new',
                    'status' => 'PENDING',
                    'expires_at' => now()->addDay()->toISOString(),
                    'paid_at' => null,
                    'payment_method' => null,
                    'raw' => ['id' => 'inv_new'],
                ];
            }
            public function getInvoice(string $invoiceId): array
            {
                return [
                    'id' => $invoiceId,
                    'invoice_url' => 'https://checkout.test/'.$invoiceId,
                    'status' => $this->status,
                    'paid_at' => null,
                    'payment_method' => null,
                    'expires_at' => null,
                    'raw' => ['id' => $invoiceId, 'status' => $this->status],
                ];
            }
            public function expireInvoice(string $invoiceId): void {}
        };
        $this->app->instance(XenditGateway::class, $gateway);
    }

    public function test_expired_payment_keeps_inquiry_pending_and_allows_resend(): void
    {
        Mail::fake();
        config(['services.xendit.callback_token' => 'tok']);
        [$inquiry, $payment] = $this->setupInquiryWithPendingPayment('inv_exp_1');
        $this->gatewayReturning('EXPIRED');

        $this->postJson('/api/webhooks/xendit', ['id' => 'inv_exp_1', 'status' => 'EXPIRED'], ['x-callback-token' => 'tok'])
            ->assertOk();

        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);
        $this->assertSame('pending', $inquiry->fresh()->status);

        $admin = User::factory()->create(['email' => 'admin@gmail.com', 'username' => 'admin', 'password' => bcrypt('password123')]);
        $this->actingAs($admin, 'web');
        $this->post(route('admin.inquiries.send-payment', $inquiry->id))->assertRedirect()->assertSessionHas('success');

        $inquiry->refresh();
        $this->assertSame(2, $inquiry->payments()->count());
        $this->assertSame(Payment::STATUS_PENDING, $inquiry->currentPayment()?->status);
        $this->assertSame('pending', $inquiry->status);
    }

    public function test_failed_payment_keeps_inquiry_pending_and_allows_resend(): void
    {
        Mail::fake();
        config(['services.xendit.callback_token' => 'tok']);
        [$inquiry, $payment] = $this->setupInquiryWithPendingPayment('inv_fail_1');
        $this->gatewayReturning('FAILED');

        $this->postJson('/api/webhooks/xendit', ['id' => 'inv_fail_1', 'status' => 'FAILED'], ['x-callback-token' => 'tok'])
            ->assertOk();

        $this->assertSame(Payment::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame('pending', $inquiry->fresh()->status);

        $admin = User::factory()->create(['email' => 'admin@gmail.com', 'username' => 'admin', 'password' => bcrypt('password123')]);
        $this->actingAs($admin, 'web');
        $this->post(route('admin.inquiries.send-payment', $inquiry->id))->assertRedirect()->assertSessionHas('success');

        $inquiry->refresh();
        $this->assertSame(Payment::STATUS_PENDING, $inquiry->currentPayment()?->status);
        $this->assertSame('pending', $inquiry->status);
    }
}