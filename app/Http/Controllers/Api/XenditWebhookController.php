<?php

namespace App\Http\Controllers\Api;

use App\Contracts\XenditGateway;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\PaymentStatusSynchronizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function __invoke(Request $request, XenditGateway $gateway, PaymentStatusSynchronizer $sync)
    {
        $token = $request->header('x-callback-token');
        $expected = (string) config('services.xendit.callback_token', '');

        if ($expected === '' || ! is_string($token) || ! hash_equals($expected, $token)) {
            return response()->json(['message' => 'Invalid callback token'], 401);
        }

        $invoiceId = $request->input('id') ?? $request->input('invoice_id') ?? $request->input('data.id');
        if (! $invoiceId) {
            return response()->json(['message' => 'Missing invoice id'], 400);
        }

        $payment = Payment::where('gateway_invoice_id', (string) $invoiceId)->first();
        if (! $payment) {
            // Unknown invoice: acknowledge to avoid noisy retries, but do not modify state.
            Log::warning('Xendit webhook for unknown invoice', ['invoice_id' => $invoiceId]);
            return response()->json(['message' => 'Ignored'], 200);
        }

        // Use gateway lookup/normalization for consistent field mapping; fall back to payload.
        try {
            $invoice = $gateway->getInvoice((string) $invoiceId);
        } catch (\Throwable $e) {
            Log::warning('Xendit webhook invoice fetch failed, using callback payload', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage(),
            ]);
            $invoice = [
                'id' => (string) $invoiceId,
                'invoice_url' => $request->input('invoice_url'),
                'status' => $request->input('status'),
                'paid_at' => $request->input('paid_at'),
                'expires_at' => $request->input('expiry_date'),
                'payment_method' => $request->input('payment_method'),
                'raw' => $request->all(),
            ];
        }

        $sync->sync($payment, $invoice);

        return response()->json(['message' => 'OK'], 200);
    }
}