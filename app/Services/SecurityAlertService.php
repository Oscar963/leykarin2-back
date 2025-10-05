<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SecurityAlertMail;

/**
 * Servicio para gestionar alertas de seguridad en tiempo real.
 * 
 * Envía notificaciones por email, Slack y otros canales cuando
 * se detectan eventos de seguridad críticos.
 */
class SecurityAlertService
{
    /**
     * Eventos que requieren alerta inmediata
     *
     * @var array
     */
    protected $criticalEvents = [
        'multiple_failed_logins',
        'session_hijacking_detected',
        'ip_mismatch',
        'user_agent_mismatch',
        'rate_limit_exceeded',
        'suspicious_activity',
        'security_audit_failed',
        'unauthorized_access_attempt'
    ];

    /**
     * Envía alerta de seguridad
     *
     * @param string $event Nombre del evento
     * @param array $data Datos del evento
     * @return void
     */
    public function sendAlert(string $event, array $data): void
    {
        if (!config('security.monitoring_enabled', true)) {
            return;
        }

        // Agregar información adicional
        $data['timestamp'] = now()->toDateTimeString();
        $data['environment'] = config('app.env');
        $data['server_ip'] = request()->server('SERVER_ADDR');

        // Log del evento
        Log::channel('security')->critical("ALERTA DE SEGURIDAD: {$event}", $data);

        // Si es un evento crítico, enviar notificaciones
        if ($this->isCriticalEvent($event)) {
            $this->sendEmailAlert($event, $data);
            $this->sendSlackAlert($event, $data);
        }
    }

    /**
     * Verifica si un evento es crítico
     *
     * @param string $event
     * @return bool
     */
    protected function isCriticalEvent(string $event): bool
    {
        return in_array($event, $this->criticalEvents);
    }

    /**
     * Envía alerta por email
     *
     * @param string $event
     * @param array $data
     * @return void
     */
    protected function sendEmailAlert(string $event, array $data): void
    {
        $emails = $this->getAlertEmails();

        if (empty($emails)) {
            return;
        }

        foreach ($emails as $email) {
            try {
                Mail::to(trim($email))->send(new SecurityAlertMail($event, $data));
            } catch (\Exception $e) {
                Log::error('Error enviando alerta de seguridad por email', [
                    'email' => $email,
                    'event' => $event,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Envía alerta a Slack
     *
     * @param string $event
     * @param array $data
     * @return void
     */
    protected function sendSlackAlert(string $event, array $data): void
    {
        $webhook = config('security.slack_webhook');
        
        if (!$webhook) {
            return;
        }

        $message = [
            'text' => "🚨 *ALERTA DE SEGURIDAD*",
            'attachments' => [
                [
                    'color' => 'danger',
                    'fields' => [
                        [
                            'title' => 'Evento',
                            'value' => $event,
                            'short' => true
                        ],
                        [
                            'title' => 'Entorno',
                            'value' => config('app.env'),
                            'short' => true
                        ],
                        [
                            'title' => 'Timestamp',
                            'value' => $data['timestamp'] ?? now()->toDateTimeString(),
                            'short' => true
                        ],
                        [
                            'title' => 'Detalles',
                            'value' => "```\n" . json_encode($data, JSON_PRETTY_PRINT) . "\n```",
                            'short' => false
                        ]
                    ]
                ]
            ]
        ];

        try {
            $ch = curl_init($webhook);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            $result = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error('Error enviando alerta a Slack', [
                    'http_code' => $httpCode,
                    'response' => $result
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Excepción enviando alerta a Slack', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtiene la lista de emails para alertas
     *
     * @return array
     */
    protected function getAlertEmails(): array
    {
        $emails = config('security.alert_emails', env('SECURITY_ALERT_EMAIL', ''));
        
        if (is_string($emails)) {
            $emails = explode(',', $emails);
        }

        return array_filter(array_map('trim', $emails));
    }
}
