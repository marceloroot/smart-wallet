<?php

namespace App\Http\Controllers\Web;

use App\Application\Wallet\Reverse\ReverseTransactionCommand;
use App\Application\Wallet\Reverse\ReverseTransactionHandler;
use App\Domain\Wallet\Exception\DomainException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(
        private ReverseTransactionHandler $reverseTransactionHandler,
    ) {
    }

    public function reverse(Request $request, int $transactionId): RedirectResponse
    {
        try {
            $result = $this->reverseTransactionHandler->handle(new ReverseTransactionCommand(
                userId: $request->user()->id,
                transactionId: $transactionId,
            ));

            return back()->with('success', sprintf(
                'Transação #%d estornada. Saldo atual: R$ %s',
                $transactionId,
                number_format($result->balanceCents / 100, 2, ',', '.'),
            ));
        } catch (DomainException $e) {
            return back()->withErrors(['wallet' => $e->getMessage()]);
        }
    }
}
