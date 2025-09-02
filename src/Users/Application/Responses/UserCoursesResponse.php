<?php

namespace App\Users\Application\Responses;

class UserCoursesResponse implements \JsonSerializable
{
    public function __construct(
        private array $courses
    ) {}

    public function toArray(): array
    {
        return ['courses' => $this->courses];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
