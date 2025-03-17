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
        $resetUrl = config('app.frontend_url') . '/reset-password/' . $this->token . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

        $logoUrl = asset('assets/img/logos/logo-blanco.png'); // Asegúrate de que la imagen esté en public/images/logo.png

        // Usamos la vista de correo personalizada
        return (new MailMessage)
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
