<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
//use Illuminate\Support\Facades\Mail;

class ErrorsFileProva extends Mailable
{
  use Queueable, SerializesModels;

  public $data;

  /**
   * Create a new message instance.
   *
   * @return void
   */
  public function __construct($data = null)
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
    return $this->subject('Notificación de errores en archivo de inventario')
                ->view('mails.error_file_prova')
                ->attachData($this->data['file_content'], $this->data['name_file'], ['mime' => 'text/csv']);
  }
}
