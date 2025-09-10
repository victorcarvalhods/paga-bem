<?php

namespace App\Exceptions\Transaction;

use App\Exceptions\ApplicationException;
use Exception;

class PayerCannotBeMerchantException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Merchant accounts cannot initiate transactions.',
            statusCode: 403,
        );
    }
}
