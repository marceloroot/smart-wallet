<?php

namespace App\Domain\Identity\Exception;

use Exception;

abstract class DomainException extends Exception
{
    abstract public function statusCode(): int;

    abstract public function errorCode(): string;
}
