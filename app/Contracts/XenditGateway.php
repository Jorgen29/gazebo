<?php

namespace App\Contracts;

interface XenditGateway
{
    /**
     * Create a Payment Session and return the normalized session data.
     */
    public function createSession(array $payload): array;

    /**
     * Retrieve a Payment Session and return the normalized session data.
     */
    public function getSession(string $sessionId): array;

    /**
     * Cancel a Payment Session and return the normalized session data.
     */
    public function cancelSession(string $sessionId): array;

    /**
     * Create a refund and return the normalized refund data.
     */
    public function createRefund(array $payload): array;

    /**
     * Retrieve a refund and return the normalized refund data.
     */
    public function getRefund(string $refundId): array;
}
