<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class PaymentModelTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    public function test_payment_casts_its_attributes_and_reports_paid_state(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        $payment = $inquiry->payments()->create([
            'payment_session_id' => 'ps_test_1',
            'reference_id' => 'inquiry-1-test',
            'status' => Payment::STATUS_PAID,
            'amount' => '4500.00',
            'currency' => 'PHP',
            'paid_at' => now(),
            'expires_at' => now()->addDay(),
            'payload' => ['status' => 'COMPLETED'],
        ]);

        $payment->refresh();

        $this->assertSame('4500.00', $payment->amount);
        $this->assertSame('COMPLETED', $payment->payload['status']);
        $this->assertTrue($payment->paid_at->isSameDay(now()));
        $this->assertTrue($payment->expires_at->isSameDay(now()->addDay()));
        $this->assertTrue($payment->isPaid());
        $this->assertFalse($payment->isPending());
        $this->assertTrue($payment->inquiry->is($inquiry));
    }

    public function test_latest_payment_wins_and_only_paid_counts_as_paid(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        $first = $inquiry->payments()->create([
            'status' => Payment::STATUS_EXPIRED,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);

        $second = $inquiry->payments()->create([
            'status' => Payment::STATUS_PAID,
            'amount' => '2000.00',
            'currency' => 'PHP',
        ]);

        $this->assertTrue($inquiry->latestPayment()->is($second));
        $this->assertTrue($inquiry->latestPaidPayment()->is($second));
        $this->assertNull($inquiry->currentPendingPayment());

        $third = $inquiry->payments()->create([
            'status' => Payment::STATUS_PENDING,
            'amount' => '2000.00',
            'currency' => 'PHP',
        ]);

        $this->assertTrue($inquiry->latestPayment()->is($third));
        $this->assertTrue($inquiry->latestPaidPayment()->is($second));
        $this->assertTrue($inquiry->currentPendingPayment()->is($third));
    }

    public function test_expired_canceled_and_superseded_payments_are_not_paid(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        foreach ([Payment::STATUS_EXPIRED, Payment::STATUS_CANCELED, Payment::STATUS_SUPERSEDED] as $status) {
            $payment = $inquiry->payments()->create([
                'status' => $status,
                'amount' => '1000.00',
                'currency' => 'PHP',
            ]);

            $this->assertFalse($payment->isPaid());
        }

        $this->assertNull($inquiry->latestPaidPayment());
    }

    public function test_refunded_payments_are_not_paid_and_expose_their_refunds(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());

        $payment = $inquiry->payments()->create([
            'status' => Payment::STATUS_REFUNDED,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'paid_at' => now()->subDay(),
        ]);

        $payment->refunds()->create([
            'reference_id' => 'refund-1-uuid',
            'payment_request_id' => 'pr_123',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => 'succeeded',
            'payload' => [],
        ]);

        $this->assertTrue($payment->isRefunded());
        $this->assertFalse($payment->isPaid());
        $this->assertSame(1, $payment->refunds()->count());
        $this->assertSame('refund-1-uuid', $payment->refunds()->first()->reference_id);
        $this->assertNotNull($payment->paid_at);
    }

    public function test_where_latest_payment_paid_scope_only_matches_current_paid_payment(): void
    {
        $venue = $this->makeVenue();
        $paid = $this->makeInquiry($venue);
        $unpaid = $this->makeInquiry($venue);

        $paid->payments()->create([
            'status' => Payment::STATUS_PAID,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);

        $unpaid->payments()->create([
            'status' => Payment::STATUS_PAID,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);
        $unpaid->payments()->create([
            'status' => Payment::STATUS_PENDING,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);

        $matches = Inquiry::query()->whereLatestPaymentPaid()->pluck('id')->all();

        $this->assertSame([$paid->id], $matches);
    }
}
