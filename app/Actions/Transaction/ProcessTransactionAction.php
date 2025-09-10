<?php

declare(strict_types=1);

namespace App\Actions\Transaction;

use App\Actions\Wallet\CreditWalletAction;
use App\Actions\Wallet\DebitWalletAction;
use App\Actions\Transaction\PayerTransactionValidator;
use App\DataTransferObjects\Transaction\TransactionDTO;
use App\Events\Transaction\TransactionCompleted;
use App\Exceptions\ApplicationException;
use App\Exceptions\Transaction\PayerCannotBeMerchantException;
use App\Exceptions\Transaction\TransactionDeclinedByServiceException;
use App\Exceptions\Wallet\InsufficientBalanceException;
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
     * @throws PayerCannotBeMerchantException|InsufficientBalanceException|TransactionDeclinedByServiceException|ApplicationException
     */
    public function handle(TransactionDTO $dto): Transaction
    {
        // An better approach could be using event sourcing or a state machine to handle transaction states
        $transaction = $this->transactionRepository->startTransaction($dto);

        try {
            return $this->executeTransaction($dto, $transaction);
        } catch (\Throwable $e) {
            $this->handleTransactionFailure($transaction, $e);
        }

        return $transaction;
    }

    /**
     * Execute the Transaction logic within a database transaction.
     *
     * @param TransactionDTO $dto
     * @param Transaction $transaction
     * @return Transaction
     */
    private function executeTransaction(TransactionDTO $dto, Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($dto, $transaction) {
            // In the code bellow, adding jobs alongside the Bus::batch is a viable approach
            $this->validateTransaction($dto);

            if (!$this->authorizationService->authorize()) {
                throw new TransactionDeclinedByServiceException();
            }

            $this->transferAmountBetweenWallets($dto);

            $transaction = $this->transactionRepository->completeTransaction($transaction);

            //this event is listened by the SendTransactionNotificationListener
            //which is queued to send the notification after commit
            event(new TransactionCompleted($transaction));

            return $transaction;
        });
    }

    /**
     * Validate the Transaction details.
     *
     * @param TransactionDTO $dto
     * @return boolean
     */
    private function validateTransaction(TransactionDTO $dto): bool
    {
        return $this->validatePayerWallet->handle($dto->payer, $dto->value);
    }

    /**
     * Transfer the amount between payer and payee wallets.
     *
     * @param TransactionDTO $dto
     * @return void
     */
    private function transferAmountBetweenWallets(TransactionDTO $dto): void
    {
        // An better approach could be using event sourcing or a state machine to handle wallet states
        $this->debitWalletAction->handle($dto->payer, $dto->value);
        $this->creditWalletAction->handle($dto->payee, $dto->value);
    }

    /**
     * Handle Transaction failure and mark it accordingly.
     *
     * @param Transaction $transaction
     * @param \Throwable $exception
     * @return void
     */
    private function handleTransactionFailure(Transaction $transaction, \Throwable $exception): void
    {
        //This could be implemented as a Strategy Pattern or an action by itself
        //but for simplicity, it is what it is.
        match (true) {
            $exception instanceof InsufficientBalanceException => $this->transactionRepository->failTransactionNoFunds($transaction),
            $exception instanceof PayerCannotBeMerchantException => $this->transactionRepository->failTransactionInvalidWalletType($transaction),
            $exception instanceof TransactionDeclinedByServiceException => $this->transactionRepository->failTransactionUnauthorized($transaction),
            default => null,
        };

        throw $exception;
    }
}
