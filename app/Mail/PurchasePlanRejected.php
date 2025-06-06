<?php

namespace App\Mail;

use App\Models\PurchasePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchasePlanRejected extends Mailable
{
    use Queueable, SerializesModels;

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
        return $this->subject('Plan de Compra Rechazado')
                    ->view('emails.purchase-plan-rejected')
                    ->with([
                        'purchasePlan' => $this->purchasePlan,
                        'user' => auth()->user(),
                    ]);
    }
} 