<?php

declare(strict_types=1);

namespace App\Repositories\Wallet;

use App\Models\Wallet;

class WalletRepository
{
    /**
     * Find a wallet by its ID.
     *
     * @param integer $id
     * @return Wallet
     */
    public function findById(int $id): Wallet
    {
        $wallet = Wallet::query()->findOrFail($id);

        return $wallet;
    }
}