<?php

namespace App\Domain\Identity\Exception;

final class InvalidEmailException extends DomainException
{
    public function __construct()
    {
        parent::__construct('E-mail inválido.');
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'invalid_email';
    }
}
