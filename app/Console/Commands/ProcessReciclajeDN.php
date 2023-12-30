<?php
/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Enero 2022
 */
namespace App\Console\Commands;

use App\Inventory;
use App\Inv_reciclers;
use App\Mail\ListReciclajeMail;
use App\SellerInventory;
use App\SellerInventoryTrack;
use App\StockProvaDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessReciclajeDN extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:ProcessReciclaje';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Proceso diario que carga en inventario y notifica via correo el proceso de reciclaje llevado a cabo en el dia';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    $ListDNInsert        = "";
    $ListDNProcessProva  = "";
    $ListDNProcessSeller = "";
    $ListDNFail          = "";
    $ListDNOffert        = "";
    $ListDNFailImei      = "";

    //Nota: El proceso de prefijo se realiza en el servicio de portabilidad.
    //
    $hoy     = date("Y-m-d");
    $Ayer    = date("Y-m-d", strtotime("-1 day", strtotime($hoy)));
    $counter = 0;

    //////////// Revision de fallos Altan //////////////////////////////

    $InvPendienteList = Inv_reciclers::getConnect('W')
      ->where([['islim_inv_reciclers.status', 'C'],
        ['islim_inv_reciclers.checkAltan', 'Y']])
      ->get();

    if (!empty($InvPendienteList) && $InvPendienteList->count() > 0) {
      $this->info("Revisando casos de falla de comunicacion profile!");

      foreach ($InvPendienteList as $InnFailAltanItem) {
        $counter++;
        $this->info($counter . "- Revisando DN " . $InnFailAltanItem->msisdn);

        $resChk = Inv_reciclers::chekkingClient($InnFailAltanItem->msisdn);

        if ($resChk['code'] == 'DIFF_OFFER') {

          $InnFailAltanItem->checkOffert = 'Y';
          $InnFailAltanItem->checkAltan  = 'N';
          $InnFailAltanItem->status      = 'C';

        } elseif ($resChk['code'] == 'DN_FIBRA'
          || $resChk['code'] == 'FAIL_ALTAN'
          || $resChk['code'] == 'OTHER_STATUS') {

          $InnFailAltanItem->status = 'E';
          if ($resChk['code'] == 'FAIL_ALTAN') {
            $InnFailAltanItem->checkAltan  = 'Y';
            $InnFailAltanItem->checkOffert = 'N';
          }
          $InnFailAltanItem->detail_error = $resChk['msg'];

        } else {
          $InnFailAltanItem->status      = 'C';
          $InnFailAltanItem->checkOffert = 'N';
          $InnFailAltanItem->checkAltan  = 'N';
        }
        if (!empty($resChk['msg'])) {
          $InnFailAltanItem->detail_error = $resChk['msg'];
        }
        $this->info("Resultado: " . $resChk['code']);
        $InnFailAltanItem->date_update = date('Y-m-d H:i:s', time());
        $InnFailAltanItem->save();
      }
    } else {
      $this->info("");
      $this->info("**************************");
      $this->info("No hay registros para procesar por fallo de Altan");
      $this->info("**************************");
    }
    /////////////// END revision de fallos Altan ////////////////////
    /////////// Proceso de carga de inventario ////////////////////

    $InvReciclerList = Inv_reciclers::getConnect('W')
      ->where([['islim_inv_reciclers.status', 'F'],
        ['islim_inv_reciclers.ReciclerType', 'C'],
        ['islim_inv_reciclers.loadInventary', 'N']])
      ->get();

    if (!empty($InvReciclerList) && $InvReciclerList->count() > 0) {

      $counter = 0;
      foreach ($InvReciclerList as $InvPendiente) {
        $loadDN  = true; // verifica que sea posible cargar el dn
        $isProva = false; // es un reciclaje de prova
        $error   = false;
        $DNprova = null;
        $counter++;
        $this->info("");
        $this->info($counter . "- Revisando DN " . $InvPendiente->msisdn . " para reciclaje diario!");

        if ($InvPendiente->origin_netwey == 'sftp') {
          $loadDN  = false;
          $DNprova = StockProvaDetail::getDetailRecicler($InvPendiente->msisdn);
          if (!empty($DNprova)) {
            $isProva = true;
            if (!empty($DNprova->user_assignment)) {
              $loadDN = true;
            }
          }
        }
        if ($loadDN) {
          $inventoryMSISDN  = Inventory::existDN($InvPendiente->msisdn);
          $inventoryMACIMEI = Inventory::existMACIMEI($InvPendiente->imei);
          if (empty($inventoryMACIMEI) && empty($inventoryMSISDN)) {

            $inv_reciclado = Inventory::getConnect('W'); //->create($InvPendiente);

            $inv_reciclado->inv_article_id = $InvPendiente->inv_article_id;
            $inv_reciclado->warehouses_id  = $InvPendiente->warehouses_id;
            $inv_reciclado->msisdn         = $InvPendiente->msisdn;
            $inv_reciclado->iccid          = $InvPendiente->iccid;
            $inv_reciclado->imei           = $InvPendiente->imei;
            $inv_reciclado->price_pay      = $InvPendiente->price_pay;
            $inv_reciclado->serial         = $InvPendiente->serial;
            $inv_reciclado->date_reception = $InvPendiente->date_reception;
            $inv_reciclado->date_sending   = $InvPendiente->date_sending;
            $inv_reciclado->obs            = $InvPendiente->obs;
            $inv_reciclado->date_reg       = date('Y-m-d H:i:s', time());
            $inv_reciclado->save();
            $InvPendiente->loadInventary = 'Y';
            $InvPendiente->date_loading  = date('Y-m-d H:i:s', time());

            $this->info("");
            $this->info("Procesado en inventario bajo el id " . $inv_reciclado->id . " el DN " . $InvPendiente->msisdn . " origen " . $InvPendiente->origin_netwey);

            if ($isProva) {
              //actualizo el reciclaje en detail prova
              $DNprova->statusRecycling = 'R';
              $DNprova->comment         = "El Dn ha sido reciclado y cargado en inventario.";
              if (!empty($DNprova->user_assignment)) {
                if (!empty($inv_reciclado->id)) {

                  SellerInventory::setInventoryUser($DNprova->user_assignment, $inv_reciclado->id);
                  $this->info("- Asignado el articulo " . $inv_reciclado->id . " al usuario " . $DNprova->user_assignment);

                  SellerInventoryTrack::setInventoryTrack(
                    $inv_reciclado->id,
                    null,
                    $inv_reciclado->warehouses_id,
                    $DNprova->user_assignment,
                    null,
                    $DNprova->user_assignment,
                    'Asignado por cron de reciclaje'
                  );
                  $this->info("Registrado el movimiento del articulo " . $inv_reciclado->id);

                  $DNprova->status = 'AS';
                  $DNprova->comment .= " Se asigno por cron de reciclaje";
                  //agrego la tabla de rastreo
                }
              } else {
                $this->info("No hay email al cual asociar el DN");
              }
              $DNprova->save();
              $this->info("Actualizado el detalle de prova " . $DNprova->id);
            }
          } else {
            if (!empty($inventoryMACIMEI)) {
              $text0 = "El imei/mac ya esta siendo usado por un equipo";
              $ListDNFailImei .= " " . $InvPendiente->msisdn . ",";
            } else {
              $text0 = "El DN esta siendo usado por un equipo";
            }
            $InvPendiente->detail_error = $text0;
            $this->info("> " . $InvPendiente->msisdn . " " . $text0);
            $error                = true;
            $InvPendiente->status = 'T';
            //Solo registro estos casos de imei o de DN debido a que fueron cargados por otro medio luego que se cargaron para reciclaje
          }
          if (!$error) {
            $InvPendiente->status = 'P';
          }
          $InvPendiente->save();
        } else {
          if ($isProva) {
            //Fue un Dn de prova que no tiene asignacion de usuario, solo informo que se reciclo
            $DNprova->statusRecycling = 'R';
            $DNprova->comment         = "El Dn ha sido reciclado";
            $DNprova->save();
          }
        }
      }
      sleep(2);
    } else {
      $this->info("");
      $this->info("**************************");
      $this->info("No hay registros reciclados que deban ser cargados en inventario");
      $this->info("**************************");
    }
    /////////////// END Proceso de carga de inventario ////////////////////
    //
    //Se recopila la informacion del dia anterior que sera enviado al correo
    //Se envia solo los que dan error y los creados que estan en espera
    $InfoMailSend = Inv_reciclers::getConnect('R')
      ->where('islim_inv_reciclers.date_reg', '>=', $Ayer . ' 00:00:00')
      ->whereIn('islim_inv_reciclers.status', ['C', 'E'])
      ->get();

    //->whereNotIn('islim_inv_reciclers.status', ['T','F','P'])

    if (!empty($InfoMailSend) && $InfoMailSend->count() > 0) {
      $this->info("----");
      $this->info("Casos del dia " . $Ayer . " a ser revisados y posiblemente se notifiquen via email: " . $InfoMailSend->count());
      $emailOK   = false; //Adjunta al correo los casos OK
      $emailFail = true; //Adjunto al correo los fallos

      foreach ($InfoMailSend as $InfoMail) {

        if ($InfoMail->status == 'P' && $InfoMail->ReciclerType == 'C' && $emailOK) {
          $ListDNInsert .= " " . $InfoMail->msisdn . ",";
        } elseif ($InfoMail->status == 'F' && $InfoMail->origin_netwey == 'sftp' && $emailOK) {
          $ListDNProcessProva .= " " . $InfoMail->msisdn . ",";
        } elseif ($InfoMail->status == 'F' && $InfoMail->origin_netwey == 'seller' && $emailOK) {
          $ListDNProcessSeller .= " " . $InfoMail->msisdn . ",";
        } elseif ($InfoMail->checkOffert == 'Y' && $emailFail) {
          //no se pudo reciclar por oferta distinta a default
          $ListDNOffert .= " " . $InfoMail->msisdn . " -> Oferta(" . $InfoMail->codeOffert . "),";
        } elseif ( /*$InfoMail->checkAltan == 'Y' &&*/$emailFail) {
          //no se pudo reciclar por error de profile altan( se presenta en: one, file, sftp, seller)
          $ListDNFail .= " " . $InfoMail->msisdn . ",";
        }
      }
      //Elimino la ultima coma presente en la lista de DN reciclados
      if (!empty($ListDNInsert)) {
        $ListDNInsert = rtrim($ListDNInsert, ',');
        $cantDNInser  = count(explode(',', $ListDNInsert));
      } else {
        $cantDNInser = 0;
      }

      if (!empty($ListDNFail)) {
        $ListDNFail = rtrim($ListDNFail, ',');
        $cantDNFail = count(explode(',', $ListDNFail));
      } else {
        $cantDNFail = 0;
      }

      if (!empty($ListDNOffert)) {
        $ListDNOffert = rtrim($ListDNOffert, ',');
        $cantDNOffert = count(explode(',', $ListDNOffert));
      } else {
        $cantDNOffert = 0;
      }

      if (!empty($ListDNProcessProva)) {
        $ListDNProcessProva = rtrim($ListDNProcessProva, ',');
        $cantDNProva        = count(explode(',', $ListDNProcessProva));
      } else {
        $cantDNProva = 0;
      }

      if (!empty($ListDNProcessSeller)) {
        $ListDNProcessSeller = rtrim($ListDNProcessSeller, ',');
        $cantDNSeller        = count(explode(',', $ListDNProcessSeller));
      } else {
        $cantDNSeller = 0;
      }

      if (!empty($ListDNFailImei)) {
        $ListDNFailImei = rtrim($ListDNFailImei, ',');
        $cantDNFailImei = count(explode(',', $ListDNFailImei));
      } else {
        $cantDNFailImei = 0;
      }
      //Se redacta un correo con los Dns que se acabaron de reciclar

      if ($cantDNFailImei > 0 || $cantDNOffert > 0 || $cantDNFail > 0) {
        //Solo se envia los DN con fallas
        $cantFil = $cantDNFailImei + $cantDNOffert + $cantDNFail;
        $this->info("Se notificaran " . $cantFil . " msisdn(s) con inconvenientes a travez del email");

        $emailsNetwey = explode(',', env('LIST_MAIL_RECICLAJE'));

        $infodata = array(
          'fecha'          => $Ayer,
          'ListDNInsert'   => $ListDNInsert,
          'cant_insert'    => $cantDNInser,
          'ListDNFail'     => $ListDNFail,
          'cant_fail'      => $cantDNFail,
          'ListDNOffert'   => $ListDNOffert,
          'cant_offert'    => $cantDNOffert,
          'ListDNProva'    => $ListDNProcessProva,
          'cant_prova'     => $cantDNProva,
          'ListDNSeller'   => $ListDNProcessSeller,
          'cant_seller'    => $cantDNSeller,
          'ListDNFailImei' => $ListDNFailImei,
          'cantDNFailImei' => $cantDNFailImei);
        try {
          $this->info("");
          $this->info("Se enviara el reporte de reciclaje al correo...");
          Mail::to($emailsNetwey)->send(new ListReciclajeMail($infodata));
          $this->info("Enviado!");
        } catch (\Exception $e) {
          $text0 = "No se pudo enviar el email de notificacion de reciclaje";
          $this->info($text0);
          Log::alert($text0 . " +Detalles: " . (String) json_encode($e->getMessage()));
        }
      } else {
        $this->info("No hay msisdn(s) con problemas a ser notificados a travez del email");
      }
    } else {
      $this->info("");
      $this->info("**************************");
      $this->info("> No hay notificaciones de reciclaje del dia de ayer que se deban notificar via email");
      $this->info("**************************");
    }
  }
}
