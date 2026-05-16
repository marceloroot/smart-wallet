<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Wallet\Aggregate\Wallet as WalletAggregate;
use App\Domain\Wallet\Entity\LedgerEntry;
use App\Domain\Wallet\Repository\TransactionWriterInterface;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObject\Money;
use App\Models\Transaction;
use App\Models\Wallet;
use Closure;
use Illuminate\Support\Facades\DB;

final class EloquentWalletRepository implements WalletRepositoryInterface, TransactionWriterInterface
{
    public function findByUserId(int $userId): ?WalletAggregate
    {
        $model = Wallet::query()->where('user_id', $userId)->first();

        return $model ? $this->toAggregate($model) : null;
    }

    public function findByUserIdForUpdate(int $userId): ?WalletAggregate
    {
        $model = Wallet::query()->where('user_id', $userId)->lockForUpdate()->first();

        return $model ? $this->toAggregate($model) : null;
    }

    public function findById(int $walletId): ?WalletAggregate
    {
        $model = Wallet::query()->find($walletId);

        return $model ? $this->toAggregate($model) : null;
    }

    public function findByIdForUpdate(int $walletId): ?WalletAggregate
    {
        $model = Wallet::query()->lockForUpdate()->find($walletId);

        return $model ? $this->toAggregate($model) : null;
    }

    public function save(WalletAggregate $wallet): WalletAggregate
    {
        $model = Wallet::query()->lockForUpdate()->findOrFail($wallet->id());
        $model->balance_cents = $wallet->balance()->cents();
        $model->version = $wallet->version() + 1;
        $model->save();

        return $this->toAggregate($model->fresh());
    }

    public function createForUser(int $userId): WalletAggregate
    {
        $model = Wallet::query()->create([
            'user_id' => $userId,
            'balance_cents' => 0,
            'version' => 0,
        ]);

        return $this->toAggregate($model);
    }

    public function findTransactionByIdempotencyKey(string $key): ?LedgerEntry
    {
        $model = Transaction::query()->where('idempotency_key', $key)->first();

        return $model ? $this->toLedgerEntry($model) : null;
    }

    public function findTransactionById(int $transactionId): ?LedgerEntry
    {
        $model = Transaction::query()->find($transactionId);

        return $model ? $this->toLedgerEntry($model) : null;
    }

    public function findTransactionsByWalletId(int $walletId, int $limit = 50): array
    {
        return Transaction::query()
            ->where('wallet_id', $walletId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(fn (Transaction $t) => $this->toLedgerEntry($t))
            ->all();
    }

    public function findTransferPair(string $transferGroupId): array
    {
        return Transaction::query()
            ->where('transfer_group_id', $transferGroupId)
            ->lockForUpdate()
            ->get()
            ->map(fn (Transaction $t) => $this->toLedgerEntry($t))
            ->all();
    }

    public function transaction(Closure $callback)
    {
        return DB::transaction($callback);
    }

    public function record(LedgerEntry $entry): LedgerEntry
    {
        $model = Transaction::query()->create([
            'wallet_id' => $entry->walletId,
            'type' => $entry->type,
            'amount_cents' => $entry->amount->cents(),
            'counterpart_wallet_id' => $entry->counterpartWalletId,
            'transfer_group_id' => $entry->transferGroupId,
            'status' => $entry->status,
            'reverses_transaction_id' => $entry->reversesTransactionId,
            'idempotency_key' => $entry->idempotencyKey,
        ]);

        return $this->toLedgerEntry($model);
    }

    public function markAsReversed(int $transactionId, int $reversalTransactionId): void
    {
        Transaction::query()
            ->where('id', $transactionId)
            ->update([
                'status' => 'reversed',
                'reversed_by_transaction_id' => $reversalTransactionId,
            ]);
    }

    private function toAggregate(Wallet $model): WalletAggregate
    {
        return WalletAggregate::reconstitute(
            $model->id,
            $model->user_id,
            Money::fromBalanceCents($model->balance_cents),
            $model->version,
        );
    }

    private function toLedgerEntry(Transaction $model): LedgerEntry
    {
        return new LedgerEntry(
            id: $model->id,
            walletId: $model->wallet_id,
            type: $model->type,
            amount: Money::fromCents($model->amount_cents),
            counterpartWalletId: $model->counterpart_wallet_id,
            transferGroupId: $model->transfer_group_id,
            status: $model->status,
            reversesTransactionId: $model->reverses_transaction_id,
            idempotencyKey: $model->idempotency_key,
            createdAt: $model->created_at?->toIso8601String(),
        );
    }
}
