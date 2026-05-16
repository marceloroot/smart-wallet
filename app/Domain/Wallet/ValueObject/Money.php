<?php

namespace App\Domain\Wallet\ValueObject;

use App\Domain\Wallet\Exception\InvalidAmountException;

final class Money
{
    private function __construct(private int $cents)
    {
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public static function fromCents(int $cents): self
    {
        if ($cents <= 0) {
            throw InvalidAmountException::mustBePositive();
        }

        return new self($cents);
    }

    public static function fromBalanceCents(int $cents): self
    {
        return new self($cents);
    }

    public static function fromAmount(float $amount): self
    {
        return self::fromCents((int) round($amount * 100));
    }

    public function cents(): int
    {
        return $this->cents;
    }

    public function amount(): float
    {
        return $this->cents / 100;
    }

    public function isZero(): bool
    {
        return $this->cents === 0;
    }

    public function isGreaterOrEqual(self $other): bool
    {
        return $this->cents >= $other->cents;
    }

    public function add(self $other): self
    {
        return new self($this->cents + $other->cents);
    }

    public function subtract(self $other): self
    {
        return new self($this->cents - $other->cents);
    }

    public function equals(self $other): bool
    {
        return $this->cents === $other->cents;
    }
}
