<?php

namespace App\Courses\Domain;

use App\Shared\Application\ValidationException;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'unique_request_id', columns: ['request_id'])]
class Activity
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private int $userId;

    #[ORM\ManyToOne(inversedBy: 'activities')]
    #[ORM\JoinColumn(nullable: false)]
    private Lesson $lesson;

    #[ORM\Column(length: 20, enumType: ActivityAction::class)]
    private ActivityAction $action;

    #[ORM\Column(length: 255, unique: true)]
    private string $requestId;

    #[ORM\Column]
    private \DateTimeImmutable $occurredAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getOccurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function getAction(): ActivityAction
    {
        return $this->action;
    }

    public function getRequestId(): string
    {
        return $this->requestId;
    }

    public static function completed(Lesson $lesson, int $userId, string $requestId): self
    {
        return new self(
            userId: $userId,
            lesson: $lesson,
            action: ActivityAction::COMPLETE,
            requestId: $requestId
        );
    }

    public static function incompleted(Lesson $lesson, int $userId, string $requestId): self
    {
        return new self(
            userId: $userId,
            lesson: $lesson,
            action: ActivityAction::INCOMPLETE,
            requestId: $requestId
        );
    }

    public static function enrolled(Lesson $lesson, int $userId, string $requestId): self
    {
        return new self(
            userId: $userId,
            lesson: $lesson,
            action: ActivityAction::ENROLL,
            requestId: $requestId
        );
    }

    public static function started(Lesson $lesson, int $userId, string $requestId): self
    {
        return new self(
            userId: $userId,
            lesson: $lesson,
            action: ActivityAction::START,
            requestId: $requestId
        );
    }

    public function __construct(int $userId, Lesson $lesson, ActivityAction $action, string $requestId)
    {
        $this->validateUserId($userId);
        $this->validateRequestId($requestId);

        $this->userId = $userId;
        $this->lesson = $lesson;
        $this->action = $action;
        $this->requestId = $requestId;
        $this->occurredAt = new \DateTimeImmutable();
    }

    private function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw new ValidationException('Activity validation failed', [
                'userId' => 'User ID must be positive'
            ]);
        }
    }

    private function validateRequestId(string $requestId): void
    {
        $validationErrors = [];

        if (empty(trim($requestId))) {
            $validationErrors['requestId'] = 'Request ID cannot be empty';
        }

        if (strlen($requestId) > 255) {
            $validationErrors['requestId'] = 'Request ID cannot exceed 255 characters';
        }

        // Basic format validation - should contain at least one alphanumeric character
        if (!preg_match('/[a-zA-Z0-9]/', $requestId)) {
            $validationErrors['requestId'] = 'Request ID must contain at least one alphanumeric character';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('Request ID validation failed', $validationErrors);
        }
    }
}
