<?php

namespace App\Console\Commands;

use App\ClientNetwey;
use App\Helpers\APIAltan;
use App\HistoryDC2;
use App\Suspend;
use Illuminate\Console\Command;

class suspendInactive extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:suspendInactive';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Suspende clientes inactivos (Churn y decay)';

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
    $skip = 0;
    $count = 0;

    do {
      $dns = HistoryDC2::getConnect('R')
        ->select('islim_history_dc_2.msisdn')
        ->join(
          'islim_client_netweys',
          'islim_client_netweys.msisdn',
          'islim_history_dc_2.msisdn'
        )
        ->where([
          ['islim_history_dc_2.status', 'LA'],
          ['islim_client_netweys.status', 'A']])
        ->whereNotIn('islim_client_netweys.dn_type', ['T', 'F'])
        ->where(function ($q) {
          $q->where('islim_history_dc_2.type', 'C90')
            ->orWhere('islim_history_dc_2.type', 'D90');
        })
        ->orderBy('islim_history_dc_2.date_event', 'ASC')
        ->skip($skip)
        ->limit(env('DAILY_SUSPEND', 267))
        ->get();

      foreach ($dns as $dn) {
        $res = APIAltan::doRequest('suspend', $dn->msisdn);
        $res = json_decode($res);

        if (is_object($res)) {
          if ($res->status == 'success') {
            Suspend::insert([
              'msisdn' => $dn->msisdn,
              'response' => (String) json_encode($res),
              'date_reg' => date('Y-m-d H:i:s'),
              'status' => 'A',
              'from' => 'I']);

            ClientNetwey::getConnect('W')
              ->where('msisdn', $dn->msisdn)
              ->update(['status' => 'S']);

            $count++;

            $this->output->writeln('Suspendio a: ' . $dn->msisdn);

            if ($count >= env('DAILY_SUSPEND', 267)) {
              break;
            }

          } else {
            if (!empty($res->message) && strripos(strtolower($res->message), 'el suscriptor no esta activo')) {
              Suspend::insert([
                'msisdn' => $dn->msisdn,
                'response' => (String) json_encode($res),
                'date_reg' => date('Y-m-d H:i:s'),
                'status' => 'E',
                'from' => 'I']);

              ClientNetwey::getConnect('W')
                ->where('msisdn', $dn->msisdn)
                ->update(['status' => 'S']);
            }

            $this->output->writeln('Error al suspender a: ' . $dn->msisdn);
          }
        }
      }

      $this->output->writeln('Suspendidos: ' . $count);

      $skip += env('DAILY_SUSPEND', 267) - $count;
    } while ($count < env('DAILY_SUSPEND', 267) || count($dns) == 0);
  }
}
