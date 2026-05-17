<?php

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\ValueObject\Email;

final class User
{
    private function __construct(
        private ?int $id,
        private string $name,
        private Email $email,
        private ?string $hashedPassword,
        private ?string $plainPassword,
    ) {
    }

    public static function register(string $name, string $email, string $plainPassword): self
    {
        return new self(
            id: null,
            name: $name,
            email: Email::fromString($email),
            hashedPassword: null,
            plainPassword: $plainPassword,
        );
    }

    public static function reconstitute(
        int $id,
        string $name,
        string $email,
        string $hashedPassword,
    ): self {
        return new self(
            id: $id,
            name: $name,
            email: Email::fromString($email),
            hashedPassword: $hashedPassword,
            plainPassword: null,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email->toString();
    }

    public function hashedPassword(): ?string
    {
        return $this->hashedPassword;
    }

    public function plainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function withId(int $id): self
    {
        return new self(
            id: $id,
            name: $this->name,
            email: $this->email,
            hashedPassword: $this->hashedPassword,
            plainPassword: null,
        );
    }

    public function withHashedPassword(string $hashedPassword): self
    {
        return new self(
            id: $this->id,
            name: $this->name,
            email: $this->email,
            hashedPassword: $hashedPassword,
            plainPassword: null,
        );
    }
}
