<?php

namespace App\Courses\Application\Controllers;

use App\Courses\Application\Queries\GetCoursesListQuery;
use App\Courses\Application\Requests\CreateNewEnrollmentRequest;
use App\Courses\Application\Responses\CoursesListResponse;
use App\Courses\Domain\EnrollmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/courses')]
final class CourseController extends AbstractController
{
    public function __construct(
        private readonly GetCoursesListQuery $getCoursesListQuery,
        private readonly EnrollmentService   $enrollmentService,
        private readonly ValidatorInterface  $validator
    )
    {
    }

    #[Route('', name: 'courses_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $coursesData = $this->getCoursesListQuery->execute();

        return $this->json(new CoursesListResponse($coursesData));
    }

    #[Route('/{id}/enroll', name: 'course_enroll', methods: ['POST'])]
    public function enroll(int $id, CreateNewEnrollmentRequest $request): JsonResponse
    {
        $violations = $this->validator->validate($request);

        if (count($violations) > 0) {
            return $this->json(['errors' => $violations], Response::HTTP_BAD_REQUEST);
        }

        $this->enrollmentService->enroll($id, $request->userId);

        return $this->json([], Response::HTTP_CREATED);
    }
}
