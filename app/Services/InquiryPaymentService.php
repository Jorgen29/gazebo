<?php

namespace App\Services;

use App\Contracts\XenditGateway;
use App\Exceptions\XenditGatewayException;
use App\Mail\PaymentLinkMail;
use App\Models\Inquiry;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class InquiryPaymentService
{
    public function __construct(
        private readonly XenditGateway $gateway,
        private readonly InquiryAmountCalculator $calculator,
        private readonly PaymentSessionWindow $window
    ) {
    }

    /**
     * Create a Xendit Payment Session for an inquiry, superseding any active session.
     */
    public function createSession(Inquiry $inquiry): Payment
    {
        if (blank($inquiry->email)) {
            throw new InvalidArgumentException('Customer email address is missing.');
        }

        $amount = $this->calculator->calculate($inquiry);
        $expiresAt = $this->window->resolve($inquiry);
        $bookingReference = $inquiry->getBookingReference();
        $referenceId = 'inquiry-' . $inquiry->id . '-' . Str::uuid();

        $payload = [
            'reference_id' => $referenceId,
            'session_type' => 'PAY',
            'mode' => 'PAYMENT_LINK',
            'amount' => (float) $amount,
            'currency' => config('services.xendit.currency', 'PHP'),
            'country' => config('services.xendit.country', 'PH'),
            'expires_at' => $expiresAt->toIso8601String(),
            'description' => Str::limit(
                "Booking {$bookingReference} - {$inquiry->venue_title}",
                255,
                ''
            ),
            'allowed_payment_channels' => config('services.xendit.allowed_channels', []),
            'metadata' => [
                'inquiry_id' => (string) $inquiry->id,
                'booking_reference' => $bookingReference,
            ],
        ];

        $session = $this->gateway->createSession($payload);

        if (blank($session['payment_session_id'] ?? null)) {
            throw new XenditGatewayException('Xendit did not return a payment session id.');
        }

        [$payment, $superseded] = DB::transaction(function () use ($inquiry, $session, $referenceId, $amount) {
            $locked = Inquiry::query()->lockForUpdate()->findOrFail($inquiry->id);

            $superseded = $locked->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->get();

            $locked->payments()
                ->where('status', Payment::STATUS_PENDING)
                ->update(['status' => Payment::STATUS_SUPERSEDED]);

            $payment = $locked->payments()->create([
                'payment_session_id' => $session['payment_session_id'],
                'reference_id' => $session['reference_id'] ?? $referenceId,
                'status' => Payment::STATUS_PENDING,
                'amount' => $amount,
                'currency' => $session['currency'] ?? config('services.xendit.currency', 'PHP'),
                'payment_link_url' => $session['payment_link_url'] ?? null,
                'payment_request_id' => $session['payment_request_id'] ?? null,
                'payment_id' => $session['payment_id'] ?? null,
                'payment_method' => $session['payment_method'] ?? null,
                'expires_at' => $session['expires_at'] ?? null,
                'payload' => $session['raw'] ?? $session,
            ]);

            return [$payment, $superseded];
        });

        foreach ($superseded as $oldPayment) {
            $this->cancelRemoteSession($oldPayment);
        }

        try {
            Mail::to($inquiry->email)->queue(new PaymentLinkMail($inquiry, $payment));
        } catch (Throwable $exception) {
            Log::error('Payment link email could not be queued.', [
                'inquiry_id' => $inquiry->id,
                'payment_id' => $payment->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return $payment;
    }

    /**
     * Cancel every active session for an inquiry and mark the payments canceled.
     */
    public function cancelActiveSessions(Inquiry $inquiry): void
    {
        $payments = $inquiry->payments()
            ->where('status', Payment::STATUS_PENDING)
            ->get();

        foreach ($payments as $payment) {
            $this->cancelRemoteSession($payment);

            $payment->update(['status' => Payment::STATUS_CANCELED]);
        }
    }

    private function cancelRemoteSession(Payment $payment): void
    {
        if (blank($payment->payment_session_id)) {
            return;
        }

        try {
            $this->gateway->cancelSession($payment->payment_session_id);
        } catch (Throwable $exception) {
            Log::warning('Unable to cancel a Xendit payment session.', [
                'payment_id' => $payment->id,
                'payment_session_id' => $payment->payment_session_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
