<?php

namespace App\Domain\Wallet\Exception;

final class InsufficientBalanceException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Saldo insuficiente para esta operação.');
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'insufficient_balance';
    }
}
