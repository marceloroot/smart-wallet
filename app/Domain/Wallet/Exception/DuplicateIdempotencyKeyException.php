<?php

namespace App\Domain\Wallet\Exception;

final class DuplicateIdempotencyKeyException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Operation with this idempotency key already exists.');
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
