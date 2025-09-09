<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApplicationException;
use Exception;

class InsufficientBalanceException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Insufficient balance to complete this transfer.',
            statusCode: 409,
        );
    }
}
