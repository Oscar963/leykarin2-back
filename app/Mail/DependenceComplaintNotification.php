<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DependenceComplaintNotification extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $complaint;
    public $logoUrl;
    public $dependenceName;

    public function __construct(Complaint $complaint, string $dependenceName)
    {
        $this->complaint = $complaint;
        $this->dependenceName = $dependenceName;
        $this->logoUrl = asset('assets/img/logos/logo-azul-2.png');
    }

    public function build()
    {
        return $this->subject('Nueva Denuncia Registrada - ' . $this->complaint->folio)
                    ->view('emails.dependence-complaint-notification');
    }
}
