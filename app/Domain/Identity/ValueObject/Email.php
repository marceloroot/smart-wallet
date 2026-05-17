<?php

namespace App\Domain\Identity\ValueObject;

use App\Domain\Identity\Exception\InvalidEmailException;

final class Email
{
    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        if (! filter_var($normalized, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException();
        }

        return new self($normalized);
    }

    public function toString(): string
    {
        return $this->value;
    }
}
