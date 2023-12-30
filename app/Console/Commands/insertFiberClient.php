<?php

namespace App\Console\Commands;

use App\Client;
use App\ClientFiberFail;
use App\ClientNetwey;
use App\FiberInstallation;
use App\Helpers\API815;
use App\Inventory;
use App\MetricsDasboardB;
use App\Sale;
use App\Service;
use Illuminate\Console\Command;

class insertFiberClient extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:insertFiberAlta';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Creacion de los registros de Alta ante netwey, se usa cuando falla en 815 el Alta, se requiere el MSISDN y el DNI';

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
    $TextError     = 'No hay registros pendientes por procesar';
    $isError       = false;
    $PkClient      = null; #Codigo primario ante 815 del cliente
    $Pkconex       = null; #Codigo primario ante 815 del conexion
    $ConectorConex = null; #Conector de la conexion
    $DateInstall   = null; #Fecha en que se instalo
    $PriceSales    = null; #Monto pagado por el equipo

    $Earring = ClientFiberFail::getEarring();
    if (!empty($Earring)) {
      $Inventario = Inventory::getInfoDN($Earring->msisdn);
      if (!empty($Inventario)) {
        if ($Inventario->status == 'A') {

          $DetailInstall = FiberInstallation::getDetailInstalation($Earring->dni_client, $Earring->id_fiber_zone);

          if (!empty($DetailInstall)) {

            //Revisamos que estemos seguros que no existe el cliente
            $exitClient = ClientNetwey::existDN($Earring->msisdn);
            if (empty($exitClient)) {

              // $infoNewClient = new \stdClass;
              $service = Service::getPeriodicityFibra($DetailInstall->service_id, $Earring->id_fiber_zone);
              if (!empty($service)) {
                $unique = uniqid('FIB-') . time();

                //Buscamos el equipo en 815 por Mac que es el dato mas seguro de busqueda
                $datain815 = array(
                  'direccion_mac' => $Inventario->imei,
                  'fiber_zone'    => $Earring->id_fiber_zone);

                $this->info("Se consultaran datos en 815 del DN " . $Earring->msisdn);

                $Conexion815 = API815::doRequest("conections-search", 'POST', $datain815);

                if ($Conexion815['success']) {

                  //Se marca como vendido
                  //
                  Inventory::setInventaryStatus($Earring->msisdn, 'V');
                  $this->info($Earring->msisdn . " Se marco vendido!");

                  $InfoClient815 = $Conexion815['data']['eightFifteen']['object'];

                  $DetailClient815 = $InfoClient815['field'];
                  $Pkconex         = $InfoClient815['attributes']['pk'];
                  $ConectorClient  = null;

                  foreach ($DetailClient815 as $item815) {
                    if ($item815['attributes']['name'] == 'fecha_de_alta') {
                      $DateInstall = $item815['value'];
                    } elseif ($item815['attributes']['name'] == 'cliente') {
                      $PkClient = $item815['value'];
                    } elseif ($item815['attributes']['name'] == 'conector') {
                      $ConectorConex = $item815['value'];
                    }
                  }
                  if (!empty($DateInstall) && !empty($PkClient)) {

                    //Parche condicional si la alta se hizo antes del 30/06 se suspende el dia 7 de Julio

                    $MaxAlta      = "2022-06-30";
                    $ExpireParche = "";
                    if ($DateInstall <= $MaxAlta) {
                      $ExpireParche = "2022-07-07";
                    }

                    $saleBuy = $DateInstall . ' ' . date('H:i:s');

                    $timeDayService = $service->days;

                    $DateExpire = date("Y-m-d", strtotime($DateInstall . "+ " . $timeDayService . " days"));
                    $Expire30   = date("Y-m-d", strtotime($DateExpire . "+ 30 days"));
                    $Expire90   = date("Y-m-d", strtotime($DateExpire . "+ 90 days"));

                    //Registro el PK de 815 en el cliente
                    //
                    Client::setPKuser815($Earring->dni_client, $PkClient, $Earring->id_fiber_zone);

                    //Revisamos si el cliente tiene plan activo o se debe suspender
                    //
                    //NO se necesario colocar en suspencion, se revisa en la noche y se suspende
                    //
                    /*$date1       = new \DateTime($DateExpire);
                    $date2       = new \DateTime("now");
                    $timeDiffSeg = $date1->getTimestamp() - $date2->getTimestamp();

                    if ($timeDiffSeg < 1) {
                    $statusNewClient = 'S';
                    }*/
                    //Se crea el registro de la tabla client Netwey
                    //
                    $dataInsertClient = array(
                      'msisdn'               => $Earring->msisdn,
                      'clients_dni'          => $Earring->dni_client,
                      'service_id'           => $DetailInstall->service_id,
                      'type_buy'             => 'CO',
                      'periodicity'          => $service->periodicity,
                      'num_dues'             => 0,
                      'paid_fees'            => 0,
                      'unique_transaction'   => $unique,
                      'date_buy'             => $saleBuy,
                      'price_remaining'      => 0,
                      'total_debt'           => 0,
                      'date_reg'             => $saleBuy,
                      'date_expire'          => !empty($ExpireParche) ? $ExpireParche : $DateExpire,
                      'date_cd30'            => $Expire30,
                      'date_cd90'            => $Expire90,
                      'date_cd90'            => $Expire90,
                      'type_cd90'            => 'D',
                      'status'               => 'A',
                      'tag'                  => 'BT',
                      'dn_type'              => 'F',
                      'type_client'          => 'C',
                      'is_band_twenty_eight' => 'N',
                      'is_suspend_by_b28'    => 'N',
                      'pk_conex815'          => $Pkconex,
                      'id_fiber_zone'        => $Earring->id_fiber_zone);

                    //
                    //Se registra el cliente
                    ClientNetwey::getConnect('W')->insert($dataInsertClient);
                    $this->info($Earring->msisdn . " Se registro como cliente!");

                    //Se realizan los registros tipo V(venta) y luego P(alta) de Sales
                    //

                    $PriceSales = floatval($DetailInstall->price);
                    $porcentaje = $PriceSales - ($PriceSales * floatval(env('TAX')));

                    $dataInsertSaleV = array(
                      'services_id'         => $DetailInstall->service_id,
                      'concentrators_id'    => 1,
                      'inv_arti_details_id' => $Inventario->id,
                      'api_key'             => env('TOKEN_815'),
                      'users_email'         => $DetailInstall->installer,
                      'packs_id'            => $DetailInstall->pack_id,
                      'order_altan'         => null,
                      'unique_transaction'  => $unique,
                      'codeAltan'           => $service->codeAltan,
                      'type'                => 'V',
                      'id_point'            => 'VENDOR',
                      'description'         => 'ARTICULO',
                      'fee_paid'            => 0,
                      'amount'              => $PriceSales,
                      'amount_net'          => $porcentaje,
                      'com_amount'          => 0,
                      'msisdn'              => $Earring->msisdn,
                      'conciliation'        => 'N',
                      'date_reg'            => $saleBuy,
                      'sale_type'           => 'F',
                      'from'                => 'S',
                      'status'              => 'E',
                      'is_migration'        => 'N',
                      'user_locked'         => 'N');

                    Sale::getConnect('W')->insert($dataInsertSaleV);

                    $this->info($Earring->msisdn . " Se registro venta!");

                    $dataInsertSaleP                = $dataInsertSaleV;
                    $dataInsertSaleP['order_altan'] = '0000';
                    $dataInsertSaleP['type']        = 'P';
                    $dataInsertSaleP['description'] = 'ALTA';
                    $dataInsertSaleP['amount']      = 0;
                    $dataInsertSaleP['amount_net']  = 0;

                    Sale::getConnect('W')->insert($dataInsertSaleP);
                    $this->info($Earring->msisdn . " Se registro alta!");

                    //Se registra procesada la instalacion
                    //
                    FiberInstallation::setInstallStatus($DetailInstall->id, 'P', $Earring->msisdn, $saleBuy);
                    $this->info($Earring->msisdn . " Se completo instalacion!");

                    //Se marca como procesado el registro de fallo815
                    //al terminar de revisar los conectores
                    $TextError = "Se registro el cliente de fibra " . $Earring->msisdn . " exitosamente!";
                  } else {
                    $TextError = "La fecha, el PkUser o el PkConex registrada en el API 815 no se pudo obtener. +Info: " . (String) json_encode($DetailClient815);
                    $isError   = true;
                  }
                } else {
                  $TextError = "Hubo un problema en buscar en 815 la conexion con la mac " . $Inventario->imei . " +Info: " . (String) json_encode($Conexion815['data']);
                  $isError   = true;
                }
              } else {
                $TextError = "No se encontro la periodicidad del servicio " . $DetailInstall->service_id . " para el fiber_Zone " . $Earring->id_fiber_zone;
                $isError   = true;
              }
            } else {
              $TextError = "El msisdn " . $Earring->msisdn . " ya esta registrado como cliente";
              $isError   = true;
            }
          } else {
            $TextError = 'El dni ' . $Earring->dni_client . ' no tiene una instalacion Activa para el fiber_Zone ' . $Earring->id_fiber_zone;
            $isError   = true;
          }
        } else {
          $TextError = 'El msisdn ' . $Earring->msisdn . ' no esta disponible para la venta!';
          $isError   = true;
        }
      } else {
        $TextError = 'El msisdn ' . $Earring->msisdn . ' no existe!';
        $isError   = true;
      }
    }

    ///////////////////////////
    if (!empty($Earring)) {
      if ($isError) {
        //Hubo un problema
        ClientFiberFail::setFiberFailStatus($Earring->id, 'E', $TextError);
      } else {
        //Se registro en Netwey de forma exitosa!
        //Se verifica que los datos cargados en 815 coincidan con netwey
        $msjRevision            = null;
        $changeConectorConexion = false;
        $changeConectorClient   = false;

        $prefijoRev = "Se debe actualizar en 815:";
        if ($Earring->msisdn != $ConectorConex) {
          $changeConectorConexion = true;
/*
$msjRevision = $prefijoRev . " conector de conexion";
 */
        }

        if (!empty($PkClient)) {
          //Se consulta el cliente para ver si el conector es diferente

          $ConectorClient = null;
          $datain815      = array(
            'pk'         => $PkClient,
            'fiber_zone' => $DetailInstall->id_fiber_city);
          //Consultamos en 815 al cliente por medio del pk

          $Client815 = API815::doRequest("search-client", 'POST', $datain815);

          if ($Client815['success']) {
            $InfoClient815 = $Client815['data']['eightFifteen']['object'];

            $DetailClient815 = $InfoClient815['field'];
            foreach ($DetailClient815 as $item815) {
              if ($item815['attributes']['name'] == 'conector') {
                $ConectorClient = $item815['value'];
              }
            }
          }

          if ($Client815['success']) {
            if ($Earring->dni_client != $ConectorClient) {
              $changeConectorClient = true;

              /*
            $text = " conector de cliente";
            if (empty($msjRevision)) {
            $msjRevision = $prefijoRev . $text;
            } else {
            $msjRevision .= ", " . $text;
            }
             */
            }
          } else {
            if (empty($msjRevision)) {
              $text        = " conector de cliente no se pudo obtener";
              $msjRevision = $prefijoRev . $text;
            } else {
              $msjRevision .= ", " . $text;
            }
          }
        }
        $TextError .= ' ' . $msjRevision;

        //Se proceso datos en netwey...
        //
        //Se actualiza los conectores en 815 de cliente y clientes_netwey
        $textUpdateConexion = false;
        $textConexion       = "";

        $TextNotify = "";
        $nosepudo   = "fallo actualizacion";
        $sisepudo   = "actualizacion OK";

        if ($changeConectorConexion) {
          $datain815 = array(
            'pk'       => $Pkconex,
            'conector' => $Earring->msisdn);

          $Conextion815 = API815::doRequest("conections-update", 'POST', $datain815);

          $textConexion = "Conector de conexion";

          if ($Conextion815['success']) {
            $this->info($textConexion . " " . $sisepudo);
          } else {
            $TextNotify = $textConexion . ' ' . $nosepudo;
            $TextError .= $TextNotify;
          }
        }
        $textUpdateClient = false;
        $textUser         = "";

        if ($changeConectorClient) {
          $datain815 = array(
            'pk'       => $PkClient,
            'conector' => $Earring->dni_client);
          $Client815 = API815::doRequest("update-client", 'POST', $datain815);

          $textUser = "Conector de cliente";

          if ($Client815['success']) {
            $this->info($textUser . " " . $sisepudo);
          } else {
            $TextNotify .= (!empty($TextNotify)) ? ', ' : '';
            $TextNotify .= $textUser . ' ' . $nosepudo;
            $TextError .= $TextNotify;
          }
        }
        //End de actualizacion de conectores en 815

        //Se registra el intento de cambio de datos en 815
        //y se procesa el alta fallida de fibra

        $textR = null;
        if (!empty($msjRevision) || !empty($TextNotify)) {
          $textR = $msjRevision . '. ' . $TextNotify;
        }
        ClientFiberFail::setFiberFailStatus($Earring->id, 'P', $textR);

        ///////Actualizamos las metricas del dashbord de netwey
        $Metrica = MetricsDasboardB::getConnect('R')
          ->select('id', 'quantity', 'amount')
          ->where([
            ['date', $DateInstall],
            ['id_org', 1],
            ['type', 'U'],
            ['type_device', 'F']])
          ->first();

        if (!empty($Metrica)) {

          $PriceSales += floatval($Metrica->amount);
          $cantSales = intval($Metrica->quantity) + 1;

          MetricsDasboardB::getConnect('W')
            ->where('id', $Metrica->id)
            ->update([
              'quantity' => $cantSales,
              'amount'   => $PriceSales]);

          $this->info($Earring->msisdn . " agregado a la metrica del dia " . $DateInstall);
        } else {
          //Se agregara la nueva metrica
          //
          $newMetricAlta = array(
            'date'        => $DateInstall,
            'quantity'    => 1,
            'amount'      => $PriceSales,
            'id_org'      => 1,
            'type'        => 'U',
            'type_device' => 'F');

          MetricsDasboardB::getConnect('W')->insert($newMetricAlta);
          $this->info("Nueva metrica de alta creada del msisdn " . $Earring->msisdn . " con fecha " . $DateInstall);

          $newMetricRecarga             = $newMetricAlta;
          $newMetricRecarga['quantity'] = 0;
          $newMetricRecarga['amount']   = 0;
          $newMetricRecarga['type']     = 'R';
          MetricsDasboardB::getConnect('W')->insert($newMetricRecarga);
          $this->info("Nueva metrica de recargas creada con fecha " . $DateInstall);
        }
        //End Actualizacion de metricas
      }
    }
    $this->info('');
    $this->info('**************************');
    $this->info($TextError);
    $this->info('**************************');
  }
}
