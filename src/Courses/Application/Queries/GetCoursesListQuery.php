<?php

namespace App\Courses\Application\Queries;

use Doctrine\DBAL\Connection;

final readonly class GetCoursesListQuery
{
    public function __construct(
        private Connection $connection
    ) {}

    public function execute(): array
    {
        $sql = "
            SELECT
                c.id,
                c.title,
                COUNT(l.id) as total_lessons
            FROM course c
            LEFT JOIN lesson l ON c.id = l.course_id
            GROUP BY c.id, c.title
            ORDER BY c.title ASC
        ";

        $stmt = $this->connection->prepare($sql);
        $result = $stmt->executeQuery();

        return $result->fetchAllAssociative();
    }
}
