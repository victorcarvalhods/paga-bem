<?php

namespace App\Models;

use App\Wallet\WalletTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property WalletTypeEnum $wallet_type
 */
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
            'balance' => 'float',
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

    /**
     * Check if the wallet is of type MERCHANT.
     * 
     * @return bool
     */
    public function isMerchant(): bool
    {
        return $this->wallet_type === WalletTypeEnum::MERCHANT;
    }

    /**
     * Check if the wallet has sufficient balance for a given amount.
     * 
     * @param float $amount
     * @return bool
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->balance >= $amount;
    }
}
