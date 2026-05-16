<?php

namespace App\Application\Wallet\Reverse;

final class ReverseTransactionCommand
{
    public function __construct(
        public int $userId,
        public int $transactionId,
    ) {
    }
}
