<?php

namespace App\Courses\Domain;

use App\Shared\Domain\DomainException;

class LessonNotFoundException extends DomainException
{
    protected int $httpStatusCode = 404;

    public function __construct(int $lessonId)
    {
        parent::__construct("Lesson with ID {$lessonId} not found");
    }
}
