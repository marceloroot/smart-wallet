<?php

namespace App\Http\Controllers\Api;

use App\Application\Wallet\Deposit\DepositCommand;
use App\Application\Wallet\Deposit\DepositHandler;
use App\Application\Wallet\Transfer\TransferCommand;
use App\Application\Wallet\Transfer\TransferHandler;
use App\Domain\Wallet\Exception\WalletNotFoundException;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
        private DepositHandler $depositHandler,
        private TransferHandler $transferHandler,
    ) {
    }

    public function balance(Request $request): JsonResponse
    {
        $wallet = $this->walletRepository->findByUserId($request->user()->id);

        if (! $wallet) {
            throw new WalletNotFoundException();
        }

        return response()->json([
            'balance_cents' => $wallet->balance()->cents(),
            'balance' => $wallet->balance()->amount(),
        ]);
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $result = $this->depositHandler->handle(new DepositCommand(
            userId: $request->user()->id,
            amountCents: (int) round($request->validated('amount') * 100),
            idempotencyKey: $request->header('Idempotency-Key'),
        ));

        return response()->json($result->toArray(), 201);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $result = $this->transferHandler->handle(new TransferCommand(
            fromUserId: $request->user()->id,
            toUserId: (int) $request->validated('to_user_id'),
            amountCents: (int) round($request->validated('amount') * 100),
            idempotencyKey: $request->header('Idempotency-Key'),
        ));

        return response()->json($result->toArray(), 201);
    }
}
