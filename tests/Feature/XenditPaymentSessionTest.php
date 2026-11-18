<?php

namespace Tests\Feature;

use App\Mail\PaymentLinkMail;
use App\Models\Inquiry;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\CreatesBookings;
use Tests\TestCase;

class XenditPaymentSessionTest extends TestCase
{
    use CreatesBookings;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-11-17 09:00:00'));

        config([
            'services.xendit.secret_key' => 'xnd_development_test_key',
            'services.xendit.callback_token' => 'test-callback-token',
            'services.xendit.session_expiry_hours' => 24,
            'services.xendit.country' => 'PH',
            'services.xendit.currency' => 'PHP',
            'services.xendit.allowed_channels' => ['CARDS', 'GCASH'],
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function fakeSessionCreation(array $overrides = []): void
    {
        Http::fake([
            'https://api.xendit.co/sessions' => Http::response(array_merge([
                'payment_session_id' => 'ps_new',
                'reference_id' => 'inquiry-1-reference',
                'status' => 'ACTIVE',
                'payment_link_url' => 'https://checkout.xendit.co/web/ps_new',
                'expires_at' => '2026-11-18T09:00:00+08:00',
                'payment_request_id' => 'pr_new',
                'payment_method' => 'GCASH',
            ], $overrides), 200),
        ]);
    }

    private function futureInquiry(array $overrides = []): Inquiry
    {
        return $this->makeInquiry($this->makeVenue(['price_per_hour' => 2500]), array_merge([
            'booking_date' => '2026-11-19',
            'start_time' => '09:00:00',
            'end_time' => '11:00:00',
        ], $overrides));
    }

    public function test_admin_can_create_a_session_and_the_link_is_emailed(): void
    {
        Mail::fake();
        $this->fakeSessionCreation();
        $admin = $this->makeAdmin();
        $inquiry = $this->futureInquiry();

        $this->actingAs($admin, 'web')
            ->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $payment = Payment::firstOrFail();

        $this->assertSame('ps_new', $payment->payment_session_id);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertSame('5000.00', $payment->amount);
        $this->assertSame('PHP', $payment->currency);
        $this->assertSame('https://checkout.xendit.co/web/ps_new', $payment->payment_link_url);
        $this->assertSame('pr_new', $payment->payment_request_id);
        $this->assertSame('GCASH', $payment->payment_method);
        $this->assertSame($inquiry->id, $payment->inquiry_id);
        $this->assertIsArray($payment->payload);

        Mail::assertQueued(PaymentLinkMail::class, function (PaymentLinkMail $mail) use ($inquiry) {
            return $mail->hasTo($inquiry->email)
                && $mail->payment->payment_session_id === 'ps_new';
        });

        $bookingReference = $inquiry->fresh()->booking_reference;

        Http::assertSent(function (Request $request) use ($bookingReference, $inquiry): bool {
            $payload = $request->data();

            return $request->url() === 'https://api.xendit.co/sessions'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Basic ' . base64_encode('xnd_development_test_key:'))
                && $payload['session_type'] === 'PAY'
                && $payload['mode'] === 'PAYMENT_LINK'
                && (float) $payload['amount'] === 5000.0
                && $payload['currency'] === 'PHP'
                && $payload['country'] === 'PH'
                && $payload['expires_at'] === '2026-11-18T09:00:00+08:00'
                && $payload['allowed_payment_channels'] === ['CARDS', 'GCASH']
                && $payload['metadata']['inquiry_id'] === (string) $inquiry->id
                && $payload['metadata']['booking_reference'] === $bookingReference
                && ! array_key_exists('customer', $payload)
                && ! array_key_exists('success_return_url', $payload)
                && ! array_key_exists('cancel_return_url', $payload);
        });
    }

    public function test_session_expiry_uses_the_event_start_when_it_is_sooner_than_the_window(): void
    {
        Mail::fake();
        $this->fakeSessionCreation(['expires_at' => '2026-11-18T08:00:00+08:00']);
        $admin = $this->makeAdmin();
        $inquiry = $this->futureInquiry([
            'booking_date' => '2026-11-18',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
        ]);

        $this->actingAs($admin, 'web')
            ->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect();

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.xendit.co/sessions'
                && $request['expires_at'] === '2026-11-18T08:00:00+08:00';
        });

        $this->assertSame('2026-11-18 08:00:00', Payment::firstOrFail()->expires_at->format('Y-m-d H:i:s'));
    }

