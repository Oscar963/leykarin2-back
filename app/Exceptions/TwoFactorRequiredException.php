<?php

namespace App\Exceptions;

use Exception;
use App\Helpers\EmailHelper;

class TwoFactorRequiredException extends Exception
{
    protected $userEmail;

    /**
     * Create a new exception instance.
     */
    public function __construct(string $message = null, string $userEmail = null, int $code = 0, \Throwable $previous = null)
    {
        $this->userEmail = $userEmail;
        
        // Si no se proporciona mensaje y tenemos email, generar mensaje con email enmascarado
        if (!$message && $userEmail) {
            $message = EmailHelper::getTwoFactorMessage($userEmail);
        } elseif (!$message) {
            $message = 'Se requiere autenticación de dos factores. Se ha enviado un código de verificación a tu correo electrónico.';
        }
        
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get additional data to include in the API response.
     */
    public function getAdditionalData(): array
    {
        $data = ['two_factor_required' => true];
        
        if ($this->userEmail) {
            $data['masked_email'] = EmailHelper::maskEmail($this->userEmail);
        }
        
        return $data;
    }

    /**
     * Get the HTTP status code for this exception.
     */
    public function getStatusCode(): int
    {
        return 200;
    }
}
