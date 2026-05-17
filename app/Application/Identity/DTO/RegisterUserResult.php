<?php

namespace App\Application\Identity\DTO;

final class RegisterUserResult
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $email,
        public int $walletId,
        public int $balanceCents,
    ) {
    }
}
