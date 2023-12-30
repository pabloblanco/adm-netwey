<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\MetricsDasboard;
//use App\MetricsDasboardB;
use App\Organization;
use App\Sale;

class dashboard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:dashboard';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta calculo de metricas(ventas, recargas) del dia que se ejecute - 1';

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
        //$dataStart = '2019-04-01 00:00:00';
        //$dateEnd = date('Y-m-d');
        $lastHour = (3600 * 23) + 3599;

        //Organizaciones activas
        $orgs = Organization::select('id')->where('status', 'A')->get();

        //Cantidad de dias a calcular
        //$cicles = ((((strtotime($dateEnd) - strtotime($dataStart)) / 60) / 60) / 24);

        //Fecha que se esta calculando
        $dataCurrent = strtotime('-1 day', strtotime(date('Y-m-d')));
        //$dataCurrent = strtotime($dataStart);

        //for($i = 0; $i < $cicles; $i++){
            $totalU = 0;
            $totalAU = 0;
            $totalR = 0;
            $totalAR = 0;

            $db = date('Y-m-d H:i:s', $dataCurrent);
            $de = date('Y-m-d H:i:s', ($dataCurrent + $lastHour));

            $cv = DB::raw('COUNT(islim_sales.id) as total_u');
            $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

            foreach ($orgs as $org){
                $notSaveUp = true;
                $notSaveRe = true;
                //Calculando altas
                $altas = Sale::select($cv, $ta)
                             ->join('islim_users', 'islim_users.email', 'islim_sales.users_email')
                             ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
                             ->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
                             ->where([
                                ['islim_clients.name', '!=', 'TEMPORAL'],
                                ['islim_clients.last_name', '!=', 'TEMPORAL'],
                                ['islim_client_netweys.status','A'],
                                ['islim_sales.date_reg', '>=', $db],
                                ['islim_sales.date_reg', '<=', $de],
                                ['islim_users.id_org', $org->id],
                                ['islim_sales.type', 'P']
                             ])
                             ->whereIn('islim_sales.status', ['A', 'E'])
                             ->first();

                if(!empty($altas)){
                    $amountorg = !empty($altas->total_mount) ? $altas->total_mount : 0;
                    $totalU += $altas->total_u;
                    $totalAU += !empty($altas->total_mount) ? $altas->total_mount : 0;

                    //Esto calcula el monto total de ventas para los (Retail)
                    $sub = DB::raw('(SELECT id FROM islim_sales as b WHERE (b.status = "A" OR b.status = "E") AND b.type = "P" AND b.unique_transaction = islim_sales.unique_transaction AND islim_users.id_org = '.$org->id.')');

                    $ventas = Sale::select($ta)
                             ->join('islim_users', 'islim_users.email', 'islim_sales.users_email')
                             ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
                             ->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
                             ->where([
                                ['islim_clients.name', '!=', 'TEMPORAL'],
                                ['islim_clients.last_name', '!=', 'TEMPORAL'],
                                ['islim_client_netweys.status','A'],
                                ['islim_sales.date_reg', '>=', $db],
                                ['islim_sales.date_reg', '<=', $de],
                                ['islim_users.id_org', $org->id],
                                ['islim_sales.type', 'V']
                             ])
                             ->whereIn('islim_sales.status', ['A', 'E'])
                             ->whereNotNull($sub)
                             ->first();

                    if(!empty($ventas)){
                        $totalAU += !empty($ventas->total_mount) ? $ventas->total_mount : 0;
                        $amountorg += !empty($ventas->total_mount) ? $ventas->total_mount : 0;
                    }

                    //Guardando altas por organizacion
                    if(!empty($altas->total_u)){
                        $metric = new MetricsDasboard;
                        $metric->date = date('Y-m-d', $dataCurrent);
                        $metric->quantity = $altas->total_u;
                        $metric->amount = $amountorg;
                        $metric->id_org = $org->id;
                        $metric->type = 'U';
                        $metric->save();
                        $notSaveUp = false;
                    }
                }

                //Calculando recargas
                $recargas = Sale::select($cv, $ta)
                             ->leftJoin('islim_users', 'islim_users.email', 'islim_sales.users_email')
                             ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
                             ->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
                             ->where([
                                ['islim_clients.name', '!=', 'TEMPORAL'],
                                ['islim_clients.last_name', '!=', 'TEMPORAL'],
                                ['islim_client_netweys.status','A'],
                                ['islim_sales.date_reg', '>=', $db],
                                ['islim_sales.date_reg', '<=', $de],
                                ['islim_users.id_org', $org->id],
                                ['islim_sales.type', 'R']
                             ])
                             ->whereIn('islim_sales.status', ['A', 'E'])
                             ->first();

                if(!empty($recargas)){
                    $totalR += $recargas->total_u;
                    $totalAR += !empty($recargas->total_mount) ? $recargas->total_mount : 0;

                    //Guardando recargas por organizacion
                    if(!empty($recargas->total_mount)){
                        $metric = new MetricsDasboard;
                        $metric->date = date('Y-m-d', $dataCurrent);
                        $metric->quantity = $recargas->total_u;
                        $metric->amount = $recargas->total_mount;
                        $metric->id_org = $org->id;
                        $metric->type = 'R';
                        $metric->save();
                        $notSaveRe = false;
                    }
                }
            }

            //Si no hubo altas guardamos 0 en ese dia
            if($notSaveUp){
                $metric = new MetricsDasboard;
                $metric->date = date('Y-m-d', $dataCurrent);
                $metric->quantity = 0;
                $metric->amount = 0;
                $metric->type = 'U';
                $metric->save();
            }

            //Recargas no relacionadas a un vendedor
            $recargasn = Sale::select($cv, $ta)
                             ->leftJoin('islim_users', 'islim_users.email', 'islim_sales.users_email')
                             ->where([
                                ['islim_sales.date_reg', '>=', $db],
                                ['islim_sales.date_reg', '<=', $de],
                                ['islim_sales.type', 'R']
                             ])
                             ->whereIn('islim_sales.status', ['A', 'E'])
                             ->where(function($query){
                                $query->whereNull('islim_sales.users_email')
                                      ->orWhereNull('islim_users.id_org');
                             })
                             //->whereNull('islim_sales.users_email')
                             ->first();

            if(!empty($recargasn)){
                $totalR += $recargasn->total_u;
                $totalAR += !empty($recargasn->total_mount) ? $recargasn->total_mount : 0;

                //Guardando recargas por organizacion
                if(!empty($recargasn->total_mount)){
                    $metric = new MetricsDasboard;
                    $metric->date = date('Y-m-d', $dataCurrent);
                    $metric->quantity = $recargasn->total_u;
                    $metric->amount = $recargasn->total_mount;
                    $metric->type = 'R';
                    $metric->save();
                    $notSaveRe = false;
                }
            }

            if($notSaveRe){
                $metric = new MetricsDasboard;
                $metric->date = date('Y-m-d', $dataCurrent);
                $metric->quantity = 0;
                $metric->amount = 0;
                $metric->type = 'R';
                $metric->save();
            }

            //Altas para usuarios que no pertenecen a una org
            $altas = Sale::select($cv, $ta)
                             ->join('islim_users', 'islim_users.email', 'islim_sales.users_email')
                             ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
                             ->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
                             ->where([
                                ['islim_clients.name', '!=', 'TEMPORAL'],
                                ['islim_clients.last_name', '!=', 'TEMPORAL'],
                                ['islim_client_netweys.status','A'],
                                ['islim_sales.date_reg', '>=', $db],
                                ['islim_sales.date_reg', '<=', $de],
                                ['islim_sales.type', 'P']
                             ])
                             ->whereNull('islim_users.id_org')
                             ->whereIn('islim_sales.status', ['A', 'E'])
                             ->first();

            if(!empty($altas) && $altas->total_u){
                $sub = DB::raw('(SELECT id FROM islim_sales as b WHERE (b.status = "A" OR b.status = "E") AND b.type = "P" AND b.unique_transaction = islim_sales.unique_transaction AND islim_users.id_org IS NULL)');

                $ventas = Sale::select($ta)
                         ->join('islim_users', 'islim_users.email', 'islim_sales.users_email')
                         ->where([
                            ['islim_sales.date_reg', '>=', $db],
                            ['islim_sales.date_reg', '<=', $de],
                            ['islim_sales.type', 'V']
                         ])
                         ->whereNull('islim_users.id_org')
                         ->whereIn('islim_sales.status', ['A', 'E'])
                         ->whereNotNull($sub)
                         ->first();

                $totala = !empty($altas->total_mount) ? $altas->total_mount : 0;
                $totala += !empty($ventas->total_mount) ? $ventas->total_mount : 0;


                $metric = new MetricsDasboard;
                $metric->date = date('Y-m-d', $dataCurrent);
                $metric->quantity = $altas->total_u;
                $metric->amount = $totala;
                $metric->type = 'U';
                $metric->save();
            }

            //echo 'Ventas totales: '.$totalU.' Monto Total en ventas: '.$totalAU.' Recargas totales: '.$totalR.' Monto en recargas: '.$totalAR;

            //$dataCurrent = strtotime('+1 day', $dataCurrent);
        //}
    }
}