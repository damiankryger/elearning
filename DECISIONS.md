# Architectural and Technical Decisions

## Architecture Pattern: Domain-Driven Design

The application follows Domain-Driven Design principles with clear separation between domain logic and infrastructure. The codebase is organized into two bounded contexts: Courses and Users. Each context maintains its own domain models, services, and repositories. This decision was made to ensure that business logic remains isolated from framework-specific code, making the system more maintainable and testable.

The domain layer contains entities like Course, Lesson, User, Enrollment, and Activity that encapsulate business rules. Domain services such as ActivityService and EnrollmentService orchestrate complex business operations. This approach ensures that business rules are enforced consistently regardless of how the application is accessed.

## Technology Stack Selection

### PHP 8.4 and Symfony 7.2

The choice of PHP 8.4 provides access to modern language features such as readonly properties, constructor property promotion, and improved type system. Symfony 7.2 was selected as the framework due to its mature ecosystem, excellent dependency injection container, and robust HTTP foundation components. The framework's flexibility allows for clean implementation of Domain-Driven Design patterns without forcing specific architectural decisions.

### PostgreSQL for Production Database

PostgreSQL was chosen as the production database for its reliability, advanced features, and excellent support for complex queries. The decision to use PostgreSQL over MySQL was driven by its superior handling of concurrent transactions, which is important for an e-learning platform where multiple users might be updating progress simultaneously. The database runs in a Docker container for consistency across development environments.

### Doctrine ORM

Doctrine ORM provides a powerful abstraction layer between the domain models and database. The decision to use Doctrine was based on its support for the Data Mapper pattern, which aligns well with Domain-Driven Design principles. Unlike Active Record ORMs, Doctrine allows domain entities to remain free of persistence concerns. The ORM's migration system ensures database schema changes are version-controlled and reproducible.

## API Design Decisions

### RESTful Endpoints

The API follows REST conventions with resource-based URLs and appropriate HTTP methods. GET requests retrieve data, POST creates new resources, and DELETE removes them. This conventional approach makes the API predictable and easy to integrate with various client applications.

### Request/Response Objects

Dedicated request and response objects are used for API communication. Classes like RecordLessonActivityRequest and UserProgressResponse provide type safety and clear contracts for API consumers. This approach separates API concerns from domain logic and enables validation at the application boundary.

### Idempotency Through Request IDs

The progress tracking endpoint requires a request ID to ensure idempotency. This prevents duplicate activity records if network issues cause request retries. The system returns a 409 Conflict status when a duplicate request ID is detected, allowing clients to handle retries safely.

## Business Rule Implementation

### Lesson Prerequisites in Domain Layer

The requirement that lessons must be completed in sequential order is enforced within the Course domain entity. The completeLesson method checks prerequisites before allowing completion, throwing a PrerequisiteNotMetException with details about missing lessons. This ensures the rule is consistently applied regardless of how the action is triggered.

### Enrollment Constraints

The system enforces unique enrollments per user and course combination through the domain model. The Course entity's enroll method checks for existing enrollments and throws UserAlreadyEnrolledException when duplicates are detected. This domain-level validation ensures data integrity even if database constraints were to fail.

## Error Handling Strategy

### Domain Exceptions

Custom domain exceptions extend a base DomainException class that includes HTTP status code mapping. Exceptions like CourseNotFoundException, UserNotFoundException, and PrerequisiteNotMetException provide specific error information while maintaining clean separation between domain and HTTP concerns.

### Exception Listener

A centralized ExceptionListener handles all exceptions and maps them to appropriate HTTP responses. Domain exceptions are automatically converted to JSON responses with appropriate status codes. In development mode, additional debugging information is included in responses. This approach ensures consistent error handling across all endpoints.

### Validation Errors

Request validation uses Symfony's validator component with custom validation rules. Validation errors return a 400 status with detailed error messages in a consistent format. The decision to validate at the application boundary ensures invalid data never reaches the domain layer.

## Database Design

