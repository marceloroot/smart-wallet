<?php

namespace Database\Seeders;

use App\Application\Wallet\Deposit\DepositCommand;
use App\Application\Wallet\Deposit\DepositHandler;
use App\Application\Wallet\Register\RegisterUserCommand;
use App\Application\Wallet\Register\RegisterUserHandler;
use Illuminate\Database\Seeder;

class WalletDemoSeeder extends Seeder
{
    public function run(): void
    {
        $register = app(RegisterUserHandler::class);
        $deposit = app(DepositHandler::class);

        $alice = $register->handle(new RegisterUserCommand(
            name: 'Alice Demo',
            email: 'alice@demo.test',
            password: 'password123',
        ));

        $bob = $register->handle(new RegisterUserCommand(
            name: 'Bob Demo',
            email: 'bob@demo.test',
            password: 'password123',
        ));

        $deposit->handle(new DepositCommand(
            userId: $alice->id,
            amountCents: 100000,
        ));

        $deposit->handle(new DepositCommand(
            userId: $bob->id,
            amountCents: 50000,
        ));
    }
}
