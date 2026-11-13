<?php

namespace App\Services\Xendit;

use App\Contracts\XenditGateway;
use Xendit\Configuration;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;

class XenditInvoiceGateway implements XenditGateway
{
    public function __construct(
        private readonly ?InvoiceApi $api = null,
    ) {
        $key = (string) config('services.xendit.secret_key', '');
        if ($key !== '') {
            Configuration::setXenditKey($key);
        }
    }

    private function api(): InvoiceApi
    {
        return $this->api ?? new InvoiceApi();
    }

    public function createInvoice(array $data): array
    {
        $request = new CreateInvoiceRequest([
            'external_id' => $data['external_id'],
            'amount' => (float) $data['amount'],
            'payer_email' => $data['payer_email'] ?? null,
            'description' => $data['description'],
            'invoice_duration' => (int) ($data['invoice_duration'] ?? 86400),
            'currency' => $data['currency'] ?? 'PHP',
            'should_send_email' => false,
        ]);

        $result = $this->callSdk(fn () => $this->api()->createInvoice($request));

        return $this->normalize($result);
    }

    public function getInvoice(string $invoiceId): array
    {
        $result = $this->callSdk(fn () => $this->api()->getInvoiceById($invoiceId));

        return $this->normalize($result);
    }

    public function expireInvoice(string $invoiceId): void
    {
        // Best-effort: expire prior pending invoice remotely when feasible.
        // Xendit SDK exposes expireInvoice in newer APIs; if unavailable, ignore.
        try {
            $api = $this->api();
            if (method_exists($api, 'expireInvoice')) {
                $this->callSdk(fn () => $api->expireInvoice($invoiceId));
            }
        } catch (XenditSdkException|\Throwable $e) {
            // Non-fatal: local supersession remains authoritative.
        }
    }

    /**
     * Work around xendit-php 6.x ObjectSerializer incompatibilities on newer PHP
     * that can emit "Only variables should be passed by reference" notices.
     *
     * @template T
     * @param callable():T $callback
     * @return T
     */
    private function callSdk(callable $callback): mixed
    {
        $previous = set_error_handler(static function (int $severity, string $message, string $file): bool {
            if (str_contains($file, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'xendit' . DIRECTORY_SEPARATOR)
                && str_contains($message, 'Only variables should be passed by reference')) {
                return true;
            }

            return false;
        });

        try {
            return $callback();
        } finally {
            restore_error_handler();
        }
    }

    /**
     * @param mixed $invoice
     */
    private function normalize(mixed $invoice): array
    {
        $arr = is_array($invoice) ? $invoice : json_decode(json_encode($invoice), true);
        $arr = is_array($arr) ? $arr : [];

        return [
            'id' => (string) ($arr['id'] ?? ''),
            'invoice_url' => $arr['invoice_url'] ?? ($arr['invoiceUrl'] ?? null),
            'status' => isset($arr['status']) ? strtoupper((string) $arr['status']) : null,
            'expires_at' => $arr['expiry_date'] ?? ($arr['expiryDate'] ?? null),
            'paid_at' => $arr['paid_at'] ?? ($arr['paidAt'] ?? null),
            'payment_method' => $arr['payment_method'] ?? ($arr['paymentMethod'] ?? null),
            'raw' => $arr,
        ];
    }
}