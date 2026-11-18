<?php

namespace Tests\Feature;

use App\Mail\RefundSucceededMail;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\RefundStatusSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class RefundStatusSynchronizerTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    private function pendingRefund(string $status = Refund::STATUS_PENDING, ?string $bookingReference = null): Refund
    {
        $inquiry = $this->makeInquiry($this->makeVenue(), ['booking_reference' => $bookingReference]);

        $payment = $inquiry->payments()->create([
            'payment_session_id' => 'ps_sync_refund_' . uniqid(),
            'status' => Payment::STATUS_PAID,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'payment_request_id' => 'pr_sync_refund',
            'paid_at' => now()->subDays(2),
        ]);

        return $payment->refunds()->create([
            'refund_id' => 'rf_sync_' . uniqid(),
            'reference_id' => 'refund-reference',
            'payment_request_id' => 'pr_sync_refund',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => $status,
            'payload' => ['status' => 'PENDING'],
        ]);
    }

    private function synchronizer(): RefundStatusSynchronizer
    {
        return new RefundStatusSynchronizer();
    }

    public function test_success_marks_the_refund_succeeded_flips_the_payment_and_sends_one_email(): void
    {
        Mail::fake();
        $refund = $this->pendingRefund();
        $paidAt = $refund->payment->paid_at;

        $this->synchronizer()->apply($refund, 'SUCCEEDED', [
            'status' => 'SUCCEEDED',
            'updated' => '2026-11-18T10:30:00+08:00',
        ], 'refund.succeeded');

        $refund->refresh();
        $payment = $refund->payment;

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->status);
        $this->assertSame('2026-11-18 10:30:00', $refund->refunded_at->format('Y-m-d H:i:s'));
        $this->assertSame('refund.succeeded', $refund->payload['last_event']);
        $this->assertSame(Payment::STATUS_REFUNDED, $payment->status);
        $this->assertTrue($payment->paid_at->equalTo($paidAt));
        Mail::assertQueued(RefundSucceededMail::class, function (RefundSucceededMail $mail) use ($refund): bool {
            return $mail->refund->is($refund)
                && $mail->hasTo($refund->payment->inquiry->email);
        });
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_failed_and_cancelled_keep_the_payment_paid_and_store_the_failure_code(): void
    {
        Mail::fake();

        $failed = $this->pendingRefund();
        $this->synchronizer()->apply($failed, 'FAILED', [
            'failure_code' => 'INSUFFICIENT_BALANCE',
        ], 'refund.failed');

        $failed->refresh();
        $this->assertSame(Refund::STATUS_FAILED, $failed->status);
        $this->assertSame('INSUFFICIENT_BALANCE', $failed->failure_code);
        $this->assertNull($failed->refunded_at);
        $this->assertSame(Payment::STATUS_PAID, $failed->payment->status);

        $cancelled = $this->pendingRefund();
        $this->synchronizer()->apply($cancelled, 'CANCELLED', [], 'refund.cancelled');

        $cancelled->refresh();
        $this->assertSame(Refund::STATUS_CANCELLED, $cancelled->status);
        $this->assertSame(Payment::STATUS_PAID, $cancelled->payment->status);

        Mail::assertNothingQueued();
    }

    public function test_canceled_spelling_is_also_accepted(): void
    {
        Mail::fake();
        $refund = $this->pendingRefund();

        $this->synchronizer()->apply($refund, 'CANCELED', [], 'refund.canceled');

        $this->assertSame(Refund::STATUS_CANCELLED, $refund->fresh()->status);
    }

    public function test_pending_is_applied_without_changing_the_payment(): void
    {
        Mail::fake();
        $refund = $this->pendingRefund();

        $this->synchronizer()->apply($refund, 'PENDING', ['status' => 'PENDING'], 'refund.pending');

        $this->assertSame(Refund::STATUS_PENDING, $refund->fresh()->status);
        $this->assertSame(Payment::STATUS_PAID, $refund->payment->status);
        Mail::assertNothingQueued();
    }

    public function test_unknown_status_only_updates_the_payload(): void
    {
        Mail::fake();
        $refund = $this->pendingRefund();

        $this->synchronizer()->apply($refund, 'SOMETHING_NEW', ['note' => 'extra'], 'refund.updated');

        $refund->refresh();
        $this->assertSame(Refund::STATUS_PENDING, $refund->status);
        $this->assertSame('extra', $refund->payload['note']);
        Mail::assertNothingQueued();
    }

    public function test_duplicate_success_does_not_resend_the_email_or_change_the_refunded_timestamp(): void
    {
        Mail::fake();
        $refund = $this->pendingRefund();

        $this->synchronizer()->apply($refund, 'SUCCEEDED', ['updated' => '2026-11-18T10:30:00+08:00'], 'refund.succeeded');
        $refundedAt = $refund->fresh()->refunded_at;

        $this->synchronizer()->apply($refund->fresh(), 'SUCCEEDED', ['updated' => '2026-11-19T10:30:00+08:00'], 'refund.succeeded');

        $this->assertTrue($refund->fresh()->refunded_at->equalTo($refundedAt));
        $this->assertSame(Payment::STATUS_REFUNDED, $refund->payment->status);
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_a_success_is_not_regressed_by_a_late_failure(): void
    {
        Mail::fake();
        $refund = $this->pendingRefund();

        $this->synchronizer()->apply($refund, 'SUCCEEDED', [], 'refund.succeeded');
        $this->synchronizer()->apply($refund->fresh(), 'FAILED', ['failure_code' => 'LATE'], 'refund.failed');

        $refund->refresh();

        $this->assertSame(Refund::STATUS_SUCCEEDED, $refund->status);
        $this->assertNull($refund->failure_code);
        $this->assertSame(Payment::STATUS_REFUNDED, $refund->payment->status);
        Mail::assertQueued(RefundSucceededMail::class, 1);
    }

    public function test_the_refund_email_contains_the_reference_amount_and_original_payment_method_wording(): void
    {
        $refund = $this->pendingRefund(Refund::STATUS_SUCCEEDED, 'R000456');
        $mail = new RefundSucceededMail($refund);
        $mail->build();
        $html = $mail->render();

        $this->assertSame('Refund confirmation - R000456', $mail->subject);
        $this->assertStringContainsString('R000456', $html);
        $this->assertStringContainsString('1,000.00', $html);
        $this->assertStringContainsString('original payment method', $html);
        $this->assertStringNotContainsString('business days', $html);
        $this->assertStringNotContainsString('days', $html);
    }
}
