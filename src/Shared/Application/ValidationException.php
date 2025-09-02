<?php

namespace App\Shared\Application;

use Exception;
use RuntimeException;

class ValidationException extends RuntimeException
{
    private array $validationErrors;

    public function __construct(string $message = "Validation failed", array $validationErrors = [], int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->validationErrors = $validationErrors;
    }

    public function getValidationErrors(): array
    {
        return $this->validationErrors;
    }

    public function getValidationErrorsAsString(): string
    {
        if (empty($this->validationErrors)) {
            return $this->getMessage();
        }

        $errors = [];
        foreach ($this->validationErrors as $field => $message) {
            $errors[] = "{$field}: {$message}";
        }

        return implode('; ', $errors);
    }
}
