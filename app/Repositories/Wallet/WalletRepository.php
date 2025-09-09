<?php

declare(strict_types=1);

namespace App\Repositories\Wallet;

use App\DataTransferObjects\Wallet\WalletDTO;
use App\Models\Wallet;

class WalletRepository
{

    public function create(WalletDTO $dto): Wallet
    {
        $wallet = Wallet::query()->create([
            'user_id' => $dto->userId,
            'balance' => $dto->balance,
            'wallet_type' => $dto->walletType,
        ]);

        return $wallet;
    }
    
    /**
     * Find a wallet by its ID.
     *
     * @param integer $walletId
     * @return Wallet
     */
    public function findById(int $walletId): Wallet
    {
        $wallet = Wallet::query()->findOrFail($walletId);

        return $wallet;
    }

    public function withdraw(int $walletId, float $amount): Wallet
    {
        $wallet = $this->findById($walletId);

        $wallet->decrement('balance', $amount);

        $wallet->save();

        return $wallet;
    }

    public function deposit(int $walletId, float $amount): Wallet
    {
        $wallet = $this->findById($walletId);

        $wallet->increment('balance', $amount);

        $wallet->save();

        return $wallet;
    }
}