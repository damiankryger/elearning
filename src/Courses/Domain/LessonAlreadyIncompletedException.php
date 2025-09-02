<?php

namespace App\Courses\Domain;

use App\Shared\Domain\DomainException;

class LessonAlreadyIncompletedException extends DomainException
{
    protected int $httpStatusCode = 409;

    public function __construct(int $lessonId, int $userId)
    {
        parent::__construct("Lesson with ID {$lessonId} has already been incompleted by user with ID {$userId}");
    }
}
