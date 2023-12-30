<?php

namespace App\Console\Commands;

use App\BillingMasive;
use App\Client;
use App\ClientNetwey;
use App\Helpers\APIAltan;
use App\Mail\BillingMasiveMail;
use App\Sale;
use App\Service;
use App\CoordinateChanges;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class test extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:test';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'pruebas de cron';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  // calcula fecha de expiracion de los DNs que tengan la esa fecha en null
  public function dateExpiredCalculate()
  {

    $dns = ClientNetwey::getConnect('W')->whereNull('date_expire')->where('date_reg', '<', date('Y-m-d 00:00:00'))->get();

    foreach ($dns as $key => $dn) {
      $recharge = Sale::getLastRecharge($dn->msisdn);
      if (!empty($recharge)) {

        $periodicity = Service::getPeriodicity($recharge->services_id);
        $date_exp    = Carbon::createFromFormat('Y-m-d H:i:s', $recharge->date_reg)
          ->addDays(1 + $periodicity->days)
          ->endOfDay();
      } else {
        $alta = Sale::getConnect('R')
          ->select('msisdn', 'date_reg', 'services_id')
          ->where([
            ['msisdn', $dn->msisdn],
            ['type', 'P'],
          ])
          ->whereIn('status', ['A', 'E'])
          ->orderBy('date_reg', 'DESC')->first();

        if (!empty($alta)) {
          $periodicity = Service::getPeriodicity($alta->services_id);
          $date_exp    = Carbon::createFromFormat('Y-m-d H:i:s', $alta->date_reg)
            ->addDays(1 + $periodicity->days)
            ->endOfDay();
        } else {
          //$date_base = $dn->date_reg;
          $date_exp = Carbon::createFromFormat('Y-m-d H:i:s', $dn->date_reg)
            ->addDays(1)
            ->endOfDay();
        }
      }

      $dn->date_expire = $date_exp->format('Y-m-d');
      $dn->save();

      //$this->output->writeln($dn->msisdn." - ".$dn->date_reg." - ".$date_exp->format('Y-m-d'));
    }

    $this->output->writeln('Finalizo date_expire');
    sleep(3);
    self::dateCDCalculate();
    exit;
  }

  // calcula fechas de churn y decay de todos los dn en funcion de la fecha de expiracion
  public function dateCDCalculate()
  {
    $this->output->writeln('Inicia date_cd');
    $dns = ClientNetwey::getConnect('W')->whereNotNull('date_expire')->whereNull('date_cd90')->where('date_reg', '<', date('Y-m-d 00:00:00'))->get();

    foreach ($dns as $key => $dn) {

      $date_cd30 = Carbon::createFromFormat('Y-m-d', $dn->date_expire)
        ->addDays(28)
        ->endOfDay();

      $date_cd90 = Carbon::createFromFormat('Y-m-d', $dn->date_expire)
        ->addDays(88)
        ->endOfDay();

      $dn->date_cd30 = $date_cd30->format('Y-m-d');
      $dn->date_cd90 = $date_cd90->format('Y-m-d');
      $dn->save();
    }

    $this->output->writeln('Finaliza date_cd');
    exit;
  }

  // calcula type dc90
  public function typeCDCalculate()
  {
    $this->output->writeln('Inicia ' . date('Y-m-d H:i:s'));
    $dns = ClientNetwey::getConnect('W')->whereNotNull('date_expire')->whereNull('type_cd90')->where('date_reg', '<', date('Y-m-d 00:00:00'))->get();

    foreach ($dns as $key => $dn) {

      $recharge = Sale::getLastRecharge(
        $dn->msisdn,
        $dn->date_expire . " 23:59:59"
      );

      if (empty($recharge)) {
        // decay
        $dn->type_cd90 = 'D';
      } else {
        //churn
        $dn->type_cd90 = 'C';
      }

      $dn->save();
    }

    $this->output->writeln('Finaliza ' . date('Y-m-d H:i:s'));
    exit;
  }

  //Carga codigos postales a 99min
  public function set99PCs()
  {
    $path = base_path('uploads') . '/pc99_50.csv';
    ini_set('auto_detect_line_endings', true);

    $this->output->writeln(
      'Inicia'
    );

    if (($gestor = fopen($path, "r")) !== false) {
      $count       = 1;
      $countInsert = 0;
      $date        = date('Y-m-d H:i:s');

      while (($datos = fgetcsv($gestor, 1000, ";")) !== false) {
        if ($count > 1) {
          $dat = explode(",", (String) $datos[0]);

          $cp = htmlentities((String) $dat[0]);
          $cp = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $cp));

          $state = htmlentities(str_replace('"', '', $dat[1]));
          $state = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $state));

          $muni = htmlentities(str_replace('"', '', $dat[2]));
          $muni = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $muni));

          if (strlen($cp) < 5) {
            $cp = '0' . $cp;
          }

          $pcs = DB::table('islim_pcs_99')->where([
            ['postal_code', $cp],
            ['status', 'A']])
            ->get();

          $insert = true;
          foreach ($pcs as $pc) {
            if ($pc->status == 'A') {
              if ($pc->warehouse != 35) {
                DB::table('islim_pcs_99')
                  ->where([
                    ['postal_code', $cp],
                    ['warehouse', $pc->warehouse]])
                  ->update(['status' => 'T']);
              } else {
                $insert = false;
              }
            }
          }

          if ($insert) {
            DB::table('islim_pcs_99')
              ->insert([
                'postal_code'  => $cp,
                'municipality' => $muni,
                'state'        => $state,
                'warehouse'    => 35,
                'update_date'  => $date,
                'date_reg'     => $date,
                'status'       => 'A']);

            $countInsert++;

            $this->output->writeln(
              'Agregados: ' . $countInsert
            );
          }
        }
        // $this->output->writeln($datos[0]);
        // $this->output->writeln("");
        $count++;
      }
    }
  }

  //Reenvia correo de facturacion masive
  public function masiveBillingSendMail($id_gen)
  {
    $this->output->writeln('log CRON: Inicia ' . date("Y-m-d H:i:s"));

    $billings = BillingMasive::getConnect('R')
      ->whereIn('status_gen', ['P'])
      ->where('id_gen', $id_gen)
      ->whereNotNull('mk_serie')
      ->whereNotNull('mk_folio')
      ->whereNotNull('billing_nro')
      ->whereNotNull('xml_id')
      ->get();

    if ($billings) {
      $mode     = env('APP_ENV') == 'production' ? '0' : '1';
      $folder   = env('APP_ENV') == 'production' ? 'masive/' : 'masive/test/';
      $dataMail = array();
      $date_gen = null;

      foreach ($billings as $key => $billing) {

        if (empty($date_gen)) {
          $date_gen = date("d-m-Y", strtotime($billing->date_gen));
        }

        $fileName     = $billing->billing_nro . '.pdf';
        $file_dir_pdf = $folder . $fileName;
        $file_pdf     = Storage::disk('s3-masive-billing')->url($file_dir_pdf);

        $fileName     = $billing->billing_nro . '.xml';
        $file_dir_xml = $folder . $fileName;
        $file_xml     = Storage::disk('s3-masive-billing')->url($file_dir_xml);

        $dataMailReng = array(
          'oxxo_folio_id'    => $billing->oxxo_folio_id,
          'oxxo_folio_nro'   => $billing->oxxo_folio_nro,
          'serie'            => $billing->mk_serie,
          'folio'            => $billing->mk_folio,
          'billing_nro'      => $billing->billing_nro,
          'date_gen'         => $billing->date_gen,
          'url_download_pdf' => $file_pdf,
          'url_download_xml' => $file_xml);

        array_push($dataMail, $dataMailReng);

      }

      $fileName    = 'facturacion_oxxo_csv' . '_' . $id_gen . '_' . $date_gen;
      $folderDir   = env('APP_ENV') == 'production' ? 'masive_billing/' : 'masive_billing/test/';
      $filePath    = '/reports/' . $folderDir . $fileName . '.csv';
      $fileContent = Storage::disk('s3')->get($filePath);

      $listDest = explode(',', env('BILLING_EMAILS', ''));

      if (count($listDest)) {
        try {
          Mail::to($listDest)->send(new BillingMasiveMail($dataMail, $fileContent, $fileName . '.csv'));
        } catch (\Exception $e) {
          Log::error('No se pudo enviar el correo de Facturacion Masiva. ' . $e->getMessage());
        }
      }
    }

    $this->output->writeln('log CRON: Termina ' . date("Y-m-d H:i:s"));
  }

  //corrige folios de oxxo_way
  public function oxxoFolioCorrector()
  {
    $this->output->writeln('log CRON: Inicia ' . date("Y-m-d H:i:s"));

    $oxxosales = DB::table('islim_oxxo_sales')
      ->select(
        'islim_oxxo_sales.id',
        'islim_oxxo_sales.token',
        'islim_oxxo_sales.client',
        'islim_oxxo_sales.tran_date',
        'islim_oxxo_sales.cash_machine',
        'islim_oxxo_sales.entry_mode',
        'islim_oxxo_sales.ticket',
        'islim_oxxo_sales.account',
        'islim_oxxo_sales.amount',
        'islim_oxxo_sales.admin_date',
        'islim_oxxo_sales.store',
        'islim_oxxo_sales.partial'
      )
      ->where([
        ['islim_oxxo_sales.folio', '=', '2147483647'],
        ['islim_oxxo_sales.admin_date', '>=', '2022-04-01']])
      ->limit(1000)
      ->get();

    foreach ($oxxosales as $key => $oxxosale) {
      $log = DB::table('islim_log_oxxo_way')
        ->select('islim_log_oxxo_way.data_in')
        ->where([
          ['islim_log_oxxo_way.msisdn', '=', $oxxosale->client],
          ['islim_log_oxxo_way.request', '=', 'service/OXXO_CoiF54eTrD/pay'],
          ['islim_log_oxxo_way.data_in', 'like', '%<token>' . $oxxosale->token . '</token>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<tranDate>' . str_replace(array("-", " ", ":"), '', $oxxosale->tran_date) . '</tranDate>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<cashMachine>' . $oxxosale->cash_machine . '</cashMachine>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<entryMode>' . $oxxosale->entry_mode . '</entryMode>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<ticket>' . $oxxosale->ticket . '</ticket>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<account>' . $oxxosale->account . '</account>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<amount>' . $oxxosale->amount . '</amount>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<adminDate>' . str_replace(array("-", " ", ":"), '', $oxxosale->admin_date) . '</adminDate>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<store>' . $oxxosale->store . '</store>%'],
          ['islim_log_oxxo_way.data_in', 'like', '%<partial>' . $oxxosale->partial . '</partial>%'],
        ])->first();

      if ($log) {
        $findme  = '<folio>';
        $findme2 = '</folio>';
        $pos     = strpos($log->data_in, $findme) + 7;
        $pos2    = strpos($log->data_in, $findme2);
        $folio   = substr($log->data_in, $pos, $pos2 - $pos);

        DB::table('islim_oxxo_sales')
          ->where('islim_oxxo_sales.id', '=', $oxxosale->id)
          ->update([
            'folio' => $folio]);

        $this->output->writeln("#" . ($key + 1) . " - id actualizado: " . $oxxosale->id . " con folio: " . $folio);
      }
    }
    $this->output->writeln('log CRON: Finaliza ' . date("Y-m-d H:i:s"));
  }

