<?php

namespace App\Console\Commands;

use App\Inventory;
use App\Inv_reciclers;
use App\Product;
use App\StockProva;
use App\StockProvaDetail;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Mail\ErrorsFileProva;
use Illuminate\Support\Facades\Mail;

class getOrderProva extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:getOrderProva';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Consulta y procesa el archivo dejado por prova en el SFTP';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  private function getCleanData($data){
    return trim(preg_replace('/\&(.)[^;]*;/', '\\1', htmlentities($data)));
  }

  private function getCleanDataArray($line){
    return [
      'box' => $this->getCleanData($line[0]),
      'sku' => $this->getCleanData($line[1]),
      'msisdn' => $this->getCleanData($line[2]),
      'iccid' => $this->getCleanData($line[3]),
      'imei' => $this->getCleanData($line[4]),
      'branch' => $this->getCleanData($line[5]),
      'name' => $this->getCleanData($line[6]),
      'user' => $this->getCleanData($line[7]),
      'folio' => $this->getCleanData($line[8])
    ];
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    //Consultando directorio del bucket de prova
    $dirs = Storage::disk('s3-prova')->directories();

    //Validando que exista el directorio indicado para que prova almacene sus archivos
    if (in_array('files', $dirs)) {
      $today = Carbon::now();

      //Consultando archivos del directorio files
      $files = Storage::disk('s3-prova')->files('files');

      //Recorriendo los archivos
      foreach ($files as $file) {
        //Obteniendo la última fecha de modificación del arhcivo
        $dateM = Storage::disk('s3-prova')->lastModified($file);
        $dateM = Carbon::createFromTimestamp($dateM);

        //Obteniendo la extención del arhivo
        $ext = strtolower(substr($file, -3));

        //Se toman en cuenta solo los archivos que no tengan mas de dos días de creados en el sftp
        $diffD = $dateM->diffInDays($today);

        if ($diffD <= 2 && $ext == 'csv') {
          //Se procesan los archivos que tengan mas de 5min de creados en el sftp
          $diffM = $dateM->diffInMinutes($today);

          if ($diffM >= 5) {
            $pro = StockProva::existsFile($file, false);

            if (!$pro) {
              $fileContent = Storage::disk('s3-prova')->get($file);
              $lines = explode(PHP_EOL, $fileContent);
              $datos = array_map('str_getcsv', $lines);

              $pos = 1;
              $errorArr = [];
              foreach ($datos as $dato) {
                if ($pos > 1) {
                  if (count($dato) == 9) {
                    $error = '';

                    $fieldsCsv = $this->getCleanDataArray($dato);

                    if(empty($fieldsCsv['iccid'])){
                      $error .= ($error != '' ? ', ' : '') . 'No viene el ICCID';
                    }

                    if(empty($fieldsCsv['folio'])){
                      $error .= ($error != '' ? ', ' : '') . 'No viene el folio';
                    }

                    if(empty($fieldsCsv['box']) || !ctype_digit($fieldsCsv['box'])){
                      $error .= ($error != '' ? ', ' : '') . 'El código de caja no es válido';
                    }

                    if(empty($fieldsCsv['branch'])){
                      $error .= ($error != '' ? ', ' : '') . 'No viene el branch';
                    }

                    if(empty($fieldsCsv['name'])){
                      $error .= ($error != '' ? ', ' : '') . 'No viene el nombre del usuario';
                    }

                    if(empty($fieldsCsv['sku'])){
                      $error .= ($error != '' ? ', ' : '') . 'No viene el SKU ó contiene caracteres extraños';
                    }else{
                      $product = Product::getProducts_fromSKU($fieldsCsv['sku']);
                      if(empty($product) || $product->status != 'A'){
                        $error .= ($error != '' ? ', ' : '') . 'SKU no válido';
                      }
                    }

                    if(!empty($fieldsCsv['imei'])){
                      if (!ctype_digit($fieldsCsv['imei']) || (strlen($fieldsCsv['imei']) != 15 && strlen($fieldsCsv['imei']) != 16)) {
                        $error .= ($error != '' ? ', ' : '') . 'El imei no es válido';
                      }
                    }else{
                      if(!empty($product) && $product->category_id != 2){
                        $error .= ($error != '' ? ', ' : '') . 'Falta el IMEI, este producto no esta registrado como simcard';
                      }
                    }
                    
                    $user  = User::getUserByEmail($fieldsCsv['user']);
                    if (empty($user)) {
                      $error .= ($error != '' ? ', ' : '') . 'El usuario no es válido';
                    }
                    
                    if (empty($fieldsCsv['msisdn']) || strlen($fieldsCsv['msisdn']) != 10 || !ctype_digit($fieldsCsv['msisdn'])) {
                      $error .= ($error != '' ? ', ' : '') . 'El msisdn no es válido ó no viene';
                    } else {
                      //Validando que no haya sido notificado en un archivo previo y no se haya procesado
                      $isreport = StockProvaDetail::getReportByDn($fieldsCsv['msisdn']);
                      if (!empty($isreport) && ($isreport->status == 'A' || 
                          $isreport->status == 'P' || 
                          $isreport->status == 'PR' || 
                          ($isreport->status == 'AS' && $isreport->box == $fieldsCsv['box']) ||
                          ($isreport->status == 'E' && $isreport->box == $fieldsCsv['box']))
                         ){
                        $error .= ($error != '' ? ', ' : '') . 'El msisdn ya fue notificado en otro archivo.';
                      }
                    }

                    if(!empty($error)){
                      $errorArr []= [
                        'pos_line' => $pos,
                        'line' => implode(',', $dato),
                        'error' => $error
                      ];
                    }
                  } else {
                    if (count($dato) > 1){
                      $errorArr []= [
                        'pos_line' => $pos,
                        'line' => implode(',', $dato),
                        'error' => 'Faltan datos'
                      ];
                    }
                  }
                }
                $pos++;
              }

              if(count($errorArr)){
                //Enviado correo de error, no se procesa el archivo
                $data = [
                  'name_file' => $file,
                  'date_file' => $dateM->format('d-m-Y H:i'),
                  'errors' => $errorArr
                ];

                Log::error('Error en archivo de prova. ', $data);

                $data['file_content'] = $fileContent;

                $listDest = explode(',', env('EMAILS_PROVA_FILE', ''));

                if(count($listDest)){
                  try {
                    Mail::to($listDest)->send(new ErrorsFileProva($data));
                  } catch (\Exception $e) {
                    Log::error('No se pudo enviar el correo de notificación de error a prova. '.$e->getMessage());
                  }
                }
              }else{
                $fs = new StockProva;
                $fs->file_name = $file;
                $fs->date_reg = date('Y-m-d H:i:s');
                $fs->status = 'A';
                $fs->save();

                $pos = 0;
                foreach ($datos as $dato) {
                  if ($pos) {
                    if (count($dato) == 9) {
                      $error = '';

                      $fieldsCsv = $this->getCleanDataArray($dato);

                      $detail = new StockProvaDetail;
                      $detail->id_stock_prova = $fs->id;
                      $detail->box = $fieldsCsv['box'];
                      $detail->sku = $fieldsCsv['sku'];
                      $detail->msisdn = $fieldsCsv['msisdn'];
                      $detail->iccid = $fieldsCsv['iccid'];
                      $detail->branch = $fieldsCsv['branch'];
                      $detail->name = $fieldsCsv['name'];
                      $detail->users = $fieldsCsv['user'];
                      $detail->folio = $fieldsCsv['folio'];
                      $detail->statusRecycling = 'N';

                      if (!empty($fieldsCsv['imei'])) {
                        $detail->imei = $fieldsCsv['imei'];
                      }

                      //Verificando si el dn ya esta dado de alta y de ser asi si es candidato a reciclaje                    
                      $dn = Inventory::existDN($fieldsCsv['msisdn']);
                      if (!empty($dn)) {
                        if($dn->status != 'A'){                        
                          $product = Product::getProducts_fromSKU($fieldsCsv['sku']);

                          $infoReciclers = array(
                            'inv_article_id' => $product->id,
                            'warehouses_id'  => env('WHEREHOUSE', 5),
                            'iccid'          => $fieldsCsv['iccid'],
                            'imei'           => !empty($fieldsCsv['imei']) ? $fieldsCsv['imei'] : null,
                            'imsi'           => null,
                            'msisdn'         => $fieldsCsv['msisdn']
                          );
                    
                          $recycling = Inv_reciclers::Verify_msisdn_recicler($infoReciclers, 'sftp');

                          if($recycling['code'] == 'RECICLER' || $recycling['code'] == 'DIFF_OFFER'){
                            $detail->statusRecycling = 'P';
                          }else{
                            $error .= ($error != '' ? ', ' : '') . $recycling['msg'];
                          }
                        }else{
                          $error .= ($error != '' ? ', ' : '') . 'El DN ya se encontraba iluminado y disponible para la venta';
                        }
                      }

                      if ($error != '') {
                        $detail->comment = $error;
                        $detail->status  = 'E';
                      } else {
                        $detail->status = 'A';
                      }

                      $detail->date_reg = date('Y-m-d H:i:s');
                      $detail->save();
                    }
                  }
                  $pos++;
                }
              }
            }
          }
        }
      }
    }
  }
}