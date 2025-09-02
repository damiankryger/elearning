<?php

namespace App\Courses\Domain;

use App\Shared\Domain\DomainException;

class UserAlreadyEnrolledException extends DomainException
{
    protected int $httpStatusCode = 409;

    public function __construct(int $userId, int $courseId)
    {
        parent::__construct("User with ID {$userId} is already enrolled in course with ID {$courseId}");
    }
}
