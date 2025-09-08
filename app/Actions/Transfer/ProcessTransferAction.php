<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Wallet\EnsurePayerCanTransferAction;
use App\DataTransferObjects\Transfer\TransferDataDTO;
use App\Exceptions\Transfer\TransferDeclinedByServiceException;
use App\Exceptions\Wallet\InsufficientBalanceException;
use App\Models\Transfer;
use App\Services\AuthorizationGatewayInterface;
use Illuminate\Support\Facades\DB;

class ProcessTransferAction
{
    private AuthorizationGatewayInterface $authorizationService;

    public function __construct(
        private EnsurePayerCanTransferAction $ensurePayerCanTransferAction,
        private DebitWalletAction $debitWalletAction,
        private CreditWalletAction $creditWalletAction
    ) {
        $this->authorizationService = app(AuthorizationGatewayInterface::class);
    }

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

            //TODO: Rename This method and move to a action class
            if (!$this->callAuthorizationService()) {
                throw new TransferDeclinedByServiceException();
            }

            $this->validatePayerWallet($dto);
            
            $this->debitWalletAction->handle($dto->payer, $dto->value);
            $this->creditWalletAction->handle($dto->payee, $dto->value);

            $transfer = Transfer::create($dto->toArray());
            
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

    /**
     * Call the external authorization service.
     *
     * @return bool
     */
    private function callAuthorizationService(): bool
    {
        return $this->authorizationService->authorize();
    }
}
