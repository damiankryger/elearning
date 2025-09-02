<?php

namespace App\Tests\Integration;

use App\Courses\Domain\Activity;
use App\Courses\Domain\Course;
use App\Courses\Domain\Lesson;
use App\Users\Domain\User;
use App\Tests\BaseTestCase;

class ActivityControllerTest extends BaseTestCase
{
    private int $userId;
    private int $courseId;
    private int $lessonId;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use existing data from fixtures
        // User ID 3: Bob Johnson (enrolled in course 1)
        // Course ID 1: PHP Fundamentals
        // Lesson ID 2: Second lesson (no activity yet for user 3)
        
        $this->userId = 3;
        $this->courseId = 1;
        $this->lessonId = 2;
        
        // Verify data exists in database
        $user = $this->entityManager->find(User::class, $this->userId);
        $course = $this->entityManager->find(Course::class, $this->courseId);
        $lesson = $this->entityManager->find(Lesson::class, $this->lessonId);
        
        $this->assertNotNull($user, 'User with ID 1 should exist in fixtures');
        $this->assertNotNull($course, 'Course with ID 1 should exist in fixtures');
        $this->assertNotNull($lesson, 'Lesson with ID 1 should exist in fixtures');
    }

    public function testRecordLessonActivity(): void
    {
        // Given: We have a user, course, and lesson
        $requestId = 'test-request-' . uniqid();
        
        // When: We record a lesson activity
        $activityData = [
            'userId' => $this->userId,
            'courseId' => $this->courseId,
            'lessonId' => $this->lessonId,
            'action' => 'start',
            'requestId' => $requestId
        ];
        
        $response = $this->makeRequest('POST', '/progress', $activityData);
        
        // Then: We should get a successful response (empty array is expected)
        $this->assertEquals(201, $response['status']);
        $this->assertIsArray($response['content']);
        $this->assertEmpty($response['content']);
        
        // Verify activity was created in database
        $this->clearEntityManager();
        $activity = $this->entityManager->getRepository(Activity::class)
            ->findOneBy([
                'userId' => $this->userId,
                'requestId' => $requestId
            ]);
        
        $this->assertNotNull($activity);
        $this->assertEquals('start', $activity->getAction()->value);
    }

    public function testRecordLessonActivityWithInvalidData(): void
    {
        // Given: We have test data
        
        // When: We try to record activity with invalid data
        $activityData = [
            'userId' => $this->userId,
            'courseId' => $this->courseId,
            'lessonId' => $this->lessonId,
            // Missing action and requestId
        ];
        
        $response = $this->makeRequest('POST', '/progress', $activityData);
        
        // Then: We should get a validation error
        $this->assertEquals(400, $response['status']);
        $this->assertArrayHasKey('errors', $response['content']);
    }

    public function testRecordLessonActivityWithNonExistentUser(): void
    {
        // Given: We have course and lesson but no user
        
        // When: We try to record activity with non-existent user
        $activityData = [
            'userId' => 99999,
            'courseId' => $this->courseId,
            'lessonId' => $this->lessonId,
            'action' => 'start',
            'requestId' => 'test-request-' . uniqid()
        ];
        
        $response = $this->makeRequest('POST', '/progress', $activityData);
        
        // Then: We should get an error response (404 - UserNotFound)
        $this->assertErrorResponse(404, $response);
    }

    public function testRecordLessonActivityWithNonExistentLesson(): void
    {
        // Given: We have user and course but no lesson
        
        // When: We try to record activity with non-existent lesson
        $activityData = [
            'userId' => $this->userId,
            'courseId' => $this->courseId,
            'lessonId' => 99999,
            'action' => 'start',
            'requestId' => 'test-request-' . uniqid()
        ];
        
        $response = $this->makeRequest('POST', '/progress', $activityData);
        
        // Then: We should get an error response (500 - InvalidArgumentException from Course)
        $this->assertErrorResponse(500, $response);
    }

    public function testRecordLessonActivityWithInvalidAction(): void
    {
        // Given: We have test data
        
        // When: We try to record activity with invalid action
        $activityData = [
            'userId' => $this->userId,
            'courseId' => $this->courseId,
            'lessonId' => $this->lessonId,
            'action' => 'invalid_action',
            'requestId' => 'test-request-' . uniqid()
        ];
        
        $response = $this->makeRequest('POST', '/progress', $activityData);
        
        // Then: We should get a validation error (400 - validation should catch invalid action)
        $this->assertEquals(400, $response['status']);
        $this->assertArrayHasKey('errors', $response['content']);
    }

    public function testIncompleteLesson(): void
    {
        // Given: We have a user and lesson
        
        // When: We mark a lesson as incomplete
        $response = $this->makeRequest('DELETE', "/progress/{$this->userId}/lessons/{$this->lessonId}");
        
        // Then: We should get a successful response
        $this->assertJsonResponse(200, $response);
        
        // Verify lesson was marked as incomplete in database
        $this->clearEntityManager();
        $lesson = $this->entityManager->find(Lesson::class, $this->lessonId);
        $this->assertFalse($lesson->hasUserCompleted($this->userId));
    }

    public function testIncompleteLessonWithNonExistentUser(): void
    {
        // Given: We have a lesson but no user
        
        // When: We try to mark lesson as incomplete for non-existent user
        $response = $this->makeRequest('DELETE', "/progress/99999/lessons/{$this->lessonId}");
        
        // Then: We should get an error response (404 - UserNotFound)
        $this->assertErrorResponse(404, $response);
    }

    public function testIncompleteLessonWithNonExistentLesson(): void
    {
        // Given: We have a user but no lesson
        
        // When: We try to mark non-existent lesson as incomplete
        $response = $this->makeRequest('DELETE', "/progress/{$this->userId}/lessons/99999");
        
        // Then: We should get an error response (404 - CourseNotFound)
        $this->assertErrorResponse(404, $response);
    }

    public function testRecordDuplicateActivity(): void
    {
        // Given: We have test data
        
        // When: We record the same activity twice
        $activityData = [
            'userId' => $this->userId,
            'courseId' => $this->courseId,
            'lessonId' => $this->lessonId,
            'action' => 'start',
            'requestId' => 'duplicate-request-123'
        ];
        
        // First record
        $this->makeRequest('POST', '/progress', $activityData);
        
        // Second record with same requestId
        $response = $this->makeRequest('POST', '/progress', $activityData);
        
        // Then: We should get an error response (409 - DuplicateActivityException)
        $this->assertErrorResponse(409, $response);
    }
}