### Event Sourcing Pattern for Activities

The Activity entity implements a partial event sourcing pattern, recording all user actions with timestamps and request IDs. This provides a complete audit trail of user interactions and enables reconstruction of user progress history. The decision to store individual events rather than just current state enables future analytics and debugging capabilities.

### Separate Tables for Domain Concepts

Each domain concept has its own table: course, lesson, user, enrollment, and activity. The decision to avoid denormalization keeps the schema clean and maintains referential integrity. Join tables are used for many-to-many relationships, following conventional database design patterns.

### No Soft Deletes

The system uses hard deletes rather than soft deletes. This decision simplifies queries and maintains data integrity. If audit trails are needed, the activity table provides a complete history of actions.

## Testing Strategy

### Integration Tests Over Unit Tests

The test suite focuses on integration tests that verify complete request/response cycles. This decision was based on the need to ensure API contracts are maintained and business rules work correctly in context. Integration tests provide more confidence that the system works as expected from a client's perspective.

### Database Transactions for Test Isolation

Tests use database transactions that are rolled back after each test. This ensures tests don't affect each other and can run in any order. The decision to use real database operations rather than mocks ensures tests accurately reflect production behavior.

### Fixture Data

Comprehensive fixture data provides a realistic testing environment with various scenarios: users enrolled in multiple courses, partially completed courses, and courses at capacity. This approach enables thorough testing of edge cases and business rules.

## Development Environment

### Docker Compose Configuration

Docker Compose orchestrates four containers: PHP-FPM, Nginx, PostgreSQL, and Adminer. This decision ensures all developers work with identical environments regardless of their host operating system. The configuration uses explicit container names and a dedicated network for service communication.

### Environment Variables

Configuration uses environment variables following twelve-factor app principles. Sensitive data like database credentials are never committed to version control. The docker.env file provides defaults for development while production deployments can override these values.

### Development Tools

Adminer is included for database inspection during development. The decision to include this tool in the Docker setup reduces the need for developers to install additional database clients. The tool is accessible on a separate port and can be disabled in production.

## Code Organization

### Namespace Structure

The code is organized by bounded context first (Courses, Users), then by layer (Domain, Application, Infrastructure). This structure makes it clear which context owns each piece of functionality and maintains clear boundaries between contexts.

### Repository Pattern

Repositories provide an abstraction over database access. The CourseRepository and UserRepository interfaces define contracts that are implemented by Doctrine-specific classes. This decision allows for potential database technology changes without affecting domain logic.

### Query Objects

Complex read operations use dedicated query objects like GetProgressForUserQuery. These objects contain optimized SQL queries that bypass the ORM for performance. The decision to use raw SQL for complex queries provides better performance while keeping the queries isolated and testable.

## Performance Considerations

### Lazy Loading Strategy

Doctrine is configured to use lazy loading for entity relationships. This prevents unnecessary database queries when relationships aren't accessed. The decision balances performance with code simplicity, avoiding the complexity of explicit eager loading configuration.

### Direct SQL for Read Operations

Read-heavy operations like progress calculation use direct SQL queries rather than ORM operations. This decision significantly improves performance for complex aggregations while maintaining the benefits of ORM for write operations.

### No Caching Layer

The current implementation doesn't include a caching layer. This decision keeps the initial implementation simple while allowing for future cache integration if performance requirements demand it. The stateless nature of the API makes it cache-friendly when needed.

## Security Decisions

### No Authentication Implementation

The current implementation doesn't include authentication or authorization. This decision was made to focus on core business logic implementation. The API expects user IDs to be provided by an external authentication layer, following a microservices pattern where authentication is handled by a dedicated service.

### Input Validation

All user input is validated at the application boundary using Symfony's validation component. The decision to validate early prevents invalid data from reaching the domain layer and provides clear error messages to API consumers.

### SQL Injection Prevention

All database queries use parameterized statements through Doctrine's query builder or prepared statements. This decision ensures the application is protected against SQL injection attacks regardless of input validation.