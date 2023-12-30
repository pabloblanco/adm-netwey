<?php

namespace App\Console\Commands;

use App\ClientNetwey;
use App\DNMigration;
use App\HistoryDC2;
use App\Sale;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class activeClients extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:activeClients';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Verifica las recargas realizadas en un dia y marca los clientes como active90';

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
   * Execute the console command. Se ignora sale_type tipo F (fibra) estas se manejan desde la Api 815
   *
   * @return mixed
   */
  public function handle()
  {
    //$date = date('Y-m-d');
    $date = Carbon::now()->subDay();

    $recharges = Sale::distinct()
      ->select(
        'islim_sales.msisdn',
        'islim_sales.services_id',
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
        ['islim_sales.sale_type', '!=', 'F'],
        [DB::raw("DATE_FORMAT(islim_sales.date_reg, '%Y-%m-%d')"), $date->format('Y-m-d')]])
      ->whereIn('islim_sales.status', ['A', 'E'])
      ->get();

    if ($recharges->count() > 0) {
      $dataActive = $date->format('Y-m-d H:i:s'); //date('Y-m-d H:i:s');

      foreach ($recharges as $recharge) {
        $isRec = HistoryDC2::isReactivation($recharge->msisdn);
        HistoryDC2::createRecord(
          $recharge->msisdn,
          'A90',
          $dataActive,
          $dataActive,
          $isRec
        );

        //La fecha de vencimiento de los DNs de tipo fibra se calcula en la api de 815

        $dateExp = Carbon::createFromFormat('Y-m-d', $date->format('Y-m-d'))->startOfDay();
        $dateExp = $dateExp->addDays((int) $recharge->days + 1)->format('Y-m-d');

        $dateCD30 = Carbon::createFromFormat('Y-m-d', $date->format('Y-m-d'))->startOfDay();
        $dateCD30 = $dateCD30->addDays((int) $recharge->days + 29)->format('Y-m-d');

        $dateCD90 = Carbon::createFromFormat('Y-m-d', $date->format('Y-m-d'))->startOfDay();
        $dateCD90 = $dateCD90->addDays((int) $recharge->days + 89)->format('Y-m-d');

        ClientNetwey::getConnect('W')
          ->where('msisdn', $recharge->msisdn)
          ->update([
            'date_expire' => $dateExp,
            'date_cd30' => $dateCD30,
            'date_cd90' => $dateCD90,
            'type_cd90' => 'C'
          ]);

        DNMigration::deleteDN($recharge->msisdn);

      }
    }

  }
}
