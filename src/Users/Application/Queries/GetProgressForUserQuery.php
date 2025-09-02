<?php

namespace App\Users\Application\Queries;

use Doctrine\DBAL\Connection;

class GetProgressForUserQuery
{
    public function __construct(
        private Connection $connection
    ) {}

    public function execute(int $userId, int $courseId): array
    {
        $sql = "
            SELECT
                l.id as lesson_id,
                l.title,
                l.order_number,
                CASE
                    WHEN a.action = 'complete' THEN 'complete'
                    WHEN a.action = 'start' THEN 'in_progress'
                    ELSE 'pending'
                END as status
            FROM lesson l
            LEFT JOIN (
                SELECT lesson_id, action, user_id,
                       ROW_NUMBER() OVER (PARTITION BY lesson_id, user_id ORDER BY occurred_at DESC) as rn
                FROM activity
                WHERE user_id = :userId
            ) a ON l.id = a.lesson_id AND a.rn = 1
            WHERE l.course_id = :courseId
            ORDER BY l.order_number ASC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->bindValue('courseId', $courseId);
        $result = $stmt->executeQuery();

        $lessons = $result->fetchAllAssociative();

        $totalLessons = count($lessons);
        $completedLessons = count(array_filter($lessons, fn($l) => $l['status'] === 'complete'));
        $progressPercentage = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100, 2) : 0;

        return [
            'completed' => $completedLessons,
            'total' => $totalLessons,
            'percent' => $progressPercentage,
            'lessons' => $lessons
        ];
    }
}
