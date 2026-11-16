<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;

class PaymentStatusSynchronizer
{
    /**
     * Apply gateway invoice state to a local payment idempotently.
     *
     * @param array{id:string,invoice_url:?string,status:?string,expires_at?:mixed,paid_at?:mixed,payment_method?:?string,raw:mixed} $invoice
     */
    public function sync(Payment $payment, array $invoice): Payment
    {
        $status = strtoupper((string) ($invoice['status'] ?? ''));

        // Xendit invoice statuses: PENDING, PAID, SETTLED, EXPIRED.
        // Treat SETTLED as paid; treat EXPIRED as expired. Unknown/unchanged states are no-ops.
        if (in_array($status, ['PAID', 'SETTLED'], true)) {
            if ($payment->status === Payment::STATUS_PAID) {
                // Idempotent: already processed.
                $this->mergePayload($payment, $invoice);
                return $payment->fresh();
            }

            $paidAt = $payment->paid_at;
            if (! $paidAt && ! empty($invoice['paid_at'])) {
                try { $paidAt = Carbon::parse($invoice['paid_at']); } catch (\Throwable $e) { $paidAt = now(); }
            }
            $paidAt = $paidAt ?: now();

            $payment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => $paidAt,
                'payment_method' => $invoice['payment_method'] ?? $payment->payment_method,
                'payload' => $this->mergedPayload($payment, $invoice),
            ]);

            return $payment->fresh();
        }

        if ($status === 'EXPIRED') {
            if (in_array($payment->status, [Payment::STATUS_EXPIRED, Payment::STATUS_PAID, Payment::STATUS_SUPERSEDED], true)) {
                $this->mergePayload($payment, $invoice);
                return $payment->fresh();
            }

            $expiresAt = $payment->expires_at;
            if (! empty($invoice['expires_at'])) {
                try { $expiresAt = Carbon::parse($invoice['expires_at']); } catch (\Throwable $e) {}
            }

            $payment->update([
                'status' => Payment::STATUS_EXPIRED,
                'expires_at' => $expiresAt,
                'payload' => $this->mergedPayload($payment, $invoice),
            ]);

            return $payment->fresh();
        }

        if ($status === 'FAILED') {
            if (in_array($payment->status, [Payment::STATUS_FAILED, Payment::STATUS_PAID, Payment::STATUS_EXPIRED, Payment::STATUS_SUPERSEDED], true)) {
                $this->mergePayload($payment, $invoice);
                return $payment->fresh();
            }

            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'payload' => $this->mergedPayload($payment, $invoice),
            ]);

            return $payment->fresh();
        }

        // Pending/unknown: keep local state, refresh payload for observability.
        $this->mergePayload($payment, $invoice);
        return $payment->fresh();
    }

    private function mergedPayload(Payment $payment, array $invoice): array
    {
        $existing = is_array($payment->payload) ? $payment->payload : [];
        $raw = is_array($invoice['raw'] ?? null) ? $invoice['raw'] : $invoice;

        return array_replace_recursive($existing, ['last_sync' => $raw, 'last_status' => $invoice['status'] ?? null]);
    }

    private function mergePayload(Payment $payment, array $invoice): void
    {
        $payment->update(['payload' => $this->mergedPayload($payment, $invoice)]);
    }
}