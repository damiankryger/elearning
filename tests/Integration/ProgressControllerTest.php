<?php

namespace App\Tests\Integration;

use App\Courses\Domain\Course;
use App\Users\Domain\User;
use App\Tests\BaseTestCase;

class ProgressControllerTest extends BaseTestCase
{
    private int $userId;
    private int $courseId;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use existing data from fixtures
        // User ID 2: Jane Smith (enrolled in courses 1 and 3)
        // Course ID 3: Advanced PHP Patterns
        
        $this->userId = 2;
        $this->courseId = 3;
        
        // Verify data exists in database
        $user = $this->entityManager->find(User::class, $this->userId);
        $course = $this->entityManager->find(Course::class, $this->courseId);
        
        $this->assertNotNull($user, 'User with ID 2 should exist in fixtures');
        $this->assertNotNull($course, 'Course with ID 3 should exist in fixtures');
    }

    public function testGetUserProgress(): void
    {
        // Given: We have a user and course
        
        // When: We request the user's progress for a course
        $response = $this->makeRequest('GET', "/progress/{$this->userId}/courses/{$this->courseId}");
        
        // Then: We should get a successful response
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('completed', $response['content']);
        $this->assertArrayHasKey('total', $response['content']);
        $this->assertArrayHasKey('percent', $response['content']);
        $this->assertArrayHasKey('lessons', $response['content']);
        $this->assertIsArray($response['content']['lessons']);
    }

    public function testGetUserProgressWithNonExistentUser(): void
    {
        // Given: We have no user with ID 99999
        
        // When: We try to get progress for non-existent user
        $response = $this->makeRequest('GET', "/progress/99999/courses/{$this->courseId}");
        
        // Then: We should get a successful response (query returns empty results)
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('lessons', $response['content']);
    }

    public function testGetUserProgressWithNonExistentCourse(): void
    {
        // Given: We have a user but no course with ID 99999
        
        // When: We try to get progress for non-existent course
        $response = $this->makeRequest('GET', "/progress/{$this->userId}/courses/99999");
        
        // Then: We should get a successful response with no lessons
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('lessons', $response['content']);
        $this->assertEmpty($response['content']['lessons']);
        $this->assertEquals(0, $response['content']['total']);
    }

    public function testGetUserProgressWithUserNotEnrolled(): void
    {
        // Given: User 4 (David Miller) is not enrolled in course 3
        $notEnrolledUserId = 4;
        
        // When: We request the user's progress for a course they're not enrolled in
        $response = $this->makeRequest('GET', "/progress/{$notEnrolledUserId}/courses/{$this->courseId}");
        
        // Then: We should get a successful response with lessons but no progress
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('lessons', $response['content']);
        $this->assertIsArray($response['content']['lessons']);
        // User not enrolled means no activities, so lessons will be 'pending'
        foreach ($response['content']['lessons'] as $lesson) {
            $this->assertEquals('pending', $lesson['status']);
        }
    }
}
