<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\CommonHelpers;
use App\Sale;
use App\Service;
use App\Migrations;
use Carbon\Carbon;

class KPIMonth extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:kpiMonth {type} {month} {year} {dateEnd?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Ejecuta calculo de KPI para el mes y año enviado';

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
    $type = $this->argument('type');
    $month = $this->argument('month');
    $year = $this->argument('year');
    $dateEnd = $this->argument('dateEnd');

    //Primer dia del mes
    $begin = Carbon::createFromFormat('Y-m', $year . '-' . $month)->startOfMonth();

    $this->output->writeln(
      'Fecha a analizar: ' . $begin->toDateTimeString()
    );

    //ultimo dia del mes
    if(!empty($dateEnd)){
      $endDate = Carbon::createFromFormat('Y-m-d H:i:s', $dateEnd);
    }else{
      $endDate = $begin->copy()->endOfMonth();
    }

    //Inicio de busqueda
    $starSearch = $begin->copy()->subMonths(6);

    //Dns a analizar
    $totalR = Sale::select(
      'islim_sales.msisdn',
      'islim_sales.services_id',
      'islim_sales.type',
      'islim_sales.date_reg',
      'islim_client_netweys.status'
    )
    ->join(
      'islim_client_netweys',
      'islim_client_netweys.msisdn',
      'islim_sales.msisdn'
    )
    ->where([
      ['islim_sales.date_reg', '>=', $starSearch->toDateTimeString()],
      ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()],
      ['islim_sales.sale_type', $type]
    ])
    ->whereIn('islim_sales.type', ['P', 'R'])
    ->whereIn('islim_sales.status', ['A', 'E'])
    ->whereIn('islim_client_netweys.status', ['A', 'S', 'I'])
    ->groupBy('islim_sales.msisdn')
    ->get();

    $this->output->writeln(
      'msisdns a analizar para la fecha ' . $endDate->toDateTimeString() . ': ' . $totalR->count()
    );

    $dataKPI []= [
      'msisdn',
      'Fecha de evento',
      'tipo',
      'tipo dn'
    ];

    //calculando Decays 90
    $decay90 = [];
    foreach ($totalR as $key => $data) {
      if ($data->type == 'P') {
        $timeAlta = Service::getPeriodicity($data->services_id);

        $recharge = Sale::getLastRecharge(
          $data->msisdn,
          $endDate->toDateTimeString()
        );

        $decayDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->date_reg)
        ->addMonths(3)
        ->addDays($timeAlta->days)
        //->addDays(89 + (int)$timeAlta->days)
        ->endOfDay();

        $proM = true;
        if($data->status == 'I'){
          $proM = false;
          $isM = Migrations::select('id')->where('msisdn_old', $data->msisdn)->count();
          if($isM){
            $proM = true;
          }
        }

        if (
          empty($recharge) && $proM &&
          $decayDate->timestamp <= $endDate->timestamp &&
          $decayDate->format('Y-m') == $begin->format('Y-m')
        ) {

          $decay90[] = $data->msisdn;

          $dataKPI []= [
            $data->msisdn,
            $decayDate->toDateTimeString(),
            'Decay90',
            $type
          ];

          $totalR->forget($key);
        }
      }
    }

    $this->output->writeln(
      'Decay90 para el '.$begin->format('Y-m').': '. count($decay90)
    );

    //calculando churn90
    $churn90 = [];
    foreach ($totalR as $key => $data) {
      $recharge = Sale::getLastRecharge(
        $data->msisdn,
        $endDate->toDateTimeString()
      );

      if (!empty($recharge)) {
        $timeAlta = Service::getPeriodicity(
          $recharge->services_id
        );

        $churnDate = Carbon::createFromFormat(
          'Y-m-d H:i:s',
          $recharge->date_reg
        )
        ->addMonths(3)
        ->addDays($timeAlta->days)
        //->addDays(89 + (int)$timeAlta->days)
        ->endOfDay();

        $proM = true;
        if($data->status == 'I'){
          $proM = false;
          $isM = Migrations::select('id')->where('msisdn_old', $data->msisdn)->count();
          if($isM){
            $proM = true;
          }
        }

        if (
          $churnDate->timestamp <= $endDate->timestamp && $proM &&
          $churnDate->format('Y-m') == $begin->format('Y-m')
        ) {

          $churn90[] = $data->msisdn;

          $dataKPI []= [
            $data->msisdn,
            $churnDate->toDateTimeString(),
            'Churn90',
            $type
          ];

          $totalR->forget($key);
        }
      }
    }

    $this->output->writeln(
      'Churn90 para el '.$begin->format('Y-m').': '. count($churn90)
    );

    //calculando active90
    $active90 = [];
    //$contRec = 0;
    foreach($totalR as $key => $data){
        $recharge = Sale::getLastRecharge(
                                $data->msisdn,
                                $endDate->toDateTimeString()
                            );

        $timeAlta = Service::getPeriodicity(
                        !empty($recharge)? $recharge->services_id : $data->services_id
                    );

        $activeDate = Carbon::createFromFormat(
                                'Y-m-d H:i:s',
                                !empty($recharge)? $recharge->date_reg : $data->date_reg
                            )
                            ->addMonths(3)
                            ->addDays($timeAlta->days)
                            //->addDays(89 + (int)$timeAlta->days)
                            ->endOfDay();

        $proM = true;
        if($data->status == 'I'){
          $proM = false;
          $isM = Migrations::select('id')->where('msisdn_old', $data->msisdn)->count();
          if($isM){
            $proM = true;
          }
        }

        if($activeDate->timestamp >= $endDate->timestamp && $proM){
            $active90 []= $data->msisdn;

            $dataKPI []= [
              $data->msisdn,
              $endDate->toDateTimeString(),
              'Active90',
              $type
            ];
            
            $totalR->forget($key);

            //$isRec = HistoryDC2::isReactivation($data->msisdn);

            /*if($isRec){
                $contRec++;
            }*/

            /*HistoryDC2::createRecord(
                            $data->msisdn,
                            'A90',
                            $endDate->toDateTimeString(),
                            $endDate->toDateTimeString(),
                            $isRec
                        );*/
        }
    }

    $this->output->writeln(
      'Active90 para el '.$begin->format('Y-m').': '. count($active90)
    );

    $url = CommonHelpers::saveFile('/public/reports/kpi', 'churn_decay', $dataKPI, 'churn_decay_'.$begin->format('Y-m').'_'.time());

    $this->output->writeln(
      'URL para descargar el csv: '.(String)$url
    );
  }
}
