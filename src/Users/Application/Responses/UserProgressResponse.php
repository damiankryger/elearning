<?php

namespace App\Users\Application\Responses;

class UserProgressResponse implements \JsonSerializable
{
    public function __construct(
        private array $progress
    ) {}

    public function toArray(): array
    {
        return $this->progress;
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }
}
