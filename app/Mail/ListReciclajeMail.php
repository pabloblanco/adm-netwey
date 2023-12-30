<?php
/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Enero 2022
 */
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ListReciclajeMail extends Mailable
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

    fecha = fecha del reciclaje
    ListDNInsert = Lista de Dn que se reciclaron
    cant_insert = Cantidad de dn reciclados
    ListDNFail = Lista de Dn que dio problemas Altan
    cant_fail = Cantidad de Dn que dio problemas Altan
    ListDNOffert = Lista de Dn que poseen una oferta activa distinta a la default
    cant_offert = Cantidad de Dn que poseen una oferta distinta a dafault
    ListDNProva = Lista de Dn que se les anexo el prefijo y que se cargaron de prova
    cant_prova = cantidad de DN reciclados de prova
    ListDNSeller = Lista de DN agregados prefijos y que son dn a portar hacia netwey
    cant_seller = cantidad de DN reciclados del seller
    ListDNFailImei = reciclado exitosamente pero con falla en imei
    cantDNFailImei = cantidad de reciclado exitoso pero con falla en imei

    Ejemplo:

    $infodata = array(
    'fecha'        => date("Y/m/d", strtotime("-1 day", strtotime($hoy))),
    'ListDNInsert' => $ListDNInsert,
    'cant_insert'  => count($ListDNInsert),
    'ListDNFail'   => $ListDNFail,
    'cant_fail'    => count($ListDNFail),
    'ListDNOffert' => $ListDNOffert,
    'cant_offert'  => count($ListDNOffert),
    'ListDNProva'  => $ListDNProcessProva,
    'cant_prova'   => $cantDNProva,
    'ListDNSeller' => $ListDNProcessSeller,
    'cant_seller'  => $cantDNSeller);
    'ListDNFailImei' => $ListDNFailImei
    'cantDNFailImei' => $cantDNFailImei);

     */
    $this->dataBody = $data;
    /*
  Para ejecutar se debe enviar el correo y la data del correo

  Ejemplo de ejecucion:

  $emailsNetwey = 'luis@gdalab.com';
  try {
  Mail::to($emailsNetwey)->send(new ListReciclajeMail($infodata));
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
    return $this->subject('Reciclaje de msisdn del ' . $this->dataBody['fecha'])->view('mails.mail_Reciclaje');

  }

}
