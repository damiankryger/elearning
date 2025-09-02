<?php

namespace App\Courses\Domain;

use App\Shared\Application\ValidationException;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_user_course', columns: ['user_id', 'course_id'])]
class Enrollment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $userId;

    #[ORM\ManyToOne(inversedBy: 'enrollments')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\Column]
    private \DateTimeImmutable $enrolledAt;

    public function __construct(int $userId, Course $course)
    {
        $this->validateUserId($userId);

        $this->userId = $userId;
        $this->course = $course;
        $this->enrolledAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    private function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new ValidationException('Enrollment validation failed', [
                'userId' => 'User ID must be positive'
            ]);
        }
    }
}
