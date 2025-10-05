<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Mailable para enviar alertas de seguridad por email.
 */
class SecurityAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Nombre del evento de seguridad
     *
     * @var string
     */
    public $event;

    /**
     * Datos del evento
     *
     * @var array
     */
    public $data;

    /**
     * Create a new message instance.
     *
     * @param string $event
     * @param array $data
     */
    public function __construct(string $event, array $data)
    {
        $this->event = $event;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject("🚨 Alerta de Seguridad: {$this->event}")
                    ->view('emails.security-alert')
                    ->with([
                        'event' => $this->event,
                        'data' => $this->data,
                        'timestamp' => $this->data['timestamp'] ?? now()->toDateTimeString(),
                        'environment' => $this->data['environment'] ?? config('app.env')
                    ]);
    }
}
