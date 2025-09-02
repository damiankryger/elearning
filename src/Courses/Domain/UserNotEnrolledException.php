<?php

namespace App\Courses\Domain;

use App\Shared\Domain\DomainException;

class UserNotEnrolledException extends DomainException
{
    protected int $httpStatusCode = 404;

    public function __construct(int $userId, int $courseId)
    {
        parent::__construct("User with ID {$userId} not enrolled to the course with ID {$courseId}");
    }
}
