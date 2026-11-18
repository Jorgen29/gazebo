<?php

namespace App\Exceptions;

use RuntimeException;

class XenditGatewayException extends RuntimeException
{
    public function __construct(
        string $message,
        private readonly ?int $httpStatus = null,
        private readonly array $response = []
    ) {
        parent::__construct($message);
    }

    public function httpStatus(): ?int
    {
        return $this->httpStatus;
    }

    public function response(): array
    {
        return $this->response;
    }
}
