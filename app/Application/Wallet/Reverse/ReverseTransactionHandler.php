<?php

namespace App\Application\Wallet\Reverse;

use App\Application\Wallet\DTO\TransactionResult;
use App\Domain\Wallet\Enum\TransactionStatus;
use App\Domain\Wallet\Enum\TransactionType;
use App\Domain\Wallet\Entity\LedgerEntry;
use App\Domain\Wallet\Exception\TransactionAlreadyReversedException;
use App\Domain\Wallet\Exception\TransactionNotFoundException;
use App\Domain\Wallet\Exception\UnauthorizedTransactionException;
use App\Domain\Wallet\Repository\TransactionWriterInterface;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use Illuminate\Support\Facades\Log;

final class ReverseTransactionHandler
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private TransactionWriterInterface $transactionWriter,
    ) {
    }

    public function handle(ReverseTransactionCommand $command): TransactionResult
    {
        $result = $this->walletRepository->transaction(function () use ($command) {
            $ledgerEntry = $this->walletRepository->findTransactionById($command->transactionId);

            if (! $ledgerEntry) {
                throw new TransactionNotFoundException();
            }

            $wallet = $this->walletRepository->findByUserId($command->userId);

            if (! $wallet || $wallet->id() !== $ledgerEntry->walletId) {
                throw new UnauthorizedTransactionException();
            }
             // Melhorar essa  validaçao estorno duplicado fraude.
            if ($ledgerEntry->status === TransactionStatus::REVERSED) {
                throw new TransactionAlreadyReversedException();
            }
            
            if ($ledgerEntry->type === TransactionType::TRANSFER_OUT) {
                return $this->reverseTransfer($ledgerEntry, $command->userId);
            }

            return $this->reverseSingleEntry($ledgerEntry, $wallet->id());
        });

        Log::info('wallet.reverse.completed', [
            'user_id' => $command->userId,
            'original_transaction_id' => $command->transactionId,
            'reversal_transaction_id' => $result->transactionId,
            'amount_cents' => $result->amountCents,
            'transfer_group_id' => $result->transferGroupId,
        ]);

        return $result;
    }

    private function reverseSingleEntry(LedgerEntry $entry, int $walletId): TransactionResult
    {
        $wallet = $this->walletRepository->findByIdForUpdate($walletId);

        if ($entry->type === TransactionType::DEPOSIT) {
            $wallet->withdraw($entry->amount);
        } elseif ($entry->type === TransactionType::TRANSFER_IN) {
            $wallet->withdraw($entry->amount);
        } elseif ($entry->type === TransactionType::TRANSFER_OUT) {
            $wallet->deposit($entry->amount);
        } else {
            throw new TransactionNotFoundException();
        }

        $wallet = $this->walletRepository->save($wallet);

        $reversal = $this->transactionWriter->record(new LedgerEntry(
            id: null,
            walletId: $wallet->id(),
            type: TransactionType::REVERSAL,
            amount: $entry->amount,
            counterpartWalletId: $entry->counterpartWalletId,
            transferGroupId: $entry->transferGroupId,
            status: TransactionStatus::COMPLETED,
            reversesTransactionId: $entry->id,
            idempotencyKey: null,
        ));

        $this->transactionWriter->markAsReversed($entry->id, $reversal->id);

        return new TransactionResult(
            $reversal->id,
            TransactionType::REVERSAL,
            $entry->amount->cents(),
            $wallet->balance()->cents(),
            $entry->transferGroupId,
        );
    }

    private function reverseTransfer(LedgerEntry $outEntry, int $userId): TransactionResult
    {
        $pair = $this->walletRepository->findTransferPair($outEntry->transferGroupId);

        $out = null;
        $in = null;

        foreach ($pair as $item) {
            if ($item->type === TransactionType::TRANSFER_OUT) {
                $out = $item;
            }
            if ($item->type === TransactionType::TRANSFER_IN) {
                $in = $item;
            }
        }

        if (! $out || ! $in) {
            throw new TransactionNotFoundException();
        }

        if ($out->status === TransactionStatus::REVERSED) {
            throw new TransactionAlreadyReversedException();
        }

        $firstId = min($out->walletId, $in->walletId);
        $secondId = max($out->walletId, $in->walletId);
        $this->walletRepository->findByIdForUpdate($firstId);
        $this->walletRepository->findByIdForUpdate($secondId);

        $originWallet = $this->walletRepository->findById($out->walletId);
        $destWallet = $this->walletRepository->findById($in->walletId);

        $originWallet->deposit($out->amount);
        $destWallet->withdraw($out->amount);

        $originWallet = $this->walletRepository->save($originWallet);
        $destWallet = $this->walletRepository->save($destWallet);

        $reversalOut = $this->transactionWriter->record(new LedgerEntry(
            id: null,
            walletId: $originWallet->id(),
            type: TransactionType::REVERSAL,
            amount: $out->amount,
            counterpartWalletId: $destWallet->id(),
            transferGroupId: $out->transferGroupId,
            status: TransactionStatus::COMPLETED,
            reversesTransactionId: $out->id,
            idempotencyKey: null,
        ));

        $reversalIn = $this->transactionWriter->record(new LedgerEntry(
            id: null,
            walletId: $destWallet->id(),
            type: TransactionType::REVERSAL,
            amount: $in->amount,
            counterpartWalletId: $originWallet->id(),
            transferGroupId: $in->transferGroupId,
            status: TransactionStatus::COMPLETED,
            reversesTransactionId: $in->id,
            idempotencyKey: null,
        ));

        $this->transactionWriter->markAsReversed($out->id, $reversalOut->id);
        $this->transactionWriter->markAsReversed($in->id, $reversalIn->id);

        $userWallet = $this->walletRepository->findByUserId($userId);

        return new TransactionResult(
            $reversalOut->id,
            TransactionType::REVERSAL,
            $out->amount->cents(),
            $userWallet->balance()->cents(),
            $out->transferGroupId,
        );
    }
}
