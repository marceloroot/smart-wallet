<?php

namespace App\Domain\Wallet\Exception;

final class InvalidAmountException extends DomainException
{
    public static function mustBePositive(): self
    {
        return new self('O valor deve ser maior que zero.');
    }

    public function statusCode(): int
    {
        return 422;
    }

    public function errorCode(): string
    {
        return 'invalid_amount';
    }
}
