<?php

namespace App\Exceptions;

use Exception;

class InvalidTwoFactorCodeException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'El código de verificación proporcionado no es válido o ha expirado.', int $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function getStatusCode(): int
    {
        return 422;
    }

    /**
     * Get additional data to include in the API response.
     */
    public function getAdditionalData(): array
    {
        return [
            'errors' => [
                'code' => ['El código proporcionado es incorrecto o ha expirado.']
            ],
            'error_type' => 'invalid_two_factor_code'
        ];
    }
}
