<?php

namespace App\Courses\Domain;

use App\Shared\Application\ValidationException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column]
    private int $orderNumber;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    private Course $course;

    #[ORM\OneToMany(targetEntity: Activity::class, mappedBy: 'lesson', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $activities;

    public function __construct(string $title, int $orderNumber, Course $course)
    {
        $this->validateTitle($title);
        $this->validateOrderNumber($orderNumber);

        $this->title = $title;
        $this->orderNumber = $orderNumber;
        $this->course = $course;
        $this->activities = new ArrayCollection();
    }

    public function complete(int $userId, string $requestId)
    {
        if ($this->isDuplicated($requestId)) {
            throw new DuplicateRequestException($requestId);
        }

        if ($this->canBeCompleted($userId)) {
            throw new LessonAlreadyCompletedException($this->id, $userId);
        }

        $this->activities->add(Activity::completed($this, $userId, $requestId));
    }

    public function incomplete(int $userId, string $requestId): void
    {
        if ($this->isDuplicated($requestId)) {
            throw new DuplicateRequestException($requestId);
        }

        if ($this->canBeIncompleted($userId)) {
            throw new LessonAlreadyIncompletedException($this->id, $userId);
        }

        $this->activities->add(Activity::incompleted($this, $userId, $requestId));
    }

    public function start(int $userId, string $requestId): void
    {
        if ($this->isDuplicated($requestId)) {
            throw new DuplicateRequestException($requestId);
        }

        if ($this->canBeStarted($userId)) {
            throw new LessonAlreadyStartedException($this->id, $userId);
        }

        $this->activities->add(Activity::started($this, $userId, $requestId));
    }

    public function enroll(int $userId, string $requestId): void
    {
        if ($this->isDuplicated($requestId)) {
            throw new DuplicateRequestException($requestId);
        }

        if ($this->canBeEnrolled($userId)) {
            throw new LessonAlreadyStartedException($this->id, $userId);
        }

        $this->activities->add(Activity::enrolled($this, $userId, $requestId));
    }

    private function isDuplicated(string $requestId): bool
    {
        foreach ($this->activities as $activity) {
            if ($activity->getRequestId() === $requestId) {
                return true;
            }
        }

        return false;
    }

    private function getLastActivityOrNull(int $userId): ?Activity
    {
        $userActivities = $this->activities
            ->filter(function (Activity $activity) use ($userId) {
                return $activity->getUserId() === $userId;
            })
            ->toArray();

        if (empty($userActivities)) {
            return null;
        }

        usort($userActivities, function (Activity $a, Activity $b) {
            return $b->getOccurredAt() <=> $a->getOccurredAt();
        });

        return $userActivities[0];
    }

    private function canBeCompleted(int $userId): bool
    {
        $activity = $this->getLastActivityOrNull($userId);

        if (!$activity) {
            return false;
        }

        return $activity->getAction() === ActivityAction::START || $activity->getAction() === ActivityAction::INCOMPLETE;
    }

    private function canBeIncompleted(int $userId): bool
    {
        $activity = $this->getLastActivityOrNull($userId);

        if (!$activity) {
            return false;
        }

        return $activity->getAction() === ActivityAction::COMPLETE;
    }

    private function canBeEnrolled(int $userId): bool
    {
        $activity = $this->getLastActivityOrNull($userId);

        if (!$activity) {
            return true;
        }

        return false;
    }

    private function canBeStarted(int $userId): bool
    {
        $activity = $this->getLastActivityOrNull($userId);

        if (!$activity) {
            return false;
        }

        $canBeStarted = $activity->getAction() === ActivityAction::ENROLL || $activity->getAction() === ActivityAction::INCOMPLETE;

        if ($canBeStarted) {
            if (count($this->course->getPreviousMissingLessons($userId, $this->orderNumber)) > 0) {
                $canBeStarted = false;
            }
        }

        return $canBeStarted;
    }

    public function hasUserCompleted(int $userId): bool
    {
        foreach ($this->activities as $activity) {
            if ($activity->getUserId() === $userId && $activity->getAction() === ActivityAction::COMPLETE) {
                return true;
            }
        }
        return false;
    }

    private function validateTitle(string $title): void
    {
        $validationErrors = [];

        if (empty(trim($title))) {
            $validationErrors['title'] = 'Lesson title cannot be empty';
        }

        if (strlen($title) > 255) {
            $validationErrors['title'] = 'Lesson title cannot exceed 255 characters';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('Lesson title validation failed', $validationErrors);
        }
    }

    private function validateOrderNumber(int $orderNumber): void
    {
        $validationErrors = [];

        if ($orderNumber <= 0) {
            $validationErrors['orderNumber'] = 'Lesson order number must be positive';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('Lesson order number validation failed', $validationErrors);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderNumber(): int
    {
        return $this->orderNumber;
    }
}
