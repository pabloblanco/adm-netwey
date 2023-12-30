<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class BillingMasiveMail extends Mailable
{
    use Queueable, SerializesModels;
    public $data;
    public $file;
    public $name;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = null, $file = null, $name = null)
    {
        $this->data = $data;
        $this->file = $file;
        $this->name = $name;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

        return $this->subject('Facturacion masiva Oxxo')
                    ->view('mails.mail_Billing_Masive')
                    ->attachData($this->file, $this->name, ['mime' => 'text/csv']);

    }
}