<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Wallet\EnsurePayerCanTransferAction;
use App\Actions\Wallet\EnsureWalletHasFundsForOperationAction;
use App\DataTransferObjects\Transfer\TransferDataDTO;
use App\Exceptions\Transfer\PayerCannotBeMerchantException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Transfer;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class ProcessTransferAction
{
    public function __construct(private EnsurePayerCanTransferAction $ensurePayerCanTransferAction, private DebitWalletAction $debitWalletAction, private CreditWalletAction $creditWalletAction) {}

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

            $this->validatePayerWallet($dto);
            
            $this->debitWalletAction->handle($transfer->payer_id, $transfer->value);
            $this->creditWalletAction->handle($transfer->payee_id, $transfer->value);

            return $transfer;
        });
    }

    /**
     * Validate wallets before processing the transfer.
     *
     * @param TransferDataDTO $dto
     * @return bool
     */
    private function validatePayerWallet(TransferDataDTO $dto): bool
    {
       return $this->ensurePayerCanTransferAction->handle($dto->payer, $dto->value);
    }
}
