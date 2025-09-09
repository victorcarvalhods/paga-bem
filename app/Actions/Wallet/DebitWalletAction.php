<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;
use App\Repositories\Wallet\WalletRepository;

class DebitWalletAction
{
    public function __construct(private readonly WalletRepository $walletRepository) {}

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
        $wallet = $this->walletRepository->withdraw($walletId, $amount);

        return $wallet;
    }
}
