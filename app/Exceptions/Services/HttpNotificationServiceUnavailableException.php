<?php

namespace App\Exceptions\Services;

use App\Exceptions\ApplicationException;

class HttpNotificationServiceUnavailableException extends ApplicationException
{
    /** @var string */
    protected $message = 'Notification service is currently unavailable.';
    /** @var int */
    protected $code = 504;
}
