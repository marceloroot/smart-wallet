<?php

namespace App\Application\Wallet\Transfer;

final class TransferCommand
{
    public function __construct(
        public int $fromUserId,
        public int $toUserId,
        public int $amountCents,
        public ?string $idempotencyKey = null,
    ) {
    }
}
