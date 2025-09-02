<?php

namespace App\Users\Domain;

use App\Shared\Application\ValidationException;
use App\Users\Infrastructure\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, unique: true)]
    private string $email;

    public function __construct(string $name, string $email)
    {
        $this->validateName($name);
        $this->validateEmail($email);

        $this->name = $name;
        $this->email = $email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    /**
     * Validate user name
     */
    private function validateName(string $name): void
    {
        $validationErrors = [];

        if (empty(trim($name))) {
            $validationErrors['name'] = 'User name cannot be empty';
        }

        if (strlen($name) > 255) {
            $validationErrors['name'] = 'User name cannot exceed 255 characters';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('User name validation failed', $validationErrors);
        }
    }

    /**
     * Validate user email
     */
    private function validateEmail(string $email): void
    {
        $validationErrors = [];

        if (empty(trim($email))) {
            $validationErrors['email'] = 'User email cannot be empty';
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $validationErrors['email'] = 'Invalid email format';
        }

        if (strlen($email) > 255) {
            $validationErrors['email'] = 'User email cannot exceed 255 characters';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('User email validation failed', $validationErrors);
        }
    }
}
