<?php

namespace App\Courses\Infrastructure;

use App\Courses\Domain\Course;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

final class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function save(Course $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByLessonId(int $lessonId): ?Course
    {
        $qb = $this->createQueryBuilder('c')
            ->join('c.lessons', 'l')
            ->where('l.id = :lessonId')
            ->setParameter('lessonId', $lessonId);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
