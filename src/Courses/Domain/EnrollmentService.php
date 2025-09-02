<?php

declare(strict_types=1);

namespace App\Courses\Domain;

use App\Courses\Infrastructure\CourseRepository;

class EnrollmentService
{
    public function __construct(
        private CourseRepository $courses,
    )
    {
    }

    public function enroll(int $courseId, int $userId): void
    {
        /** @var Course|false $course */
        $course = $this->courses->find($courseId);

        if (!$course) {
            throw new CourseNotFoundException($courseId);
        }

        $course->enroll($userId);

        $this->courses->save($course, true);
    }
}
