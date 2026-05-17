<?php

namespace App\Application\Identity\Register;

use App\Application\Identity\DTO\RegisterUserResult;
use App\Domain\Identity\Entity\User;
use App\Domain\Identity\Exception\DuplicateEmailException;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Domain\Wallet\Repository\WalletRepositoryInterface;

final class RegisterUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalletRepositoryInterface $walletRepository,
    ) {
    }

    public function handle(RegisterUserCommand $command): RegisterUserResult
    {
        if ($this->userRepository->existsByEmail($command->email)) {
            throw new DuplicateEmailException();
        }

        return $this->walletRepository->transaction(function () use ($command) {
            $user = $this->userRepository->save(User::register(
                name: $command->name,
                email: $command->email,
                plainPassword: $command->password,
            ));

            $wallet = $this->walletRepository->createForUser($user->id());

            return new RegisterUserResult(
                userId: $user->id(),
                name: $user->name(),
                email: $user->email(),
                walletId: $wallet->id(),
                balanceCents: $wallet->balance()->cents(),
            );
        });
    }
}
