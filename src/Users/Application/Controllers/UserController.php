<?php

namespace App\Users\Application\Controllers;

use App\Users\Application\Queries\GetUserCoursesQuery;
use App\Users\Application\Responses\UserCoursesResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/users')]
final class UserController extends AbstractController
{
    public function __construct(
        private readonly GetUserCoursesQuery $getUserCoursesQuery
    )
    {
    }

    #[Route('/{id}/courses', name: 'user_courses', methods: ['GET'])]
    public function courses(int $id): JsonResponse
    {
        $coursesData = $this->getUserCoursesQuery->execute($id);

        return $this->json(new UserCoursesResponse($coursesData));
    }
}
