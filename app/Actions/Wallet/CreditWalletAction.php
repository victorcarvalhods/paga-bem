<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;

class CreditWalletAction
{
    /**
     * Credit a specified amount to the wallet.
     *
     * @param int $walletId
     * @param float $amount
     * @return Wallet
     */
    public function handle(int $walletId, float $amount): Wallet
    {
        $wallet = Wallet::query()->findOrFail($walletId);

        $wallet->increment('balance', $amount);

        return $wallet;
    }
}