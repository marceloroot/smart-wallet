<?php

namespace App\Domain\Wallet\Exception;

final class SelfTransferException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot transfer to your own wallet.');
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
