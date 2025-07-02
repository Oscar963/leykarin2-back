<?php

namespace App\Mail;

use App\Models\PurchasePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DecretoRemoved extends Mailable
{
    use Queueable;
    use SerializesModels;

    public $purchasePlan;

    /**
     * Create a new message instance.
     */
    public function __construct(PurchasePlan $purchasePlan)
    {
        $this->purchasePlan = $purchasePlan;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Decreto Eliminado - Plan de Compra Revertido')
                    ->view('emails.decreto-removed')
                    ->with([
                        'purchasePlan' => $this->purchasePlan,
                        'user' => auth()->user(),
                    ]);
    }
} 