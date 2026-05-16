<?php

namespace App\Http\Controllers\Web;

use App\Domain\Wallet\Repository\WalletRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
    ) {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $wallet = $this->walletRepository->findByUserId($user->id);
        $transactions = $wallet
            ? $this->walletRepository->findTransactionsByWalletId($wallet->id())
            : [];

        $recipients = User::query()
            ->where('id', '!=', $user->id)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('dashboard', [
            'balance' => $wallet?->balance()->amount() ?? 0,
            'balanceCents' => $wallet?->balance()->cents() ?? 0,
            'transactions' => $transactions,
            'recipients' => $recipients,
        ]);
    }

}
