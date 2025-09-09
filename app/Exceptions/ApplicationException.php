<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ApplicationException extends Exception
{
    /** @var int */
    protected $statusCode;
    /** @var string */
    protected $errorCode;
    /** @var array<string, mixed> */
    protected $context;

    /**
     * ApplicationException constructor.
     *
     * @param string $message
     * @param int $statusCode
     * @param string|null $errorCode
     * @param array<string, mixed> $context
     * @param Exception|null $previous
     */
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

    /**
     * Get additional context about the exception.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getStatusCode(),
        ], $this->getStatusCode());
    }
}
