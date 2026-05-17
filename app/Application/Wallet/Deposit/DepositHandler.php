<?php

namespace App\Application\Wallet\Deposit;

use App\Application\Wallet\DTO\TransactionResult;
use App\Domain\Wallet\Enum\TransactionType;
use App\Domain\Wallet\Entity\LedgerEntry;
use App\Domain\Wallet\Exception\WalletNotFoundException;
use App\Domain\Wallet\Repository\TransactionWriterInterface;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Domain\Wallet\ValueObject\Money;
use Illuminate\Support\Facades\Log;

final class DepositHandler
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private TransactionWriterInterface $transactionWriter,
    ) {
    }

    public function handle(DepositCommand $command): TransactionResult
    {
        if ($command->idempotencyKey) {
            $existing = $this->walletRepository->findTransactionByIdempotencyKey($command->idempotencyKey);
            if ($existing) {
                $wallet = $this->walletRepository->findById($existing->walletId);

                Log::info('wallet.deposit.idempotent_hit', [
                    'user_id' => $command->userId,
                    'transaction_id' => $existing->id,
                    'idempotency_key' => $command->idempotencyKey,
                ]);

                return new TransactionResult(
                    $existing->id,
                    $existing->type,
                    $existing->amount->cents(),
                    $wallet?->balance()->cents() ?? 0,
                );
            }
        }

        return $this->walletRepository->transaction(function () use ($command) {
            $wallet = $this->walletRepository->findByUserIdForUpdate($command->userId);

            if (! $wallet) {
                throw new WalletNotFoundException();
            }

            $amount = Money::fromCents($command->amountCents); 
            $wallet->deposit($amount); // A entidade Executa a Regra de Negócio
            $wallet = $this->walletRepository->save($wallet); 
            
            $entry = $this->transactionWriter->record(new LedgerEntry(
                id: null,
                walletId: $wallet->id(),
                type: TransactionType::DEPOSIT,
                amount: $amount,
                counterpartWalletId: null,
                transferGroupId: null,
                status: 'completed',
                reversesTransactionId: null,
                idempotencyKey: $command->idempotencyKey,
            ));

            $result = new TransactionResult(
                $entry->id,
                TransactionType::DEPOSIT,
                $amount->cents(),
                $wallet->balance()->cents(),
            );

            Log::info('wallet.deposit.completed', [
                'user_id' => $command->userId,
                'transaction_id' => $result->transactionId,
                'amount_cents' => $result->amountCents,
                'balance_cents' => $result->balanceCents,
                'idempotency_key' => $command->idempotencyKey,
            ]);

            return $result;
        });
    }
}
