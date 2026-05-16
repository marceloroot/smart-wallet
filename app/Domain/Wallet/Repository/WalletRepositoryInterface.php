<?php

namespace App\Domain\Wallet\Repository;

use App\Domain\Wallet\Aggregate\Wallet;
use App\Domain\Wallet\Entity\LedgerEntry;
use Closure;

interface WalletRepositoryInterface
{
    public function findByUserId(int $userId): ?Wallet;

    public function findByUserIdForUpdate(int $userId): ?Wallet;

    public function findById(int $walletId): ?Wallet;

    public function findByIdForUpdate(int $walletId): ?Wallet;

    public function save(Wallet $wallet): Wallet;

    public function createForUser(int $userId): Wallet;

    public function findTransactionByIdempotencyKey(string $key): ?LedgerEntry;

    public function findTransactionById(int $transactionId): ?LedgerEntry;

    public function findTransactionsByWalletId(int $walletId, int $limit = 50): array;

    public function findTransferPair(string $transferGroupId): array;

    /**
     * @return mixed
     */
    public function transaction(Closure $callback);
}
