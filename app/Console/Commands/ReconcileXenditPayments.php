<?php

namespace App\Console\Commands;

use App\Contracts\XenditGateway;
use App\Models\Payment;
use App\Services\PaymentStatusSynchronizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileXenditPayments extends Command
{
    protected $signature = 'payments:reconcile-xendit';

    protected $description = 'Poll Xendit for locally pending invoices and synchronize paid/expired/failed states (webhook backup).';

    public function handle(XenditGateway $gateway, PaymentStatusSynchronizer $sync): int
    {
        $pending = Payment::query()
            ->where('gateway', 'xendit')
            ->where('status', Payment::STATUS_PENDING)
            ->whereNotNull('gateway_invoice_id')
            ->get();

        $synced = 0;
        foreach ($pending as $payment) {
            try {
                $invoice = $gateway->getInvoice((string) $payment->gateway_invoice_id);
                $sync->sync($payment, $invoice);
                $synced++;
            } catch (\Throwable $e) {
                Log::warning('Xendit reconciliation failed for payment', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $payment->gateway_invoice_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Reconciled {$synced} pending payment(s).");

        return Command::SUCCESS;
    }
}