<?php
namespace App\Console\Commands;

use App\Helpers\CommonHelpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FiberSuspend extends Command
{

  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:fiberSuspend';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Se encarga de mantener Suspendidos en 815 a los clientes suspendidos previamente por Netwey.';

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

    $clients = DB::connection('netwey-r')
      ->table('islim_client_netweys')
      ->select('msisdn')
      ->where([
        ['dn_type', 'F'],
        ['status', 'S'],
        ['date_expire', '<' ,date('Y-m-d')]])
      ->get();

    $suspend = [[]];
    $index = 0;
    $cont = 0;
    $cant = count($clients);
    $date = date('Y-m-d H:i:s');

    $this->output->writeln('Cantidad de clients a revisar: ' . $cant);

    foreach ($clients as $client) {

      $result = CommonHelpers::executeCurl(
        env('URL_API_815') . 'conections-search?msisdn=' . $client->msisdn,
        'POST',
        [
          "Authorization: Bearer " . env('TOKEN_815'),
        ]
      );

      if ($result['success'] == true) {
        if ($result['data']->success == true) {

          $field = $result['data']->data->eightFifteen->object->field;

          foreach ($field as $value) {

            if ($value->attributes->name == 'activa') {
              $cont++;
              if ($value->value == 'True') {
                $this->output->writeln($cont . ' - ' . $cant . '-> Client activo en 815: ' . $client->msisdn);

                if (count($suspend[$index]) == 20) {

                  $suspend[][] = $client->msisdn;
                  $index++;
                } else {
                  $suspend[$index][] = $client->msisdn;
                }
              } else {
                $this->output->writeln($cont . ' - ' . $cant . '-> Client se conserva apagado en 815: ' . $client->msisdn);
              }
            }
          }
        }else{
          Log::alert((String) json_encode($result['data']->data->errores->error));
        }
      }else{
        Log::alert((String) json_encode($result['msg']));
      }
    }
    $this->output->writeln('******************** ');
    $this->output->writeln('Vector de suspension: ' . (String) json_encode($suspend));
    $this->output->writeln('******************** ');

    $cont = 0;

    foreach ($suspend as $batch) {

      $validated = DB::connection('netwey-r')
      ->table('islim_client_netweys')
      ->select('msisdn')
      ->whereIn('msisdn', $batch)
      ->where('status', 'A')
      ->get();

      foreach ($validated as $val) {
        
        if (($matrix_key = array_search($val->msisdn, $batch)) !== false){

          $cont++;
          $this->output->writeln($cont . '-> Cliente Posiblemente Recargó: ' . $val->msisdn);
          unset($batch[$matrix_key]);

        }
      }

      $result = CommonHelpers::executeCurl(
        env('URL_API_815') . 'conections-lock-list?msisdn=' . implode(',', $batch),
        'POST',
        [
          "Authorization: Bearer " . env('TOKEN_815'),
        ]
      );

      if ($result['success'] == true) {
        if ($result['data']->success == true) {

          foreach ($batch as $value) {

            DB::connection('netwey-w')
            ->table('islim_history_fiber_suspend')
            ->insert([
              'msisdn' => $value,
              'date_suspend' => $date,
              'enum' => 'ALERT',
            ]);
          }
        }else{
          Log::alert((String) json_encode($result['data']->data->errores->error));
        }
      }else{
        Log::alert((String) json_encode($result['msg']));
      }
    }
  }
}