<?php

namespace Tests\Feature;

use App\Mail\InquiryStatusNotification;
use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class AdminPaymentGateTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.secret_key' => 'xnd_development_test_key']);
    }

    private function withPayment(Inquiry $inquiry, string $status, string $sessionId = 'ps_gate'): Payment
    {
        return $inquiry->payments()->create([
            'payment_session_id' => $sessionId,
            'status' => $status,
            'amount' => '1000.00',
            'currency' => 'PHP',
        ]);
    }

    public function test_approval_succeeds_when_the_latest_payment_is_paid(): void
    {
        Mail::fake();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->withPayment($inquiry, Payment::STATUS_PAID);

        $this->actingAs($this->makeAdmin(), 'web')
            ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('approved', $inquiry->fresh()->status);
        Mail::assertQueued(InquiryStatusNotification::class);
    }

    public function test_approval_is_rejected_without_a_paid_payment(): void
    {
        Mail::fake();
        $admin = $this->makeAdmin();
        $withoutPayment = $this->makeInquiry($this->makeVenue());
        $expired = $this->makeInquiry($this->makeVenue());
        $this->withPayment($expired, Payment::STATUS_EXPIRED, 'ps_expired');

        foreach ([$withoutPayment, $expired] as $inquiry) {
            $this->actingAs($admin, 'web')
                ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'approved'])
                ->assertRedirect()
                ->assertSessionHas('error');

            $this->assertSame('pending', $inquiry->fresh()->status);
        }

        Mail::assertNothingQueued();
    }

    public function test_declining_a_pending_payment_cancels_the_active_session(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.xendit.co/sessions/ps_gate/cancel' => Http::response([
                'payment_session_id' => 'ps_gate',
                'status' => 'CANCELED',
            ], 200),
        ]);
        $inquiry = $this->makeInquiry($this->makeVenue());
        $payment = $this->withPayment($inquiry, Payment::STATUS_PENDING);

        $this->actingAs($this->makeAdmin(), 'web')
            ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'declined'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('declined', $inquiry->fresh()->status);
        $this->assertSame(Payment::STATUS_CANCELED, $payment->fresh()->status);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.xendit.co/sessions/ps_gate/cancel');
    }

    public function test_decline_and_cancel_are_blocked_when_the_latest_payment_is_paid(): void
    {
        Mail::fake();
        Http::fake();
        $admin = $this->makeAdmin();

        foreach (['declined', 'cancelled'] as $status) {
            $inquiry = $this->makeInquiry($this->makeVenue());
            $payment = $this->withPayment($inquiry, Payment::STATUS_PAID, 'ps_paid_' . $status);

            $this->actingAs($admin, 'web')
                ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => $status])
                ->assertRedirect()
                ->assertSessionHas('error');

            $this->assertSame('pending', $inquiry->fresh()->status);
            $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        }

        Http::assertNothingSent();
    }

    public function test_cancelling_an_unpaid_inquiry_cancels_its_active_session(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.xendit.co/sessions/ps_cancel/cancel' => Http::response([
                'payment_session_id' => 'ps_cancel',
                'status' => 'CANCELED',
            ], 200),
        ]);
        $inquiry = $this->makeInquiry($this->makeVenue());
        $payment = $this->withPayment($inquiry, Payment::STATUS_PENDING, 'ps_cancel');

        $this->actingAs($this->makeAdmin(), 'web')
            ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame('cancelled', $inquiry->fresh()->status);
        $this->assertSame(Payment::STATUS_CANCELED, $payment->fresh()->status);
    }

    public function test_decline_and_cancel_are_allowed_after_a_successful_refund(): void
    {
        Mail::fake();
        Http::fake();
        $admin = $this->makeAdmin();

        foreach (['declined', 'cancelled'] as $status) {
            $inquiry = $this->makeInquiry($this->makeVenue());
            $payment = $this->withPayment($inquiry, Payment::STATUS_REFUNDED, 'ps_refunded_' . $status);

            $this->actingAs($admin, 'web')
                ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => $status])
                ->assertRedirect()
                ->assertSessionHas('success');

            $this->assertSame($status, $inquiry->fresh()->status);
            $this->assertSame(Payment::STATUS_REFUNDED, $payment->fresh()->status);
        }

        Http::assertNothingSent();
    }

    public function test_a_pending_refund_does_not_unblock_decline_or_cancel(): void
    {
        Mail::fake();
        Http::fake();
        $admin = $this->makeAdmin();

        foreach (['declined', 'cancelled'] as $status) {
            $inquiry = $this->makeInquiry($this->makeVenue());
            $payment = $this->withPayment($inquiry, Payment::STATUS_PAID, 'ps_refund_pending_' . $status);
            $payment->refunds()->create([
                'refund_id' => 'rf_pending_' . $status,
                'reference_id' => 'refund-pending-' . $status,
                'payment_request_id' => 'pr_gate',
                'amount' => '1000.00',
                'currency' => 'PHP',
                'reason' => 'CANCELLATION',
                'status' => 'pending',
                'payload' => [],
            ]);

            $this->actingAs($admin, 'web')
                ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => $status])
                ->assertRedirect()
                ->assertSessionHas('error');

            $this->assertSame('pending', $inquiry->fresh()->status);
            $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
        }

        Http::assertNothingSent();
    }

    public function test_a_refunded_payment_cannot_be_approved(): void
    {
        Mail::fake();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->withPayment($inquiry, Payment::STATUS_REFUNDED, 'ps_refunded_approve');

        $this->actingAs($this->makeAdmin(), 'web')
            ->patch(route('admin.inquiries.update-status', $inquiry->id), ['status' => 'approved'])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertSame('pending', $inquiry->fresh()->status);
        Mail::assertNothingQueued();
    }
}
