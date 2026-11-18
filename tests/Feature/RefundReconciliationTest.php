<?php

namespace Tests\Feature;

use App\Mail\RefundSucceededMail;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class RefundReconciliationTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.secret_key' => 'xnd_development_test_key']);
    }

    private function refund(array $overrides = [], string $paymentStatus = Payment::STATUS_PAID): Refund
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        $payment = $inquiry->payments()->create([
            'payment_session_id' => 'ps_refund_reconcile_' . uniqid(),
            'status' => $paymentStatus,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'payment_request_id' => 'pr_refund_reconcile',
            'paid_at' => now()->subDay(),
        ]);

        return $payment->refunds()->create(array_merge([
            'refund_id' => 'rf_reconcile_' . uniqid(),
            'reference_id' => 'refund-reconciliation',
            'payment_request_id' => 'pr_refund_reconcile',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => Refund::STATUS_PENDING,
            'payload' => [],
        ], $overrides));
    }

    public function test_a_missed_successful_refund_is_reconciled(): void
    {
        Mail::fake();
        $refund = $this->refund();

        Http::fake([
            'https://api.xendit.co/refunds/'.$refund->refund_id => Http::response([
                'id' => $refund->refund_id,
                'status' => 'SUCCEEDED',
                'updated' => '2026-11-18T12:00:00+08:00',
            ], 200),
        ]);

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        $refund->refresh();

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->status);
        $this->assertSame(Payment::STATUS_REFUNDED, $refund->payment->status);
        $this->assertSame('reconciliation', $refund->payload['last_event']);
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_a_missed_failed_refund_stores_the_failure_without_changing_the_payment(): void
    {
        Mail::fake();
        $refund = $this->refund();

        Http::fake([
            'https://api.xendit.co/refunds/'.$refund->refund_id => Http::response([
                'id' => $refund->refund_id,
                'status' => 'FAILED',
                'failure_code' => 'INSUFFICIENT_BALANCE',
            ], 200),
        ]);

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        $refund->refresh();

        $this->assertSame(Refund::STATUS_FAILED, $refund->status);
        $this->assertSame('INSUFFICIENT_BALANCE', $refund->failure_code);
        $this->assertSame(Payment::STATUS_PAID, $refund->payment->status);
        Mail::assertNothingQueued();
    }

    public function test_pending_refunds_without_a_gateway_identifier_are_skipped(): void
    {
        $this->refund(['refund_id' => null]);

        Http::fake();

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        Http::assertNothingSent();
    }

    public function test_an_already_succeeded_refund_is_not_reconciled_or_regressed(): void
    {
        Mail::fake();
        $refund = $this->refund([
            'status' => Refund::STATUS_SUCCEEDED,
            'refunded_at' => now(),
        ], Payment::STATUS_REFUNDED);

        Http::fake();

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        Http::assertNothingSent();

        $refund->refresh();

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->status);
        $this->assertSame(Payment::STATUS_REFUNDED, $refund->payment->status);
        Mail::assertNothingQueued();
    }

    public function test_one_failed_refund_does_not_abort_the_refund_batch(): void
    {
        Mail::fake();
        $broken = $this->refund();
        $working = $this->refund();

        Http::fake([
            'https://api.xendit.co/refunds/'.$broken->refund_id => Http::response(['message' => 'Server error'], 500),
            'https://api.xendit.co/refunds/'.$working->refund_id => Http::response([
                'id' => $working->refund_id,
                'status' => 'SUCCEEDED',
            ], 200),
        ]);

        $this->artisan('payments:reconcile-xendit')->assertExitCode(0);

        $this->assertSame(Refund::STATUS_PENDING, $broken->fresh()->status);
        $this->assertSame(Refund::STATUS_SUCCEEDED, $working->fresh()->status);
        $this->assertSame(Payment::STATUS_REFUNDED, $working->payment->status);
    }
}
