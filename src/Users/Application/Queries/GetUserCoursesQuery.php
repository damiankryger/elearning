<?php

namespace App\Users\Application\Queries;

use Doctrine\DBAL\Connection;

class GetUserCoursesQuery
{
    public function __construct(
        private Connection $connection
    ) {}

    public function execute(int $userId): array
    {
        $sql = "
            SELECT
                c.id,
                c.title,
                COUNT(l.id) as total_lessons
            FROM course c
            INNER JOIN enrollment e ON c.id = e.course_id
            LEFT JOIN lesson l ON c.id = l.course_id
            WHERE e.user_id = :userId
            GROUP BY c.id, c.title
            ORDER BY c.title ASC
        ";

        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }
}
