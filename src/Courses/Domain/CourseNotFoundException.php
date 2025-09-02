<?php

namespace App\Courses\Domain;

use App\Shared\Domain\DomainException;

class CourseNotFoundException extends DomainException
{
    protected int $httpStatusCode = 404;

    public function __construct(?int $courseId = null, ?int $lessonId = null)
    {
        if ($courseId !== null) {
            parent::__construct("Course with ID {$courseId} not found");
        } else if ($lessonId !== null) {
            parent::__construct("Course with lessonID {$lessonId} not found");
        } else {
            parent::__construct("Course not found");
        }
    }
}
