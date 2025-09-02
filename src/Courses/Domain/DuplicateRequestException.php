<?php

namespace App\Courses\Domain;

use App\Shared\Domain\DomainException;

class DuplicateRequestException extends DomainException
{
    protected int $httpStatusCode = 409;

    public function __construct(string $requestId)
    {
        parent::__construct("Request with ID '{$requestId}' has already been processed");
    }
}
