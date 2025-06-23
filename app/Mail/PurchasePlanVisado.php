<?php

namespace App\Mail;

use App\Models\PurchasePlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchasePlanVisado extends Mailable
{
    use Queueable, SerializesModels;

    public $purchasePlan;
    public $comment;

    /**
     * Create a new message instance.
     */
    public function __construct(PurchasePlan $purchasePlan, $comment = null)
    {
        $this->purchasePlan = $purchasePlan;
        $this->comment = $comment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Plan de Compra Visado')
                    ->view('emails.purchase-plan-visado')
                    ->with([
                        'purchasePlan' => $this->purchasePlan,
                        'comment' => $this->comment,
                        'user' => auth()->user(),
                    ]);
    }
} 