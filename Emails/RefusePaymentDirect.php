<?php

namespace Modules\PaiementGateways\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefusePaymentDirect extends Mailable
{
    use Queueable, SerializesModels;

    public $data = [];

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {


        return $this->subject(__("Échec du paiement ").': '.__('Transaction').' '. $this->data['transaction']['reference'])
                    ->markdown('paiementgateways::emails.send.refusedirect', ['data', $this->data]);
    }
}
