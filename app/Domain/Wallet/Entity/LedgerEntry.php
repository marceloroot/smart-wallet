<?php

namespace App\Domain\Wallet\Entity;

use App\Domain\Wallet\ValueObject\Money;

final class LedgerEntry
{
    public function __construct(
        public ?int $id,
        public int $walletId,
        public string $type,
        public Money $amount,
        public ?int $counterpartWalletId,
        public ?string $transferGroupId,
        public string $status,
        public ?int $reversesTransactionId,
        public ?string $idempotencyKey,
        public ?string $createdAt = null,
    ) {
    }
}
