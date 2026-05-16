<?php

namespace App\Domain\Wallet\Repository;

use App\Domain\Wallet\Entity\LedgerEntry;

interface TransactionWriterInterface
{
    public function record(LedgerEntry $entry): LedgerEntry;

    public function markAsReversed(int $transactionId, int $reversalTransactionId): void;
}
