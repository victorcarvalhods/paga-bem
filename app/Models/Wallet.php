<?php

namespace App\Models;

use App\Wallet\WalletTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    /** @use HasFactory<\Database\Factories\WalletFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'wallet_type',
    ];

    /**
     * The available wallet types.
     * @var string[]
     */
    public const WALLET_TYPES = [
        WalletTypeEnum::USER->value,
        WalletTypeEnum::MERCHANT->value,
    ];

    protected function casts()
    {
        return [
            'wallet_type' => WalletTypeEnum::class,
        ];
    }

    /**
     * Get the user that owns the wallet.
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
