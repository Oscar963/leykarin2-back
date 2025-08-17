<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $code;
    public $logoUrl;

    /**
     * Create a new message instance.
     *
     * @param User $user
     * @param string $code
     */
    public function __construct(User $user, string $code)
    {
        $this->user = $user;
        $this->code = $code;
        $this->logoUrl = asset('assets/img/logos/logo-blanco-2.png');
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Código de Verificación - ' . config('app.name'))
                    ->view('emails.two-factor-code')
                    ->with([
                        'userName' => $this->user->name . ' ' . $this->user->paternal_surname,
                        'logoUrl' => $this->logoUrl,
                        'code' => $this->code,
                        'expiresInMinutes' => 10
                    ]);
    }
}
