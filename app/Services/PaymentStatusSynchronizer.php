<?php

namespace App\Services;

use App\Models\Payment;
use Carbon\Carbon;

class PaymentStatusSynchronizer
{
    /**
     * Apply a gateway status to a payment idempotently.
     */
    public function apply(Payment $payment, ?string $gatewayStatus, array $data = [], ?string $event = null): Payment
    {
        $updates = [
            'payload' => $this->mergePayload($payment, $data, $event),
        ];

        $localStatus = $this->mapStatus($gatewayStatus);

        if ($localStatus === Payment::STATUS_PAID) {
            if (! $payment->isPaid()) {
                $updates['status'] = Payment::STATUS_PAID;
                $updates['paid_at'] = $this->paidAt($data) ?? now();
            }
        } elseif ($localStatus !== null && ! $payment->isPaid()) {
            $updates['status'] = $localStatus;
        }

        foreach (['payment_request_id', 'payment_id'] as $field) {
            if (filled($data[$field] ?? null)) {
                $updates[$field] = $data[$field];
            }
        }

        if (filled($data['payment_method'] ?? null)) {
            $updates['payment_method'] = $this->paymentMethod($data['payment_method']);
        }

        if (filled($data['expires_at'] ?? null)) {
            $updates['expires_at'] = Carbon::parse($data['expires_at']);
        }

        $payment->update($updates);

        return $payment;
    }

    private function mapStatus(?string $status): ?string
    {
        return match (strtoupper((string) $status)) {
            'COMPLETED', 'PAID', 'SUCCEEDED' => Payment::STATUS_PAID,
            'EXPIRED' => Payment::STATUS_EXPIRED,
            'CANCELED', 'CANCELLED' => Payment::STATUS_CANCELED,
            default => null,
        };
    }

    private function paidAt(array $data): ?Carbon
    {
        $value = $data['paid_at'] ?? $data['updated'] ?? null;

        return filled($value) ? Carbon::parse($value) : null;
    }

    private function paymentMethod(mixed $method): ?string
    {
        if (is_string($method)) {
            return $method;
        }

        if (is_array($method)) {
            return $method['type'] ?? $method['channel'] ?? $method['name'] ?? null;
        }

        return null;
    }

    private function mergePayload(Payment $payment, array $data, ?string $event): array
    {
        $payload = array_replace_recursive($payment->payload ?? [], $data);

        if (filled($event)) {
            $payload['last_event'] = $event;
        }

        return $payload;
    }
}
