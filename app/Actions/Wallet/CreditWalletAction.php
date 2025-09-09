<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;
use App\Repositories\Wallet\WalletRepository;

class CreditWalletAction
{
    public function __construct(private readonly WalletRepository $walletRepository) {}

    /**
     * Credit a specified amount to the wallet.
     *
     * @param int $walletId
     * @param float $amount
     * @return Wallet
     */
    public function handle(int $walletId, float $amount): Wallet
    {
        $wallet = $this->walletRepository->findById($walletId);

        $wallet->increment('balance', $amount);

        $wallet->save();

        return $wallet;
    }
}
