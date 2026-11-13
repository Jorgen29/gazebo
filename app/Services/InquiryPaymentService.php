<?php

namespace App\Services;

use App\Contracts\XenditGateway;
use App\Mail\PaymentLinkMail;
use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class InquiryPaymentService
{
    public function __construct(
        private readonly XenditGateway $gateway,
        private readonly InquiryAmountCalculator $calculator,
    ) {}

    public function sendPaymentLink(Inquiry $inquiry): Payment
    {
        $inquiry->getBookingReference();

        $amount = $this->calculator->calculate($inquiry);
        $expiryHours = max((int) config('services.xendit.invoice_expiry_hours', 24), 1);
        $durationSeconds = $expiryHours * 3600;

        return DB::transaction(function () use ($inquiry, $amount, $durationSeconds) {
            // Supersede prior pending invoices for current-payment selection.
            $pending = $inquiry->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->get();

            foreach ($pending as $old) {
                $old->update(['status' => Payment::STATUS_SUPERSEDED]);
                if ($old->gateway_invoice_id) {
                    $this->gateway->expireInvoice((string) $old->gateway_invoice_id);
                }
            }

            $payment = $inquiry->payments()->create([
                'amount' => $amount,
                'currency' => (string) config('services.xendit.currency', 'PHP'),
                'gateway' => 'xendit',
                'status' => Payment::STATUS_PENDING,
                'payload' => null,
            ]);

            $invoice = $this->gateway->createInvoice([
                'external_id' => 'inquiry-' . $inquiry->id . '-payment-' . $payment->id,
                'amount' => $amount,
                'payer_email' => $inquiry->email ?: null,
                'description' => 'Booking payment for ' . ($inquiry->booking_reference ?? ('#' . $inquiry->id)) . ' - ' . $inquiry->venue_title,
                'invoice_duration' => $durationSeconds,
                'currency' => (string) config('services.xendit.currency', 'PHP'),
            ]);

            $expiresAt = null;
            if (! empty($invoice['expires_at'])) {
                try {
                    $expiresAt = \Carbon\Carbon::parse($invoice['expires_at']);
                } catch (\Throwable $e) {
                    $expiresAt = null;
                }
            }
            if (! $expiresAt) {
                $expiresAt = now()->addSeconds($durationSeconds);
            }

            $payment->update([
                'gateway_invoice_id' => $invoice['id'] ?? null,
                'invoice_url' => $invoice['invoice_url'] ?? null,
                'expires_at' => $expiresAt,
                'payload' => $invoice['raw'] ?? $invoice,
            ]);

            if ($inquiry->email) {
                Mail::to($inquiry->email)->queue(new PaymentLinkMail($inquiry->fresh(), $payment->fresh()));
            }

            return $payment->fresh();
        });
    }
}