/**
 * [DeactiveMSISDN Mata ante altan los DN que tiene N meses sin recargar]
 * @param boolean $type_dn  [Tipo de DN: H,M,T]
 * @param integer $time_old [Tiempo en meses sin recargar]
 */
  public function DeactiveMSISDN($type_dn = false, $time_old = 8, $limite = false)
  {
    if (!$type_dn) {
      $this->info('Se deben especificar un tipo de producto a suspender ante altan');
      return 0;
    }
    $list = array('H', 'M', 'MH', 'T');
    if (in_array($type_dn, $list)) {

      $hoy      = date('Y-m-d H:i:s');
      $mod_date = strtotime($hoy . "- " . $time_old . " month");

      $dateOut = date('Y-m-d H:i:s', $mod_date);

      $dns = ClientNetwey::getConnect('R')
        ->select('islim_client_netweys.msisdn',
          'islim_client_netweys.date_cd90')
        ->whereNotNull('islim_client_netweys.date_expire')
        ->where([
          ['islim_client_netweys.status', '!=', 'T'],
          ['islim_client_netweys.dn_type', $type_dn],
          ['islim_client_netweys.date_expire', '<', $dateOut]])
        ->orderBy('islim_client_netweys.date_expire', 'ASC');

      if ($limite) {
        $dns = $dns->limit($limite);
      }
      $dns = $dns->get();

      if (count($dns) > 0) {
        $this->info('Hay ' . count($dns) . ' DNs de tipo ' . $type_dn . ' con mas de ' . $time_old . ' meses sin recargar');

        $startGolbal = microtime(true);

        $POS = 1;
        foreach ($dns as $itemDN) {
          $startTime = microtime(true);

          $this->info($POS . '- ' . $itemDN->msisdn);
          $POS++;
          $PAltan = true;
          //Bloqueo de envio de data a altan: True - permitido. False - bloqueado
          if ($PAltan) {
            $msisdn = trim($itemDN->msisdn);
            //Log::info('* ' . $msisdn);

            if (strlen($msisdn) == 10) {
              $resP = APIAltan::doRequest('deactivate', $msisdn);
              $res  = json_decode($resP);

              if (is_object($res)) {
                //Log::info((String) json_encode($res));

                if (isset($res->orderId) && isset($res->effectiveDate)) {
                  $ordeAltan = $res->orderId;
                  $oldDate   = strtotime($res->effectiveDate);
                  $dateAltan = date('Y-m-d H:i:s', $oldDate);
                } else {
                  $ordeAltan = null;
                  $dateAltan = null;
                }

                if ($res->status === "success" ||
                  (!empty($res->message) && strripos(strtolower($res->message), 'subscriber does not exist'))) {

                  DeactiveMasivesDetails::insert([
                    'date_reg'       => date('Y-m-d H:i:s'),
                    'msisdn'         => $msisdn,
                    'status_line'    => 'deactive',
                    'response_altan' => (String) json_encode($res),
                    'altan_order'    => $ordeAltan,
                    'obs_status'     => 'Ejecucion exitosa',
                    'prc_status'     => 'P',
                    'prc_date'       => $dateAltan]);

                  ClientNetwey::getConnect('W')
                    ->where('msisdn', $msisdn)
                    ->update(['status' => 'T']);
                  $this->info('DN ' . $msisdn . ' procesado!');

                  //exit();
                } else {
                  $this->info('DN ' . $msisdn . ' presenta error en altan.');

                  DeactiveMasivesDetails::insert([
                    'date_reg'       => date('Y-m-d H:i:s'),
                    'msisdn'         => $msisdn,
                    'status_line'    => 'deactive',
                    'response_altan' => (String) json_encode($res),
                    'altan_order'    => $ordeAltan,
                    'obs_status'     => 'Altan Retorno Error',
                    'prc_status'     => 'R',
                    'prc_date'       => $dateAltan]);
                }
                $endTime = round((microtime(true) - $startTime), 2);

                $this->info('Tiempo: ' . $endTime . ' seg');
                $this->info('------- ');
              }
            } else {
              $this->info('El DN ' . $msisdn . ' no se puede procesar, no tiene el tamano correcto');
            }
          } else {
            $this->info('Proceso de noticacion a altan esta des-habilitado');
          }
        }
        $endTimeGlobal = round((microtime(true) - $startGolbal), 2);

        $this->info('Tiempo total: ' . $endTimeGlobal . ' seg con ' . count($dns) . ' Dns de tipo ' . $type_dn);
        $this->info('Finalizado el cron!');
      } else {
        $this->info('No hay DNs de tipo ' . $type_dn . ' con mas de ' . $time_old . ' meses sin recargar');
      }
    } else {
      $this->info('Se deben especificar un tipo de producto valido para notificar ante altan');
      return 0;
    }
  }


