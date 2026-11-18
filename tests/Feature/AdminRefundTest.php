<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class AdminRefundTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.xendit.secret_key' => 'xnd_development_test_key']);
    }

    private function paidPayment(Inquiry $inquiry, array $overrides = []): Payment
    {
        return $inquiry->payments()->create(array_merge([
            'payment_session_id' => 'ps_admin_refund_' . uniqid(),
            'status' => Payment::STATUS_PAID,
            'amount' => '1000.00',
            'currency' => 'PHP',
            'payment_request_id' => 'pr_admin_refund',
            'paid_at' => now()->subDay(),
        ], $overrides));
    }

    private function fakeRefund(): void
    {
        Http::fake([
            'https://api.xendit.co/refunds' => Http::response([
                'id' => 'rf_admin_' . uniqid(),
                'payment_request_id' => 'pr_admin_refund',
                'status' => 'PENDING',
            ], 200),
        ]);
    }

    public function test_an_authorized_admin_can_request_a_full_refund(): void
    {
        Mail::fake();
        $this->fakeRefund();
        $admin = $this->makeAdmin();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $payment = $this->paidPayment($inquiry);

        $this->actingAs($admin, 'web')
            ->post(route('admin.inquiries.refund', $inquiry->id), [
                'reason' => 'REQUESTED_BY_CUSTOMER',
                'note' => 'Customer asked for a refund.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $refund = Refund::firstOrFail();

        $this->assertSame($payment->id, $refund->payment_id);
        $this->assertSame(Refund::STATUS_PENDING, $refund->status);
        $this->assertSame('REQUESTED_BY_CUSTOMER', $refund->reason);
        $this->assertSame('Customer asked for a refund.', $refund->note);
        $this->assertSame($admin->id, $refund->requested_by);
        $this->assertSame(Payment::STATUS_PAID, $payment->fresh()->status);
    }

    public function test_an_invalid_reason_is_rejected_without_creating_a_refund(): void
    {
        Http::fake();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->paidPayment($inquiry);

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.refund', $inquiry->id), ['reason' => 'BECAUSE'])
            ->assertRedirect()
            ->assertSessionHasErrors('reason');

        $this->assertDatabaseCount('refunds', 0);
        Http::assertNothingSent();
    }

    public function test_an_overlong_note_is_rejected(): void
    {
        Http::fake();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->paidPayment($inquiry);

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.refund', $inquiry->id), [
                'reason' => 'CANCELLATION',
                'note' => str_repeat('a', 501),
            ])
            ->assertRedirect()
            ->assertSessionHasErrors('note');

        $this->assertDatabaseCount('refunds', 0);
    }

    public function test_an_unpaid_payment_is_rejected_with_an_admin_facing_error(): void
    {
        Http::fake();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->paidPayment($inquiry, ['status' => Payment::STATUS_PENDING]);

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.refund', $inquiry->id), ['reason' => 'CANCELLATION'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Only a paid payment can be refunded.');

        $this->assertDatabaseCount('refunds', 0);
        Http::assertNothingSent();
    }

    public function test_an_unauthenticated_request_is_rejected(): void
    {
        Http::fake();
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->paidPayment($inquiry);

        $this->post(route('admin.inquiries.refund', $inquiry->id), ['reason' => 'CANCELLATION'])
            ->assertForbidden();

        $this->assertDatabaseCount('refunds', 0);
        Http::assertNothingSent();
    }

    public function test_the_admin_payload_exposes_refund_eligibility_for_a_paid_payment(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());
        $this->paidPayment($inquiry);

        $this->actingAs($this->makeAdmin(), 'web');

        $this->getJson('/admin/inquiries')
            ->assertOk()
            ->assertJsonPath('data.0.modal.paymentStatus', 'paid')
            ->assertJsonPath('data.0.modal.canRefund', true)
            ->assertJsonPath('data.0.modal.refundStatus', null)
            ->assertJsonPath('data.0.modal.refundAmount', '₱1,000.00')
            ->assertJsonPath('data.0.modal.refundFailureCode', null);
    }

    public function test_the_admin_payload_reports_pending_and_refunded_states_as_not_refundable(): void
    {
        $admin = $this->makeAdmin();

        $pendingInquiry = $this->makeInquiry($this->makeVenue());
        $pendingPayment = $this->paidPayment($pendingInquiry);
        $pendingPayment->refunds()->create([
            'refund_id' => 'rf_pending',
            'reference_id' => 'refund-pending',
            'payment_request_id' => 'pr_admin_refund',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => Refund::STATUS_PENDING,
            'payload' => [],
        ]);

        $refundedInquiry = $this->makeInquiry($this->makeVenue());
        $refundedPayment = $this->paidPayment($refundedInquiry, ['status' => Payment::STATUS_REFUNDED]);
        $refundedPayment->refunds()->create([
            'refund_id' => 'rf_succeeded',
            'reference_id' => 'refund-succeeded',
            'payment_request_id' => 'pr_admin_refund',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => Refund::STATUS_SUCCEEDED,
            'payload' => [],
        ]);

        $this->actingAs($admin, 'web');

        $pendingInquiry->forceFill(['created_at' => now()->subMinutes(2)])->save();
        $refundedInquiry->forceFill(['created_at' => now()->subMinute()])->save();

        $this->getJson('/admin/inquiries')
            ->assertOk()
            ->assertJsonPath('data.0.payment.status', 'refunded')
            ->assertJsonPath('data.0.modal.canRefund', false)
            ->assertJsonPath('data.0.modal.refundStatus', 'succeeded')
            ->assertJsonPath('data.1.modal.canRefund', false)
            ->assertJsonPath('data.1.modal.refundStatus', 'pending');
    }

    public function test_the_admin_payload_offers_a_retry_with_failure_details(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue());
        $payment = $this->paidPayment($inquiry);
        $payment->refunds()->create([
            'refund_id' => 'rf_failed',
            'reference_id' => 'refund-failed',
            'payment_request_id' => 'pr_admin_refund',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'note' => 'First attempt failed.',
            'status' => Refund::STATUS_FAILED,
            'failure_code' => 'INSUFFICIENT_BALANCE',
            'payload' => [],
        ]);

        $this->actingAs($this->makeAdmin(), 'web');

        $this->getJson('/admin/inquiries')
            ->assertOk()
            ->assertJsonPath('data.0.modal.canRefund', true)
            ->assertJsonPath('data.0.modal.refundStatus', 'failed')
            ->assertJsonPath('data.0.modal.refundFailureCode', 'INSUFFICIENT_BALANCE')
            ->assertJsonPath('data.0.modal.refundNote', 'First attempt failed.');
    }

    public function test_the_refund_payload_keeps_a_constant_query_count(): void
    {
        $venue = $this->makeVenue();

        $first = $this->makeInquiry($venue);
        $firstPayment = $this->paidPayment($first);
        $firstPayment->refunds()->create([
            'refund_id' => 'rf_budget_1',
            'reference_id' => 'refund-budget-1',
            'payment_request_id' => 'pr_admin_refund',
            'amount' => '1000.00',
            'currency' => 'PHP',
            'reason' => 'CANCELLATION',
            'status' => Refund::STATUS_FAILED,
            'payload' => [],
        ]);

        $this->actingAs($this->makeAdmin(), 'web');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/admin/inquiries')->assertOk();

        $singleRowQueries = count(DB::getQueryLog());

        foreach (range(2, 4) as $index) {
            $inquiry = $this->makeInquiry($venue);
            $payment = $this->paidPayment($inquiry);
            $payment->refunds()->create([
                'refund_id' => 'rf_budget_' . $index,
                'reference_id' => 'refund-budget-' . $index,
                'payment_request_id' => 'pr_admin_refund',
                'amount' => '1000.00',
                'currency' => 'PHP',
                'reason' => 'CANCELLATION',
                'status' => Refund::STATUS_PENDING,
                'payload' => [],
            ]);
        }

        DB::flushQueryLog();

        $this->getJson('/admin/inquiries')->assertOk();

        $this->assertSame($singleRowQueries, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }

    public function test_the_inquiry_shell_renders_the_refund_controls_and_distinct_refunded_badge(): void
    {
        $this->actingAs($this->makeAdmin(), 'web');

        $response = $this->get('/admin/inquiries');

        $response->assertOk();
        $response->assertSee('Refund', false);
        $response->assertSee('Full amount', false);
        $response->assertSee('CANCELLATION', false);
        $response->assertSee('I confirm a full refund', false);
        $response->assertSee('Retry full refund', false);
        $response->assertSee('refunded:', false);
        $response->assertDontSee('until refunds are available');
    }

    public function test_the_rules_page_visible_copy_is_unchanged(): void
    {
        $response = $this->get('/rules');

        $response->assertOk();
        $response->assertSee('A non-refundable reservation deposit is required', false);
        $response->assertDontSee('refund policy copy', false);
    }
}
