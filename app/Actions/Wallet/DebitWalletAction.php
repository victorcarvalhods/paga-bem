<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;

class DebitWalletAction
{
    /**
     * Debit a specified amount from the wallet.
     *
     * @param int $walletId
     * @param float $amount
     * @return Wallet
     * 
     * @throws InsufficientBalanceException
     */
    public function handle(int $walletId, float $amount): Wallet
    {
        $wallet = Wallet::query()->findOrFail($walletId);

        if ($wallet->balance < $amount) {
            throw new InsufficientBalanceException('Insufficient balance to complete this operation.');
        }

        $wallet->decrement('balance', $amount);

        return $wallet;
    }
}