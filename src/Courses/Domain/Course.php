<?php

namespace App\Courses\Domain;

use App\Courses\Infrastructure\CourseRepository;
use App\Shared\Application\ValidationException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Enrollment::class, cascade: ['persist', 'remove'])]
    private Collection $enrollments;

    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Lesson::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['orderNumber' => 'ASC'])]
    private Collection $lessons;

    public function __construct(string $title)
    {
        $this->validateTitle($title);

        $this->title = $title;
        $this->enrollments = new ArrayCollection();
        $this->lessons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function completeLesson(int $userId, int $lessonId, string $requestId): void
    {
        if (!$this->isUserEnrolled($userId)) {
            throw new UserNotEnrolledException($userId, $this->id);
        }

        if (!$lesson = $this->findLesson($lessonId)) {
            throw new LessonNotFoundException($lessonId);
        }

        $lesson->complete($userId, $requestId);
    }

    public function incompleteLesson(int $userId, int $lessonId, string $requestId): void
    {
        if (!$this->isUserEnrolled($userId)) {
            throw new UserNotEnrolledException($userId, $this->id);
        }

        if (!$lesson = $this->findLesson($lessonId)) {
            throw new LessonNotFoundException($lessonId);
        }

        $lesson->incomplete($userId, $requestId);
    }

    public function startLesson(int $userId, int $lessonId, string $requestId): void
    {
        if (!$this->isUserEnrolled($userId)) {
            throw new UserNotEnrolledException($userId, $this->id);
        }

        if (!$lesson = $this->findLesson($lessonId)) {
            throw new LessonNotFoundException($lessonId);
        }

        $lesson->start($userId, $requestId);
    }

    public function enroll(int $userId): void
    {
        if ($this->isUserEnrolled($userId)) {
            throw new UserAlreadyEnrolledException($userId, $this->id);
        }

        $this->enrollments->add(new Enrollment($userId, $this));

        foreach ($this->lessons as $lesson) {
            /** @var Lesson $lesson */
            $lesson->enroll($userId, uniqid('', true));
        }
    }

    private function findLesson(int $lessonId): Lesson
    {
        foreach ($this->lessons as $lesson) {
            if ($lesson->getId() === $lessonId) {
                return $lesson;
            }
        }
        
        throw new \InvalidArgumentException("Lesson with ID {$lessonId} not found in course");
    }

    private function isUserEnrolled(int $userId): bool
    {
        foreach ($this->enrollments as $enrollment) {
            if ($enrollment->getUserId() === $userId) {
                return true;
            }
        }

        return false;
    }

    public function getPreviousMissingLessons(int $userId, int $lessonOrderNumber): array
    {
        $missingLessons = [];

        for ($i = 1; $i < $lessonOrderNumber; $i++) {
            $previousLesson = $this->getLessonByOrder($i);
            if (!$previousLesson || !$previousLesson->hasUserCompleted($userId)) {
                $missingLessons[] = $i;
            }
        }

        return $missingLessons;
    }

    private function getLessonByOrder(int $orderNumber): ?Lesson
    {
        foreach ($this->lessons as $lesson) {
            if ($lesson->getOrderNumber() === $orderNumber) {
                return $lesson;
            }
        }

        return null;
    }

    private function validateTitle(string $title): void
    {
        $validationErrors = [];

        if (empty(trim($title))) {
            $validationErrors['title'] = 'Course title cannot be empty';
        }

        if (strlen($title) > 255) {
            $validationErrors['title'] = 'Course title cannot exceed 255 characters';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('Course title validation failed', $validationErrors);
        }
    }

}
