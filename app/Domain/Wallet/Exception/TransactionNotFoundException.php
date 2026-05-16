<?php

namespace App\Domain\Wallet\Exception;

final class TransactionNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Transaction not found.');
    }

    public function statusCode(): int
    {
        return 404;
    }

    public function errorCode(): string
    {
        return 'transaction_not_found';
    }
}
