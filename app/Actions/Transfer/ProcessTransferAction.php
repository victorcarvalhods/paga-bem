<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

use App\DataTransferObjects\Transfer\TransferDataDTO;
use App\Exceptions\Transfer\PayerCannotBeMerchantException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Transfer;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class ProcessTransferAction
{
    /**
     * Process a transfer between two wallets.
     * 
     * @param TransferDataDTO $dto
     * @return Transfer
     * 
     * @throws InsufficientBalanceException
     */
    public function handle(TransferDataDTO $dto): Transfer
    {
        return DB::transaction(function () use ($dto) {
            //TODO: add authorization service connection
            $transfer = Transfer::create($dto->toArray());

            $this->validateWallets($dto);

            //TODO: use an wallet action to handle balance deposit and withdraws
            $transfer->payer->decrement('balance', $transfer->value);
            $transfer->payee->increment('balance', $transfer->value);

            return $transfer;
        });
    }

    /**
     * Validate wallets before processing the transfer.
     *
     * @param TransferDataDTO $dto
     * @return void
     */
    private function validateWallets(TransferDataDTO $dto): void
    {
        //TODO: Move this logic to a dedicated Action class to handle wallet validations
        $payee = Wallet::findOrFail($dto->payee);
        $payer = Wallet::findOrFail($dto->payer);

        if ($payer->isMerchant()) {
            throw new PayerCannotBeMerchantException();
        }

        if ($payer->balance < $dto->value) {
            throw new InsufficientBalanceException();
        }
    }
}
