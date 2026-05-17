<?php

namespace App\Domain\Identity\Exception;

final class DuplicateEmailException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Este e-mail já está em uso.');
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'duplicate_email';
    }
}
