<?php

namespace App\Exceptions\Import;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

abstract class ImportException extends Exception
{
    protected $errorCode;
    protected $httpStatus;
    protected $context;

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get the error code for this exception
     */
    public function getErrorCode(): string
    {
        return $this->errorCode ?? 'IMPORT_ERROR';
    }

    /**
     * Get HTTP status code for this exception
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus ?? 500;
    }

    /**
     * Get additional context for this exception
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'code' => $this->getErrorCode(),
                'message' => $this->getMessage(),
                'context' => $this->getContext(),
            ],
            'timestamp' => now()->toISOString(),
        ], $this->getHttpStatus());
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        Log::error('Import Exception', [
            'error_code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'context' => $this->getContext(),
            'trace' => $this->getTraceAsString(),
        ]);
    }
} 