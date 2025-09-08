<?php

namespace App\Exceptions\Transfer;

use App\Exceptions\ApplicationException;
use Exception;

class PayerCannotBeMerchantException extends ApplicationException
{
    protected $message = 'Merchant accounts cannot initiate transfers.';
    protected $code = 422;
}
