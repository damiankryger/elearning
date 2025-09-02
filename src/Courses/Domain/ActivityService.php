<?php

declare(strict_types=1);

namespace App\Courses\Domain;

use App\Courses\Infrastructure\CourseRepository;

final readonly class ActivityService
{
    /**
     * @param CourseRepository<Course> $courses
     */
    public function __construct(
        private CourseRepository $courses,
    )
    {
    }

    public function record(int $userId, int $courseId, int $lessonId, string $action, string $requestId): void
    {
        /** @var Course $course */
        $course = $this->courses->find($courseId);

        if (!$course) {
            throw new CourseNotFoundException($courseId);
        }

        match ($action) {
            'complete' => $course->completeLesson($userId, $lessonId, $requestId),
            'incomplete' => $course->incompleteLesson($userId, $lessonId, $requestId),
            'start' => $course->startLesson($userId, $lessonId, $requestId),
            default => throw new \InvalidArgumentException("Invalid action: $action")
        };

        $this->courses->save($course, true);
    }

    public function incomplete(int $userId, int $lessonId): void
    {
        $course = $this->courses->findByLessonId($lessonId);

        if (!$course) {
            throw new CourseNotFoundException(lessonId: $lessonId);
        }

        $course->incompleteLesson($userId, $lessonId, uniqid('', true));

        $this->courses->save($course, true);
    }

    public function start(int $userId, int $lessonId): void
    {
        $course = $this->courses->findByLessonId($lessonId);

        if (!$course) {
            throw new CourseNotFoundException(lessonId: $lessonId);
        }

        $course->startLesson($userId, $lessonId);

        $this->courses->save($course, true);
    }
}
