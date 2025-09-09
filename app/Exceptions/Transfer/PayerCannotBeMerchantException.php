<?php

namespace App\Exceptions\Transfer;

use App\Exceptions\ApplicationException;
use Exception;

class PayerCannotBeMerchantException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Merchant accounts cannot initiate transfers.',
            statusCode: 403,
        );
    }
}
