<?php

namespace App\Exceptions;

use Exception;

class InvalidPasswordException extends Exception
{
    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = 'La contraseña proporcionada es incorrecta.', int $code = 0, \Throwable $previous = null)
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
                'password' => ['La contraseña es incorrecta.']
            ],
            'error_type' => 'invalid_password'
        ];
    }
}
