<?php

namespace Tests\Feature;

use App\Exceptions\XenditGatewayException;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\RefundService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class RefundServiceTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.secret_key' => 'xnd_development_test_key']);
    }

    private function paidPayment(array $overrides = []): Payment
    {
        $inquiry = $this->makeInquiry($this->makeVenue(), ['booking_reference' => 'R000123']);

        return $inquiry->payments()->create(array_merge([
            'payment_session_id' => 'ps_refund_' . uniqid(),
            'status' => Payment::STATUS_PAID,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'payment_request_id' => 'pr_refund_123',
            'paid_at' => now()->subDay(),
        ], $overrides));
    }

    private function fakeRefund(array $overrides = [], int $status = 200): void
    {
        Http::fake([
            'https://api.xendit.co/refunds' => Http::response(array_merge([
                'id' => 'rf_123',
                'payment_request_id' => 'pr_refund_123',
                'payment_id' => 'pay_123',
                'reference_id' => 'refund-reference',
                'status' => 'PENDING',
                'failure_code' => null,
                'refund_fee_amount' => 0,
                'created' => '2026-11-18T09:00:00+08:00',
                'updated' => '2026-11-18T09:00:01+08:00',
            ], $overrides), $status),
        ]);
    }

    private function service(): RefundService
    {
        return app(RefundService::class);
    }

    public function test_initiating_a_refund_records_a_pending_full_refund_and_calls_the_gateway(): void
    {
        Mail::fake();
        $this->fakeRefund();
        $payment = $this->paidPayment();
        $admin = $this->makeAdmin();

        $refund = $this->service()->initiate($payment, 'CANCELLATION', 'Customer changed plans.', $admin);

        $this->assertSame(Refund::STATUS_PENDING, $refund->status);
        $this->assertSame('rf_123', $refund->refund_id);
        $this->assertSame('1000.00', $refund->amount);
        $this->assertSame('PHP', $refund->currency);
        $this->assertSame('CANCELLATION', $refund->reason);
        $this->assertSame('Customer changed plans.', $refund->note);
        $this->assertSame($payment->id, $refund->payment_id);
        $this->assertTrue($refund->requestedBy->is($admin));
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);

        Http::assertSent(function (Request $request) use ($refund): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.xendit.co/refunds'
                && $request->method() === 'POST'
                && $payload['payment_request_id'] === 'pr_refund_123'
                && str_starts_with($payload['reference_id'], 'refund-'.$refund->payment_id.'-')
                && (float) $payload['amount'] === 1000.0
                && $payload['currency'] === 'PHP'
                && $payload['reason'] === 'CANCELLATION';
        });

        Mail::assertNothingQueued();
    }

    public function test_an_immediate_success_marks_the_payment_refunded_and_queues_one_email(): void
    {
        Mail::fake();
        $this->fakeRefund(['status' => 'SUCCEEDED']);
        $payment = $this->paidPayment();

        $refund = $this->service()->initiate($payment, 'REQUESTED_BY_CUSTOMER', null, $this->makeAdmin());

        $this->assertTrue($refund->fresh()->isSucceeded());
        $this->assertSame(Payment::STATUS_REFUNDED, $payment->fresh()->status);
        Mail::assertQueued(\App\Mail\RefundSucceededMail::class, 1);
    }

    public function test_a_partial_amount_is_rejected_without_creating_a_refund(): void
    {
        Http::fake();
        $payment = $this->paidPayment();

        try {
            $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin(), '500.00');
            $this->fail('A partial refund should be rejected.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('Refunds must be for the full payment amount.', $exception->getMessage());
        }

        $this->assertDatabaseCount('refunds', 0);
        Http::assertNothingSent();
    }

    public function test_a_full_amount_argument_is_accepted(): void
    {
        $this->fakeRefund();
        $payment = $this->paidPayment();

        $refund = $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin(), '1000.0');

        $this->assertSame('1000.00', $refund->amount);
    }

    public function test_a_payment_without_a_payment_request_identifier_cannot_be_refunded(): void
    {
        Http::fake();
        $payment = $this->paidPayment(['payment_request_id' => null]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('payment request identifier');

        try {
            $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
        } finally {
            $this->assertDatabaseCount('refunds', 0);
            Http::assertNothingSent();
        }
    }

    public function test_an_unpaid_payment_cannot_be_refunded(): void
    {
        Http::fake();
        $payment = $this->paidPayment(['status' => Payment::STATUS_PENDING]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only a paid payment can be refunded.');

        try {
            $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
        } finally {
            $this->assertDatabaseCount('refunds', 0);
        }
    }

    public function test_an_already_refunded_payment_cannot_be_refunded_again(): void
    {
        Http::fake();
        $payment = $this->paidPayment(['status' => Payment::STATUS_REFUNDED]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This payment has already been refunded.');

        $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
    }

    public function test_a_pending_refund_blocks_another_attempt(): void
    {
        Http::fake();
        $payment = $this->paidPayment();
        $payment->refunds()->create([
            'reference_id' => 'refund-existing',
            'payment_request_id' => 'pr_refund_123',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => Refund::STATUS_PENDING,
            'payload' => [],
        ]);

        try {
            $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
            $this->fail('A pending refund should block another attempt.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame('A refund for this payment is already in progress.', $exception->getMessage());
        }

        $this->assertDatabaseCount('refunds', 1);
        Http::assertNothingSent();
    }

    public function test_a_successful_refund_blocks_another_attempt(): void
    {
        Http::fake();
        $payment = $this->paidPayment();
        $payment->refunds()->create([
            'reference_id' => 'refund-existing',
            'payment_request_id' => 'pr_refund_123',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => Refund::STATUS_SUCCEEDED,
            'payload' => [],
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('This payment has already been refunded.');

        $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
    }

    public function test_a_gateway_failure_marks_the_attempt_failed_and_keeps_the_payment_paid(): void
    {
        $this->fakeRefund(['message' => 'Refund rejected.'], 400);
        $payment = $this->paidPayment();

        try {
            $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
            $this->fail('A gateway failure should surface to the caller.');
        } catch (XenditGatewayException $exception) {
            $this->assertSame('Refund rejected.', $exception->getMessage());
        }

        $refund = Refund::firstOrFail();

        $this->assertSame(Refund::STATUS_FAILED, $refund->status);
        $this->assertNull($refund->refund_id);
        $this->assertNull($refund->failure_code);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }

    public function test_a_gateway_response_without_a_refund_identifier_is_treated_as_a_failure(): void
    {
        $this->fakeRefund(['id' => null]);
        $payment = $this->paidPayment();

        try {
            $this->service()->initiate($payment, 'CANCELLATION', null, $this->makeAdmin());
            $this->fail('A malformed refund response should fail.');
        } catch (XenditGatewayException $exception) {
            $this->assertSame('Xendit did not return a refund identifier.', $exception->getMessage());
        }

        $this->assertSame(Refund::STATUS_FAILED, Refund::firstOrFail()->status);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }
}
