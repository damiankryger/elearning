<?php

namespace App\Courses\Application\Controllers;

use App\Courses\Application\Requests\RecordLessonActivityRequest;
use App\Courses\Domain\ActivityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/progress')]
final class ActivityController extends AbstractController
{
    public function __construct(
        private readonly ActivityService    $activityService,
        private readonly ValidatorInterface $validator
    )
    {
    }

    #[Route('', name: 'record', methods: ['POST'])]
    public function record(RecordLessonActivityRequest $request): JsonResponse
    {
        $violations = $this->validator->validate($request);

        if (count($violations) > 0) {
            return $this->json(['errors' => $violations], Response::HTTP_BAD_REQUEST);
        }

        $this->activityService->record(
            $request->userId,
            $request->courseId,
            $request->lessonId,
            $request->action,
            $request->requestId
        );

        return $this->json([], Response::HTTP_CREATED);
    }

    #[Route('/{userId}/lessons/{lessonId}', name: 'incomplete', methods: ['DELETE'])]
    public function incomplete(int $userId, int $lessonId): JsonResponse
    {
        $this->activityService->incomplete($userId, $lessonId);

        return $this->json([], Response::HTTP_OK);
    }
}
