<?php

namespace App\Domain\Wallet\Exception;

final class UnauthorizedTransactionException extends DomainException
{
    public function __construct()
    {
        parent::__construct('You are not allowed to reverse this transaction.');
    }

    public function statusCode(): int
    {
        return 403;
    }

    public function errorCode(): string
    {
        return 'unauthorized_transaction';
    }
}
