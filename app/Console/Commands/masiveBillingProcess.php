<?php

namespace App\Console\Commands;


use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use App\Helpers\CommonHelpers;

use App\BillingMasive;

use \Curl;
use DateTime;

use App\Mail\BillingMasiveMail;



class masiveBillingProcess extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:masiveBillingProcess';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Procesa facturas pendientes del servicio OXXO y las envia por correo a administracion para el control y seguimiento de las mismas';

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
    //$date = date('Y-m-d');
    //$date = Carbon::now()->subDay();

    $this->output->writeln('log CRON: Inicia '.date("Y-m-d H:i:s"));

    $dtini=date('Y-m-d H:i:s');

    $bash=DB::table('islim_bash')
        ->where('action','billing_masive_process')
        ->where('unlook','N')
        ->first();

    if($bash){
      DB::table('islim_bash')
        ->where('id', $bash->id)
        ->update([
            'unlook' => 'Y',
            'date_begin' => $dtini
        ]);

      $billings = BillingMasive::getConnect('W')
                  ->whereIn('status_gen',['W'])
                  ->where('billable','Y')
                  ->whereNull('mk_serie')
                  ->whereNull('mk_folio')
                  ->get();

      if($billings){

        $this->output->writeln('Si hay pendientes por facturar: '.date("Y-m-d H:i:s"));

        $folio = 0;
        $id_gen = time();

        $lastfolio = BillingMasive::where('mk_serie','Z')->orderBy('mk_folio','DESC')->first();
        if($lastfolio)
           $folio = $lastfolio->mk_folio;

        $mode=env('APP_ENV')=='production'?'0':'1';
        $folder=env('APP_ENV')=='production'?'masive/':'masive/test/';

        $fecha = new \DateTime();
        $fechagen=$fecha->format('Y-m-d H:i:s');
        $fecha=$fecha->format('Y-m-d\TH:i:s');


        $dataMail = array();
        $hasError = 0;
        $max_intentos = 3;

        foreach ($billings as $key => $billing) {
          $folio++;

          $this->output->writeln('Procesando Folio: '.$folio.' :'.date("Y-m-d H:i:s"));

          $sub_total=round($billing->total/(1+(env('IVA')/100)),2);
          $total=round($billing->total,2);
          $tasa=(env('IVA')/100);
          $price_iva=round(($sub_total*$tasa),2);
          $tasat=number_format($tasa,6);

          if($sub_total+$price_iva != $total){
              $price_iva=$total-$sub_total;
          }


          $url = "http://app.servidormk.com/___DATA/_scripts/external_cfdi_33.php";
          $data = array(
              'data' => '
              {
                "Comprobante":
                {
                  "Version":3.3,
                  "Serie":"Z",
                  "Folio":'.$folio.',
                  "Fecha":"'.$fecha.'",
                  "Sello":"","FormaPago":"'.env('BILLING_PAYMETHOD').'",
                  "NoCertificado":"'.env('BILLING_CERTIFIED').'",
                  "Certificado":"",
                  "CondicionesDePago":"",
                  "SubTotal":'.$sub_total.',
                  "Descuento":0,
                  "Moneda":"MXN",
                  "TipoCambio":1,
                  "Total":'.$total.',
                  "TipoDeComprobante":"I",
                  "MetodoPago":"'.$billing->pay_type.'",
                  "LugarExpedicion":"'.env('BILLING_LUGAR').'",
                  "Emisor":
                  {
                    "Rfc":"'.env('BILLING_EMISOR_RFC').'",
                    "Nombre":"'.env('BILLING_EMISOR_NOMBRE').'",
                    "RegimenFiscal":"'.env('BILLING_EMISOR_REGIMEN').'"
                  },
                  "Receptor":
                  {
                    "Rfc":"'.env('BILLING_RECEPTOR_RFC').'",
                    "Nombre":"'.env('BILLING_RECEPTOR_NOMBRE').'",
                    "UsoCFDI":"'.env('BILLING_USOCFDI').'"
                  },
                  "Conceptos":
                  {
                    "Concepto":
                    {
                      "ClaveProdServ":"'.env('BILLING_CONCEPT_PRODUCTKEY').'",
                      "NoIdentificacion":"'.env('BILLING_CONCEPT_NROID').'",
                      "Cantidad":"1",
                      "ClaveUnidad":"'.env('BILLING_CONCEPT_UNITKEY').'",
                      "Unidad":"'.env('BILLING_CONCEPT_UNIT').'",
                      "Descripcion":"'.env('BILLING_CONCEPT_DESCRIPTION').'",
                      "ValorUnitario":"'.$sub_total.'","Importe":"'.$sub_total.'",
                      "Impuestos":
                      {
                        "Traslados":
                        {
                          "Traslado":
                          [
                            {
                              "Base":"'.$sub_total.'",
                              "Impuesto":"002",
                              "TipoFactor":"Tasa",
                              "TasaOCuota":"'.$tasat.'",
                              "Importe":"'.$price_iva.'"
                            }
                          ]
                        }
                      }
                    }
                  },
                  "Impuestos":
                  {
                    "TotalImpuestosTrasladados":"'.$price_iva.'",
                    "Traslados":
                    {
                      "Traslado":
                      [
                        {
                          "Impuesto":"002",
                          "TipoFactor":"Tasa",
                          "TasaOCuota":"'.$tasat.'",
                          "Importe":"'.$price_iva.'"
                        }
                      ]
                    }
                  }
                }
              }','mode' => $mode,'excludeXML' => 'false','file' => 'true','debug' => 'false'
          );
          $header = array(
              " : "
            );

          $intento = 0;
          $exit = 0;

          while ($exit == 0 && $intento < $max_intentos ) {

            $intento++;

            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => $header,
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            $res=json_decode($response,true);

            //$this->output->writeln(date("Y-m-d H:i:s").' respuesta Folio: '.$folio.' : '.$res);

            if($res['error']==false && !empty($res['idXML']) && !empty($res['pdf']) && !empty($res['uuid'])){

                 $this->output->writeln(date("Y-m-d H:i:s").' respuesta Folio: '.$folio.' : OKOK');

                if($res['uuid'] != null){
                    $billing->status_gen='P';
                    $billing->billing_nro=$res['uuid'];
                    $billing->xml_id=$res['idXML'];
                    $billing->date_gen=$fechagen;
                    $billing->mk_serie='Z';
                    $billing->mk_folio=$folio;

                    $billing->id_gen=$id_gen;

                    $fileName = $billing->billing_nro.'.pdf';
                    $file_dir_pdf=$folder.$fileName;
                    $content = base64_decode($res['pdf']);
                    Storage::disk('s3-masive-billing')->put($file_dir_pdf, $content,'public');

                    $fileName = $billing->billing_nro.'.xml';
                    $file_dir_xml=$folder.$fileName;
                    $content = base64_decode($res['xml']);
                    Storage::disk('s3-masive-billing')->put($file_dir_xml, $content,'public');

                    $billing->save();

                    $file_pdf = Storage::disk('s3-masive-billing')->url($file_dir_pdf);
                    $file_xml = Storage::disk('s3-masive-billing')->url($file_dir_xml);

                    //print_r($bill->billing_nro." -> Generada");
                    //$this->output->writeln('file_pdf: '.$file_pdf);
                    //$this->output->writeln('file_xml: '.$file_xml);
                    $this->output->writeln('Factura Generada (Facturacion Masiva): '.$billing->billing_nro);
                    Log::info('Factura Generada (Facturacion Masiva): '.$billing->billing_nro);


                    $dataMailReng = array(
                            'oxxo_folio_id' => $billing->oxxo_folio_id,
                            'oxxo_folio_nro' => $billing->oxxo_folio_nro,
                            'serie' => $billing->mk_serie,
                            'folio' => $billing->mk_folio,
                            'billing_nro' => $billing->billing_nro,
                            'date_gen' => $billing->date_gen,
                            'url_download_pdf' => $file_pdf,
                            'url_download_xml' => $file_xml
                        );

                    array_push($dataMail,$dataMailReng);

                    $exit = 1;
                }
                else{
                  if($intento == $max_intentos){
                      $this->output->writeln('Error Generando Factura (Facturacion Masiva - intento = '.$intento.'): oxxo_folio_id:'.$billing->oxxo_folio_id.' - oxxo_folio_nro '.$billing->oxxo_folio_nro.' billing_nro vacio - '.$data['data']);

                      Log::error('Error Generando Factura (Facturacion Masiva - intento = '.$intento.'): oxxo_folio_id:'.$billing->oxxo_folio_id.' - oxxo_folio_nro '.$billing->oxxo_folio_nro.' billing_nro vacio - '.$data['data']);

                      $exit == 1;
                    }
                }

            }
            else{
              $this->output->writeln(date("Y-m-d H:i:s").' respuesta Folio: '.$folio.' : ERRR');

              $this->output->writeln('int: '.$intento." == max: ".$max_intentos);

              if($intento == $max_intentos){
                $this->output->writeln('Error Generando Factura (Facturacion Masiva - intento = '.$intento.'): oxxo_folio_id:'.$billing->oxxo_folio_id.' - oxxo_folio_nro '.$billing->oxxo_folio_nro.' resp:'.$res['message']);

                Log::error('Error Generando Factura (Facturacion Masiva - intento = '.$intento.'): oxxo_folio_id:'.$billing->oxxo_folio_id.' - oxxo_folio_nro '.$billing->oxxo_folio_nro.' resp:'.$res['message']);

                $exit == 1;
              }
            }



            if($exit == 0){
              $sleep = (($intento*30) <= 300) ? (30*$intento) : 300;
              sleep($sleep);
            }
          }
          //usleep(500);
        }

        if(count($dataMail)){

          $now = date("Y-m-d 23:59:59");

          $data_file = BillingMasive::getConnect('R')
                  ->whereRaw('TIMESTAMPDIFF(YEAR,date_reg,?) = 0',[$now])
                  ->get();

          $reportxls []= [
              'place',
              'date_expired',
              'term',
              'oxxo_folio_date',
              'oxxo_folio_id',
              'oxxo_folio_nro',
              'date_pay',
              'doc_pay',
              'status_pay',
              'sub_total',
              'tax',
              'total',
              'pay_type',
              'mk_serie',
              'mk_folio'
          ];

          foreach($data_file as $r){

              switch ($r->term) {
                case 'C':
                  $term = "Contado";
                break;
                case '30':
                  $term = "30 dias";
                break;
              }

              switch ($r->status_pay) {
                case 'Y':
                  $status_pay = "Pago Completo";
                break;
                case 'N':
                  $status_pay = "No Pagado";
                break;
              }

              if(!empty($r->date_expired)){
                $date_expired = new \DateTime($r->date_expired);
                $date_expired = $date_expired->format('d/m/Y');
              }
              else{
                $date_expired = '';
              }

              if(!empty($r->oxxo_folio_date)){
                $oxxo_folio_date = new \DateTime($r->oxxo_folio_date);
                $oxxo_folio_date = $oxxo_folio_date->format('d/m/Y');
              }
              else{
                $oxxo_folio_date = '';
              }

              if(!empty($r->date_pay)){
                $date_pay = new \DateTime($r->date_pay);
                $date_pay = $date_pay->format('d/m/Y');
              }
              else{
                $date_pay = '';
              }


              $reportxls []= [
                  $r->place,
                  $date_expired,
                  $term,
                  $oxxo_folio_date,
                  $r->oxxo_folio_id,
                  $r->oxxo_folio_nro,
                  $date_pay,
                  $r->doc_pay,
                  $status_pay,
                  $r->sub_total,
                  $r->tax,
                  $r->total,
                  $r->pay_type,
                  $r->mk_serie,
                  $r->mk_folio
              ];
          }


          $fileName = 'facturacion_oxxo_csv'.'_'.$id_gen.'_'.date('d-m-Y');
          $folderDir=env('APP_ENV')=='production'?'masive_billing/':'masive_billing/test/';
          $urlcsv = CommonHelpers::saveFile('/public/reports', $folderDir, $reportxls, $fileName,30000,'csv');
          $filePath = '/reports/'.$folderDir.$fileName.'.csv';
          $fileContent = Storage::disk('s3')->get($filePath);


          //$this->output->writeln($urlcsv);

          $listDest = explode(',', env('BILLING_EMAILS', ''));

          if(count($listDest)){
            try {
              Mail::to($listDest)->send(new BillingMasiveMail($dataMail,$fileContent,$fileName.'.csv'));
            } catch (\Exception $e) {
              Log::error('No se pudo enviar el correo de Facturacion Masiva. '.$e->getMessage());
            }
          }

        }

      }


      $this->output->writeln('log CRON: Termina '.date("Y-m-d H:i:s"));

      DB::table('islim_bash')
        ->where('id', $bash->id)
        ->update([
          'unlook' => 'N'
        ]);
    }
    else{
      $now = date("Y-m-d H:i:s");

      $bash=DB::table('islim_bash')
        ->where('action','billing_masive_process')
        ->where('unlook','Y')
        ->whereRaw('TIMESTAMPDIFF(HOUR,date_begin,?) >= 4',[$now])
        ->first();

      if($bash){
        DB::table('islim_bash')
          ->where('id', $bash->id)
          ->update([
            'unlook' => 'N'
          ]);
      }
    }
  }
}
