<?php

namespace App\Exceptions\Transaction;

use App\Exceptions\ApplicationException;

class InsufficientBalanceException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Insufficient balance to complete this transaction.',
            statusCode: 409,
        );
    }
}
