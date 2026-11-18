<?php

namespace Tests\Feature;

use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class PaymentReconciliationTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.secret_key' => 'xnd_development_test_key']);
    }

    private function payment(string $sessionId, string $status = Payment::STATUS_PENDING): Payment
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        return $inquiry->payments()->create([
            'payment_session_id' => $sessionId,
            'status' => $status,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);
    }

    public function test_it_reconciles_completed_expired_and_canceled_sessions(): void
    {
        $paid = $this->payment('ps_paid');
        $expired = $this->payment('ps_expired');
        $canceled = $this->payment('ps_canceled');

        Http::fake([
            'https://api.xendit.co/sessions/ps_paid' => Http::response([
                'payment_session_id' => 'ps_paid',
                'status' => 'COMPLETED',
                'payment_id' => 'pay_paid',
            ], 200),
            'https://api.xendit.co/sessions/ps_expired' => Http::response([
                'payment_session_id' => 'ps_expired',
                'status' => 'EXPIRED',
            ], 200),
            'https://api.xendit.co/sessions/ps_canceled' => Http::response([
                'payment_session_id' => 'ps_canceled',
                'status' => 'CANCELED',
            ], 200),
        ]);

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        $this->assertSame(Payment::STATUS_PAID, $paid->fresh()->status);
        $this->assertSame(Payment::STATUS_PAID, $paid->fresh()->status);
        $this->assertSame('pay_paid', $paid->fresh()->payment_id);
        $this->assertSame(Payment::STATUS_EXPIRED, $expired->fresh()->status);
        $this->assertSame(Payment::STATUS_CANCELED, $canceled->fresh()->status);
    }

    public function test_one_failed_retrieval_does_not_abort_the_batch(): void
    {
        $broken = $this->payment('ps_broken');
        $working = $this->payment('ps_working');

        Http::fake([
            'https://api.xendit.co/sessions/ps_broken' => Http::response(['message' => 'Server error'], 500),
            'https://api.xendit.co/sessions/ps_working' => Http::response([
                'payment_session_id' => 'ps_working',
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        $this->assertSame(Payment::STATUS_PENDING, $broken->fresh()->status);
        $this->assertSame(Payment::STATUS_PAID, $working->fresh()->status);
    }

    public function test_paid_payments_are_skipped(): void
    {
        $paid = $this->payment('ps_already_paid', Payment::STATUS_PAID);

        Http::fake();

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        Http::assertNothingSent();
        $this->assertSame(Payment::STATUS_PAID, $paid->fresh()->status);
    }
}
