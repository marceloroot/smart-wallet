<?php

namespace App\Domain\Wallet\Exception;

final class SelfTransferException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Não é possível transferir para a própria carteira.');
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'self_transfer';
    }
}
