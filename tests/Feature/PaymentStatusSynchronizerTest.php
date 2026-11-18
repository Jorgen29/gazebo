<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Services\PaymentStatusSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class PaymentStatusSynchronizerTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    private function pendingPayment(): Payment
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        return $inquiry->payments()->create([
            'payment_session_id' => 'ps_sync_' . uniqid(),
            'status' => Payment::STATUS_PENDING,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'payload' => ['status' => 'ACTIVE'],
        ]);
    }

    public function test_completed_marks_the_payment_paid_and_merges_details(): void
    {
        $payment = $this->pendingPayment();

        (new PaymentStatusSynchronizer())->apply($payment, 'COMPLETED', [
            'payment_id' => 'pay_123',
            'payment_request_id' => 'pr_123',
            'payment_method' => ['type' => 'GCASH'],
            'paid_at' => '2026-11-17T10:30:00+08:00',
        ], 'payment_session.completed');

        $payment->refresh();

        $this->assertSame(Payment::STATUS_PAID, $payment->status);
        $this->assertSame('pay_123', $payment->payment_id);
        $this->assertSame('pr_123', $payment->payment_request_id);
        $this->assertSame('GCASH', $payment->payment_method);
        $this->assertSame('2026-11-17 10:30:00', $payment->paid_at->format('Y-m-d H:i:s'));
        $this->assertSame('payment_session.completed', $payment->payload['last_event']);
        $this->assertSame('pay_123', $payment->payload['payment_id']);
    }

    public function test_expired_and_canceled_are_applied(): void
    {
        $expired = $this->pendingPayment();
        (new PaymentStatusSynchronizer())->apply($expired, 'EXPIRED', [], 'payment_session.expired');
        $this->assertSame(Payment::STATUS_EXPIRED, $expired->fresh()->status);

        $canceled = $this->pendingPayment();
        (new PaymentStatusSynchronizer())->apply($canceled, 'CANCELED', [], 'payment_session.canceled');
        $this->assertSame(Payment::STATUS_CANCELED, $canceled->fresh()->status);
    }

    public function test_duplicate_completion_keeps_the_original_paid_timestamp(): void
    {
        $payment = $this->pendingPayment();
        $synchronizer = new PaymentStatusSynchronizer();

        $synchronizer->apply($payment, 'COMPLETED', ['paid_at' => '2026-11-17T10:30:00+08:00'], 'payment_session.completed');
        $firstPaidAt = $payment->fresh()->paid_at;

        $synchronizer->apply($payment->fresh(), 'COMPLETED', ['paid_at' => '2026-11-17T11:30:00+08:00'], 'payment_session.completed');

        $this->assertTrue($payment->fresh()->paid_at->equalTo($firstPaidAt));
        $this->assertSame(1, $payment->inquiry->payments()->count());
    }

    public function test_paid_is_terminal_and_is_not_regressed(): void
    {
        $payment = $this->pendingPayment();
        $synchronizer = new PaymentStatusSynchronizer();

        $synchronizer->apply($payment, 'COMPLETED', [], 'payment_session.completed');
        $synchronizer->apply($payment->fresh(), 'EXPIRED', [], 'payment_session.expired');
        $synchronizer->apply($payment->fresh(), 'CANCELED', [], 'payment_session.canceled');

        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }

    public function test_unknown_status_leaves_the_payment_pending(): void
    {
        $payment = $this->pendingPayment();

        (new PaymentStatusSynchronizer())->apply($payment, 'ACTIVE', [], 'payment_session.updated');

        $this->assertSame(Payment::STATUS_PENDING, $payment->fresh()->status);
    }
}
