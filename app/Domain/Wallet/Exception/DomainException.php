<?php

namespace App\Domain\Wallet\Exception;

use Exception;

abstract class DomainException extends Exception
{
    abstract public function statusCode(): int;

    abstract public function errorCode(): string;
}
