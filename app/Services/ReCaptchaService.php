<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

/**
 * Servicio para manejar la validación de Google reCAPTCHA v3
 */
class ReCaptchaService
{
    private array $config;

    public function __construct()
    {
        $this->config = config('services.recaptcha');
    }

    /**
     * Verificar token reCAPTCHA v3
     *
     * @param string $token Token reCAPTCHA del frontend
     * @param string|null $remoteIp IP del cliente
     * @param string|null $action Acción esperada (opcional)
     * @return array Resultado de la verificación
     */
    public function verify(string $token, ?string $remoteIp = null, ?string $action = null): array
    {
        // Si reCAPTCHA está deshabilitado, retornar éxito
        if (!$this->isEnabled()) {
            Log::info('reCAPTCHA deshabilitado, saltando validación');
            return [
                'success' => true,
                'score' => 1.0,
                'action' => $action,
                'message' => 'reCAPTCHA deshabilitado'
            ];
        }

        // Validar que tengamos la secret key
        if (empty($this->config['secret_key'])) {
            Log::error('RECAPTCHA_SECRET_KEY no configurada');
            return [
                'success' => false,
                'error' => 'RECAPTCHA_SECRET_KEY_MISSING',
                'message' => 'Configuración de reCAPTCHA incompleta'
            ];
        }

        try {
            // Preparar datos para la petición
            $postData = [
                'secret' => $this->config['secret_key'],
                'response' => $token,
            ];

            if ($remoteIp) {
                $postData['remoteip'] = $remoteIp;
            }

            // Realizar petición a Google reCAPTCHA API
            $httpClient = Http::timeout($this->config['timeout'] ?? 10);
            
            // En desarrollo, deshabilitar verificación SSL para evitar problemas de certificados
            if (app()->environment('local', 'development')) {
                $httpClient = $httpClient->withOptions([
                    'verify' => false
                ]);
            }
            
            $response = $httpClient
                ->asForm()
                ->post($this->config['verify_url'] ?? 'https://www.google.com/recaptcha/api/siteverify', $postData);

            if (!$response->successful()) {
                Log::error('Error HTTP en petición reCAPTCHA', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'success' => false,
                    'error' => 'HTTP_ERROR',
                    'message' => 'Error de comunicación con reCAPTCHA'
                ];
            }

            $data = $response->json();

            // Log de la respuesta para debugging (sin datos sensibles)
            Log::info('Respuesta reCAPTCHA recibida', [
                'success' => $data['success'] ?? false,
                'score' => $data['score'] ?? null,
                'action' => $data['action'] ?? null,
                'hostname' => $data['hostname'] ?? null,
                'challenge_ts' => $data['challenge_ts'] ?? null,
                'error_codes' => $data['error-codes'] ?? null
            ]);

            // Verificar éxito básico
            if (!($data['success'] ?? false)) {
                $errorCodes = $data['error-codes'] ?? ['unknown-error'];
                Log::warning('Validación reCAPTCHA fallida', [
                    'error_codes' => $errorCodes,
                    'remote_ip' => $remoteIp
                ]);

                return [
                    'success' => false,
                    'error' => 'RECAPTCHA_FAILED',
                    'error_codes' => $errorCodes,
                    'message' => $this->getErrorMessage($errorCodes)
                ];
            }

            // Verificar acción si se especificó
            if ($action && isset($data['action']) && $data['action'] !== $action) {
                Log::warning('Acción reCAPTCHA no coincide', [
                    'expected' => $action,
                    'received' => $data['action'],
                    'remote_ip' => $remoteIp
                ]);

                return [
                    'success' => false,
                    'error' => 'ACTION_MISMATCH',
                    'message' => 'Acción de reCAPTCHA no válida'
                ];
            }

            // Verificar score mínimo
            $score = $data['score'] ?? 0;
            $minScore = $this->getMinScore();

            if ($score < $minScore) {
                Log::warning('Score reCAPTCHA bajo', [
                    'score' => $score,
                    'min_score' => $minScore,
                    'remote_ip' => $remoteIp,
                    'action' => $data['action'] ?? null
                ]);

                return [
                    'success' => false,
                    'error' => 'LOW_SCORE',
                    'score' => $score,
                    'min_score' => $minScore,
                    'message' => 'Validación de seguridad fallida'
                ];
            }

            // Éxito
            Log::info('Validación reCAPTCHA exitosa', [
                'score' => $score,
                'action' => $data['action'] ?? null,
                'remote_ip' => $remoteIp
            ]);

            return [
                'success' => true,
                'score' => $score,
                'action' => $data['action'] ?? null,
                'hostname' => $data['hostname'] ?? null,
                'challenge_ts' => $data['challenge_ts'] ?? null,
                'message' => 'Validación exitosa'
            ];
        } catch (\Exception $e) {
            Log::error('Excepción en validación reCAPTCHA', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'remote_ip' => $remoteIp
            ]);

            return [
                'success' => false,
                'error' => 'EXCEPTION',
                'message' => 'Error interno en validación de seguridad'
            ];
        }
    }

    /**
     * Verificar token y retornar JsonResponse si falla
     *
     * @param string $token
     * @param string|null $remoteIp
     * @param string|null $action
     * @return JsonResponse|null Null si es válido, JsonResponse si falla
     */
    public function verifyOrFail(string $token, ?string $remoteIp = null, ?string $action = null): ?JsonResponse
    {
        $result = $this->verify($token, $remoteIp, $action);

        if (!$result['success']) {
            $error = $result['error'] ?? 'UNKNOWN';
            $statusCode = 422; // Default

            switch ($error) {
                case 'LOW_SCORE':
                case 'ACTION_MISMATCH':
                case 'RECAPTCHA_FAILED':
                    $statusCode = 422;
                    break;
                case 'HTTP_ERROR':
                    $statusCode = 503;
                    break;
                case 'RECAPTCHA_SECRET_KEY_MISSING':
                    $statusCode = 500;
                    break;
            }

            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'errors' => [
                    'recaptcha' => [$result['message']]
                ]
            ], $statusCode);
        }

        return null;
    }

    /**
     * Verificar si reCAPTCHA está habilitado
     */
    public function isEnabled(): bool
    {
        return (bool) ($this->config['enabled'] ?? true);
    }

    /**
     * Obtener score mínimo configurado
     */
    public function getMinScore(): float
    {
        return (float) ($this->config['min_score'] ?? 0.5);
    }

    /**
     * Obtener site key para el frontend
     */
    public function getSiteKey(): ?string
    {
        return $this->config['site_key'] ?? null;
    }

    /**
     * Obtener mensaje de error amigable basado en códigos de error
     */
    private function getErrorMessage(array $errorCodes): string
    {
        $messages = [
            'missing-input-secret' => 'Configuración de reCAPTCHA incompleta',
            'invalid-input-secret' => 'Clave secreta de reCAPTCHA inválida',
            'missing-input-response' => 'Token reCAPTCHA requerido',
            'invalid-input-response' => 'Token reCAPTCHA inválido',
            'bad-request' => 'Petición reCAPTCHA malformada',
            'timeout-or-duplicate' => 'Token reCAPTCHA expirado o duplicado'
        ];

        foreach ($errorCodes as $code) {
            if (isset($messages[$code])) {
                return $messages[$code];
            }
        }

        return 'Error de validación reCAPTCHA';
    }
}
