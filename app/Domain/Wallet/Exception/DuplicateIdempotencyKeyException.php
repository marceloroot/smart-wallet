<?php

namespace App\Domain\Wallet\Exception;

final class DuplicateIdempotencyKeyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Já existe uma operação com esta chave de idempotência.');
    }

    public function statusCode(): int
    {
        return 409;
    }

    public function errorCode(): string
    {
        return 'duplicate_idempotency_key';
    }
}
