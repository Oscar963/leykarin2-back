<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    protected $token;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        // Usar variable de entorno para la URL del frontend
        $frontendUrl = env('FRONTEND_URL', config('app.url'));
        $resetUrl = $frontendUrl . '/reset-password/' . $this->token . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        $logoUrl = asset('assets/img/logos/logo-blanco.png');

        // Usar variables de entorno para configuración de email
        $fromAddress = env('NOTIFICATION_MAIL_FROM_ADDRESS', config('mail.from.address'));
        $fromName = env('NOTIFICATION_MAIL_FROM_NAME', config('mail.from.name'));

        return (new MailMessage())
            ->from($fromAddress, $fromName)
            ->subject('Restablece tu contraseña')
            ->markdown('emails.reset-password', ['resetUrl' => $resetUrl, 'logoUrl' => $logoUrl]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
