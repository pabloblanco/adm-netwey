<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class RetentionMail extends Mailable
{
    use Queueable, SerializesModels;
    public $dataBody;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data = null)
    {
        /*
        Se recibe un array en la varible $data con la informacion a ser adjuntada en el correo, los campos son:

        GB = La cantidad de gb a ser utilizada como compesacion
        tiempo = Vigencia de la cantidad de Gb obsequiados
        name = Nombre de cliente
        last_name = APellido del cliente

        Ejemplo:

        $infodata = array(
        'GB'        => '2gb',
        'tiempo'    => '15 dias',
        'name'      => 'Luis',
        'last_name' => 'Jose',
        );
         */
        $this->dataBody = $data;
        /*
    Para ejecutar se debe enviar el correo y la data del correo

    Ejemplo de ejecucion:

    $mailuser = 'luis@gdalab.com';
    try {
    Mail::to($mailuser)->send(new RetentionMail($infodata));
    } catch (\Exception $e) {}
     */
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Gg de compensacion Netwey.')->view('mails.mail_Retencion');

    }

}
