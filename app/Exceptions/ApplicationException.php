<?php

namespace App\Exceptions;

use Exception;

class ApplicationException extends Exception
{
    protected $statusCode;
    protected $errorCode;
    protected $context;

    public function __construct(
        string $message = "",
        int $statusCode = 400,
        ?string $errorCode = null,
        array $context = [],
        ?Exception $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode ?? 'APPLICATION_ERROR';
        $this->context = $context;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function render()
    {
        return response()->json([
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getStatusCode(),
        ], $this->getStatusCode());
    }
}
