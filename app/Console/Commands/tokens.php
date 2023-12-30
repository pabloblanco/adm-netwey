<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\TokensInstallments;
use App\ConfigIstallments;
use App\Sale;
use App\User;

class tokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcula tokens para los coordinadores';

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
        $days = explode(',', env('INSTALLMENT_DAYS'));

        $config = ConfigIstallments::where('status', 'A')
                                     ->orderBy('date_reg', 'DESC')
                                     ->first();
        //Fecha de desarrollo
        //$dd = '2019-11-11 01:00:00';
        $dd = date('Y-m-d H:i:s');

        if(!empty($config)){
            $day = $days[$config->end_day - 1];

            //Valida que el dia de ejecucion de la configuración sea igual a hoy
            if($day == date('l', strtotime($dd))){
                //Eliminando los calculos anteriores.
                TokensInstallments::where('status', 'A')->update(['status' => 'T']);

                //Consutando coordinadores.
                $coordinadores = User::select('email')
                                       ->where([
                                            ['status', 'A'],
                                            ['platform', 'coordinador']
                                        ])
                                       ->get();

                //Las ventas tomadas en cuenta son las del dia anterior de ejecución del comando.
                $dateSalesF = strtotime('-1 day', strtotime($dd));

                //Array de coordindores
                $salesC = [];

                //Ciclo para calcular las ventas el numero de semanas configurado
                for($i = 0; $i < $config->week_sales; $i++){
                    //Limite inferior de la fecha
                    $dateSalesI = strtotime('-6 day', $dateSalesF);
                    
                    foreach ($coordinadores as $coordinador){
                        $totalSales = 0;
                        //consultando vendedores
                        $sellers = User::select('email')
                                           ->where([
                                                ['status', 'A'],
                                                ['platform', 'vendor'],
                                                ['parent_email', $coordinador->email]
                                            ])
                                           ->get();

                        $sellers = $sellers->pluck('email');

                        //Calculando ventas
                        $sales = Sale::select('id')
                                       ->where([
                                        ['type', 'P'],
                                        ['date_reg', '>=', date('Y-m-d', $dateSalesI).' 00:00:00'],
                                        ['date_reg', '<=', date('Y-m-d', $dateSalesF).' 23:59:59']
                                       ])
                                       ->whereIn('status', ['A', 'E']);

                        //Si el coordinador tiene vendedores se buscan ventas de el y sus vendedores
                        if(count($sellers))
                            $sales = $sales->where(function($query) use ($coordinador, $sellers){
                                        $query->where('users_email', $coordinador->email)
                                              ->orWhereIn('users_email', $sellers);
                                       });
                        else
                            $sales = $sales->where('users_email', $coordinador->email);

                        $totalSales += $sales->count();

                        $salesC[$coordinador->email] = !empty($salesC[$coordinador->email]) ? $salesC[$coordinador->email] + $totalSales : $totalSales;
                    }

                    //Limite superior de la fecha
                    $dateSalesF = strtotime('-7 day', $dateSalesF);
                }

                foreach ($salesC as $coord => $sales){
                    $tokens = 0;

                    if($sales)
                        $tokens = floor(($sales/$config->week_sales) * ($config->percentage / 100));

                    TokensInstallments::insert([
                        'tokens_cron' => $tokens,
                        'tokens_assigned' => 0,
                        'tokens_available' => 0,
                        'assigned_user' => $coord,
                        'config_id' => $config->id,
                        'date_reg' => $dd,
                        'date_update' => $dd,
                        'status' => 'A'
                    ]);
                }
            }
        }
    }
}
