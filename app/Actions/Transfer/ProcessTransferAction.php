<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Wallet\EnsurePayerCanTransferAction;
use App\DataTransferObjects\Transfer\TransferDataDTO;
use App\Exceptions\ApplicationException;
use App\Exceptions\Transfer\TransferDeclinedByServiceException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Transfer;
use App\Services\AuthorizationGatewayInterface;
use Illuminate\Support\Facades\DB;

class ProcessTransferAction
{
    public function __construct(
        private readonly EnsurePayerCanTransferAction $ensurePayerCanTransferAction,
        private readonly DebitWalletAction $debitWalletAction,
        private readonly CreditWalletAction $creditWalletAction,
        private readonly AuthorizationGatewayInterface $authorizationService,
    ) {
    }

    /**
     * Process a transfer between two wallets.
     * 
     * @param TransferDataDTO $dto
     * @return Transfer
     * 
     * @throws ApplicationException
     */
    public function handle(TransferDataDTO $dto): Transfer
    {
        return DB::transaction(function () use ($dto) {

            $this->ensurePayerCanTransferAction->handle($dto->payer, $dto->value);

            if (!$this->authorizationService->authorize()) {
                throw new TransferDeclinedByServiceException();
            }
            
            $this->debitWalletAction->handle($dto->payer, $dto->value);
            $this->creditWalletAction->handle($dto->payee, $dto->value);

            $transfer = Transfer::create([
                'payer_id' => $dto->payer,
                'payee_id' => $dto->payee,
                'value' => $dto->value,
            ]);
            
            return $transfer;
        });
    }
}
