<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class AdminPaymentUiTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function payment(Inquiry $inquiry, array $overrides = []): Payment
    {
        return $inquiry->payments()->create(array_merge([
            'payment_session_id' => 'ps_ui_'.uniqid(),
            'status' => Payment::STATUS_PENDING,
            'amount' => '2500.00',
            'currency' => 'PHP',
            'payment_link_url' => 'https://checkout.xendit.co/web/'.uniqid(),
            'expires_at' => '2026-11-18 09:00:00',
        ], $overrides));
    }

    public function test_the_inquiries_page_shell_loads_then_the_list_json_shows_payment_details(): void
    {
        $inquiry = $this->makeInquiry($this->makeVenue(), ['full_name' => 'Payment Guest']);
        $payment = $this->payment($inquiry);

        $shell = $this->actingAs($this->makeAdmin(), 'web')->get('/admin/inquiries');

        $shell->assertOk();
        $shell->assertSee('Loading inquiries...');
        $shell->assertSee('Refresh');
        $shell->assertSee('Open payment link');
        $shell->assertSee('No payment link');
        $shell->assertDontSee('Payment Guest');
        $shell->assertDontSee('View Receipt');
        $shell->assertDontSee('Receipt Proof');

        $list = $this->getJson('/admin/inquiries');

        $list->assertOk();
        $list->assertJsonPath('total', 1);
        $list->assertJsonPath('data.0.full_name', 'Payment Guest');
        $list->assertJsonPath('data.0.status', 'pending');
        $list->assertJsonPath('data.0.payment.status', 'pending');
        $list->assertJsonPath('data.0.payment.amount', '₱2,500.00');
        $list->assertJsonPath('data.0.payment.expires_at', 'Nov 18, 2026 9:00 AM');
        $list->assertJsonPath('data.0.payment.payment_link_url', $payment->payment_link_url);
        $list->assertJsonPath('data.0.modal.customer', 'Payment Guest');
        $list->assertJsonPath('data.0.modal.paymentStatus', 'pending');
    }

    public function test_the_action_modal_targets_sessions_and_removes_proof_actions(): void
    {
        $pending = $this->makeInquiry($this->makeVenue(), ['full_name' => 'Pending Guest']);
        $this->payment($pending);

        $response = $this->actingAs($this->makeAdmin(), 'web')->get('/admin/inquiries');

        $response->assertOk();
        $response->assertSee('Send Payment Link');
        $response->assertSee('Payment Session');
        $response->assertSee('Requires the latest payment to be');
        $response->assertDontSee('Resend Payment Email');
        $response->assertDontSee('upload-proof', false);
        $response->assertDontSee('payment_proof', false);
    }

    public function test_the_inquiries_page_renders_a_refresh_control_targeting_the_current_query(): void
    {
        $this->actingAs($this->makeAdmin(), 'web');

        $response = $this->get('/admin/inquiries?status=pending');

        $response->assertOk();
        $response->assertSee('Refresh');
        $response->assertSee('/admin/inquiries?status=pending', false);
    }

    public function test_the_list_json_supports_status_filtering_and_pagination(): void
    {
        $venue = $this->makeVenue();

        foreach (range(1, 11) as $index) {
            $inquiry = $this->makeInquiry($venue, [
                'full_name' => "Pending Guest {$index}",
                'status' => 'pending',
            ]);
            $this->payment($inquiry, ['status' => Payment::STATUS_EXPIRED]);
        }

        $this->makeInquiry($venue, ['full_name' => 'Approved Guest', 'status' => 'approved']);

        $this->actingAs($this->makeAdmin(), 'web');

        $page = $this->getJson('/admin/inquiries?status=pending&page=2');

        $page->assertOk();
        $page->assertJsonStructure(['data', 'current_page', 'last_page', 'total', 'links', 'prev_page_url', 'next_page_url']);
        $page->assertJsonPath('current_page', 2);
        $page->assertJsonPath('last_page', 2);
        $page->assertJsonPath('total', 11);
        $page->assertJsonCount(1, 'data');
        $page->assertJsonPath('data.0.status', 'pending');
        $page->assertJsonPath('data.0.payment.status', 'expired');

        $this->getJson('/admin/inquiries?status=pending&page=2')
            ->assertJsonMissing(['full_name' => 'Approved Guest']);
    }

    public function test_the_list_json_runs_a_constant_number_of_queries_regardless_of_row_count(): void
    {
        $venue = $this->makeVenue();
        $this->makeInquiry($venue, ['full_name' => 'First Guest', 'status' => 'pending']);
        $this->actingAs($this->makeAdmin(), 'web');

        DB::flushQueryLog();
        DB::enableQueryLog();

        $this->getJson('/admin/inquiries')->assertOk();

        $singleRowQueries = count(DB::getQueryLog());

        foreach (range(2, 5) as $index) {
            $this->makeInquiry($venue, ['full_name' => "Guest {$index}", 'status' => 'pending']);
        }

        DB::flushQueryLog();

        $this->getJson('/admin/inquiries')->assertOk();

        $this->assertSame($singleRowQueries, count(DB::getQueryLog()));
        DB::disableQueryLog();
    }
}
