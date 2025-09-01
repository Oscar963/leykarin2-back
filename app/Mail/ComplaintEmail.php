<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Complaint;

class ComplaintEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The complaint instance.
     *
     * @var \App\Models\Complaint
     */
    public Complaint $complaint;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Complaint $complaint)
    {
        $this->complaint = $complaint;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject('Comprobante de denuncia Nº ' . ($this->complaint->folio ?? ''))
            ->view('emails.complaint')
            ->with([
                'complaint' => $this->complaint,
                'folio' => $this->complaint->folio,
                'createdAt' => $this->complaint->created_at,
                'complainant' => $this->complaint->complainant,
            ]);
    }
}
