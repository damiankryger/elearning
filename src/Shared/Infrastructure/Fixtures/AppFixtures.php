<?php

namespace App\Shared\Infrastructure\Fixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\DBAL\Connection;

final class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $connection = $manager->getConnection();
        
        echo "🚀 Loading fixtures with direct SQL queries...\n";
        
        try {
            // Clear existing data
            $this->clearData($connection);
            
            // Insert test data
            $this->insertUsers($connection);
            $this->insertCourses($connection);
            $this->insertLessons($connection);
            $this->insertEnrollments($connection);
            $this->insertActivities($connection);
            
            echo "✅ Fixtures loaded successfully!\n";
            $this->displaySummary($connection);
            
        } catch (\Exception $e) {
            echo "❌ Error loading fixtures: " . $e->getMessage() . "\n";
            throw $e;
        }
    }
    
    private function clearData(Connection $connection): void
    {
        echo "🧹 Clearing existing data...\n";
        
        $connection->executeStatement('DELETE FROM activity');
        $connection->executeStatement('DELETE FROM enrollment');
        $connection->executeStatement('DELETE FROM lesson');
        $connection->executeStatement('DELETE FROM course');
        $connection->executeStatement('DELETE FROM user');
        
        // Reset auto-increment counters
        $connection->executeStatement('DELETE FROM sqlite_sequence WHERE name IN ("user", "course", "lesson", "enrollment", "activity")');
    }
    
    private function insertUsers(Connection $connection): void
    {
        echo "👥 Creating users...\n";
        
        $sql = "INSERT INTO user (name, email) VALUES 
                ('John Doe', 'john@example.com'),
                ('Jane Smith', 'jane@example.com'),
                ('Bob Johnson', 'bob@example.com'),
                ('Alice Brown', 'alice@example.com'),
                ('Charlie Wilson', 'charlie@example.com')";
        
        $connection->executeStatement($sql);
    }
    
    private function insertCourses(Connection $connection): void
    {
        echo "📚 Creating courses...\n";
        
        $sql = "INSERT INTO course (title) VALUES 
                ('PHP Fundamentals'),
                ('Symfony Framework'),
                ('Advanced PHP Patterns'),
                ('Web Security'),
                ('API Development')";
        
        $connection->executeStatement($sql);
    }
    
    private function insertLessons(Connection $connection): void
    {
        echo "📖 Creating lessons...\n";
        
        $sql = "INSERT INTO lesson (course_id, title, order_number) VALUES 
                -- PHP Fundamentals course (id: 1)
                (1, 'Introduction to PHP', 1),
                (1, 'Variables and Data Types', 2),
                (1, 'Control Structures', 3),
                (1, 'Functions', 4),
                (1, 'Arrays', 5),
                (1, 'Object-Oriented Programming', 6),
                
                -- Symfony Framework course (id: 2)
                (2, 'Symfony Overview', 1),
                (2, 'Routing', 2),
                (2, 'Controllers', 3),
                (2, 'Doctrine ORM', 4),
                (2, 'Forms', 5),
                (2, 'Security', 6),
                (2, 'Twig Templates', 7),
                
                -- Advanced PHP Patterns course (id: 3)
                (3, 'Design Patterns', 1),
                (3, 'SOLID Principles', 2),
                (3, 'Dependency Injection', 3),
                (3, 'Event-Driven Architecture', 4),
                
                -- Web Security course (id: 4)
                (4, 'OWASP Top 10', 1),
                (4, 'SQL Injection Prevention', 2),
                (4, 'XSS Protection', 3),
                (4, 'CSRF Protection', 4),
                
                -- API Development course (id: 5)
                (5, 'REST API Design', 1),
                (5, 'Authentication & Authorization', 2),
                (5, 'API Versioning', 3),
                (5, 'Rate Limiting', 4)";
        
        $connection->executeStatement($sql);
    }
    
    private function insertEnrollments(Connection $connection): void
    {
        echo "🎯 Creating enrollments...\n";
        
        $sql = "INSERT INTO enrollment (course_id, user_id, enrolled_at) VALUES 
                (1, 1, '2024-01-15 10:00:00'),
                (1, 2, '2024-01-16 14:30:00'),
                (1, 3, '2024-01-17 09:15:00'),
                (2, 1, '2024-01-20 11:00:00'),
                (2, 4, '2024-01-21 16:45:00'),
                (3, 1, '2024-02-01 13:20:00'),
                (3, 2, '2024-02-02 10:30:00'),
                (4, 3, '2024-02-05 15:00:00'),
                (4, 5, '2024-02-06 12:00:00'),
                (5, 1, '2024-02-10 09:00:00'),
                (5, 4, '2024-02-11 14:00:00')";
        
        $connection->executeStatement($sql);
    }
    
    private function insertActivities(Connection $connection): void
    {
        echo "📊 Creating activities...\n";
        
        $sql = "INSERT INTO activity (lesson_id, user_id, action, request_id, occurred_at) VALUES 
                -- User 1 activities (różne lekcje)
                (1, 1, 'complete', 'req_001_001', '2024-01-15 10:25:00'),
                (2, 1, 'complete', 'req_001_002', '2024-01-15 11:00:00'),
                (6, 1, 'complete', 'req_001_003', '2024-01-18 11:30:00'),
                (7, 1, 'complete', 'req_001_004', '2024-01-20 12:00:00'),
                (8, 1, 'complete', 'req_001_005', '2024-01-20 13:00:00'),
                (14, 1, 'complete', 'req_001_006', '2024-02-01 14:15:00'),
                (22, 1, 'complete', 'req_001_007', '2024-02-10 10:00:00'),
                
                -- User 2 activities
                (1, 2, 'complete', 'req_002_001', '2024-01-16 15:00:00'),
                (14, 2, 'complete', 'req_002_002', '2024-02-02 11:20:00'),
                
                -- User 3 activities
                (1, 3, 'start', 'req_003_001', '2024-01-17 09:20:00'),
                (18, 3, 'complete', 'req_003_002', '2024-02-05 15:45:00'),
                
                -- User 4 activities
                (7, 4, 'complete', 'req_004_001', '2024-01-21 17:30:00'),
                (22, 4, 'complete', 'req_004_002', '2024-02-11 15:00:00'),
                
                -- User 5 activities
                (18, 5, 'complete', 'req_005_001', '2024-02-06 12:40:00')";
        
        $connection->executeStatement($sql);
    }
    
    private function displaySummary(Connection $connection): void
    {
        echo "\n📊 Database Summary:\n";
        echo "==================\n";
        
        $tables = ['user', 'course', 'lesson', 'enrollment', 'activity'];
        
        foreach ($tables as $table) {
            $count = $connection->fetchOne("SELECT COUNT(*) FROM $table");
            echo sprintf("   %-12s: %d records\n", ucfirst($table), $count);
        }
        
        echo "\n🎯 Sample Data:\n";
        echo "==============\n";
        
        // Show some sample users
        $users = $connection->fetchAllAssociative("SELECT name, email FROM user LIMIT 3");
        echo "Users: " . implode(', ', array_map(fn($u) => $u['name'], $users)) . "...\n";
        
        // Show some sample courses
        $courses = $connection->fetchAllAssociative("SELECT title FROM course LIMIT 3");
        echo "Courses: " . implode(', ', array_map(fn($c) => $c['title'], $courses)) . "...\n";
        
        // Show enrollment count
        $enrollmentCount = $connection->fetchOne("SELECT COUNT(*) FROM enrollment");
        echo "Total Enrollments: $enrollmentCount\n";
        
        // Show activity count
        $activityCount = $connection->fetchOne("SELECT COUNT(*) FROM activity");
        echo "Total Activities: $activityCount\n";
    }
}
