<?php

namespace App\Domain\Wallet\Exception;

final class TransactionAlreadyReversedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Esta transação já foi estornada.');
    }

    public function statusCode(): int
    {
        return 409;
    }

    public function errorCode(): string
    {
        return 'transaction_already_reversed';
    }
}
