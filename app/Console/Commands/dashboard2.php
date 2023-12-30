<?php

namespace App\Console\Commands;

use App\MetricsDasboardB;
use App\Organization;
use App\SaleMetrics;
use Carbon\Carbon;
use Illuminate\Console\Command;

class dashboard2 extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:dashboard2 {typeService?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Ejecuta cálculo de métricas(ventas, recargas) del dia que se ejecute - 1';

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
    $typeService = $this->argument('typeService');

    if (!empty($typeService) && ($typeService == 'H' || $typeService == 'T' || $typeService == 'M' || $typeService == 'MH' || $typeService == 'MH_M' || $typeService == 'F')) {

      /*
       * Calculo de un dia en especifico (Descomentar el $dataStart siguiente)
       */
      //$dataStart = '2021-05-20 00:00:00';

      //Organizaciones activas
      $orgs = Organization::select('id')->where('status', 'A')->get();

      //Fecha que se esta calculando
      $today = Carbon::now()->subDay();
      
      /*
       * Calculo de un dia en especifico (Descomentar el $today siguiente)
       */
      //$today = Carbon::createFromFormat('Y-m-d H:i:s', $dataStart);

      //Cantidad de dias a calcular
      //$cicles = ((((strtotime('2020-09-28 00:00:00') - strtotime($dataStart)) / 60) / 60) / 24);

      //for($i = 0; $i < $cicles; $i++){
      $db = $today->startOfDay()->toDateTimeString();
      $de = $today->copy()->endOfDay()->toDateTimeString();

      foreach ($orgs as $org) {
        $notSaveUp = true;
        $notSaveRe = true;
        //Calculando altas
        $altas = SaleMetrics::getTotalSalesMD($db, $de, 'P', $typeService, $org->id);

        if (!empty($altas)) {
          $amountorg = !empty($altas->total_mount) ? $altas->total_mount : 0;
          $ventas = SaleMetrics::getTotalSalesV($db, $de, $typeService, $org->id);

          if (!empty($ventas)) {
            $amountorg += !empty($ventas->total_mount) ? $ventas->total_mount : 0;
          }

          //Guardando altas por organizacion
          if (!empty($altas->total_u)) {
            $metric = new MetricsDasboardB;
            $metric->date = $today->format('Y-m-d');
            $metric->quantity = $altas->total_u;
            $metric->amount = $amountorg;
            $metric->id_org = $org->id;
            $metric->type = 'U';
            $metric->type_device = $typeService;
            $metric->save();
            $notSaveUp = false;

            //$this->info('altas totales: '.$altas->total_u.' Monto: '.$amountorg.' org: '.$org->id);
          }
        }

        //Calculando recargas
        $recargas = SaleMetrics::getTotalSalesMD($db, $de, 'R', $typeService, $org->id);

        if (!empty($recargas) && $typeService != 'MH_M') {
          //Guardando recargas por organizacion
          if (!empty($recargas->total_mount)) {
            $metric = new MetricsDasboardB;
            $metric->date = $today->format('Y-m-d');
            $metric->quantity = $recargas->total_u;
            $metric->amount = $recargas->total_mount;
            $metric->id_org = $org->id;
            $metric->type = 'R';
            $metric->type_device = $typeService;
            $metric->save();
            $notSaveRe = false;

            //$this->info('recargas totales: '.$recargas->total_u.' Monto: '.$recargas->total_mount.' org: '.$org->id);
          }
        }
      }

      //Si no hubo altas guardamos 0 en ese dia
      if ($notSaveUp) {
        $metric = new MetricsDasboardB;
        $metric->date = $today->format('Y-m-d');
        $metric->quantity = 0;
        $metric->amount = 0;
        $metric->type = 'U';
        $metric->type_device = $typeService;
        $metric->save();
      }

      //Recargas no relacionadas a un vendedor
      $recargasn = SaleMetrics::getTotalRechargeWO($db, $de, $typeService);

      if (!empty($recargasn) && $typeService != 'MH_M') {
        if (!empty($recargasn->total_mount)) {
          $metric = new MetricsDasboardB;
          $metric->date = $today->format('Y-m-d');//date('Y-m-d', $dataCurrent);
          $metric->quantity = $recargasn->total_u;
          $metric->amount = $recargasn->total_mount;
          $metric->type = 'R';
          $metric->type_device = $typeService;
          $metric->save();
          $notSaveRe = false;

          //$this->info('recargas totales: '.$recargasn->total_u.' Monto: '.$recargasn->total_mount.' sin org');
        }
      }

      if ($notSaveRe && $typeService != 'MH_M') {
        $metric = new MetricsDasboardB;
        $metric->date = $today->format('Y-m-d');
        $metric->quantity = 0;
        $metric->amount = 0;
        $metric->type = 'R';
        $metric->type_device = $typeService;
        $metric->save();
      }

      //Altas para usuarios que no pertenecen a una org
      $altas = SaleMetrics::getTotalSalesMD($db, $de, 'P', $typeService, false);

      if (!empty($altas) && $altas->total_u) {
        $ventas = SaleMetrics::getTotalRechargeWO($db, $de, $typeService);

        $totala = !empty($altas->total_mount) ? $altas->total_mount : 0;
        $totala += !empty($ventas->total_mount) ? $ventas->total_mount : 0;

        $metric = new MetricsDasboardB;
        $metric->date = $today->format('Y-m-d');
        $metric->quantity = $altas->total_u;
        $metric->amount = $totala;
        $metric->type = 'U';
        $metric->type_device = $typeService;
        $metric->save();

        //$this->info('altas totales: '.$altas->total_u.' Monto: '.$totala.' sin org');
      }

      //$today->addDay();
      //}
    } else {
      $this->info('No se pudo ejecutar el comando, debes enviar como parámetro el tipo de servicio a consultar H->HBB, T->MBB, MH->Mifi Huella altan, MH_M->Mifi Huella altan migrado, F->Fibra');
    }
  }
}
