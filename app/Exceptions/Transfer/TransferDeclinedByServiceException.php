<?php

namespace App\Exceptions\Transfer;

use App\Exceptions\ApplicationException;

class TransferDeclinedByServiceException extends ApplicationException
{
    public function __construct() {
        parent::__construct(
            message: 'Transfer not authorized by payment service.',
            statusCode: 401,
        );
    }
}
