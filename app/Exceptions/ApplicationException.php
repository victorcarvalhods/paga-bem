<?php

namespace App\Exceptions;

use Exception;
use RuntimeException;

class ApplicationException extends RuntimeException
{

    /**
     * Render the exception as an array.
     *
     * @return array<string, int|string>
     */
    public function render(): array
    {
        return [
            'error' => $this->getMessage(),
            'code' => $this->getCode(),
        ];
    }
}
