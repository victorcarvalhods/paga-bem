<?php

namespace App\Models;

use App\Wallet\WalletTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    protected $fillable = [
        'user_id',
        'balance',
        'wallet_type',
    ];

    protected function casts()
    {
        return [
            'balance' => 'decimal:2',
            'wallet_type' => WalletTypeEnum::class,
        ];
    }

    /**
     * Get the user that owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
