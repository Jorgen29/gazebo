<?php

namespace App\Services;

use App\Mail\RefundSucceededMail;
use App\Models\Payment;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class RefundStatusSynchronizer
{
    /**
     * Apply a gateway status to a refund attempt idempotently.
     */
    public function apply(Refund $refund, ?string $gatewayStatus, array $data = [], ?string $event = null): Refund
    {
        $updates = [
            'payload' => $this->mergePayload($refund, $data, $event),
        ];

        $localStatus = $this->mapStatus($gatewayStatus);

        // A successful refund is terminal: duplicates and late events may only enrich the payload.
        if ($localStatus === null || $refund->isSucceeded()) {
            $refund->update($updates);

            return $refund;
        }

        if ($localStatus === Refund::STATUS_SUCCEEDED) {
            $updates['status'] = Refund::STATUS_SUCCEEDED;
            $updates['refunded_at'] = $this->refundedAt($data) ?? now();

            $refund->update($updates);

            $this->markPaymentRefunded($refund);

            return $refund;
        }

        $updates['status'] = $localStatus;

        if (filled($data['failure_code'] ?? null)) {
            $updates['failure_code'] = $data['failure_code'];
        }

        $refund->update($updates);

        return $refund;
    }

    private function mapStatus(?string $status): ?string
    {
        return match (strtoupper((string) $status)) {
            'SUCCEEDED' => Refund::STATUS_SUCCEEDED,
            'FAILED' => Refund::STATUS_FAILED,
            'CANCELLED', 'CANCELED' => Refund::STATUS_CANCELLED,
            'PENDING' => Refund::STATUS_PENDING,
            default => null,
        };
    }

    private function markPaymentRefunded(Refund $refund): void
    {
        $payment = $refund->payment()->first();

        if ($payment === null || $payment->isRefunded()) {
            return;
        }

        // paid_at stays untouched so the original payment remains historical fact.
        $payment->update(['status' => Payment::STATUS_REFUNDED]);

        try {
            Mail::to($payment->inquiry->email)->queue(new RefundSucceededMail($refund->fresh()));
        } catch (Throwable $exception) {
            Log::error('Refund succeeded email could not be queued.', [
                'refund_id' => $refund->id,
                'payment_id' => $payment->id,
                'inquiry_id' => $payment->inquiry_id,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function refundedAt(array $data): ?Carbon
    {
        $value = $data['updated'] ?? $data['refunded_at'] ?? null;

        return filled($value) ? Carbon::parse($value) : null;
    }

    private function mergePayload(Refund $refund, array $data, ?string $event): array
    {
        $payload = array_replace_recursive($refund->payload ?? [], $data);

        if (filled($event)) {
            $payload['last_event'] = $event;
        }

        return $payload;
    }
}
