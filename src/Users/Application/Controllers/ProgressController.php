<?php

declare(strict_types=1);

namespace App\Users\Application\Controllers;

use App\Users\Application\Queries\GetProgressForUserQuery;
use App\Users\Application\Responses\UserProgressResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/progress')]
final class ProgressController extends AbstractController
{
    public function __construct(
        private readonly GetProgressForUserQuery $getProgressForUser,
    )
    {
    }

    #[Route('/{userId}/courses/{courseId}', name: 'get_user_progress', methods: ['GET'])]
    public function progress(int $userId, int $courseId): JsonResponse
    {
        $progressData = $this->getProgressForUser->execute($userId, $courseId);

        return $this->json(new UserProgressResponse($progressData));
    }
}
