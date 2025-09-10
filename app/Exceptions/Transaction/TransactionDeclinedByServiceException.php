<?php

namespace App\Exceptions\Transaction;

use App\Exceptions\ApplicationException;

class TransactionDeclinedByServiceException extends ApplicationException
{
    public function __construct() {
        parent::__construct(
            message: 'Transaction not authorized by payment service.',
            statusCode: 401,
        );
    }
}
