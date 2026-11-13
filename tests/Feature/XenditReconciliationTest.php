<?php

namespace Tests\Feature;

use App\Contracts\XenditGateway;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class XenditReconciliationTest extends TestCase
{
    use RefreshDatabase;

    private function makePending(string $invoiceId): Payment
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
            'gateway_invoice_id' => $invoiceId,
            'status' => Payment::STATUS_PENDING,
            'paid_at' => null,
        ]);
    }

    public function test_reconciliation_syncs_missed_paid_and_expired_invoices(): void
    {
        $paid = $this->makePending('inv_paid_missed');
        $expired = $this->makePending('inv_expired_missed');

        $gateway = new class implements XenditGateway {
            public function createInvoice(array $data): array { return []; }
            public function getInvoice(string $invoiceId): array
            {
                if ($invoiceId === 'inv_paid_missed') {
                    return [
                        'id' => $invoiceId,
                        'invoice_url' => 'https://checkout.test/'.$invoiceId,
                        'status' => 'PAID',
                        'paid_at' => '2026-11-12T04:00:00Z',
                        'payment_method' => 'CREDIT_CARD',
                        'expires_at' => null,
                        'raw' => ['id' => $invoiceId, 'status' => 'PAID'],
                    ];
                }
                return [
                    'id' => $invoiceId,
                    'invoice_url' => 'https://checkout.test/'.$invoiceId,
                    'status' => 'EXPIRED',
                    'paid_at' => null,
                    'payment_method' => null,
                    'expires_at' => '2026-11-12T05:00:00Z',
                    'raw' => ['id' => $invoiceId, 'status' => 'EXPIRED'],
                ];
            }
            public function expireInvoice(string $invoiceId): void {}
        };
        $this->app->instance(XenditGateway::class, $gateway);

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        $this->assertSame(Payment::STATUS_PAID, $paid->fresh()->status);
        $this->assertNotNull($paid->fresh()->paid_at);
        $this->assertSame(Payment::STATUS_EXPIRED, $expired->fresh()->status);
        $this->assertSame('pending', $expired->fresh()->inquiry->status);
    }
}