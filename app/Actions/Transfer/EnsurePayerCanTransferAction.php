<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

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
        //TODO: Find a better way to deal with retrieving the wallet
        $payer = Wallet::findOrFail($payerId);

        if ($payer->isMerchant()) {
            throw new PayerCannotBeMerchantException();
        }

        if (!$payer->hasSufficientBalance($amount)) {
            throw new InsufficientBalanceException();
        }

        return true;
    }
}
