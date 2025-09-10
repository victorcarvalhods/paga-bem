<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Transaction\PayerTransactionValidator;
use App\DataTransferObjects\Transaction\TransactionDTO;
use App\Events\Transaction\TransactionCompleted;
use App\Exceptions\ApplicationException;
use App\Exceptions\Transaction\TransactionDeclinedByServiceException;
use App\Models\Transaction;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Transaction\AuthorizationGatewayInterface;
use Illuminate\Support\Facades\DB;

class ProcessTransactionAction
{
    public function __construct(
        private readonly PayerTransactionValidator $validatePayerWallet,
        private readonly DebitWalletAction $debitWalletAction,
        private readonly CreditWalletAction $creditWalletAction,
        private readonly AuthorizationGatewayInterface $authorizationService,
        private readonly TransactionRepository $transactionRepository,
    ) {}

    /**
     * Process a Transaction between two wallets.
     * 
     * @param TransactionDTO $dto
     * @return Transaction
     * @throws ApplicationException
     */
    public function handle(TransactionDTO $dto): Transaction
    {
        return DB::transaction(function () use ($dto) {

            $this->validatePayerWallet->handle($dto->payer, $dto->value);

            $this->debitWalletAction->handle($dto->payer, $dto->value);
            $this->creditWalletAction->handle($dto->payee, $dto->value);

            $transaction = $this->transactionRepository->create($dto);

            if (!$this->authorizationService->authorize()) {
                throw new TransactionDeclinedByServiceException();
            }

            //This event is dispatched after the transaction is committed
            //and is listened to by the SendTransactionNotificationListener to send the notification
            event(new TransactionCompleted($transaction));

            return $transaction;
        });
    }
}
