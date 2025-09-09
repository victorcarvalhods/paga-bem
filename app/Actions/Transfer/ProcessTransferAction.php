<?php

declare(strict_types=1);

namespace App\Actions\Transfer;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Transfer\EnsurePayerCanTransferAction;
use App\DataTransferObjects\Transfer\TransferDTO;
use App\Events\Transfer\TransferCompleted;
use App\Exceptions\ApplicationException;
use App\Exceptions\Transfer\TransferDeclinedByServiceException;
use App\Models\Transfer;
use App\Repositories\Transfer\TransferRepository;
use App\Services\Transfers\AuthorizationGatewayInterface;
use Illuminate\Support\Facades\DB;

class ProcessTransferAction
{
    public function __construct(
        private readonly EnsurePayerCanTransferAction $payerCanTransferAction,
        private readonly DebitWalletAction $debitWalletAction,
        private readonly CreditWalletAction $creditWalletAction,
        private readonly AuthorizationGatewayInterface $authorizationService,
        private readonly TransferRepository $transferRepository,
    ) {}

    /**
     * Process a transfer between two wallets.
     * 
     * @param TransferDTO $dto
     * @return Transfer
     * 
     * @throws ApplicationException
     */
    public function handle(TransferDTO $dto): Transfer
    {
        return DB::transaction(function () use ($dto) {

            $this->payerCanTransferAction->handle($dto->payer, $dto->value);

            $this->debitWalletAction->handle($dto->payer, $dto->value);
            $this->creditWalletAction->handle($dto->payee, $dto->value);

            $transfer = $this->transferRepository->create($dto);

            if (!$this->authorizationService->authorize()) {
                throw new TransferDeclinedByServiceException();
            }

            //This event is dispatched after the transaction is committed
            //and is listened to by the SendTransferNotificationListener to send the notification
            event(new TransferCompleted($transfer));

            return $transfer;
        });
    }
}
