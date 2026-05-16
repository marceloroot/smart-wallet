<?php

namespace App\Domain\Wallet\Exception;

final class UnauthorizedTransactionException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Você não tem permissão para estornar esta transação.');
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
