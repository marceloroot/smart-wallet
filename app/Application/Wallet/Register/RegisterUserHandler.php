<?php

namespace App\Application\Wallet\Register;

use App\Domain\Wallet\Repository\WalletRepositoryInterface;
//Melhorar  isso aqui não esquecer -> criar um Bounded Context para o RegisterUser 
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class RegisterUserHandler
{
    public function __construct(
        private WalletRepositoryInterface $walletRepository,
    ) {
    }

    public function handle(RegisterUserCommand $command): User
    {
        //Melhorar  isso aqui não esquecer -> criar um Bounded Context para o RegisterUser 
        $user = User::create([
            'name' => $command->name,
            'email' => $command->email,
            'password' => Hash::make($command->password),
        ]);

        $this->walletRepository->createForUser($user->id);

        return $user->load('wallet');
    }
}
