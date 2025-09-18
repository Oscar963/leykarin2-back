<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Services\ReCaptchaService;

/**
 * Regla de validación personalizada para reCAPTCHA v3
 */
class ReCaptchaRule implements Rule
{
    private ReCaptchaService $recaptchaService;
    private ?string $remoteIp;
    private ?string $action;
    private ?string $errorMessage;

    public function __construct(?string $remoteIp = null, ?string $action = null)
    {
        $this->recaptchaService = app(ReCaptchaService::class);
        $this->remoteIp = $remoteIp;
        $this->action = $action;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Si reCAPTCHA está deshabilitado, pasar la validación
        if (!$this->recaptchaService->isEnabled()) {
            return true;
        }

        // Si no hay valor, fallar
        if (empty($value)) {
            $this->errorMessage = 'El token reCAPTCHA es requerido.';
            return false;
        }

        // Verificar el token
        $result = $this->recaptchaService->verify($value, $this->remoteIp, $this->action);

        if (!$result['success']) {
            $this->errorMessage = $result['message'] ?? 'Validación reCAPTCHA fallida.';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->errorMessage ?? 'Validación reCAPTCHA fallida.';
    }
}
