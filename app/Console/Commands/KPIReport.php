<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Sale;
use App\Service;
use App\ClientNetweyBackup;
use App\ClientNetwey;
use App\MetricsBi;
use App\MetricsBiNew;
use App\HistoryDC;
use App\HistoryDCBackup;
use Carbon\Carbon;

class KPIReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kpi';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta cron para calcular kpis';

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
        //$dateRegM = Carbon::now()->subMonth()->format('Y-m');
        $dateRegM = '2020-06';

        $mc = MetricsBiNew::select('id')
                         ->where([
                            [DB::raw("DATE_FORMAT(date_reg, '%Y-%m')"), $dateRegM],
                            ['status', 'A']
                         ])
                         ->first();

        if(!empty($mc)){
            $mc->status = 'I';
            $mc->save();
        }

        $lastMetrcis = MetricsBiNew::where('status','A')
                                  ->orderBy('date_reg', 'DESC')
                                  ->first();

        
        if(!empty($lastMetrcis)){
            //Reiniciando los tags de todos los clientes
            //ClientNetwey::where('tag', '!=', 'C90')->update(['tag' => 'BT']);

            $this->output->writeln(
                'BoP: '.$lastMetrcis->EOP, 
                false
            );
            
            $month = $dateRegM;
            //Primer dia del mes
            $begin = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            //ultimo dia del mes
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            //Buscando las altas del mes que se esta calculando
            $ga = Sale::distinct()->select('islim_sales.msisdn')
                        ->join(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_sales.msisdn'
                        )
                        ->where([
                            ['islim_sales.type', 'P'],
                            ['islim_sales.date_reg', '>=', $begin->toDateTimeString()],
                            ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()]
                        ])
                        ->whereIn('islim_sales.status',['A', 'E'])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->get();

            $this->output->writeln(
                'GA: '.$ga->count(), 
                false
            );

            //Calculando churn30
            $arrch30 = [];
            $dateReg = $endDate->copy()->subMonth()->toDateTimeString();
            $dateRegB = $endDate->copy()->subMonths(2)->toDateTimeString();
            $recharges = Sale::distinct()
                            ->select('msisdn')
                            ->where([
                                ['type','R'],
                                ['date_reg', '<=', $dateReg],
                                ['date_reg', '>=', $dateRegB]
                            ])
                            ->whereIn('status', ['A', 'E'])
                            ->get();

            foreach($recharges as $recharge){
                $lastRecharge = Sale::select(
                                        'islim_sales.msisdn',
                                        'islim_periodicities.days',
                                        'islim_sales.date_reg'
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
                                        ['islim_sales.msisdn', $recharge->msisdn],
                                        ['islim_sales.type', 'R'],
                                        ['islim_sales.date_reg','<=',$endDate->toDateTimeString()]
                                     ])
                                     ->whereIn('islim_sales.status',['A', 'E'])
                                     ->orderBy('islim_sales.date_reg', 'DESC')
                                     ->first();

                $lastDate = Carbon::createFromFormat('Y-m-d H:i:s', $lastRecharge->date_reg)
                                    ->addMonth()
                                    ->addDays($lastRecharge->days)
                                    ->endOfDay();

                if($lastDate->timestamp <= $endDate->timestamp){
                    $arrch30 []= $recharge->msisdn;

                    HistoryDCBackup::createRecord(
                                        $recharge->msisdn,
                                        'C30',
                                        $lastDate->toDateTimeString()
                                    );
                }
            }

            $this->output->writeln(
                'C30: '.count($arrch30), 
                false
            );

            //Actualizando tag de los clientes churn
            $step = 400;
            if(count($arrch30) > $step){
                $v = ceil(count($arrch30) / $step);
                $t = 0;
                for($qq = 0; $qq < $v; $qq++){
                    $dup = array_slice($arrch30, $t, $step);

                    //ClientNetwey::whereIn('msisdn', $dup)->update(['tag' => 'C30']);

                    $t += 400;
                }
            }elseif(count($arrch30)){
                //ClientNetwey::whereIn('msisdn', $arrch30)->update(['tag' => 'C30']);
            }
            
            //Esto se va a borrar
            //HistoryDCBackup::processChurn30($arrch30, $endDate->toDateTimeString());

            //Calculando los Decay 90
            //quitando 3 meses a la fecha que se esta consultando
            $dateReg = $endDate->copy()->subMonths(3)->toDateTimeString();

            //Sub-consulta para filtrar altas que nunca hayan hecho una recarga
            $cr = DB::raw("(select count(tc.msisdn) from islim_sales as tc where tc.msisdn = islim_sales.msisdn  and tc.type = 'R' and tc.status in ('A', 'E'))");

            //Buscando las altas que pueden ser decay
            $altas = Sale::distinct()->select(
                            'islim_sales.msisdn',
                            'islim_sales.services_id',
                            'islim_sales.date_reg'
                        )
                        ->join(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_sales.msisdn'
                        )
                        ->where([
                            ['islim_sales.date_reg', '<=', $dateReg], 
                            ['islim_sales.type', 'P'],
                            [$cr, '=', 0]
                        ])
                        ->whereIn('islim_sales.status',['A', 'E'])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->groupBy('islim_sales.msisdn')
                        ->get();

            $decay90T = 0;
            $arrDecay90 = [];
            foreach ($altas as $alta){
                //Consultando dias del plan que tiene contradado el cliente
                $timeAlta = Service::select(
                                        'periodicity_id', 
                                        'periodicity', 
                                        'days'
                                     )
                                     ->join(
                                        'islim_periodicities',
                                        'islim_periodicities.id',
                                        'islim_services.periodicity_id'
                                     )
                                     ->where('islim_services.id', $alta->services_id)
                                     ->first();
                
                //Vencimiento de la fecha de gracia en la que un cliente puede pasar a ser decay
                $decayDate = Carbon::createFromFormat('Y-m-d H:i:s', $alta->date_reg)
                                     ->addMonths(3)
                                     ->addDays($timeAlta->days)
                                     ->endOfDay();

                //Si la fecha para ser decay es menor o igual a la actual contamos al cliente como decay
                if($decayDate->timestamp <= $endDate->timestamp){
                    $decay90T ++;
                    $arrDecay90 []= $alta->msisdn;

                    HistoryDCBackup::createRecord(
                                        $alta->msisdn, 
                                        'D90', 
                                        $decayDate->toDateTimeString()
                                    );
                }
            }

            //Actualizando clientes decay
            if($decay90T > 0){
                $lastMetrcisD90 = MetricsBiNew::orderBy('date_reg', 'DESC')
                                            ->where([
                                                ['status', 'A'],
                                                ['date_reg', '<', $begin->toDateTimeString()]
                                            ])
                                            ->sum('D90');

                $step = 400;
                if(count($arrDecay90) > $step){
                    $v = ceil(count($arrDecay90) / $step);
                    $t = 0;
                    for($qq = 0; $qq < $v; $qq++){
                        $dup = array_slice($arrDecay90, $t, $step);

                        //ClientNetwey::whereIn('msisdn', $dup)
                                  //->update(['tag' => 'D90']);

                        $t += 400;
                    }
                }elseif(count($arrDecay90)){
                    //ClientNetwey::whereIn('msisdn', $arrDecay90)
                                  //->update(['tag' => 'D90']);
                }
                
                $decay90 = $decay90T - $lastMetrcisD90;
            }
            
            $this->output->writeln(
                'D90: '.$decay90, 
                false
            );

            //Calculando Active 90
            $dateA90 = $endDate->copy()->subMonths(4)->startOfDay()->toDateTimeString();

            //Consultando todas las altas y recargas que se dieron desde hace 3 meses hasta el mes actual
            $queryActive90 = Sale::select(
                                    'islim_sales.msisdn',
                                    'islim_sales.services_id',
                                    'islim_sales.date_reg'
                                  )
                                  ->join(
                                    'islim_client_netweys',
                                    'islim_client_netweys.msisdn',
                                    'islim_sales.msisdn'
                                  )
                                  ->whereIn('islim_sales.status', ['A', 'E'])
                                  ->where([
                                    ['islim_sales.date_reg', '>=', $dateA90], 
                                    ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()]
                                  ])
                                  ->whereIn('islim_sales.type',['R','P'])
                                  ->whereIn('islim_client_netweys.status',['A', 'S'])
                                  //->orderBy('islim_sales.msisdn', 'DESC')
                                  ->orderBy('islim_sales.date_reg', 'DESC')
                                  //->orderBy('islim_sales.id', 'DESC')
                                  ->get();

            $arrActive90 = []; //Array que va a guardar los msisdn que son active90
            //Fecha limite inferior en la que un cliente se puede contar como active90
            $activeDate = $endDate->copy()->subMonths(3)->startOfDay()->timestamp;

            foreach ($queryActive90 as $client) {
                if(!in_array($client->msisdn, $arrActive90)){
                    $timeAlta = Service::select('periodicity_id', 'periodicity', 'days')
                                         ->join(
                                            'islim_periodicities', 
                                            'islim_periodicities.id', 
                                            'islim_services.periodicity_id'
                                         )
                                         ->where('islim_services.id', $client->services_id)
                                         ->first();

                    $rDate = strtotime($client->date_reg);

                    //Sumando los dias del plan en caso de que no se active90 inmediatamente por la recarga o alta
                    if($rDate <= $activeDate)
                        $rDate = Carbon::createFromFormat(
                                    'Y-m-d H:i:s', 
                                    $client->date_reg
                                 )->addDays($timeAlta->days)->timestamp;

                    //Verificando si el cliente es active90
                    if($activeDate <= $rDate && !in_array($client->msisdn, $arrDecay90)){
                        $arrActive90 []= $client->msisdn;

                        HistoryDCBackup::createRecord(
                                        $client->msisdn, 
                                        'A90', 
                                        date('Y-m-d H:i:s', $rDate)
                                    );
                    }
                }
            }

            $this->output->writeln(
                'active90: '.count($arrActive90), 
                false
            );

            //Actualizando tags active90 en los clientes
            $step = 400;
            if(count($arrActive90) > $step){
                $v = ceil(count($arrActive90) / $step);
                $t = 0;
                for($qq = 0; $qq < $v; $qq++){
                    $dup = array_slice($arrActive90, $t, $step);

                    //ClientNetwey::whereIn('msisdn', $dup)
                                //->update(['tag' => 'A90']);

                    $t += 400;
                }
            }elseif(count($arrActive90)){
                //ClientNetwey::whereIn('msisdn', $arrActive90)
                                //->update(['tag' => 'A90']);
            }
            

            //Calculando clientes churn90
            //Altas totales desde el inicio de los tiempos hasta el ultimo dia del mes seleccionado
            $TotalUp = Sale::distinct()->select('islim_sales.msisdn')
                            ->join(
                                'islim_client_netweys',
                                'islim_client_netweys.msisdn',
                                'islim_sales.msisdn')
                            ->where([
                                ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()],
                                ['islim_sales.type', 'P']
                            ])
                            ->whereIn('islim_sales.status',['A', 'E'])
                            ->whereIn('islim_client_netweys.status',['A', 'S'])
                            ->get();

            $lastMetrcisC90 = MetricsBiNew::orderBy('date_reg', 'DESC')
                                            ->where([
                                                ['status', 'A'],
                                                ['date_reg', '<', $begin->toDateTimeString()]
                                            ])
                                            ->sum('C90');

            //Actualizando tag de los clientes churn
            $arrc90 = array_diff($TotalUp->pluck('msisdn')->toArray(), $arrDecay90);
            $arrc90 = array_diff($arrc90, $arrActive90);
            //$arrc90 = array_diff($arrc90, $arrch30);

            $c90 = $TotalUp->count() - ($decay90T + count($arrActive90) + $lastMetrcisC90);
            //$c90 = count($arrc90) - $lastMetrcisC90;

            $this->output->writeln(
                'churn90: '.$c90, 
                false
            );

            $step = 400;
            if(count($arrc90) > $step){
                $v = ceil(count($arrc90) / $step);
                $t = 0;
                for($qq = 0; $qq < $v; $qq++){
                    $dup = array_slice($arrc90, $t, $step);

                    //ClientNetwey::whereIn('msisdn', $dup)->update(['tag' => 'C90']);

                    $t += 400;
                }
            }elseif(count($arrc90)){
                //ClientNetwey::whereIn('msisdn', $arrc90)->update(['tag' => 'C90']);
            }
            
            HistoryDCBackup::processChurn($arrc90, $endDate->toDateTimeString());

            $eop = ($lastMetrcis->EOP + $ga->count()) - ($c90 + $decay90);
            
            $this->output->writeln(
                'EoP: '.$eop, 
                false
            );

            $aop = ($eop + $lastMetrcis->EOP) / 2;

            $this->output->writeln(
                'AoP: '.$aop, 
                false
            );

            //Array de kpis
            $saveData = [
                'A90' => count($arrActive90),
                'C90' => $c90,
                'C30' => count($arrch30),
                'D90' => $decay90,
                'BOP' => $lastMetrcis->EOP,
                'EOP' => $eop,
                'AOP' => $aop,
                'date_reg' => date('Y-m-d H:i:s', strtotime($dateRegM)),
                'status' => 'A'
            ];

            MetricsBiNew::create($saveData)->save();
        }else{
            //Aqui solo va a entrar la primera vez que se ejecute el cron para calcular de forma concentrada todos los kpis de año 2018 en diciembre de ese mismo año, despues de ejecutada la primera vez esta parte del codigo se puede borrar
            //ClientNetwey::whereIn('status', ['A','S'])->update(['tag' => 'BT']);

            $month = "2018-12";
            //Primer dia del mes
            $begin = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            //ultimo dia del mes
            $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

            //Altas totales
            $ga = Sale::distinct()->select(
                            'islim_sales.msisdn', 
                            'islim_sales.services_id', 
                            'islim_sales.date_reg'
                        )
                        ->join(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_sales.msisdn'
                        )
                        ->where([
                            ['islim_sales.type', 'P'], 
                            ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()]
                        ])
                        ->whereIn('islim_sales.status',['A', 'E'])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->get();

            $this->output->writeln(
                'Altas totales hasta 31 de diciembre 2018: '.$ga->count(), 
                false
            );

            //Calculando decay
            //$decay90 = 0;
            $arrDecay90 = [];
            $arrch30 = [];
            foreach ($ga as $alta){
                $timeAlta = Service::select('periodicity_id', 'periodicity', 'days')
                                     ->join(
                                        'islim_periodicities',
                                        'islim_periodicities.id',
                                        'islim_services.periodicity_id'
                                     )
                                     ->where('islim_services.id', $alta->services_id)
                                     ->first();

                $decayDate = Carbon::createFromFormat('Y-m-d H:i:s', $alta->date_reg)
                                    ->addMonths(3)
                                    ->addDays($timeAlta->days)
                                    ->endOfDay();

                $isDecay = Sale::select('msisdn')
                                 ->where([['msisdn', $alta->msisdn], ['type', 'R']])
                                 ->whereIn('status',['A', 'E'])
                                 ->count();

                if($isDecay == 0 && ($decayDate->timestamp <= strtotime('2018-12-31 23:59:59'))){
                    //$decay90 ++;
                    $arrDecay90 []= $alta->msisdn;

                    HistoryDCBackup::createRecord(
                                        $alta->msisdn, 
                                        'D90', 
                                        $decayDate->toDateTimeString()
                                    );
                }elseif($isDecay){
                    //calculando churn30
                    $lastRecharge = Sale::select(
                                            'islim_sales.msisdn',
                                            'islim_periodicities.days',
                                            'islim_sales.date_reg'
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
                                            ['islim_sales.msisdn', $alta->msisdn],
                                            ['islim_sales.type', 'R'],
                                            ['islim_sales.date_reg', '>=', '2018-10-01 00:00:00'],
                                            ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()]
                                         ])
                                         ->whereIn('islim_sales.status',['A', 'E'])
                                         ->orderBy('islim_sales.date_reg', 'DESC')
                                         ->first();

                    if(!empty($lastRecharge)){
                        $lastDate = Carbon::createFromFormat('Y-m-d H:i:s', $lastRecharge->date_reg)
                                            ->addMonth()
                                            ->addDays($lastRecharge->days)
                                            ->endOfDay();

                        if($lastDate->timestamp <= strtotime('2018-12-31 23:59:59')){
                            $arrch30 []= $alta->msisdn;

                            HistoryDCBackup::createRecord(
                                        $alta->msisdn,
                                        'C30',
                                        $lastDate->toDateTimeString()
                                    );
                        }
                    }
                }
            }

            if(count($arrch30)){
                //Esto se va a borrar
                //HistoryDCBackup::processChurn30($arrch30, $endDate->toDateTimeString());

                //ClientNetwey::whereIn('msisdn', $arrch30)
                            //->update(['tag' => 'C30']);
            }

            //Actualizando clientes decay
            if(count($arrDecay90)){
                //ClientNetwey::whereIn('msisdn', $arrDecay90)
                            //->update(['tag' => 'D90']);
            }

            $this->output->writeln(
                'decay: '.count($arrDecay90), 
                false
            );

            $this->output->writeln(
                'churn30: '.count($arrch30), 
                false
            );

            $base = $ga->count() - count($arrDecay90);

            $this->output->writeln(
                'Base: '.$base, 
                false
            );

            $dateA90 = $endDate->copy()
                               ->subMonths(4)
                               ->startOfDay()
                               ->toDateTimeString();

            $queryActive90 = Sale::select(
                                    'islim_sales.msisdn', 
                                    'islim_sales.services_id', 
                                    'islim_sales.date_reg'
                                  )
                                  ->join(
                                    'islim_client_netweys',
                                    'islim_client_netweys.msisdn',
                                    'islim_sales.msisdn'
                                  )
                                  ->whereIn('islim_sales.status', ['A', 'E'])
                                  ->where([
                                    ['islim_sales.date_reg', '>=', $dateA90], 
                                    ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()]
                                  ])
                                  ->whereIn('islim_sales.type',['R','P'])
                                  ->whereIn('islim_client_netweys.status',['A', 'S'])
                                  //->orderBy('islim_sales.msisdn', 'DESC')
                                  //->orderBy('islim_sales.date_reg', 'DESC')
                                  ->orderBy('islim_sales.id', 'DESC')
                                  ->get();

            $arrActive90 = [];
            $activeDate = $endDate->copy()->subMonths(3)->timestamp;

            foreach ($queryActive90 as $client) {
                if(!in_array($client->msisdn, $arrActive90)){
                    $timeAlta = Service::select('periodicity_id', 'periodicity', 'days')
                                         ->join(
                                            'islim_periodicities', 
                                            'islim_periodicities.id',
                                            'islim_services.periodicity_id'
                                         )
                                         ->where('islim_services.id', $client->services_id)
                                         ->first();

                    $rDate = strtotime($client->date_reg);

                    if($rDate < $activeDate)
                        $rDate = Carbon::createFromFormat(
                                    'Y-m-d H:i:s', 
                                    $client->date_reg
                                 )->addDays($timeAlta->days)
                                  ->timestamp;

                    if($activeDate <= $rDate){
                        $arrActive90 []= $client->msisdn;

                        HistoryDCBackup::createRecord(
                                        $client->msisdn, 
                                        'A90', 
                                        date('Y-m-d H:i:s', $rDate)
                                    );
                    }
                }
            }

            //ClientNetwey::whereIn('msisdn', $arrActive90)
                                //->update(['tag' => 'A90']);

            //Buscando usuarios churn
            $arrc90 = array_diff($ga->pluck('msisdn')->toArray(), $arrDecay90);
            $arrc90 = array_diff($arrc90, $arrActive90);
            //$arrc90 = array_diff($arrc90, $arrch30);
            $c90 = $base - count($arrActive90);

            $this->output->writeln(
                'churn90: '.$c90, 
                false
            );

            //ClientNetwey::whereIn('msisdn', $arrc90)->update(['tag' => 'C90']);
            HistoryDCBackup::processChurn($arrc90, $endDate->toDateTimeString());

            //Calculando kpi de la base
            $eop = $base - ($c90);

            $this->output->writeln(
                'Base EoP: '.$eop, 
                false
            );

            $aop = $eop / 2;

            $this->output->writeln(
                'Base AP: '.$eop, 
                false
            );

            $saveData = [
                'A90' => count($arrActive90),
                'C90' => $c90,
                'C30' => count($arrch30),
                'D90' => count($arrDecay90),
                'BOP' => 0,
                'EOP' => $eop,
                'AOP' => $aop,
                'date_reg' => date('Y-m-d H:i:s', strtotime('2018-12-31 23:59:59')),
                'status' => 'A'
            ];

            MetricsBiNew::create($saveData)->save();
        }
    }
}
