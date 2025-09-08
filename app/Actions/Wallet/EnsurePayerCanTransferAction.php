<?php

declare(strict_types=1);

namespace App\Actions\Wallet;

use App\Exceptions\Transfer\PayerCannotBeMerchantException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Wallet;

class EnsurePayerCanTransferAction
{

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
        $payer = Wallet::findOrFail($payerId);

        if ($payer->isMerchant()) {
            throw new PayerCannotBeMerchantException();
        }

        if ($payer->balance < $amount) {
            throw new InsufficientBalanceException();
        }

        return true;
    }
}
