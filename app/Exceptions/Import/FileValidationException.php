<?php

namespace App\Exceptions\Import;

class FileValidationException extends ImportException
{
    protected $errorCode = 'FILE_VALIDATION_ERROR';
    protected $httpStatus = 422;

    public function __construct(string $message, array $validationErrors = [])
    {
        parent::__construct(
            $message,
            0,
            null,
            [
                'validation_errors' => $validationErrors,
                'error_count' => count($validationErrors)
            ]
        );
    }
} 