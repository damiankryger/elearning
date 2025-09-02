# E-Learning Platform API

## Project Overview

This is a backend API for an e-learning platform designed to manage online courses, track student progress, and enforce learning prerequisites. The system provides RESTful endpoints for course enrollment, lesson completion tracking, and progress monitoring. Built with Domain-Driven Design principles, the application separates business logic into distinct bounded contexts for courses and users.

## Technology Stack

The application is built using modern PHP technologies and follows enterprise-level architectural patterns. The core framework is Symfony 7.2 running on PHP 8.4, utilizing Doctrine ORM for database persistence. PostgreSQL serves as the primary database in the Docker environment, while SQLite can be used for local development. The project uses Composer for dependency management and PHPUnit for testing.

## Architecture

The codebase follows Domain-Driven Design with clear separation between domain logic, application services, and infrastructure concerns. The project is organized into two main bounded contexts: Courses and Users. Each context maintains its own domain models, repositories, and application services. The architecture ensures that business rules are enforced at the domain level, making the system robust and maintainable.

## Docker Environment

The application runs in a containerized environment using Docker Compose. The setup includes four services: PHP-FPM 8.4 for application processing, Nginx for web serving, PostgreSQL 16 for data persistence, and Adminer for database management. All services are connected through a dedicated Docker network.

To start the application with Docker Compose, ensure Docker and Docker Compose are installed on your system. Navigate to the project directory and run:

```bash
docker compose up -d
```

The application will be available at http://localhost:8080 and Adminer database interface at http://localhost:8081.

## Database Configuration

The Docker environment uses PostgreSQL with the following default credentials:
- Database name: elearning
- Username: elearning_user  
- Password: elearning_password
- Host: database (within Docker network)
- Port: 5432

For local development without Docker, you can configure SQLite by updating the DATABASE_URL in your .env file.

## Installation and Setup

After starting the Docker containers, you need to set up the database schema and load sample data. Execute the following commands:

```bash
# Run database migrations
docker exec elearning_php_fpm php bin/console doctrine:migrations:migrate

# Load fixture data for testing
docker exec elearning_php_fpm php bin/console doctrine:fixtures:load
```

The fixture data includes sample users, courses, lessons, enrollments, and activity records that demonstrate the system's capabilities.

## Running Tests

The project includes comprehensive integration tests for all API endpoints. Tests are written using PHPUnit and cover various scenarios including success cases, validation errors, and business rule violations.

To run all tests:

```bash
docker exec elearning_php_fpm php bin/phpunit
```

To run tests for a specific controller:

```bash
docker exec elearning_php_fpm php bin/phpunit tests/Integration/CourseControllerTest.php
```

To run a specific test method:

```bash
docker exec elearning_php_fpm php bin/phpunit --filter testEnrollUserToCourse
```

The test suite uses database transactions to ensure test isolation. Each test runs in its own transaction that gets rolled back after completion, maintaining a clean state between tests.

## API Endpoints

### Course Management

The courses endpoint provides functionality for listing available courses and managing enrollments.

GET /courses returns a list of all courses with their details including title, description, and total number of lessons.

POST /courses/{id}/enroll enrolls a user in a specific course. The request requires a JSON payload with the user ID. The system validates that the user is not already enrolled. Returns 201 on success or appropriate error codes for various failure scenarios.

### User Management

GET /users/{id}/courses retrieves all courses in which a specific user is enrolled. The response includes course IDs, titles, and the total number of lessons in each course.

### Progress Tracking

The progress endpoints handle lesson completion and progress monitoring.

POST /progress records a lesson activity such as starting or completing a lesson. The request must include user ID, course ID, lesson ID, action type (start, complete, or incomplete), and a unique request ID for idempotency. The system enforces prerequisites, ensuring lessons are completed in order.

GET /progress/{userId}/courses/{courseId} retrieves detailed progress information for a user in a specific course. The response includes the number of completed lessons, total lessons, completion percentage, and the status of each lesson.

DELETE /progress/{userId}/lessons/{lessonId} marks a lesson as incomplete, allowing users to reset their progress for a specific lesson.

## Business Rules

The system enforces several important business rules to maintain data integrity and proper learning flow.

Lesson prerequisites ensure that students must complete lessons in sequential order. When attempting to complete lesson N, the system verifies that lessons 1 through N-1 have been completed. If prerequisites are not met, the API returns a 409 Conflict status with details about missing lessons.

Enrollment uniqueness prevents duplicate enrollments. A user can only be enrolled once in each course. Attempts to re-enroll result in a 409 Conflict response.

Activity idempotency uses request IDs to prevent duplicate activity records. If the same request ID is used twice, the system returns a 409 Conflict, ensuring that network retries don't create duplicate progress entries.

## Domain Model

The domain model consists of several key entities that represent the business concepts.

The Course entity represents educational courses with properties for title and description. It maintains collections of lessons and enrollments, enforcing business rules through domain methods.

The Lesson entity belongs to a course and has an order number that determines the sequence in which lessons must be completed. Each lesson tracks which users have completed it.

The User entity represents system users with basic properties like name and email. Users can be enrolled in multiple courses and track progress across different lessons.

The Enrollment entity represents the many-to-many relationship between users and courses, including the enrollment date.

The Activity entity records user actions on lessons, supporting event sourcing patterns for tracking the complete history of user interactions with course content.

## Error Handling

The application implements comprehensive error handling with appropriate HTTP status codes and meaningful error messages.

Validation errors (400 Bad Request) occur when request data doesn't meet requirements, such as missing required fields or invalid data types. The response includes specific validation error details.

Domain exceptions are mapped to appropriate HTTP status codes. For example, CourseNotFoundException returns 404, UserAlreadyEnrolledException returns 409, and PrerequisiteNotMetException returns 409 with details about missing prerequisites.

All exceptions are logged with context information including request details, making debugging and monitoring easier in production environments.

## CLI Commands

The application includes a command-line interface for administrative tasks.

The progress:summary command displays a formatted summary of a user's progress in a course:

```bash
docker exec elearning_php_fpm php bin/console progress:summary {userId} {courseId}
```

This command queries the database and presents a human-readable summary showing completed lessons, pending lessons, and overall progress percentage.

## Development Workflow

When developing new features, follow the established architectural patterns. Domain logic should reside in domain services and entities. Application services orchestrate use cases and handle request/response transformation. Infrastructure concerns like database access are isolated in repository implementations.

The codebase uses PHP 8.4 features including readonly properties, constructor property promotion, and typed properties. Follow PSR-12 coding standards and ensure all new code includes appropriate type declarations.

## Environment Variables

The application uses environment variables for configuration. Key variables include:

- APP_ENV: Set to 'dev' for development or 'prod' for production
- APP_SECRET: Application secret for security features
- DATABASE_URL: Database connection string
- POSTGRES_DB: PostgreSQL database name (Docker environment)
- POSTGRES_USER: PostgreSQL username (Docker environment)
- POSTGRES_PASSWORD: PostgreSQL password (Docker environment)

## Monitoring and Logging

The application includes comprehensive logging through Symfony's monolog integration. All exceptions are logged with full context, including request details and stack traces. In development mode, detailed error information is returned in API responses to aid debugging.

## Performance Considerations

The application uses Doctrine's query optimization features including lazy loading and query result caching where appropriate. Database queries are optimized to minimize N+1 problems, particularly in endpoints that return collections of entities.

For production deployments, consider implementing API rate limiting, response caching, and database query optimization based on actual usage patterns.