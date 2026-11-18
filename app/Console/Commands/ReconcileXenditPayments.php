<?php

namespace App\Console\Commands;

use App\Contracts\XenditGateway;
use App\Models\Payment;
use App\Models\Refund;
use App\Services\PaymentStatusSynchronizer;
use App\Services\RefundStatusSynchronizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class ReconcileXenditPayments extends Command
{
    protected $signature = 'payments:reconcile-xendit';

    protected $description = 'Reconcile locally pending Xendit Payment Sessions and refunds with the gateway.';

    public function handle(
        XenditGateway $gateway,
        PaymentStatusSynchronizer $synchronizer,
        RefundStatusSynchronizer $refundSynchronizer
    ): int {
        $processed = 0;
        $failed = 0;

        Payment::query()
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('payment_session_id')
            ->orderBy('id')
            ->chunkById(100, function ($payments) use ($gateway, $synchronizer, &$processed, &$failed): void {
                foreach ($payments as $payment) {
                    try {
                        $session = $gateway->getSession($payment->payment_session_id);

                        $synchronizer->apply(
                            $payment,
                            $session['status'],
                            $session['raw'] ?? $session,
                            'reconciliation'
                        );

                        $processed++;
                    } catch (Throwable $exception) {
                        $failed++;

                        Log::warning('Xendit payment reconciliation failed for one payment.', [
                            'payment_id' => $payment->id,
                            'payment_session_id' => $payment->payment_session_id,
                            'error' => $exception->getMessage(),
                        ]);

                        $this->warn("Payment #{$payment->id} could not be reconciled: {$exception->getMessage()}");
                    }
                }
            });

        $this->info("Reconciled {$processed} payment(s); {$failed} failure(s).");

        $refundsProcessed = 0;
        $refundsFailed = 0;

        Refund::query()
            ->where('status', Refund::STATUS_PENDING)
            ->whereNotNull('refund_id')
            ->orderBy('id')
            ->chunkById(100, function ($refunds) use ($gateway, $refundSynchronizer, &$refundsProcessed, &$refundsFailed): void {
                foreach ($refunds as $refund) {
                    try {
                        $response = $gateway->getRefund($refund->refund_id);

                        $refundSynchronizer->apply(
                            $refund,
                            $response['status'],
                            $response['raw'] ?? $response,
                            'reconciliation'
                        );

                        $refundsProcessed++;
                    } catch (Throwable $exception) {
                        $refundsFailed++;

                        Log::warning('Xendit refund reconciliation failed for one refund.', [
                            'refund_id' => $refund->id,
                            'gateway_refund_id' => $refund->refund_id,
                            'error' => $exception->getMessage(),
                        ]);

                        $this->warn("Refund #{$refund->id} could not be reconciled: {$exception->getMessage()}");
                    }
                }
            });

        $this->info("Reconciled {$refundsProcessed} refund(s); {$refundsFailed} failure(s).");

        return self::SUCCESS;
    }
}
