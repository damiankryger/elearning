<?php

namespace App\Shared\Domain;

abstract class DomainException extends \RuntimeException
{
    protected int $httpStatusCode = 400;

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->getMessage(),
            'code' => $this->getCode(),
            'http_status' => $this->getHttpStatusCode()
        ];
    }
}
