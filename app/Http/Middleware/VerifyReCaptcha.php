<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\ReCaptchaService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para verificar reCAPTCHA v3
 */
class VerifyReCaptcha
{
    private ReCaptchaService $recaptchaService;

    public function __construct(ReCaptchaService $recaptchaService)
    {
        $this->recaptchaService = $recaptchaService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $action = null): Response
    {
        // Si reCAPTCHA está deshabilitado, continuar
        if (!$this->recaptchaService->isEnabled()) {
            return $next($request);
        }

        $recaptchaToken = $request->input('recaptcha_token');

        // Verificar que el token esté presente
        if (empty($recaptchaToken)) {
            return response()->json([
                'success' => false,
                'message' => 'Token reCAPTCHA requerido',
                'errors' => [
                    'recaptcha_token' => ['Token reCAPTCHA requerido']
                ]
            ], 422);
        }

        // Verificar el token
        $validationResponse = $this->recaptchaService->verifyOrFail(
            $recaptchaToken,
            $request->ip(),
            $action
        );

        // Si hay respuesta, significa que falló la validación
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        // Continuar con la petición
        return $next($request);
    }
}
