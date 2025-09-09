<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

use App\Exceptions\Transfer\PayerCannotBeMerchantException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;
use App\Repositories\Wallet\WalletRepository;

class EnsurePayerCanTransferAction
{

    public function __construct(private readonly WalletRepository $walletRepository) {}

    /**
     * Ensure that the payer can make a transfer.
     *
     * @param integer $payerId
     * @param float $amount
     * @return bool
     * @throws PayerCannotBeMerchantException
     * @throws InsufficientBalanceException
     */
    public function handle(int $payerId, float $amount): bool
    {
        $payer = $this->walletRepository->findById($payerId);

        if ($payer->isMerchant()) {
            throw new PayerCannotBeMerchantException();
        }

        if (!$payer->hasSufficientBalance($amount)) {
            throw new InsufficientBalanceException();
        }

        return true;
    }
}
