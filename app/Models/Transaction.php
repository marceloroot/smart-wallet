<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',
        'amount_cents',
        'counterpart_wallet_id',
        'transfer_group_id',
        'status',
        'reverses_transaction_id',
        'reversed_by_transaction_id',
        'idempotency_key',
    ];

    protected $casts = [
        'amount_cents' => 'integer',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function counterpartWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'counterpart_wallet_id');
    }

    public function reversesTransaction(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reverses_transaction_id');
    }
}
