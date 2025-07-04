<?php

namespace App\Mail;

use App\Models\Modification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ModificationCreated extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $modification;
    public $emailContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Modification $modification, ?string $emailContent = null)
    {
        $this->modification = $modification;
        $this->emailContent = $emailContent;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Nueva Modificación Creada - Plan de Compras')
                    ->view('emails.modification-created');
    }
}
