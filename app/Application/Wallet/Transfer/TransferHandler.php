<?php

namespace App\Application\Wallet\Transfer;

use App\Application\Wallet\DTO\TransactionResult;
use App\Domain\Wallet\Enum\TransactionType;
use App\Domain\Wallet\Entity\LedgerEntry;
use App\Domain\Wallet\Exception\SelfTransferException;
use App\Domain\Wallet\Exception\WalletNotFoundException;
use App\Domain\Wallet\Repository\TransactionWriterInterface;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObject\Money;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class TransferHandler
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private TransactionWriterInterface $transactionWriter,
    ) {
    }

    public function handle(TransferCommand $command): TransactionResult
    {
        if ($command->fromUserId === $command->toUserId) {
            throw new SelfTransferException();
        }

        if ($command->idempotencyKey) {
            $existing = $this->walletRepository->findTransactionByIdempotencyKey($command->idempotencyKey);
            if ($existing) {
                $wallet = $this->walletRepository->findById($existing->walletId);

                Log::info('wallet.transfer.idempotent_hit', [
                    'from_user_id' => $command->fromUserId,
                    'to_user_id' => $command->toUserId,
                    'transaction_id' => $existing->id,
                    'idempotency_key' => $command->idempotencyKey,
                ]);

                return new TransactionResult(
                    $existing->id,
                    $existing->type,
                    $existing->amount->cents(),
                    $wallet?->balance()->cents() ?? 0,
                    $existing->transferGroupId,
                );
            }
        }

        return $this->walletRepository->transaction(function () use ($command) {
            // logica de race condition // verificar depois se ta evitando deadlock 
            $firstUserId = min($command->fromUserId, $command->toUserId);
            $secondUserId = max($command->fromUserId, $command->toUserId);
            $this->walletRepository->findByUserIdForUpdate($firstUserId);
            $this->walletRepository->findByUserIdForUpdate($secondUserId);

            $origin = $this->walletRepository->findByUserId($command->fromUserId);
            $destination = $this->walletRepository->findByUserId($command->toUserId);

            if (! $origin || ! $destination) {
                throw new WalletNotFoundException();
            }

            $amount = Money::fromCents($command->amountCents);
            $transferGroupId = (string) Str::uuid();

            $origin->withdraw($amount);
            $destination->deposit($amount);

            $origin = $this->walletRepository->save($origin);
            $destination = $this->walletRepository->save($destination);

            $outEntry = $this->transactionWriter->record(new LedgerEntry(
                id: null,
                walletId: $origin->id(),
                type: TransactionType::TRANSFER_OUT,
                amount: $amount,
                counterpartWalletId: $destination->id(),
                transferGroupId: $transferGroupId,
                status: 'completed',
                reversesTransactionId: null,
                idempotencyKey: $command->idempotencyKey,
            ));

            $this->transactionWriter->record(new LedgerEntry(
                id: null,
                walletId: $destination->id(),
                type: TransactionType::TRANSFER_IN,
                amount: $amount,
                counterpartWalletId: $origin->id(),
                transferGroupId: $transferGroupId,
                status: 'completed',
                reversesTransactionId: null,
                idempotencyKey: null,
            ));

            $result = new TransactionResult(
                $outEntry->id,
                TransactionType::TRANSFER_OUT,
                $amount->cents(),
                $origin->balance()->cents(),
                $transferGroupId,
            );

            Log::info('wallet.transfer.completed', [
                'from_user_id' => $command->fromUserId,
                'to_user_id' => $command->toUserId,
                'transaction_id' => $result->transactionId,
                'amount_cents' => $result->amountCents,
                'transfer_group_id' => $transferGroupId,
                'idempotency_key' => $command->idempotencyKey,
            ]);

            return $result;
        });
    }
}
