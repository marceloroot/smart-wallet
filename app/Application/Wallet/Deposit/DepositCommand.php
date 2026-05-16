<?php

namespace App\Application\Wallet\Deposit;

final class DepositCommand
{
    public function __construct(
        public int $userId,
        public int $amountCents,
        public ?string $idempotencyKey = null,
    ) {
    }
}
