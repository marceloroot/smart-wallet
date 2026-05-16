<?php

namespace App\Application\Wallet\DTO;

final class TransactionResult
{
    public function __construct(
        public int $transactionId,
        public string $type,
        public int $amountCents,
        public int $balanceCents,
        public ?string $transferGroupId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'transaction_id' => $this->transactionId,
            'type' => $this->type,
            'amount_cents' => $this->amountCents,
            'balance_cents' => $this->balanceCents,
            'balance' => $this->balanceCents / 100,
            'transfer_group_id' => $this->transferGroupId,
        ];
    }
}
