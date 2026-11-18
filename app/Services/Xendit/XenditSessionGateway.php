<?php

namespace App\Services\Xendit;

use App\Contracts\XenditGateway;
use App\Exceptions\XenditGatewayException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class XenditSessionGateway implements XenditGateway
{
    private const BASE_URL = 'https://api.xendit.co';

    private const TIMEOUT_SECONDS = 15;

    public function createSession(array $payload): array
    {
        return $this->normalizeSession($this->request('post', '/sessions', $payload));
    }

    public function getSession(string $sessionId): array
    {
        return $this->normalizeSession($this->request('get', '/sessions/' . rawurlencode($sessionId)));
    }

    public function cancelSession(string $sessionId): array
    {
        return $this->normalizeSession($this->request('post', '/sessions/' . rawurlencode($sessionId) . '/cancel'));
    }

    public function createRefund(array $payload): array
    {
        return $this->normalizeRefund($this->request('post', '/refunds', $payload));
    }

    public function getRefund(string $refundId): array
    {
        return $this->normalizeRefund($this->request('get', '/refunds/' . rawurlencode($refundId)));
    }

    private function request(string $method, string $path, array $payload = []): array
    {
        $response = $this->client()->{$method}(self::BASE_URL . $path, $payload);

        if ($response->failed()) {
            $body = $response->json();

            throw new XenditGatewayException(
                is_array($body) && isset($body['message'])
                    ? (string) $body['message']
                    : "Xendit request failed with status {$response->status()}.",
                $response->status(),
                is_array($body) ? $body : []
            );
        }

        $data = $response->json();

        if (! is_array($data)) {
            throw new XenditGatewayException('Xendit returned an unexpected response.');
        }

        return $data;
    }

    private function client(): PendingRequest
    {
        return Http::withBasicAuth((string) config('services.xendit.secret_key'), '')
            ->acceptJson()
            ->asJson()
            ->timeout(self::TIMEOUT_SECONDS);
    }

    private function normalizeSession(array $data): array
    {
        return [
            'payment_session_id' => $data['payment_session_id'] ?? $data['id'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'status' => $data['status'] ?? null,
            'payment_link_url' => $data['payment_link_url'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'payment_request_id' => $data['payment_request_id'] ?? null,
            'payment_id' => $data['payment_id'] ?? null,
            'payment_method' => $this->paymentMethod($data),
            'paid_at' => $data['paid_at'] ?? null,
            'raw' => $data,
        ];
    }

    private function normalizeRefund(array $data): array
    {
        return [
            'id' => $data['id'] ?? null,
            'payment_request_id' => $data['payment_request_id'] ?? null,
            'payment_id' => $data['payment_id'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'status' => $data['status'] ?? null,
            'failure_code' => $data['failure_code'] ?? null,
            'refund_fee_amount' => $data['refund_fee_amount'] ?? null,
            'created' => $data['created'] ?? null,
            'updated' => $data['updated'] ?? null,
            'raw' => $data,
        ];
    }

    private function paymentMethod(array $data): ?string
    {
        $method = $data['payment_method'] ?? null;

        if (is_string($method)) {
            return $method;
        }

        if (is_array($method)) {
            return $method['type'] ?? $method['channel'] ?? $method['name'] ?? null;
        }

        return $data['payment_channel'] ?? null;
    }
}
