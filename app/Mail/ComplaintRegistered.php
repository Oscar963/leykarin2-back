<?php

namespace App\Mail;

use App\Models\Complaint;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Barryvdh\DomPDF\Facade\Pdf;

class ComplaintRegistered extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $complaint;
    private $pdf;
    private $pdfFileName;

    /**
     * Create a new message instance.
     */
    public function __construct(Complaint $complaint, $pdf = null, $pdfFileName = null)
    {
        $this->complaint = $complaint;
        $this->pdf = $pdf;
        $this->pdfFileName = $pdfFileName;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Confirmación de Denuncia - ' . $this->complaint->folio)
                    ->view('emails.complaint-registered');

        // Si hay PDF, lo adjuntamos
        if ($this->pdf && $this->pdfFileName) {
            $mail->attachData(
                $this->pdf->output(),
                $this->pdfFileName,
                ['mime' => 'application/pdf']
            );
        }

        return $mail;
    }
}
