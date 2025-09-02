<?php

namespace App\Courses\Application\Requests;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateNewEnrollmentRequest
{
    #[Assert\NotNull(message: 'user_id is required')]
    #[Assert\Type(type: 'integer', message: 'user_id must be an integer')]
    #[Assert\Positive(message: 'user_id must be a positive integer')]
    public int $userId;

    public function __construct(array $data)
    {
        $this->userId = $data['userId'] ?? $data['user_id'] ?? 0;
    }
}
