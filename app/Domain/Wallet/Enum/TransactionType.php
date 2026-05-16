<?php

namespace App\Domain\Wallet\Enum;

final class TransactionType
{
    public const DEPOSIT = 'deposit';

    public const TRANSFER_OUT = 'transfer_out';

    public const TRANSFER_IN = 'transfer_in';

    public const REVERSAL = 'reversal';

    public static function all(): array
    {
        return [
            self::DEPOSIT,
            self::TRANSFER_OUT,
            self::TRANSFER_IN,
            self::REVERSAL,
        ];
    }
}
