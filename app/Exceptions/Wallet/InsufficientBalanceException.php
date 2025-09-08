<?php

namespace App\Exceptions\Wallet;

use App\Exceptions\ApplicationException;
use Exception;

class InsufficientBalanceException extends ApplicationException
{
    /** @var string */
    protected $message = 'Insufficient balance to complete this transfer.';
    /** @var int */
    protected $code = 422;
}
