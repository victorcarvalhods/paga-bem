<?php

declare(strict_types=1);

namespace App\Repositories\Transaction;

use App\DataTransferObjects\Transaction\TransactionDTO;
use App\Enums\Transaction\TransactionStatusEnum;
use App\Models\Transaction;

class TransactionRepository
{
    /**
     * Start a new Transaction with PENDING status.
     *
     * @param TransactionDTO $data
     * @return Transaction
     */
    public function startTransaction(TransactionDTO $data): Transaction
    {
        $transaction = Transaction::query()
            ->create([
                'value' => $data->value,
                'payer_id' => $data->payer,
                'payee_id' => $data->payee,
                'status' => TransactionStatusEnum::PENDING->value,
            ]);

        return $transaction;
    }

    /**
     * Mark a Transaction as COMPLETED.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function completeTransaction(Transaction $transaction): Transaction
    {
        return $this->markTransactionAsStatus($transaction, TransactionStatusEnum::COMPLETED);;
    }

    /**
     * Mark a Transaction as FAILED_NO_FUNDS.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function failTransactionNoFunds(Transaction $transaction): Transaction
    {
        return $this->markTransactionAsStatus($transaction, TransactionStatusEnum::FAILED_NO_FUNDS);
    }

    /**
     * Mark a Transaction as FAILED_UNAUTHORIZED.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function failTransactionUnauthorized(Transaction $transaction): Transaction
    {
        return $this->markTransactionAsStatus($transaction, TransactionStatusEnum::FAILED_UNAUTHORIZED);
    }

    /**
     * Mark a Transaction as FAILED_INVALID_WALLET_TYPE.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function failTransactionInvalidWalletType(Transaction $transaction): Transaction
    {
        return $this->markTransactionAsStatus($transaction, TransactionStatusEnum::FAILED_INVALID_WALLET_TYPE);
    }

    /**
     * Mark a Transaction as FAILED_UNKNOWN_REASON.
     *
     * @param Transaction $transaction
     * @return Transaction
     */
    public function markTransactionAsFailed(Transaction $transaction): Transaction
    {
        return $this->markTransactionAsStatus($transaction, TransactionStatusEnum::FAILED_UNKNOWN_REASON);
    }

    /**
     * Mark the Transaction with the given status.
     *
     * @param Transaction $transaction
     * @param TransactionStatusEnum $status
     * @return Transaction
     */
    private function markTransactionAsStatus(Transaction $transaction, TransactionStatusEnum $status): Transaction
    {
        $transaction->status = $status;
        $transaction->save();

        return $transaction;
    }
}
