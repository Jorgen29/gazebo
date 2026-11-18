<?php

namespace App\Services;

use App\Contracts\XenditGateway;
use App\Exceptions\XenditGatewayException;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class RefundService
{
    public function __construct(
        private readonly XenditGateway $gateway,
        private readonly RefundStatusSynchronizer $synchronizer
    ) {
    }

    /**
     * Initiate a full refund for a paid Xendit payment.
     *
     * TODO: Offline/manual refunds are not supported yet. Payments without a Xendit
     * `payment_request_id` (for example cash or bank-transfer bookings) cannot be
     * refunded here and would need a separate audit-only refund path.
     */
    public function initiate(
        Payment $payment,
        string $reason,
        ?string $note,
        User $admin,
        ?string $amount = null
    ): Refund {
        $refund = DB::transaction(function () use ($payment, $reason, $note, $admin, $amount): Refund {
            $locked = Payment::query()->lockForUpdate()->findOrFail($payment->getKey());

            $this->ensureRefundable($locked, $amount);

            return $locked->refunds()->create([
                'reference_id' => 'refund-' . $locked->id . '-' . Str::uuid(),
                'payment_request_id' => $locked->payment_request_id,
                'amount' => $locked->amount,
                'currency' => $locked->currency,
                'reason' => $reason,
                'note' => $note,
                'status' => Refund::STATUS_PENDING,
                'requested_by' => $admin->id,
                'payload' => [],
            ]);
        });

        $payload = [
            'reference_id' => $refund->reference_id,
            'payment_request_id' => $refund->payment_request_id,
            'amount' => (float) $refund->amount,
            'currency' => $refund->currency,
            'reason' => $refund->reason,
        ];

        try {
            $response = $this->gateway->createRefund($payload);

            if (blank($response['id'] ?? null)) {
                throw new XenditGatewayException('Xendit did not return a refund identifier.');
            }
        } catch (Throwable $exception) {
            $refund->update(['status' => Refund::STATUS_FAILED]);

            Log::error('Xendit refund request failed.', [
                'refund_id' => $refund->id,
                'payment_id' => $payment->id,
                'reference_id' => $refund->reference_id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $refund->update([
            'refund_id' => $response['id'],
            'reference_id' => $response['reference_id'] ?? $refund->reference_id,
            'payment_request_id' => $response['payment_request_id'] ?? $refund->payment_request_id,
            'payload' => $response['raw'] ?? $response,
        ]);

        return $this->synchronizer->apply(
            $refund->fresh(),
            $response['status'] ?? null,
            $response['raw'] ?? $response,
            'refund.created'
        );
    }

    private function ensureRefundable(Payment $payment, ?string $amount): void
    {
        if ($payment->status === Payment::STATUS_REFUNDED
            || $payment->refunds()->where('status', Refund::STATUS_SUCCEEDED)->exists()
        ) {
            throw new InvalidArgumentException('This payment has already been refunded.');
        }

        if (! $payment->isPaid()) {
            throw new InvalidArgumentException('Only a paid payment can be refunded.');
        }

        if (blank($payment->payment_request_id)) {
            throw new InvalidArgumentException(
                'This payment cannot be refunded because Xendit did not provide a payment request identifier.'
            );
        }

        if ($payment->refunds()->where('status', Refund::STATUS_PENDING)->exists()) {
            throw new InvalidArgumentException('A refund for this payment is already in progress.');
        }

        if ($amount !== null && $this->normalizeAmount($amount) !== $this->normalizeAmount($payment->amount)) {
            throw new InvalidArgumentException('Refunds must be for the full payment amount.');
        }
    }

    private function normalizeAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }
}
