<?php

namespace App\Domain\Wallet\Exception;

final class WalletNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Wallet not found.');
    }

    public function statusCode(): int
    {
        return 404;
    }

    public function errorCode(): string
    {
        return 'wallet_not_found';
    }
}
