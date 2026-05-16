<?php

namespace App\Http\Controllers\Api;

use App\Application\Wallet\Reverse\ReverseTransactionCommand;
use App\Application\Wallet\Reverse\ReverseTransactionHandler;
use App\Domain\Wallet\Exception\WalletNotFoundException;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private ReverseTransactionHandler $reverseTransactionHandler,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId($request->user()->id);

        if (! $wallet) {
            throw new WalletNotFoundException();
        }

        $transactions = $this->walletRepository->findTransactionsByWalletId($wallet->id());

        return response()->json([
            'data' => array_map(fn ($entry) => [
                'id' => $entry->id,
                'type' => $entry->type,
                'amount_cents' => $entry->amount->cents(),
                'amount' => $entry->amount->amount(),
                'status' => $entry->status,
                'counterpart_wallet_id' => $entry->counterpartWalletId,
                'transfer_group_id' => $entry->transferGroupId,
                'created_at' => $entry->createdAt,
            ], $transactions),
        ]);
    }

    public function reverse(Request $request, int $transactionId): JsonResponse
    {
        $result = $this->reverseTransactionHandler->handle(new ReverseTransactionCommand(
            userId: $request->user()->id,
            transactionId: $transactionId,
        ));

        return response()->json($result->toArray());
    }
}