    public function test_payment_link_mail_contains_the_link_amount_and_reference(): void
    {
        $inquiry = $this->futureInquiry(['booking_reference' => 'R000123']);
        $payment = $inquiry->payments()->create([
            'payment_session_id' => 'ps_mail',
            'status' => Payment::STATUS_PENDING,
            'amount' => '5000.00',
            'currency' => 'PHP',
            'payment_link_url' => 'https://checkout.xendit.co/web/ps_mail',
            'expires_at' => now()->addDay(),
        ]);

        $mail = new PaymentLinkMail($inquiry, $payment);
        $mail->build();
        $html = $mail->render();

        $this->assertSame('Payment link - R000123', $mail->subject);
        $this->assertStringContainsString('https://checkout.xendit.co/web/ps_mail', $html);
        $this->assertStringContainsString('5,000.00', $html);
        $this->assertStringContainsString('R000123', $html);
    }

    public function test_creation_is_refused_when_the_customer_email_is_missing(): void
    {
        Mail::fake();
        Http::fake();
        $inquiry = $this->futureInquiry(['email' => null]);

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_creation_is_refused_when_the_event_has_started_or_starts_too_soon(): void
    {
        Mail::fake();
        Http::fake();
        $admin = $this->makeAdmin();

        $past = $this->futureInquiry(['booking_date' => '2026-11-17', 'start_time' => '08:00:00']);
        $tooSoon = $this->futureInquiry(['booking_date' => '2026-11-17', 'start_time' => '09:30:00']);

        foreach ([$past, $tooSoon] as $inquiry) {
            $this->actingAs($admin, 'web')
                ->post(route('admin.inquiries.send-payment', $inquiry->id))
                ->assertRedirect()
                ->assertSessionHas('error');
        }

        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_creation_is_refused_for_a_zero_amount(): void
    {
        Mail::fake();
        Http::fake();
        $inquiry = $this->makeInquiry($this->makeVenue(['price_per_hour' => 0]));

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseCount('payments', 0);
        Http::assertNothingSent();
        Mail::assertNothingQueued();
    }

    public function test_a_gateway_failure_creates_no_payment_and_sends_no_email(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.xendit.co/sessions' => Http::response([
                'error_code' => 'INVALID_PAYMENT_CHANNEL',
                'message' => 'Payment channel is not available.',
            ], 400),
        ]);
        $inquiry = $this->futureInquiry();

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect()
            ->assertSessionHas('error', 'Unable to create the payment link: Payment channel is not available.');

        $this->assertDatabaseCount('payments', 0);
        Mail::assertNothingQueued();
    }

    public function test_resending_supersedes_and_cancels_the_previous_session(): void
    {
        Mail::fake();
        Http::fake([
            'https://api.xendit.co/sessions' => Http::response([
                'payment_session_id' => 'ps_new',
                'status' => 'ACTIVE',
                'payment_link_url' => 'https://checkout.xendit.co/web/ps_new',
                'expires_at' => '2026-11-18T09:00:00+08:00',
            ], 200),
            'https://api.xendit.co/sessions/ps_old/cancel' => Http::response([
                'payment_session_id' => 'ps_old',
                'status' => 'CANCELED',
            ], 200),
        ]);
        $inquiry = $this->futureInquiry();
        $old = $inquiry->payments()->create([
            'payment_session_id' => 'ps_old',
            'status' => Payment::STATUS_PENDING,
            'amount' => '5000.00',
            'currency' => 'PHP',
        ]);

        $this->actingAs($this->makeAdmin(), 'web')
            ->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(Payment::STATUS_SUPERSEDED, $old->fresh()->status);
        $this->assertSame(2, Payment::count());
        $this->assertTrue($inquiry->fresh()->currentPendingPayment()->is(Payment::latest('id')->first()));

        Http::assertSent(function (Request $request): bool {
            return $request->url() === 'https://api.xendit.co/sessions/ps_old/cancel'
                && $request->method() === 'POST';
        });
    }
}
