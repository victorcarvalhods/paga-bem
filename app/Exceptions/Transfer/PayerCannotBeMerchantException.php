<?php

namespace App\Exceptions\Transfer;

use App\Exceptions\ApplicationException;
use Exception;

class PayerCannotBeMerchantException extends ApplicationException
{
    /** @var string */
    protected $message = 'Merchant accounts cannot initiate transfers.';
    /** @var int */
    protected $code = 422;
}
