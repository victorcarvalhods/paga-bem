<?php

namespace App\Exceptions\Services;

use App\Exceptions\ApplicationException;

class HttpNotificationServiceUnavailableException extends ApplicationException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Notification service is currently unavailable.',
            statusCode: 504,
        );
    }
}
