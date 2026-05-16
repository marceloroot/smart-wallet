<?php

namespace App\Http\Controllers\Web;

use App\Application\Wallet\Deposit\DepositCommand;
use App\Application\Wallet\Deposit\DepositHandler;
use App\Application\Wallet\Transfer\TransferCommand;
use App\Application\Wallet\Transfer\TransferHandler;
use App\Domain\Wallet\Exception\DomainException;
use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use Illuminate\Http\RedirectResponse;

class WalletController extends Controller
{
    public function __construct(
        private DepositHandler $depositHandler,
        private TransferHandler $transferHandler,
    ) {
    }

    public function deposit(DepositRequest $request): RedirectResponse
    {
        try {
            $result = $this->depositHandler->handle(new DepositCommand(
                userId: $request->user()->id,
                amountCents: (int) round($request->validated('amount') * 100),
                idempotencyKey: $request->input('idempotency_key'),
            ));

            return back()->with('success', sprintf(
                'Depósito de R$ %s realizado. Saldo atual: R$ %s',
                number_format($result->amountCents / 100, 2, ',', '.'),
                number_format($result->balanceCents / 100, 2, ',', '.'),
            ));
        } catch (DomainException $e) {
            return back()->withErrors(['wallet' => $e->getMessage()])->withInput();
        }
    }

    public function transfer(TransferRequest $request): RedirectResponse
    {
        try {
            $result = $this->transferHandler->handle(new TransferCommand(
                fromUserId: $request->user()->id,
                toUserId: (int) $request->validated('to_user_id'),
                amountCents: (int) round($request->validated('amount') * 100),
                idempotencyKey: $request->input('idempotency_key'),
            ));

            return back()->with('success', sprintf(
                'Transferência de R$ %s enviada. Seu saldo: R$ %s',
                number_format($result->amountCents / 100, 2, ',', '.'),
                number_format($result->balanceCents / 100, 2, ',', '.'),
            ));
        } catch (DomainException $e) {
            return back()->withErrors(['wallet' => $e->getMessage()])->withInput();
        }
    }
}