/**
 * [UpdateCoordProfile actualiza coordenadas de un DN en funcion de las coordenadas que tiene en el profile de altan]
 */
  public function UpdateCoordProfile()
  {

    $this->output->writeln('Inicia');

    $path = base_path('uploads') . '/coordenadas.csv';
    ini_set('auto_detect_line_endings', true);

    if (($gestor = fopen($path, "r")) !== false) {
      $count = 0;

      while (($datos = fgetcsv($gestor, 1000, ";")) !== false) {
        if ($count >= 0) {
          $dat = explode(",", (String) $datos[0]);

          $msisdn = htmlentities((String) $dat[0]);
          $msisdn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $msisdn));

          $lat_csv = htmlentities(str_replace('"', '', $dat[1]));
          $lat_csv = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $lat_csv));

          $lng_csv = htmlentities(str_replace('"', '', $dat[2]));
          $lng_csv = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $lng_csv));


          $prof = APIAltan::doRequest('profile', $msisdn);
          $prof = json_decode($prof);


          if($prof->status == 'success'){
            if(!empty($prof->msisdn)){
              $prof_dn=$prof->msisdn;
              if($prof_dn->status == "active"){

                //$this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> '.$prof_dn->coordinates);

                $prof_coor = explode(',', $prof_dn->coordinates);
                if(!empty($prof_coor[0]) && !empty($prof_coor[1])){
                  $prof_lat = $prof_coor[0];
                  $prof_lng = $prof_coor[1];

                  if($prof_lat==$lat_csv && $prof_lng==$lng_csv){

                    $client = ClientNetwey::getConnect('W')
                      ->where('msisdn', $msisdn)
                      ->first();

                    if($client){
                      if($client->lat != $prof_lat || $client->lng != $prof_lng){

                        $lat_ant=$client->lat;
                        $lng_ant=$client->lng;
                        if(!empty($client->n_update_coord)){
                          $cant_ant = $client->n_update_coord;
                          $client->n_update_coord = $client->n_update_coord+1;
                        }
                        else{
                          $cant_ant = 0;
                          $client->n_update_coord = 1;
                        }

                        $client->lat = $prof_lat;
                        $client->lng = $prof_lng;
                        $client->point = DB::raw("(GeomFromText('POINT(" . $prof_lat . " " . $prof_lng . ")'))");

                        $client->save();


                        $recordChange             = new CoordinateChanges;
                        $recordChange->user_email = "admin@admin.com";
                        $recordChange->dn         = $msisdn;
                        $recordChange->old_lat    = $lat_ant;
                        $recordChange->old_lng    = $lng_ant;
                        $recordChange->old_point  = DB::raw("(GeomFromText('POINT(" . $lat_ant . " " . $lng_ant . ")'))");

                        $recordChange->new_lat    = $prof_lat;
                        $recordChange->new_lng    = $prof_lng;
                        $recordChange->new_point  = DB::raw("(GeomFromText('POINT(" . $prof_lat . " " . $prof_lng . ")'))");
                        $recordChange->sale_id    = '-1';
                        $recordChange->date_reg   = date('Y-m-d H:i:s');
                        $recordChange->save();



                        $this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> lat_ant: '.$lat_ant.' lng_ant: '.$lng_ant.' | lat_new: '.$client->lat.' lng_new: '.$client->lng.' | cant: '.$client->n_update_coord);

                      }
                      else{
                        //$this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> ya estaba actualizado en coordenadas');
                      }
                    }
                    else{
                      //$this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> no existe');
                    }
                  }
                  else{
                    //$this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> se le debe hacer el cambio de coordenadas');
                     $this->output->writeln($msisdn.' '.$lat_csv.' '.$lng_csv.'-> se le debe hacer el cambio de coordenadas');
                  }
                }
                else{
                  //$this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> sin coordenadas en profile');
                }
              }
              else{
                $this->output->writeln($msisdn.' '.$lat_csv.' '.$lng_csv.' -> '.$prof_dn->status);
                //$this->output->writeln('#'.($count+1).' msisdn: '.$msisdn.' -> '.$prof_dn->status);
              }
            }

          }
        }
        $count++;
      }
    }







    $this->output->writeln('Culmina');
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    //self::dateExpiredCalculate();
    //self::dateCDCalculate();
    //self::typeCDCalculate();
    //self::set99PCs();
    //self::masiveBillingSendMail('1657836005');
    //self::oxxoFolioCorrector();
    //self::DeactiveMSISDN('M', 8);
    self::UpdateCoordProfile();

    ///////////////////////////////////////////////

    // //Corrige asignaciones erroneas de inventario
    // $pendings = StockProvaDetail::getConnect('R')
    //             ->select('id', 'status', 'users', 'msisdn')
    //             ->where('status', 'P')
    //             ->get();

    // $dataRemove []= [
    //     'msisdn',
    //     'usuario'
    // ];

    // $count = 0;

    // foreach($pendings as $pending){
    //     $inv = Inventory::getConnect('R')
    //             ->select('id')
    //             ->where([['status', 'A'], ['msisdn', $pending->msisdn]])
    //             ->first();

    //     if(!empty($inv)){
    //         $assig = SellerInventory::getConnect('R')
    //                  ->select('inv_arti_details_id', 'users_email')
    //                  ->where([
    //                      ['status', 'A'],
    //                      ['inv_arti_details_id', $inv->id],
    //                      ['users_email', $pending->users]
    //                  ])
    //                  ->first();

    //         if(!empty($assig)){
    //             $dataRemove []= [
    //                 $pending->msisdn,
    //                 $pending->users
    //             ];

    //             //Eliminando registros
    //             SellerInventory::getConnect('W')
    //                 ->where([
    //                     ['inv_arti_details_id', $inv->id]
    //                 ])
    //                 ->delete();

    //             SellerInventoryTrack::getConnect('W')
    //                 ->where([
    //                     ['inv_arti_details_id', $inv->id]
    //                 ])
    //                 ->delete();

    //             Inventory::getConnect('W')
    //                 ->where('id', $inv->id)
    //                 ->delete();

    //             $this->output->writeln('Eliminado: '.$pending->msisdn.' al usuario: '.$pending->users);
    //             $count ++;
    //         }
    //     }

    //     if($count == 2) break;
    // }
    // //$url = CommonHelpers::saveFile('/public/reports/msisdn-inv-ret', 'msisdn-inv-ret', $dataRemove, 'msisdn-inv-ret_'.time());

    // /*$this->output->writeln(
    // 'URL para descargar el csv: '.(String)$url
    // );*/
    // $this->output->writeln('Finalizo'); exit;

    ////////////////////////////////////////////////////////////////////////

    //Corrige asignaciones duplicadas con status P
    /*$path = base_path('uploads').'/duplicados.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $this->output->writeln('archivo válido');

    while(($datos = fgetcsv($gestor, 1000, ",")) !== FALSE){
    $this->output->writeln('analizando a: '.$datos[1]);
    $sale = Sale::getConnect('R')
    ->select('inv_arti_details_id', 'users_email')
    ->where([
    ['type', 'P'],
    ['inv_arti_details_id', $datos[1]]
    ])
    ->first();

    if(!empty($sale)){
    $assigs = SellerInventory::getConnect('R')
    ->select('inv_arti_details_id', 'users_email')
    ->where([
    ['inv_arti_details_id', $sale->inv_arti_details_id],
    ['status', 'P']
    ])
    ->get();

    foreach($assigs as $assig){
    if($assig->users_email != $sale->users_email){
    $this->output->writeln('Actualizando a : '.$sale->inv_arti_details_id.' - '.$assig->users_email);

    SellerInventory::getConnect('W')
    ->where([
    ['inv_arti_details_id', $sale->inv_arti_details_id],
    ['users_email', $assig->users_email]
    ])
    ->update(['status' => 'T']);
    }
    }
    }
    }
    $this->output->writeln('Finalizo');
    }exit;*/

    //script para revertir retiro de inventario por estatus rojo
    /*$path = base_path('uploads').'/revertir_retiro.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $count = 0;
    $date = date('Y-m-d H:i:s');

    while(($datos = fgetcsv($gestor, 1000, ",")) !== FALSE){
    if($count >= 1){
    SellerInventory::where([
    ['inv_arti_details_id', $datos[0]],
    ['status', 'A']
    ])
    ->update([
    'status' => 'T',
    'date_red' => null
    ]);

    SellerInventory::where([
    ['users_email', $datos[1]],
    ['inv_arti_details_id', $datos[0]]
    ])
    ->update([
    'status' => 'A',
    'date_red' => null,
    'last_assigned_by' => 'admin@admin.com',
    'last_assignment' => $date,
    'obs' => 'Asignado por reverso de retiros de alerta roja'
    ]);

    SellerInventoryTrack::setInventoryTrack(
    $datos[0],
    $datos[2],
    null,
    $datos[1],
    null,
    'admin@admin.com',
    'Asignado por reverso de retiros de alerta roja'
    );

    $this->output->writeln('Linea editada: '.$count);
    }

    $count++;
    }
    }

    exit;*/

    //Activando DNs
    /*$path = base_path('uploads').'/active_dns.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $dnsErr []= ['msisdn', 'error'];
    $count = 1;

    while(($datos = fgetcsv($gestor, 1000, ",")) !== FALSE){
    $dn = htmlentities($datos[0]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));
    $dn = substr($dn, 2, strlen($dn));

    if(!empty($dn)){
    $this->output->writeln('Intentando activar a ('.$count.'): '.$dn);

    $res = APIAltan::doRequest('activate', $dn);
    $res = json_decode($res);

    if(is_object($res)){
    if($res->status == 'success'){
    ClientNetwey::getConnect('W')
    ->where('msisdn', $dn)
    ->update(['status' => 'A']);

    Suspend::where([
    ['msisdn', $dn],
    ['status', '!=', 'T']
    ])
    ->update(['status' => 'T']);

    $this->output->writeln('Activando a: '.$dn);
    }else{
    $dnsErr []= [$dn, (String) json_encode($res)];
    }
    }else{
    $dnsErr []= [$dn, 'API no respondio'];
    }
    }
    $count++;
    sleep(1);
    }

    $url = CommonHelpers::saveFile('/public/reports/msisdn-found', 'active-err', $dnsErr, 'msisdn-active-err_'.time());

    $this->output->writeln(
    'URL para descargar el csv: '.(String)$url
    );
    }*/

    //Asignando fecha de expiración
    /*$dns = Sale::select(
    'islim_sales.msisdn',
    'islim_sales.services_id',
    'islim_sales.date_reg',
    DB::raw('(select sa.date_reg from islim_sales as sa where sa.msisdn = islim_sales.msisdn order by sa.id DESC limit 1) as last_service_date'),
    DB::raw('(select sa.services_id from islim_sales as sa where sa.msisdn = islim_sales.msisdn order by sa.id DESC limit 1) as last_service')
    )
    ->join(
    'islim_client_netweys',
    'islim_client_netweys.msisdn',
    'islim_sales.msisdn'
    )
    ->where('islim_sales.type', 'P')
    ->whereIn('islim_sales.status', ['A', 'E'])
    ->where(function($q){
    $q->whereNull('islim_client_netweys.date_expire')
    ->orWhere('islim_client_netweys.date_expire', '<', '2020-01-01');
    })
    ->orderBy('islim_sales.date_reg', 'DESC')
    ->get();

    foreach($dns as $dn){
    $period = Service::select('islim_periodicities.days')
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    ->where('islim_services.id', $dn->last_service)
    ->first();

    if(!empty($period)){
    $dateExp = Carbon::createFromFormat('Y-m-d H:i:s', $dn->last_service_date)->startOfDay();
    $dateExp = $dateExp->addDays((int)$period->days + 1)->format('Y-m-d');

    ClientNetwey::getConnect('W')->where('msisdn', $dn->msisdn)->update(['date_expire' => $dateExp]);
    }

    $this->output->writeln('Actualizado: '.$dn->msisdn, false);
    }*/

    //Verificando si los dns ya se encuentran en bd
    /*$path = base_path('uploads').'/tripletas_3.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $statusArr = [
    'A' => 'Activo',
    'I' => 'Inactivo',
    'S' => 'Suspendido'
    ];

    $line = 0;
    $cg = 0;
    $ng = 500;
    $group = [];

    $xls []= [
    'msisdn',
    'Invetario',
    'Alta',
    'Estatus',
    'Fecha Alta',
    'Tipo de alta',
    'Fecha iluminación'
    ];

    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    $line++;

    if($line > 1){
    $dn = htmlentities($datos[1]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $group []= $dn;

    if(count($group) == $ng){
    $cg++;
    $data = Inventory::select(
    'islim_inv_arti_details.msisdn',
    'islim_client_netweys.dn_type',
    'islim_client_netweys.status',
    'islim_client_netweys.date_reg',
    'islim_inv_arti_details.date_reg as ilu'
    )
    ->leftjoin(
    'islim_client_netweys',
    'islim_client_netweys.msisdn',
    'islim_inv_arti_details.msisdn'
    )
    ->whereIn('islim_inv_arti_details.msisdn', $group)
    ->get();

    if($data->count()){
    foreach($data as $d){
    $xls []= [
    $d->msisdn,
    'SI',
    !empty($d->dn_type) ? 'SI' : 'NO',
    !empty($d->status) ? $statusArr[$d->status] : 'N/A',
    !empty($d->date_reg) ? date('d-m-Y', strtotime($d->date_reg)) : 'N/A',
    !empty($d->dn_type) ? $d->dn_type : 'N/A',
    !empty($d->ilu) ? date('d-m-Y', strtotime($d->ilu)) : 'N/A'
    ];

    $this->output->writeln('DN encontrado: '.$d->msisdn, false);
    }
    }

    $group = [];
    $this->output->writeln('grupo: '.$cg, false);
    }
    }
    }

    if(count($group)){
    $data = Inventory::select(
    'islim_inv_arti_details.msisdn',
    'islim_client_netweys.dn_type',
    'islim_client_netweys.status',
    'islim_client_netweys.date_reg'
    )
    ->leftjoin(
    'islim_client_netweys',
    'islim_client_netweys.msisdn',
    'islim_inv_arti_details.msisdn'
    )
    ->whereIn('islim_inv_arti_details.msisdn', $group)
    ->get();

    if($data->count()){
    foreach($data as $d){
    $xls []= [
    $d->msisdn,
    'SI',
    !empty($d->dn_type) ? 'SI' : 'NO',
    !empty($d->status) ? $statusArr[$d->status] : 'N/A',
    !empty($d->date_reg) ? date('d-m-Y', strtotime($d->date_reg)) : 'N/A',
    !empty($d->dn_type) ? $d->dn_type : 'N/A',
    !empty($d->ilu) ? date('d-m-Y', strtotime($d->ilu)) : 'N/A'
    ];

    $this->output->writeln('DN encontrado: '.$d->msisdn, false);
    }
    }
    }

    $this->output->writeln(
    'DNs encontrados: '.count($xls)
    );

    $this->output->writeln(
    'DNs analizados: '.$line
    );

    if(count($xls)){
    $url = CommonHelpers::saveFile('/public/reports/msisdn-found', 'msisdn-found', $xls, 'msisdn-found_'.time());

    $this->output->writeln(
    'URL para descargar el csv: '.(String)$url
    );
    }

    }*/

    //Consultando dirección y estado de altas masivas
    /*$ups = DB::table('islim_altas_speed')
    ->where([
    ['type_serv', 'HBB'],
    ['status', 'P'],
    ['date_reg', '>=', '2021-12-27 00:00:00'],
    ['date_reg', '<=', '2022-01-09 23:59:59']
    ])
    ->get();*/

    /*$ups = DB::table('islim_sales')
    ->where([
    ['sale_type', 'H'],
    ['type', 'P'],
    ['status', '!=', 'T'],
    ['date_reg', '>=', '2021-06-01 00:00:00'],
    ['date_reg', '<=', '2021-06-31 23:59:59']
    ])
    ->get();*/

    /*$xls []= [
    'msisdn',
    'Dirección',
    'Código postal',
    'Estado',
    'Fecha'
    ];

    $count = 1;

    foreach($ups as $up){
    if(!empty($up->msisdn) && !empty($up->lat) && !empty($up->lng)){
    $dataAddr = CommonHelpers::executeCurl(
    'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$up->lat.','.$up->lng.'&key='.env('GOOGLE_KEY'),
    'GET'
    );

    $data = [
    $up->msisdn,
    'N/F',
    'N/F',
    'N/F',
    $up->data_eject //$up->date_reg
    ];

    if($dataAddr['success'] && !empty($dataAddr['data']->results) && count($dataAddr['data']->results)){
    if(!empty($dataAddr['data']->results[0]->address_components) && count($dataAddr['data']->results[0]->address_components)){
    foreach($dataAddr['data']->results[0]->address_components as $component){
    if(!empty($component->types) && count($component->types)){
    if(in_array('administrative_area_level_1', $component->types) && in_array('political', $component->types)){
    $data[3] = $component->long_name;
    }

    if(in_array('postal_code', $component->types)){
    $data[2] = $component->long_name;
    }
    }
    }

    if(!empty($dataAddr['data']->results[0]->formatted_address)){
    $data[1] = $dataAddr['data']->results[0]->formatted_address;
    }
    }
    }

    $xls []= $data;
    }

    $this->output->writeln(
    'dn procesado: '.$up->msisdn.' ('.$data[2].'-'.$data[3].') '.' total: '.count($ups).' dn número: '.$count
    );

    $count++;
    }

    $url = CommonHelpers::saveFile('/public/reports/address-masive', 'activaciones_dirección', $xls, 'activaciones_dirección_'.time());

    $this->output->writeln(
    'URL para descargar el csv: '.(String)$url
    );*/

    /*$path = base_path('uploads').'/coord_address.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $xls []= [
    'Cliente',
    'Vendedor',
    'Telf Netwey',
    'Telf contacto',
    'Telf contacto 2',
    'latitud',
    'longitud',
    'dirección'
    ];

    $count = 1;

    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    if($count > 1){
    $client = htmlentities($datos[0]);
    $client = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $client));

    $seller = htmlentities($datos[1]);
    $seller = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $seller));

    $dn = htmlentities($datos[2]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $t1 = htmlentities($datos[3]);
    $t1 = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $t1));

    $t2 = htmlentities($datos[4]);
    $t2 = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $t2));

    $lat = htmlentities($datos[5]);
    $lat = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $lat));
    $lat = str_replace(',', '.', $lat);

    $lon = htmlentities($datos[6]);
    $lon = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $lon));
    $lon = str_replace(',', '.', $lon);

    $dataAddr = CommonHelpers::executeCurl(
    'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$lat.','.$lon.'&key='.env('GOOGLE_KEY'),
    'GET'
    );

    $data = [
    $client,
    $seller,
    $dn,
    $t1,
    $t2,
    $lat,
    $lon,
    'N/F'
    ];

    if($dataAddr['success'] && !empty($dataAddr['data']->results) && count($dataAddr['data']->results)){
    if(!empty($dataAddr['data']->results[0]->address_components) && count($dataAddr['data']->results[0]->address_components)){
    if(!empty($dataAddr['data']->results[0]->formatted_address)){
    $data[7] = $dataAddr['data']->results[0]->formatted_address;
    }
    }
    }

    $xls []= $data;

    $this->output->writeln(
    'Procesdos: '.$count
    );
    }

    $count++;
    }
    }

    $url = CommonHelpers::saveFile('/public/reports/address-masive', 'activaciones_dirección', $xls, 'activaciones_dirección_'.time());

    $this->output->writeln(
    'URL para descargar el csv: '.(String)$url
    );*/

    ///////////////////////////////
    ///START Cargar en BD los datos de los responsables de Division, Region y coordiancion.
    /*$path = 'public/reports/data_clean_organizacion.json';
    $data = file_get_contents($path);
    $items = json_decode($data, true);

    foreach ($items as $item) {
    //Log::info('item ');
    //Log::info($item['division']);

    $division_id =  DB::table('islim_esquema_comercial')
    ->select('id', 'name')
    ->where([['name', $item['division']], ['type', 'D']])
    ->first();

    if(!empty($division_id)){
    // Log::info('Division ');
    // Log::info($division_id->name .' - '.$division_id->id);

    DB::table('islim_users')
    ->where('email', $item['divisional'])
    ->update(['esquema_comercial_id' => $division_id->id]);

    foreach($item['regiones'] as $itemR){
    $region_id = DB::table('islim_esquema_comercial')
    ->select('id', 'name')
    ->where([['division', $division_id->id],
    ['name', $itemR['region']],
    ['type', 'R']])
    ->first();

    if(!empty($region_id)){
    //Log::info('Region OK -------------------');
    //Log::info($region_id->name.' - '.$region_id->id);

    DB::table('islim_users')
    ->where('email', $itemR['regional'])
    ->update(['esquema_comercial_id' => $region_id->id]);
    foreach($itemR['cordinacion'] as $itemC){
    $cordinacion_id = DB::table('islim_esquema_comercial')
    ->select('id', 'name')
    ->where([['region', $region_id->id],
    ['name', $itemC['cordinacion']],
    ['type', 'C']])
    ->first();

    if(!empty($cordinacion_id)){
    //Log::info('CORDINACION OK -------------------');
    //Log::info($cordinacion_id->name.' - '.$cordinacion_id->id);

    foreach($itemC['coordinador'] as $itemCuser){

    DB::table('islim_users')
    ->where('email', $itemCuser)
    ->update(['esquema_comercial_id' => $cordinacion_id->id]);
    }
    }else{
    //Log::info('COORDINACION ');
    // Log::info($region_id->id.'--'.$itemC['cordinacion']);
    }
    }
    }else{
    // Log::info('REGION ');
    // Log::info($division_id->id.'--'.$itemR['region']);
    }
    }
    }else{
    // Log::info('DIVISION ');
    // Log::info($item['division']);
    }
    }*/

    /// END Cargar en BD los datos de los responsables de Division, Region y coordiancion.
    ////////////////////////////////

    //Carga códigos de depósito banco azteca
    /*$path = base_path('uploads').'/cod_users.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $line = 0;

    $relbank = [
    1 => 3,
    2 => 4
    ];

    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    $line++;

    $cod = htmlentities($datos[0]);
    $cod = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $cod));

    $user = htmlentities($datos[1]);
    $user = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $user));

    $account = 1;

    if(!empty($user) && !empty($cod) && !empty($account) && ($account == 1 || $account == 2)){
    $usr = User::getUserByEmail($user);

    if(!empty($usr)){
    $bcod = UserDeposit::checkCodeAndBank($cod, $relbank[$account]);

    if(!$bcod){
    UserDeposit::createCode($user, $cod, $relbank[$account]);
    }else{
    $this->output->writeln('Código ya registrado: '.$user, false);
    }
    }else{
    $this->output->writeln('Usuario no registrado: '.$user, false);
    }
    }else{
    $this->output->writeln('Linea no válida: '.$line, false);
    }
    }
    }*/

    //Asignar servicios de ragalo a dns
    /*$path = base_path('uploads').'/dns-gift.csv';
    ini_set('auto_detect_line_endings', TRUE);

    $prom = ServicesProm::getPromByID(3);

    if(($gestor = fopen($path, "r")) !== FALSE){
    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    $dn = htmlentities($datos[0]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $dninf = Sale::existDN($dn);

    if(!empty($dninf)){
    $dateAct = Carbon::createFromFormat('Y-m-d H:i:s', $dninf->date_reg)
    ->startOfDay();

    if (!empty($prom->max_time)) {
    $expired = Carbon::createFromFormat('Y-m-d H:i:s', $dninf->date_reg)
    ->endOfDay()
    ->addMonths($prom->max_time);
    }

    for ($i = 0; $i < $prom->qty; $i++) {
    $dateAct->addDays($prom->period_days);

    $this->output->writeln('agregando a: '.$dn, false);

    Promontion::getConnect('W')
    ->insert([
    'msisdn' => $dn,
    'service_id' => $prom->service_id,
    'activation_date' => $dateAct->format('Y-m-d H:i:s'),
    'expired_date' => !empty($expired) ? $expired : null,
    'date_reg' => $dninf->date_reg,
    'status' => 'A',
    ]);
    }
    }
    }
    }*/

    //Consulta de status en bd
    //Consultando saldo de dns
    /*$path = base_path('uploads') . '/Churn_diciembre20.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if (($gestor = fopen($path, "r")) !== FALSE) {
    $status = [
    'A' => 'Activo',
    'S' => 'Suspendido',
    'T' => 'Eliminado',
    'I' => 'Inactivo'
    ];

    $xls[] = [
    'MSISDN',
    'Estatus'
    ];

    while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
    $dn = htmlentities($datos[0]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $data = ClientNetwey::getConnect('R')
    ->select('status')
    ->where('msisdn', $dn)
    ->first();

    if (!empty($data)) {
    $xls[] = [
    $dn,
    $status[$data->status]
    ];
    } else {
    $xls[] = [
    $dn,
    'No esta en bd'
    ];
    }

    $this->output->writeln('dn procesado: ' . $dn, false);
    }

    $url = CommonHelpers::saveFile('/public/reports', 'consumos', $xls, 'dns_status' . time());
    $this->output->writeln('URL: ' . (string)$url, false);
    }

    exit();*/

    //Consultando saldo de dns
    /*$path = base_path('uploads').'/Churn_noviembre20.csv';
    ini_set('auto_detect_line_endings', TRUE);

    if(($gestor = fopen($path, "r")) !== FALSE){
    $xls []= [
    'MSISDN',
    'Estatus',
    //'GB',
    'Error'
    ];

    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    $dn = htmlentities($datos[0]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $pro = APIAltan::doRequest('profile', $dn);
    $pro = json_decode($pro);

    if($pro->status == 'success'){
    $gb = '0.00';
    if(!empty($pro->msisdn->{'remaining-mb'})){
    $gb = round(($pro->msisdn->{'remaining-mb'}/1024),4);
    }

    $xls []= [
    $dn,
    $pro->msisdn->status,
    $gb,
    'N/A'
    ];
    }else{
    $xls []= [
    $dn,
    'S/I',
    'S/I',
    $pro->description_altan
    ];
    }

    $this->output->writeln('dn procesado: '.$dn, false);
    }

    $url = CommonHelpers::saveFile('/public/reports', 'consumos', $xls, 'dns_gb'.time());

    echo (String)$url;
    }

    exit();*/

    //Colocando políticas a usuarios
    /*$users = User::select('islim_users.email')
    ->join(
    'islim_profile_details',
    'islim_profile_details.user_email',
    'islim_users.email'
    )
    ->where('islim_users.status', 'A')
    ->whereIn('islim_profile_details.id_profile',[11,19])//11,19,18,10,17
    ->whereIn('islim_users.platform',['vendor'])//'admin','coordinador', 'vendor'
    ->get();

    foreach($users as $user){
    $c = UserRole::where([
    ['user_email', $user->email],
    ['policies_id', 254]
    ])->first();

    if(!empty($c)){
    UserRole::where([
    ['user_email', $user->email],
    ['policies_id', 254]
    ])
    ->update(['value' => 1, 'status' => 'A']);
    }else{
    UserRole::insert([
    'user_email' => $user->email,
    'policies_id' => 254,
    'roles_id' => 17,
    'value' => 1,
    'date_reg' => date('Y-m-d H:i:s'),
    'status' => 'A'
    ]);
    }
    }*/

    /*$users = User::select('email')
    ->where([['platform', 'coordinador'], ['status', 'A']])
    ->get();

    foreach($users as $user){
    $c = UserRole::where([
    ['user_email', $user->email],
    ['policies_id', 57]
    ])->first();

    $ca = 4;

    if($c->value <= 10){
    $ca = $c->value;
    }

    UserRole::where([
    ['user_email', $user->email],
    ['policies_id', 57]
    ])
    ->update(['value' => $ca]);

    $c2 = UserRole::where([
    ['user_email', $user->email],
    ['policies_id', 189]
    ])->first();

    if(!empty($c2)){
    UserRole::where([
    ['user_email', $user->email],
    ['policies_id', 189]
    ])
    ->update(['value' => 100]);
    }else{
    UserRole::insert([
    'user_email' => $user->email,
    'policies_id' => 189,
    'roles_id' => 10,
    'value' => 100,
    'date_reg' => date('Y-m-d H:i:s'),
    'status' => 'A'
    ]);
    }
    }*/

    /*$users = User::select('islim_user_roles_copy.*')
    ->join(
    'islim_user_roles_copy',
    'islim_user_roles_copy.user_email',
    'islim_users.email'
    )
    ->where([
    ['islim_users.status', 'A'],
    ['islim_users.platform', 'coordinador'],
    ['islim_user_roles_copy.policies_id', 57]
    ])
    ->get();

    foreach($users as $user){
    //$this->info($user);
    UserRole::where([
    ['user_email', $user->user_email],
    ['policies_id', 57]
    ])
    ->update(['value' => $user->value]);
    }

    exit();*/

    /*
    //$path = base_path('uploads').'/churn.csv';

    //ini_set('auto_detect_line_endings', TRUE);
    $i = 0;
    //if(($gestor = fopen($path, "r")) !== FALSE){
    $xls []= [
    'msisdn',
    'Fecha de compra',
    'tipo',
    'Ultimo consumo registrado',
    'Servicio',
    'Dias de consumo',
    'GB'
    ];

    //while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    //if($i > 0){
    //$dn = htmlentities($datos[0]);
    //$dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));
    $dn = "2020-02-20";
    $date_ini="2020-02-20 00:00:00";
    $date_end = "2020-02-28 59:59:59"; //date('Y-m-d H:i:s'); //
    $data = Sale::select(
    'islim_sales.date_reg',
    'islim_sales.msisdn',
    'islim_sales.type',
    'islim_services.title',
    'islim_sales.codeAltan',
    'islim_periodicities.days'
    )
    ->join(
    'islim_services',
    'islim_services.id',
    'islim_sales.services_id'
    )
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    //->where('islim_sales.msisdn', $dn)
    //->where(DB::raw("DATE_FORMAT(islim_sales.date_reg,'%Y-%m-%d')"),'>=', $dn)
    ->whereBetween('islim_sales.date_reg',[$date_ini, $date_end])
    //->where('islim_sales.status', 'A')
    ->where(function($query){
    $query->where('islim_sales.type', 'P')
    ->orWhere('islim_sales.type', 'R');
    })
    ->orderBy('islim_sales.date_reg', 'DESC')
    ->get();
    if(count($data)){
    foreach ($data as $rech){
    $exp = date('Ymd', strtotime('+ '.($rech->days + 1).' days', strtotime($rech->date_reg)));

    $buy = date('Y-m-d', strtotime($rech->date_reg));
    if ($rech->type == 'P') {
    $camp_cons = 'islim_consumptions.offer_prim';
    }else{
    $camp_cons = 'islim_consumptions.offer_sup';
    }
    $con = DB::table('islim_consumptions')
    ->select(
    'date_affec',
    DB::raw("(SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR(name_file,26))-6)) as date_file")
    )
    ->join(
    'islim_download_files',
    'islim_download_files.id',
    'islim_consumptions.file_id'
    )
    ->where([
    ['msisdn', $rech->msisdn],
    [$camp_cons, $rech->codeAltan],
    [DB::raw("SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR('name_file',26))-6)"), '<=', $exp],
    [DB::raw("DATE_FORMAT(date_affec,'%Y-%m-%d')"), $buy]
    ])
    ->orderBy('date_affec', 'DESC')
    ->first();

    $conT = DB::table('islim_consumptions')
    ->select('consumption')
    ->join(
    'islim_download_files',
    'islim_download_files.id',
    'islim_consumptions.file_id'
    )
    ->where([
    ['msisdn', $rech->msisdn],
    [$camp_cons, $rech->codeAltan],
    [DB::raw("SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR('name_file',26))-6)"), '<=', $exp],
    [DB::raw("DATE_FORMAT(date_affec,'%Y-%m-%d')"), $buy]
    ])
    ->sum('consumption');

    if(!empty($con)){
    $date1 = new DateTime($rech->date_reg);
    $date2 = new DateTime($con->date_file.'235959');
    $diff = $date1->diff($date2);

    $xls []= [
    $rech->msisdn,
    $rech->date_reg,
    $rech->type == 'P' ? 'Alta' : 'Plan',
    $date2->format('Y-m-d'),
    $rech->title,
    (String)$diff->days,
    round(((($conT/1024)/1024)/1024),2)
    ];
    }else{
    $xls []= [
    $rech->msisdn,
    $rech->date_reg,
    $rech->type == 'P' ? 'Alta' : 'Plan',
    'S/I',
    $rech->title,
    'S/I',
    '0'
    ];
    }
    }
    }
    //}

    $i++;
    //print_r($xls);
    //if($i == 10){
    //    break;
    //}

    //}
    //exit();

    //$url = CommonHelpers::saveFile('/public/reports', 'bi', $xls, 'churn_'.time());
    $url = CommonHelpers::saveFile('/public/reports', 'consumos', $xls, 'consumo_'.time());

    echo (String)$url;
     */

    /*$clients = ClientNetwey::select('msisdn', 'tag')
    ->where(function($query){
    $query->where('tag', 'D90')
    ->orWhere('tag', 'C90');
    })
    ->where(function($query){
    $query->where('status', 'A')
    ->orWhere('status', 'S');
    })
    ->get();

    $i = 0;
    foreach ($clients as $client){
    if($client->tag == 'D90'){
    $lastRe = Sale::select(
    'islim_sales.date_reg',
    'islim_periodicities.days'
    )
    ->join(
    'islim_services',
    'islim_services.id',
    'islim_sales.services_id'
    )
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    ->where([
    ['islim_sales.type', 'P'],
    ['islim_sales.msisdn', $client->msisdn]
    ])
    ->whereIn('islim_sales.status', ['A','E'])
    ->first();

    if(!empty($lastRe)){
    $dateD = strtotime('+ 3 months', strtotime($lastRe->date_reg));
    $dateD = date('Y-m-d H:i:s', strtotime('+ '.$lastRe->days.' days', $dateD));

    HistoryDC::createRecord(
    $client->msisdn,
    'D90',
    $dateD
    );
    }
    }

    if($client->tag == 'C90'){
    $lastRe = Sale::select(
    'islim_sales.date_reg',
    'islim_periodicities.days'
    )
    ->join(
    'islim_services',
    'islim_services.id',
    'islim_sales.services_id'
    )
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    ->where([
    ['islim_sales.type', 'R'],
    ['islim_sales.msisdn', $client->msisdn]
    ])
    ->whereIn('islim_sales.status', ['A','E'])
    ->orderBy('islim_sales.date_reg', 'DESC')
    ->first();

    if(!empty($lastRe)){
    $dateD = strtotime('+ 3 months', strtotime($lastRe->date_reg));
    $dateD = date('Y-m-d', strtotime('+ '.$lastRe->days.' days', $dateD));

    HistoryDC::createRecord(
    $client->msisdn,
    'C90',
    $dateD
    );
    }
    }

    $i++;
    }

    echo 'Termino';*/

    /*$totalUps = Sale::select(
    'islim_sales.msisdn',
    'islim_sales.services_id',
    'islim_sales.date_reg',
    'islim_clients.name',
    'islim_clients.last_name',
    'islim_clients.email',
    'islim_clients.phone_home',
    'islim_clients.phone'
    )
    ->join(
    'islim_client_netweys',
    'islim_client_netweys.msisdn',
    'islim_sales.msisdn'
    )
    ->join(
    'islim_clients',
    'islim_clients.dni',
    'islim_client_netweys.clients_dni'
    )
    ->where([
    ['islim_sales.type', 'P'],
    ['islim_sales.date_reg', '<=', '2019-12-21 23:59:59']
    ])
    ->whereIn('islim_sales.status',['A', 'E'])
    ->whereIn('islim_client_netweys.status',['A'])
    ->get();

    $count = 0;
    $dateCh = strtotime('- 2 months', time());
    $xls []= [
    'msisdn',
    'nombre',
    'Telefono1',
    'Telefono2',
    'Correo',
    'Fecha de alta',
    'Fecha ultima recarga'
    ];

    foreach($totalUps as $up){
    $lastR = Sale::select('services_id', 'date_reg', 'msisdn')
    ->where([['type', 'R'], ['msisdn', $up->msisdn]])
    ->whereIn('islim_sales.status',['A', 'E'])
    ->orderBy('date_reg', 'DESC')
    ->first();

    if(!empty($lastR)){
    $timeAlta = Service::select('periodicity_id', 'periodicity', 'days')
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    ->where('islim_services.id', $lastR->services_id)
    ->first();

    $isChurn = strtotime('- '.$timeAlta->days.' days', $dateCh);

    if(strtotime($lastR->date_reg) < $isChurn){
    $count++;

    $xls []= [
    $up->msisdn,
    $up->name.' '.$up->last_name,
    $up->phone_home,
    $up->phone,
    $up->email,
    $up->date_reg,
    $lastR->date_reg
    ];
    }
    }
    }

    $url = CommonHelpers::saveFile('/public/reports', 'bi', $xls, 'churn_60_'.time());

    echo (String)$url;

    echo 'Total churn: '.$count;*/

    /*$path = base_path('uploads').'/churn.csv';

    ini_set('auto_detect_line_endings', TRUE);
    $i = 0;
    if(($gestor = fopen($path, "r")) !== FALSE){
    $xls []= [
    'msisdn',
    'Fecha de compra',
    'tipo',
    'Ultimo consumo registrado',
    'Servicio',
    'Dias de consumo',
    'GB'
    ];

    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    if($i > 0){
    $dn = htmlentities($datos[0]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $data = Sale::select(
    'islim_sales.date_reg',
    'islim_sales.msisdn',
    'islim_sales.type',
    'islim_services.title',
    'islim_sales.codeAltan',
    'islim_periodicities.days'
    )
    ->join(
    'islim_services',
    'islim_services.id',
    'islim_sales.services_id'
    )
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    ->where('islim_sales.msisdn', $dn)
    ->where(function($query){
    $query->where('islim_sales.type', 'P')
    ->orWhere('islim_sales.type', 'R');
    })
    ->orderBy('islim_sales.date_reg', 'DESC')
    ->get();

    if(count($data)){
    foreach ($data as $rech){
    $exp = date('Ymd', strtotime('+ '.($rech->days + 1).' days', strtotime($rech->date_reg)));

    $buy = date('Y-m-d', strtotime($rech->date_reg));

    $con = DB::table('islim_consumptions')
    ->select(
    'date_affec',
    DB::raw("(SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR(name_file,26))-6)) as date_file")
    )
    ->join(
    'islim_download_files',
    'islim_download_files.id',
    'islim_consumptions.file_id'
    )
    ->where([
    ['msisdn', $rech->msisdn],
    [DB::raw("SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR('name_file',26))-6)"), '<=', $exp],
    [DB::raw("DATE_FORMAT(date_affec,'%Y-%m-%d')"), $buy]
    ])
    ->orderBy('date_affec', 'DESC')
    ->first();

    $conT = DB::table('islim_consumptions')
    ->select('consumption')
    ->join(
    'islim_download_files',
    'islim_download_files.id',
    'islim_consumptions.file_id'
    )
    ->where([
    ['msisdn', $rech->msisdn],
    [DB::raw("SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR('name_file',26))-6)"), '<=', $exp],
    [DB::raw("DATE_FORMAT(date_affec,'%Y-%m-%d')"), $buy]
    ]);

    if($rech->type == 'P')
    $conT = $conT->where('offer_prim', $rech->codeAltan);
    else
    $conT = $conT->where('offer_sup', $rech->codeAltan);

    $conT = $conT->sum('consumption');

    if(!empty($con)){
    $date1 = new DateTime($rech->date_reg);
    $date2 = new DateTime($con->date_file.'235959');
    $diff = $date1->diff($date2);

    $xls []= [
    $rech->msisdn,
    $rech->date_reg,
    $rech->type == 'P' ? 'Alta' : 'Plan',
    $date2->format('Y-m-d'),
    $rech->title,
    (String)$diff->days,
    (String)round(((($conT/1024)/1024)/1024),2)
    ];
    }else{
    $xls []= [
    $rech->msisdn,
    $rech->date_reg,
    $rech->type == 'P' ? 'Alta' : 'Plan',
    'S/I',
    $rech->title,
    'S/I',
    '0'
    ];
    }
    }
    }
    }

    $i++;
    //print_r($xls);
    //if($i == 10) break;

    }
    //exit();

    $url = CommonHelpers::saveFile('/public/reports', 'bi', $xls, 'churn_'.time());

    echo (String)$url;

    fclose($gestor);
    }*/

    /*$path = base_path('uploads').'/decay.csv';

    ini_set('auto_detect_line_endings', TRUE);
    $i = 0;
    if(($gestor = fopen($path, "r")) !== FALSE){
    $xls []= [
    'msisdn',
    'Fecha del alta',
    'Ultimo consumo registrado',
    'Servicio',
    'Dias de consumo',
    'GB'
    ];

    while(($datos = fgetcsv($gestor, 1000, ";")) !== FALSE){
    if($i > 0){
    $dn = htmlentities($datos[0]);
    $dn = trim(preg_replace('/\&(.)[^;]*;/', '\\1', $dn));

    $data = Sale::select(
    'islim_sales.date_reg',
    'islim_sales.msisdn',
    'islim_services.title',
    'islim_sales.codeAltan',
    'islim_periodicities.days'
    )
    ->join(
    'islim_services',
    'islim_services.id',
    'islim_sales.services_id'
    )
    ->join(
    'islim_periodicities',
    'islim_periodicities.id',
    'islim_services.periodicity_id'
    )
    ->where([
    ['islim_sales.type', 'P'],
    ['islim_sales.msisdn', $dn]
    ])
    ->orderBy('islim_sales.date_reg', 'DESC')
    ->first();

    if(!empty($data)){
    $exp = date('Ymd', strtotime('+ '.($data->days + 1).' days', strtotime($data->date_reg)));

    $buy = date('Y-m-d', strtotime($data->date_reg));

    $con = DB::table('islim_consumptions')
    ->select(
    'date_affec',
    DB::raw("(SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR(name_file,26))-6)) as date_file")
    )
    ->join(
    'islim_download_files',
    'islim_download_files.id',
    'islim_consumptions.file_id'
    )
    ->where([
    ['msisdn', $data->msisdn],
    ['offer_sup', '!=', $data->codeAltan],
    [DB::raw("SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR('name_file',26))-6)"), '<=', $exp],
    [DB::raw("DATE_FORMAT(date_affec,'%Y-%m-%d')"), $buy]
    ])
    ->orderBy('date_affec', 'DESC')
    ->first();

    $conT = DB::table('islim_consumptions')
    ->select('consumption')
    ->join(
    'islim_download_files',
    'islim_download_files.id',
    'islim_consumptions.file_id'
    )
    ->where([
    ['msisdn', $data->msisdn],
    [DB::raw("SUBSTR(SUBSTR(name_file,26),1,LENGTH(SUBSTR('name_file',26))-6)"), '<=', $exp],
    [DB::raw("DATE_FORMAT(date_affec,'%Y-%m-%d')"), $buy],
    ['offer_sup', '!=', $data->codeAltan],
    ['offer_prim', $data->codeAltan]
    ])
    ->sum('consumption');

    if(!empty($con)){
    $date1 = new DateTime($data->date_reg);
    $date2 = new DateTime($con->date_file.'235959');
    $diff = $date1->diff($date2);

    $xls []= [
    $data->msisdn,
    $data->date_reg,
    $date2->format('Y-m-d'),
    $data->title,
    (String)$diff->days,
    (String)round(((($conT/1024)/1024)/1024),2)
    ];
    }else{
    $xls []= [
    $data->msisdn,
    $data->date_reg,
    'S/I',
    $data->title,
    'S/I',
    '0'
    ];
    }
    }
    }

    $i++;

    //if($i == 30) break;

    }

    $url = CommonHelpers::saveFile('/public/reports', 'bi', $xls, 'decays_'.time());

    echo (String)$url;

    fclose($gestor);
    }*/

    /*$dns = Sale::select('msisdn')
    ->where([
    ['type', 'P'],
    ['packs_id', 49]
    ])
    ->get();

    foreach ($dns as $dn) {
    $cli = ClientNetwey::where('msisdn', $dn->msisdn)
    ->update([
    'type_buy' => 'CR',
    'price_remaining' => 10000,
    'total_debt' => 10000,
    'credit' => 'A'
    ]);
    }

    echo 'hecho';*/
    /*$path = base_path('uploads').'/update-imei.csv';

  ini_set('auto_detect_line_endings', TRUE);
  $i = 0;
  if(($gestor = fopen($path, "r")) !== FALSE){
  while(($datos = fgetcsv($gestor, 1000, ",")) !== FALSE){
  $datos[0] = htmlentities($datos[0]);
  $datos[0] = preg_replace('/\&(.)[^;]*;/', '\\1', $datos[0]);
  $inv = Inventory::where('msisdn', trim($datos[0]))->first();

  if(!empty($inv)){
  $inv->imei = trim($datos[1]);
  $inv->save();

  echo 'Se actualizo : '.trim($datos[0]);
  }else{
  echo 'No se encontro : '.trim($datos[0]);
  }

  $i++;
  }

  fclose($gestor);
  }*/
  }
}
