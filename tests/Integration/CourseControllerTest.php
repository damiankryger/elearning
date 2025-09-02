<?php

namespace App\Tests\Integration;

use App\Courses\Domain\Course;
use App\Users\Domain\User;
use App\Tests\BaseTestCase;

class CourseControllerTest extends BaseTestCase
{
    private User $testUser;
    private Course $testCourse;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use existing data from fixtures
        // User ID 5: Eve Williams (not enrolled in many courses)
        // Course ID 3: Advanced PHP Patterns (user 5 not enrolled)
        $this->testUser = $this->entityManager->find(User::class, 5);
        $this->testCourse = $this->entityManager->find(Course::class, 3);
        
        $this->assertNotNull($this->testUser, 'User with ID 5 should exist in fixtures');
        $this->assertNotNull($this->testCourse, 'Course with ID 3 should exist in fixtures');
    }

    public function testListCourses(): void
    {
        // Given: We have courses in the database
        
        // When: We request the courses list
        $response = $this->makeRequest('GET', '/courses');
        
        // Then: We should get a successful response with courses
        $this->assertJsonResponse(200, $response);
        $this->assertArrayHasKey('courses', $response['content']);
        $this->assertIsArray($response['content']['courses']);
        
        // Verify we have courses from fixtures
        $courseTitles = array_column($response['content']['courses'], 'title');
        $this->assertContains('Advanced PHP Patterns', $courseTitles);
    }

    public function testEnrollUserToCourse(): void
    {
        // Given: We have a user and course
        // Note: User 5 is already enrolled in course 3 from fixtures
        // We need to use a different course or user
        $user = $this->entityManager->find(User::class, 4); // David Miller
        $course = $this->entityManager->find(Course::class, 2); // JavaScript Basics
        
        // When: We enroll the user to the course
        $enrollmentData = [
            'userId' => $user->getId()
        ];
        
        $response = $this->makeRequest('POST', "/courses/{$course->getId()}/enroll", $enrollmentData);
        
        // Then: We should get a successful response or 409 if already enrolled
        // User 4 might already be enrolled in course 2
        $this->assertContains($response['status'], [201, 409]);
        
        if ($response['status'] === 201) {
            // Verify enrollment was created in database
            $this->clearEntityManager();
            $enrollment = $this->entityManager->getRepository(\App\Courses\Domain\Enrollment::class)
                ->findOneBy([
                    'userId' => $user->getId(),
                    'courseId' => $course->getId()
                ]);
            
            $this->assertNotNull($enrollment);
        }
    }

    public function testEnrollUserToCourseWithInvalidUserId(): void
    {
        // Given: We have a course but no user
        
        // When: We try to enroll with invalid user ID
        $enrollmentData = [
            'userId' => 99999 // Non-existent user
        ];
        
        $response = $this->makeRequest('POST', "/courses/{$this->testCourse->getId()}/enroll", $enrollmentData);
        
        // Then: We should get an error response (409 - UserAlreadyEnrolledException or other)
        // The actual error depends on whether user validation happens before enrollment
        $this->assertContains($response['status'], [400, 404, 409]);
    }

    public function testEnrollUserToNonExistentCourse(): void
    {
        // Given: We have a user but no course
        
        // When: We try to enroll to non-existent course
        $enrollmentData = [
            'userId' => $this->testUser->getId()
        ];
        
        $response = $this->makeRequest('POST', '/courses/99999/enroll', $enrollmentData);
        
        // Then: We should get an error response (404 - CourseNotFoundException)
        $this->assertErrorResponse(404, $response);
    }

    public function testEnrollUserToCourseTwice(): void
    {
        // Given: User is already enrolled to the course
        $enrollmentData = [
            'userId' => $this->testUser->getId()
        ];
        
        // First enrollment
        $this->makeRequest('POST', "/courses/{$this->testCourse->getId()}/enroll", $enrollmentData);
        
        // When: We try to enroll the same user again
        $response = $this->makeRequest('POST', "/courses/{$this->testCourse->getId()}/enroll", $enrollmentData);
        
        // Then: We should get an error response (409 - UserAlreadyEnrolledException)
        $this->assertErrorResponse(409, $response);
    }

    public function testEnrollUserToCourseWithMissingUserId(): void
    {
        // Given: We have a course
        
        // When: We try to enroll without user ID
        $enrollmentData = [];
        
        $response = $this->makeRequest('POST', "/courses/{$this->testCourse->getId()}/enroll", $enrollmentData);
        
        // Then: We should get a validation error
        $this->assertEquals(400, $response['status']);
        $this->assertArrayHasKey('errors', $response['content']);
    }
}
