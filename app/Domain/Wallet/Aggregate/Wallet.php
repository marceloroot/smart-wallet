<?php

namespace App\Domain\Wallet\Aggregate;

use App\Domain\Wallet\Exception\InsufficientBalanceException;
use App\Domain\Wallet\Exception\InvalidAmountException;
use App\Domain\Wallet\ValueObject\Money;

final class Wallet
{
    private function __construct(
        private ?int $id,
        private int $userId,
        private Money $balance,
        private int $version,
    ) {
    }

    public static function create(int $userId): self
    {
        return new self(null, $userId, Money::zero(), 0);
    }

    public static function reconstitute(
        int $id,
        int $userId,
        Money $balance,
        int $version,
    ): self {
        return new self($id, $userId, $balance, $version);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function userId(): int
    {
        return $this->userId;
    }

    public function balance(): Money
    {
        return $this->balance;
    }

    public function version(): int
    {
        return $this->version;
    }

    public function deposit(Money $amount): void
    {
        $this->assertPositive($amount);
        $this->balance = $this->balance->add($amount);
    }

    public function withdraw(Money $amount): void
    {
        $this->assertPositive($amount);

        if (! $this->balance->isGreaterOrEqual($amount)) {
            throw new InsufficientBalanceException();
        }

        $this->balance = $this->balance->subtract($amount);
    }

    private function assertPositive(Money $amount): void
    {
        if ($amount->isZero()) {
            throw InvalidAmountException::mustBePositive();
        }
    }
}
