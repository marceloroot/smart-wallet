<?php

namespace App\Application\Wallet\Register;

final class RegisterUserCommand
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {
    }
}
