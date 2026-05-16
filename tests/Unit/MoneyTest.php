<?php

namespace Tests\Unit;

use App\Domain\Wallet\Exception\InsufficientBalanceException;
use App\Domain\Wallet\Exception\InvalidAmountException;
use App\Domain\Wallet\Aggregate\Wallet;
use App\Domain\Wallet\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_deposit_increases_balance(): void
    {
        $wallet = Wallet::create(1);
        $wallet->deposit(Money::fromCents(5000));

        $this->assertSame(5000, $wallet->balance()->cents());
    }

    public function test_deposit_on_negative_balance(): void
    {
        $wallet = Wallet::reconstitute(1, 1, Money::fromBalanceCents(-10000), 0);
        $wallet->deposit(Money::fromCents(3000));

        $this->assertSame(-7000, $wallet->balance()->cents());
    }

    public function test_withdraw_fails_when_insufficient_balance(): void
    {
        $wallet = Wallet::create(1);
        $wallet->deposit(Money::fromCents(1000));

        $this->expectException(InsufficientBalanceException::class);
        $wallet->withdraw(Money::fromCents(2000));
    }

    public function test_zero_amount_is_invalid(): void
    {
        $this->expectException(InvalidAmountException::class);
        Money::fromCents(0);
    }
}
