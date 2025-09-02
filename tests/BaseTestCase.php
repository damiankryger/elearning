<?php

namespace App\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class BaseTestCase extends WebTestCase
{
    protected ?EntityManagerInterface $entityManager = null;
    protected ?ContainerInterface $container = null;
    protected $client;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient();
        $this->container = static::getContainer();
        $this->entityManager = $this->container->get(EntityManagerInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * Helper method to create and persist test data
     */
    protected function persistAndFlush(object $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    /**
     * Helper method to clear entity manager
     */
    protected function clearEntityManager(): void
    {
        $this->entityManager->clear();
    }

    /**
     * Helper method to make HTTP request and assert response
     */
    protected function makeRequest(string $method, string $uri, array $data = [], array $headers = []): array
    {
        $this->client->request(
            $method,
            $uri,
            [],
            [],
            array_merge([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
            ], $headers),
            json_encode($data)
        );

        $response = $this->client->getResponse();
        $content = $response->getContent();
        
        $decodedContent = json_decode($content, true);
        
        return [
            'status' => $response->getStatusCode(),
            'content' => $decodedContent !== null ? $decodedContent : $content,
            'headers' => $response->headers->all()
        ];
    }

    /**
     * Helper method to assert JSON response
     */
    protected function assertJsonResponse(int $expectedStatus, array $response): void
    {
        $this->assertEquals($expectedStatus, $response['status']);
        $this->assertIsArray($response['content']);
    }

    /**
     * Helper method to assert error response
     */
    protected function assertErrorResponse(int $expectedStatus, array $response, ?string $expectedErrorType = null): void
    {
        $this->assertEquals($expectedStatus, $response['status']);
        $this->assertArrayHasKey('error', $response['content']);
        
        if ($expectedErrorType) {
            $this->assertEquals($expectedErrorType, $response['content']['error']);
        }
    }
}
