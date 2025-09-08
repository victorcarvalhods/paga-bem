<?php

namespace App\Exceptions\Transfer;

use App\Exceptions\ApplicationException;

class TransferDeclinedByServiceException extends ApplicationException
{
    /** @var string */
    protected $message = 'Transfer not authorized by external service.';
    /** @var int */
    protected $code = 403;
}
