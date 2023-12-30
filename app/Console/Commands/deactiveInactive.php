<?php

namespace App\Console\Commands;

use App\Deactive;
use Carbon\Carbon;
//use App\HistoryDC2;
use App\ClientNetwey;
use App\Helpers\APIAltan;
use App\Helpers\CommonHelpers;
use Illuminate\Console\Command;

class deactiveInactive extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:deactiveInactive';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Da de baja clientes de telefonía inactivos (Churn y decay).';

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
  public function handle(){
    $dns = ClientNetwey::getConnect('R')
                        ->select(
                          'islim_client_netweys.msisdn',
                          'islim_services.title',
                          'islim_client_netweys.service_id',
                          'islim_client_netweys.date_expire',
                          'islim_client_netweys.date_reg',
                          'islim_periodicities.days'
                        )
                        ->join(
                          'islim_services',
                          'islim_client_netweys.service_id',
                          'islim_services.id'
                        )
                        ->join(
                          'islim_periodicities',
                          'islim_services.periodicity_id',
                          'islim_periodicities.id'
                        )
                        ->where([
                        ['islim_client_netweys.status', '!=', 'T'],
                        ['islim_client_netweys.dn_type', '=', 'T'],
                        ['islim_client_netweys.date_expire', '<', date('Y-m-d H:i:s')]
                        ])
                        ->orderBy('islim_client_netweys.date_expire', 'ASC')
                        ->get();
    
    //$count = 0;
    foreach ($dns as $dn) {
      $dateDeactive = Carbon::createFromFormat('Y-m-d', $dn->date_expire)
                            ->addDays(90);

      if($dateDeactive->lessThan(Carbon::now())){
        if(strlen($dn->msisdn) == 10){
          $res = APIAltan::doRequest('deactivate', $dn->msisdn);
          $res = json_decode($res);

          if(is_object($res)){
            if($res->status == 'success' || (!empty($res->message) && strripos(strtolower($res->message), 'subscriber does not exist'))){
              Deactive::insert([
                'msisdn' => $dn->msisdn,
                'response' => (String) json_encode($res),
                'date_reg' => date('Y-m-d H:i:s'),
                'date_inactive' => $dateDeactive->format('Y-m-d'),
                'status' => 'A',
                'from' => 'CD'
              ]);

              ClientNetwey::getConnect('W')
                            ->where('msisdn', $dn->msisdn)
                            ->update(['status' => 'T']);

              //$this->output->writeln('Dando de baja a: '.$dn->msisdn);
            }else{
              Deactive::insert([
                'msisdn' => $dn->msisdn,
                'response' => (String) json_encode($res),
                'date_reg' => date('Y-m-d H:i:s'),
                'date_inactive' => $dateDeactive->format('Y-m-d'),
                'status' => 'E',
                'from' => 'CD'
              ]);
              //$this->output->writeln('Error al dar de baja a: '.$dn->msisdn);
            }
          }
        }else{
          Deactive::insert([
            'msisdn' => $dn->msisdn,
            'response' => null,
            'date_reg' => date('Y-m-d H:i:s'),
            'date_inactive' => $dateDeactive->format('Y-m-d'),
            'status' => 'A',
            'from' => 'CD'
          ]);
  
          ClientNetwey::getConnect('W')
                        ->where('msisdn', $dn->msisdn)
                        ->update(['status' => 'T']);
        }

        //$count++;
      }
    }
  }
}