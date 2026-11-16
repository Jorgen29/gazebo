<?php

namespace Tests\Feature;

use App\Contracts\XenditGateway;
use App\Mail\PaymentLinkMail;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class InquiryPaymentSendTest extends TestCase
{
    use RefreshDatabase;

    private function fakeGateway(array &$created = [], array &$expired = []): void
    {
        $gateway = new class($created, $expired) implements XenditGateway {
            public function __construct(public array &$created, public array &$expired) {}
            public function createInvoice(array $data): array
            {
                $this->created[] = $data;
                $id = 'inv_' . count($this->created);
                return [
                    'id' => $id,
                    'invoice_url' => 'https://checkout.test/' . $id,
                    'status' => 'PENDING',
                    'expires_at' => now()->addDay()->toISOString(),
                    'paid_at' => null,
                    'payment_method' => null,
                    'raw' => ['id' => $id, 'status' => 'PENDING'],
                ];
            }
            public function getInvoice(string $invoiceId): array
            {
                return ['id' => $invoiceId, 'invoice_url' => null, 'status' => 'PENDING', 'raw' => []];
            }
            public function expireInvoice(string $invoiceId): void
            {
                $this->expired[] = $invoiceId;
            }
        };

        $this->app->instance(XenditGateway::class, $gateway);
    }

    public function test_admin_can_send_payment_link_creating_payment_and_invoice(): void
    {
        Mail::fake();
        $created = []; $expired = [];
        $this->fakeGateway($created, $expired);

        $admin = User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($admin, 'web');

        $venue = Venue::create([
            'title' => 'Pay Venue',
            'description' => null,
            'capacity' => 50,
            'price_per_hour' => 2000,
            'image' => null,
            'showcase_images' => [],
            'features' => [],
            'is_active' => true,
        ]);
        $inquiry = Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'email' => 'client@example.com',
            'start_time' => '10:00:00',
            'end_time' => '12:00:00',
            'status' => 'pending',
        ]);

        $this->post(route('admin.inquiries.send-payment', $inquiry->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertCount(1, $created);
        $this->assertDatabaseCount('payments', 1);
        $payment = Payment::first();
        $this->assertSame($inquiry->id, $payment->inquiry_id);
        $this->assertSame('4000.00', (string) $payment->amount);
        $this->assertSame(Payment::STATUS_PENDING, $payment->status);
        $this->assertNotNull($payment->gateway_invoice_id);
        $this->assertNotNull($payment->invoice_url);

        Mail::assertQueued(PaymentLinkMail::class, function ($mail) use ($inquiry, $payment) {
            return $mail->hasTo($inquiry->email)
                && $mail->inquiry->is($inquiry)
                && $mail->payment->is($payment);
        });

        $this->assertSame($payment->id, $inquiry->fresh()->currentPayment()?->id);
    }

    public function test_resend_creates_new_current_invoice_and_supersedes_prior_pending(): void
    {
        Mail::fake();
        $created = []; $expired = [];
        $this->fakeGateway($created, $expired);

        $admin = User::factory()->create([
            'email' => 'admin@gmail.com',
            'username' => 'admin',
            'password' => bcrypt('password123'),
        ]);
        $this->actingAs($admin, 'web');

        $venue = Venue::create([
            'title' => 'Resend Venue',
            'description' => null,
            'capacity' => 50,
            'price_per_hour' => 1000,
            'image' => null,
            'showcase_images' => [],
            'features' => [],
            'is_active' => true,
        ]);
        $inquiry = Inquiry::factory()->create([
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'email' => 'client@example.com',
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
            'status' => 'pending',
        ]);

        $this->post(route('admin.inquiries.send-payment', $inquiry->id))->assertRedirect();
        $this->post(route('admin.inquiries.send-payment', $inquiry->id))->assertRedirect();

        $this->assertCount(2, $created);
        $this->assertDatabaseCount('payments', 2);
        $this->assertCount(1, $expired);

        $payments = Payment::where('inquiry_id', $inquiry->id)->orderBy('id')->get();
        $this->assertSame(Payment::STATUS_SUPERSEDED, $payments[0]->status);
        $this->assertSame(Payment::STATUS_PENDING, $payments[1]->status);
        $this->assertSame($payments[1]->id, $inquiry->fresh()->currentPayment()?->id);
    }

    public function test_inquiry_submission_does_not_create_invoice(): void
    {
        Mail::fake();
        $venue = Venue::create([
            'title' => 'Submit Venue',
            'description' => null,
            'capacity' => 50,
            'price_per_hour' => 1000,
            'image' => null,
            'showcase_images' => [],
            'features' => [],
            'is_active' => true,
        ]);

        $this->post('/book/submit', [
            'venue_id' => (string) $venue->id,
            'venue_title' => $venue->title,
            'booking_date' => '2026-12-01',
            'start_time' => '09:00',
            'end_time' => '11:00',
            'full_name' => 'Guest',
            'email' => 'guest@example.com',
            'email_contact' => '0917',
        ])->assertRedirect();

        $this->assertDatabaseCount('payments', 0);
        $this->assertDatabaseHas('inquiries', ['email' => 'guest@example.com', 'status' => 'pending']);
    }
}