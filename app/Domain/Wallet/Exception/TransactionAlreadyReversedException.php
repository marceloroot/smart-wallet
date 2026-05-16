<?php

namespace App\Domain\Wallet\Exception;

final class TransactionAlreadyReversedException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Transaction has already been reversed.');
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
