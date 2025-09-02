<?php

namespace App\Courses\Application\Requests;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RecordLessonActivityRequest
{
    #[Assert\NotNull(message: 'user_id is required')]
    #[Assert\Type(type: 'integer', message: 'user_id must be an integer')]
    #[Assert\Positive(message: 'user_id must be a positive integer')]
    public int $userId;

    #[Assert\NotNull(message: 'course_id is required')]
    #[Assert\Type(type: 'integer', message: 'course_id must be an integer')]
    #[Assert\Positive(message: 'course_id must be a positive integer')]
    public int $courseId;

    #[Assert\NotNull(message: 'lesson_id is required')]
    #[Assert\Type(type: 'integer', message: 'lesson_id must be an integer')]
    #[Assert\Positive(message: 'lesson_id must be a positive integer')]
    public int $lessonId;

    #[Assert\NotNull(message: 'action is required')]
    #[Assert\Choice(choices: ['complete', 'incomplete', 'start'], message: 'action must be one of: complete, incomplete, start')]
    public string $action;

    #[Assert\NotNull(message: 'request_id is required')]
    #[Assert\NotBlank(message: 'request_id cannot be blank')]
    #[Assert\Length(min: 1, max: 255, minMessage: 'request_id must be at least {{ limit }} characters long', maxMessage: 'request_id cannot be longer than {{ limit }} characters')]
    public string $requestId;

    public function __construct(array $data)
    {
        $this->userId = $data['userId'] ?? $data['user_id'] ?? 0;
        $this->courseId = $data['courseId'] ?? $data['course_id'] ?? 0;
        $this->lessonId = $data['lessonId'] ?? $data['lesson_id'] ?? 0;
        $this->action = $data['action'] ?? '';
        $this->requestId = $data['requestId'] ?? $data['request_id'] ?? '';
    }
}
