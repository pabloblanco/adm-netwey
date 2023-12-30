<?php

namespace App\Console\Commands;

//use App\Sale;
use App\ClientNetwey;
use App\Helpers\APIAltan;
use App\Suspend;
use Carbon\Carbon;
//use App\Helpers\CommonHelpers;
use Illuminate\Console\Command;

class suspendExpiredService extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:suspendExpired';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Suspende clientes con servicio expirado en el mes de ejecución, excluye Telefonia y Fibra';

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
   * Execute the console command. Se ignora dn_type tipo F (fibra) estas se manejan desde la Api 815
   *
   * @return mixed
   */
  public function handle()
  {
    $today = Carbon::now();

    $dateE = $today->copy()->subDays(30);

    $clients = ClientNetwey::select('islim_client_netweys.msisdn')
      ->where([
        ['islim_client_netweys.status', 'A'],
        ['islim_client_netweys.date_expire', '<=', $dateE->format('Y-m-d')],
      ])
      ->whereNotIn('islim_client_netweys.dn_type', ['T', 'F'])
      ->orderBy('islim_client_netweys.date_expire', 'ASC')
      ->limit(env('MAX_SUSPEXP', 1000))
      ->get();

    foreach ($clients as $client) {
      $res = APIAltan::doRequest('suspend', $client->msisdn);
      $res = json_decode($res);

      if (is_object($res)) {
        if ($res->status == 'success') {
          Suspend::insert([
            'msisdn'   => $client->msisdn,
            'response' => (String) json_encode($res),
            'date_reg' => date('Y-m-d H:i:s'),
            'status'   => 'A',
            'from'     => 'E']);

          ClientNetwey::getConnect('W')
            ->where('msisdn', $client->msisdn)
            ->update(['status' => 'S']);

          $this->output->writeln('Suspendio a: ' . $client->msisdn);
        } else {
          if (!empty($res->message) && strripos(strtolower($res->message), 'el suscriptor no esta activo')) {
            Suspend::insert([
              'msisdn'   => $client->msisdn,
              'response' => (String) json_encode($res),
              'date_reg' => date('Y-m-d H:i:s'),
              'status'   => 'E',
              'from'     => 'E']);

            ClientNetwey::getConnect('W')
              ->where('msisdn', $client->msisdn)
              ->update(['status' => 'S']);
          }

          $this->output->writeln('Error al suspender a: ' . $client->msisdn);
        }
      }
    }

    /*$today = Carbon::now();

    $iniMonth = $today->copy()->startOfMonth();

    $dateE = $today->copy()->subDays(7)->endOfDay();

    $dateB = $today->copy()->subMonths(2)->startOfDay();

    $this->output->writeln('fecha inicio: '.$dateB->toDateTimeString());
    $this->output->writeln('fecha fin: '.$dateE->toDateTimeString());

    $sales = Sale::getConnect('R')
    ->select('islim_sales.msisdn')
    ->join(
    'islim_client_netweys',
    'islim_client_netweys.msisdn',
    'islim_sales.msisdn'
    )
    ->where([
    ['islim_client_netweys.status', 'A'],
    ['islim_sales.date_reg', '>=', $dateB->toDateTimeString()],
    ['islim_sales.date_reg', '<=', $dateE->toDateTimeString()],
    ['islim_sales.sale_type', 'H']
    ])
    ->whereIn('islim_sales.status', ['A', 'E'])
    ->whereIn('islim_sales.type', ['P', 'R'])
    ->groupBy('islim_sales.msisdn')
    ->get();

    $countS = 0;

    $xls []= ['MSISDN'];

    foreach ($sales as $sale) {
    $saleD = Sale::getConnect('R')
    ->select(
    'islim_sales.msisdn',
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
    ->where('islim_sales.msisdn', $sale->msisdn)
    ->whereIn('islim_sales.type', ['P', 'R'])
    ->whereIn('islim_sales.status', ['A', 'E'])
    ->orderBy('islim_sales.date_reg', 'DESC')
    ->first();

    $expiredDate = Carbon::createFromFormat(
    'Y-m-d H:i:s',
    $saleD->date_reg
    )
    ->addDays(($saleD->days + 1))
    ->endOfDay();

    if($today->timestamp >= $expiredDate->timestamp && $expiredDate->timestamp >= $iniMonth->timestamp){
    $this->output->writeln('DN: '.$saleD->msisdn);
    $xls []= [$saleD->msisdn];

    /*$res = APIAltan::doRequest('suspend', $saleD->msisdn);
    $res = json_decode($res);

    if(is_object($res)){
    if($res->status == 'success'){
    Suspend::insert([
    'msisdn' => $saleD->msisdn,
    'response' => (String) json_encode($res),
    'date_reg' => date('Y-m-d H:i:s'),
    'status' => 'A',
    'from' => 'E'
    ]);

    ClientNetwey::getConnect('W')
    ->where('msisdn', $saleD->msisdn)
    ->update(['status' => 'S']);
    }else{
    if(!empty($res->message) && strripos(strtolower($res->message), 'el suscriptor no esta activo')){
    Suspend::insert([
    'msisdn' => $saleD->msisdn,
    'response' => (String) json_encode($res),
    'date_reg' => date('Y-m-d H:i:s'),
    'status' => 'E',
    'from' => 'E'
    ]);

    ClientNetwey::getConnect('W')
    ->where('msisdn', $saleD->msisdn)
    ->update(['status' => 'S']);
    }
    }
    }*/

    /*$countS ++;
  }
  }

  $url = CommonHelpers::saveFile('/public/reports', 'consumos', $xls, 'expired'.time());

  $this->output->writeln('URL: '.(String)$url);
  $this->output->writeln('Suspendidos: '.$countS);*/
  }
}
