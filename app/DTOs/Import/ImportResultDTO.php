<?php

namespace App\DTOs\Import;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class ImportResultDTO implements Arrayable, Jsonable
{
    private bool $success;
    private string $message;
    private array $data;
    private int $httpStatus;
    private ?string $importId;
    private ?array $errors;
    private ?array $warnings;

    public function __construct(
        bool $success,
        string $message,
        array $data,
        int $httpStatus,
        ?string $importId = null,
        ?array $errors = null,
        ?array $warnings = null
    ) {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->httpStatus = $httpStatus;
        $this->importId = $importId;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    /**
     * Create successful import result
     */
    public static function success(
        string $message,
        array $data,
        int $httpStatus = 200,
        ?string $importId = null
    ): self {
        return new self(
            true,
            $message,
            $data,
            $httpStatus,
            $importId
        );
    }

    /**
     * Create failed import result
     */
    public static function failure(
        string $message,
        array $errors = [],
        int $httpStatus = 422,
        ?string $importId = null
    ): self {
        return new self(
            false,
            $message,
            [],
            $httpStatus,
            $importId,
            $errors
        );
    }

    /**
     * Create partial success result
     */
    public static function partial(
        string $message,
        array $data,
        array $warnings = [],
        ?string $importId = null
    ): self {
        return new self(
            true,
            $message,
            $data,
            207,
            $importId,
            null,
            $warnings
        );
    }

    /**
     * Get the instance as an array
     */
    public function toArray(): array
    {
        $result = [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'timestamp' => now()->toISOString(),
        ];

        if ($this->importId) {
            $result['import_id'] = $this->importId;
        }

        if ($this->errors) {
            $result['errors'] = $this->errors;
        }

        if ($this->warnings) {
            $result['warnings'] = $this->warnings;
        }

        return $result;
    }

    /**
     * Convert the object to its JSON representation
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Get HTTP response
     */
    public function toResponse(): \Illuminate\Http\JsonResponse
    {
        return response()->json($this->toArray(), $this->httpStatus);
    }

    /**
     * Check if import was successful
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Check if import has errors
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if import has warnings
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Get error count
     */
    public function getErrorCount(): int
    {
        return count($this->errors ?? []);
    }

    /**
     * Get warning count
     */
    public function getWarningCount(): int
    {
        return count($this->warnings ?? []);
    }

    /**
     * Get success status
     */
    public function getSuccess(): bool
    {
        return $this->success;
    }

    /**
     * Get message
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Get data
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get HTTP status
     */
    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }

    /**
     * Get import ID
     */
    public function getImportId(): ?string
    {
        return $this->importId;
    }

    /**
     * Get errors
     */
    public function getErrors(): ?array
    {
        return $this->errors;
    }

    /**
     * Get warnings
     */
    public function getWarnings(): ?array
    {
        return $this->warnings;
    }
} 