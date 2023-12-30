<?php

namespace App\Console\Commands;

use App\ClientNetwey;
use App\SMSNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class smsExpired extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:sendSMSExpired';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Envía sms a clientes de fibra cuyo servicio esta por expirar, solo envía los que el servicio expira al siguiente día de la ejecución de la tarea programada.';

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
    $date = Carbon::now()->addDay();

    $clients = ClientNetwey::getConnect('R')
                            ->select(
                              'islim_client_netweys.msisdn',
                              'islim_client_netweys.date_expire',
                              'islim_clients.phone_home'
                            )
                            ->join(
                              'islim_clients',
                              'islim_clients.dni',
                              'islim_client_netweys.clients_dni'
                            )
                            ->where([
                              ['islim_client_netweys.date_expire', $date->format('Y-m-d')],
                              ['islim_client_netweys.status', 'A'],
                              ['islim_client_netweys.dn_type', 'F']
                            ])
                            ->get();

    foreach($clients as $client){
      SMSNotification::saveSms([
        'msisdn' => $client->msisdn
      ]);
    }
  }
}