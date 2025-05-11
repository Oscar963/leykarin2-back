<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ComplaintRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $complaint;
    public $logoUrl;

    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
        $this->logoUrl = asset('assets/img/logos/logo-azul-2.png');
    }

    public function build()
    {
        return $this->subject('Confirmación de Denuncia - ' . $this->complaint->folio)
                    ->view('emails.complaint-registered');
    }
} 