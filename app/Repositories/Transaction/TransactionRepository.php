<?php

declare(strict_types=1);

namespace App\Repositories\Transaction;

use App\DataTransferObjects\Transaction\TransactionDTO;
use App\Models\Transaction;

class TransactionRepository
{
    /**
     * Store a new Transaction record.
     *
     * @param TransactionDTO $data
     * @return Transaction
     */
    public function create(TransactionDTO $data): Transaction
    {
        return Transaction::query()->create($data->toArray());
    }
}