<?php

namespace App\Shared\Infrastructure\EventListener;

use App\Shared\Application\ValidationException;
use App\Shared\Domain\DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener
{
    public function __construct(
        private ?LoggerInterface $logger = null
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Log all exceptions with context
        if ($this->logger) {
            $context = [
                'exception_class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'uri' => $request->getRequestUri(),
                'method' => $request->getMethod(),
            ];

            if ($request->getContent()) {
                $context['request_body'] = $request->getContent();
            }

            $this->logger->error('Exception occurred', $context);
        }

        if ($exception instanceof DomainException) {
            $response = new JsonResponse(
                $exception->toArray(),
                $exception->getHttpStatusCode()
            );
            $event->setResponse($response);
            return;
        }

        if ($exception instanceof ValidationException) {
            $response = new JsonResponse([
                'error' => 'Validation failed',
                'validation_errors' => $exception->getValidationErrors(),
                'message' => $exception->getValidationErrorsAsString(),
                'http_status' => 400
            ], 400);

            $event->setResponse($response);
            return;
        }

        if ($exception instanceof ValidationFailedException) {
            $violations = $exception->getViolations();
            $errors = [];

            foreach ($violations as $violation) {
                $errors[] = $violation->getMessage();
            }

            $response = new JsonResponse([
                'errors' => $errors,
                'http_status' => 400
            ], 400);

            $event->setResponse($response);
            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
                'http_status' => $exception->getStatusCode()
            ], $exception->getStatusCode());

            $event->setResponse($response);
            return;
        }

        if ($_ENV['APP_ENV'] === 'dev' || $_ENV['APP_ENV'] === 'test') {
            // In test environment, also output to console for debugging
            if ($_ENV['APP_ENV'] === 'test') {
                echo sprintf(
                    "\n[ERROR] %s: %s\n  in %s:%d\n  URI: %s %s\n",
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine(),
                    $request->getMethod(),
                    $request->getRequestUri()
                );
            }
            
            $response = new JsonResponse([
                'error' => $exception->getMessage(),
                'exception_class' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
                'http_status' => 500
            ], 500);

            $event->setResponse($response);
            return;
        }

        $response = new JsonResponse([
            'error' => 'Internal server error',
            'http_status' => 500
        ], 500);

        $event->setResponse($response);
    }
}
