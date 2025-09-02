<?php

namespace App\Tests\Integration;

use App\Courses\Domain\Course;
use App\Users\Domain\User;
use App\Tests\BaseTestCase;

class UserControllerTest extends BaseTestCase
{
    private User $testUser;
    private Course $testCourse;
    private int $userId;
    private int $courseId;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use existing data from fixtures
        // User ID 1: John Doe (enrolled in multiple courses)
        // User ID 5: Eve Williams (enrolled in few courses)
        // Course ID 1: PHP Fundamentals
        
        $this->testUser = $this->entityManager->find(User::class, 1);
        $this->testCourse = $this->entityManager->find(Course::class, 1);
        
        $this->assertNotNull($this->testUser, 'User with ID 1 should exist in fixtures');
        $this->assertNotNull($this->testCourse, 'Course with ID 1 should exist in fixtures');
        
        // Get IDs after loading from DB
        $this->userId = $this->testUser->getId();
        $this->courseId = $this->testCourse->getId();
        
        // Verify IDs are available
        $this->assertNotNull($this->userId);
        $this->assertNotNull($this->courseId);
    }

    public function testGetUserCourses(): void
    {
        // Given: We have a user and course
        
        // When: We request the user's courses
        $response = $this->makeRequest('GET', "/users/{$this->userId}/courses");
        
        // Then: We should get a successful response
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('courses', $response['content']);
        $this->assertIsArray($response['content']['courses']);
    }

    public function testGetUserCoursesWithNonExistentUser(): void
    {
        // Given: We have no user with ID 99999
        
        // When: We try to get courses for non-existent user
        $response = $this->makeRequest('GET', '/users/99999/courses');
        
        // Then: We should get a successful response with empty courses (no enrollment for non-existent user)
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('courses', $response['content']);
        $this->assertIsArray($response['content']['courses']);
        $this->assertEmpty($response['content']['courses']);
    }

    public function testGetUserCoursesWithUserHavingNoCourses(): void
    {
        // Given: We create a new user without any course enrollments
        $userWithoutCourses = new User('User Without Courses', 'nocourses_' . uniqid() . '@example.com');
        $this->persistAndFlush($userWithoutCourses);
        $userWithoutCoursesId = $userWithoutCourses->getId();
        
        // When: We request the user's courses
        $response = $this->makeRequest('GET', "/users/{$userWithoutCoursesId}/courses");
        
        // Then: We should get a successful response with empty courses array
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('courses', $response['content']);
        $this->assertIsArray($response['content']['courses']);
        $this->assertEmpty($response['content']['courses']);
    }
}
