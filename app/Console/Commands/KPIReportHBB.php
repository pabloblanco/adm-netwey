<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\DB;
use App\Sale;
use App\Service;
use App\MetricsBi2;
use App\HistoryDC2;
use Carbon\Carbon;

class KPIReportHBB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kpiHBB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta cron para calcular kpis version 2.0 de HBB';

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
        $lastMetrcis = MetricsBi2::getLastMetric('HBB');

        if(!empty($lastMetrcis)){
            $today = Carbon::now();
            //Primer dia del mes
            $begin = Carbon::createFromFormat('Y-m-d H:i:s', $lastMetrcis->date_reg)
                            ->startOfMonth()
                            ->addMonth();

            //Validación para que no calcule meses futuros
            if((int)$today->format('Ym') <= (int)$begin->format('Ym')){
                $lastMetrcis->status = 'T';
                $lastMetrcis->save();

                $begin = $begin->subMonth();
            }

            //ultimo dia del mes
            $endDate = $begin->copy()->endOfMonth();

            //Inicio de busqueda
            $starSearch = $begin->copy()->subMonths(6);

            //Dns a analizar
            $totalR = Sale::select(
                            'islim_sales.msisdn', 
                            'islim_sales.services_id',
                            'islim_sales.type',
                            'islim_sales.date_reg'
                        )
                        ->join(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_sales.msisdn'
                        )
                        ->where([
                            ['islim_sales.date_reg', '>=', $starSearch->toDateTimeString()],
                            ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()],
                            ['islim_sales.sale_type', 'H']
                        ])
                        ->whereIn('islim_sales.type',['P', 'R'])
                        ->whereIn('islim_sales.status',['A', 'E'])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->groupBy('islim_sales.msisdn')
                        ->get();

            $this->output->writeln(
                'msisdns a analizar para la fecha '.$endDate->toDateTimeString().': '.$totalR->count()
            );

            //calculando Decays 90
            $decay90 = [];
            foreach($totalR as $key => $data){
                if($data->type == 'P'){
                    $timeAlta = Service::getPeriodicity($data->services_id);

                    $recharge = Sale::getLastRecharge(
                                        $data->msisdn,
                                        $endDate->toDateTimeString()
                                    );

                    $decayDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->date_reg)
                                        //->addMonths(3)
                                        ->addDays(89 + $timeAlta->days)
                                        ->endOfDay();

                    if(empty($recharge) && 
                       $decayDate->timestamp <= $endDate->timestamp && 
                       $decayDate->format('Y-m') == $begin->format('Y-m')){

                        $decay90 []= $data;
                        $totalR->forget($key);

                        HistoryDC2::createRecord(
                                        $data->msisdn,
                                        'D90',
                                        $decayDate->toDateTimeString(),
                                        $endDate->toDateTimeString(),
                                        false
                                    );
                    }
                }
            }

            $this->output->writeln(
                'decay: '.count($decay90)
            );

            //calculando churn90
            $churn90 = [];
            foreach($totalR as $key => $data){
                $recharge = Sale::getLastRecharge(
                                        $data->msisdn,
                                        $endDate->toDateTimeString()
                                    );

                if(!empty($recharge)){
                    $timeAlta = Service::getPeriodicity(
                                $recharge->services_id
                            );

                    $churnDate = Carbon::createFromFormat(
                                        'Y-m-d H:i:s',
                                        $recharge->date_reg
                                    )
                                    //->addMonths(3)
                                    ->addDays(89 + $timeAlta->days)
                                    ->endOfDay();

                    if($churnDate->timestamp <= $endDate->timestamp &&
                       $churnDate->format('Y-m') == $begin->format('Y-m')){

                        $churn90 []= $data;
                        $totalR->forget($key);

                        HistoryDC2::createRecord(
                                        $data->msisdn,
                                        'C90',
                                        $churnDate->toDateTimeString(),
                                        $endDate->toDateTimeString(),
                                        false
                                    );
                    }
                } 
            }

            $this->output->writeln(
                'churn90: '.count($churn90)
            );

            //calculando active90
            $active90 = [];
            $contRec = 0;
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
                                    //->addMonths(3)
                                    ->addDays(89 + $timeAlta->days)
                                    ->endOfDay();

                if($activeDate->timestamp >= $endDate->timestamp){
                    $active90 []= $data;
                    $totalR->forget($key);

                    $isRec = HistoryDC2::isReactivation($data->msisdn);

                    /*if($isRec){
                        $contRec++;
                    }*/

                    HistoryDC2::createRecord(
                                    $data->msisdn,
                                    'A90',
                                    $endDate->toDateTimeString(),
                                    $endDate->toDateTimeString(),
                                    $isRec
                                );
                }
            }

            $this->output->writeln(
                'active90: '.count($active90)
            );

            //Recuperados del mes
            $contRec = HistoryDC2::getClientsByTag(['REC'], 'H')->get()->count();

            //Altas del mes
            $totalReg = Sale::select(
                            'islim_sales.msisdn'
                        )
                        ->join(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_sales.msisdn'
                        )
                        ->where([
                            ['islim_sales.date_reg', '>=', $begin->toDateTimeString()],
                            ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()],
                            ['islim_sales.type', 'P'],
                            ['islim_sales.sale_type', 'H']
                        ])
                        ->whereIn('islim_sales.status',['A', 'E'])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->groupBy('islim_sales.msisdn')
                        ->get();

            $totalReg = $totalReg->count();

            $eop = ($lastMetrcis->EOP + $totalReg) - ((count($decay90) + count($churn90)) - $contRec);

            $aop = ($eop + $lastMetrcis->EOP) / 2;

            $saveData = [
                'total_reg' => $totalReg,
                'A90' => count($active90),
                'C90' => count($churn90),
                'CB90' => count($decay90) + count($churn90),
                'CN90' => (count($decay90) + count($churn90)) - $contRec,
                'D90' => count($decay90),
                'REC' => $contRec,
                'BOP' => $lastMetrcis->EOP,
                'EOP' => $eop,
                'AOP' => $aop,
                'type' => 'HBB',
                'date_reg' => $endDate->toDateTimeString(),
                'status' => 'A'
            ];

            MetricsBi2::create($saveData)->save();
        }else{
            //Aqui solo va a entrar la primera vez que se ejecute el cron para calcular de forma concentrada todos los kpis de año 2018 hasta diciembre de ese mismo año

            $month = "2018-12";
            //Primer dia del mes
            $begin = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
            //ultimo dia del mes
            $endDate = $begin->copy()->endOfMonth();

            //Altas totales
            $totalR = Sale::select(
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
                            ['islim_sales.date_reg', '<=', $endDate->toDateTimeString()],
                            ['islim_sales.sale_type', 'H']
                        ])
                        ->whereIn('islim_sales.status',['A', 'E'])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->groupBy('islim_sales.msisdn')
                        ->get();

            $totalReg = $totalR->count();

            $this->output->writeln(
                'Altas totales hasta 31 de diciembre 2018: '.$totalReg
            );

            //calculando Decays 90
            $decay90 = [];
            foreach($totalR as $key => $data){
                $timeAlta = Service::getPeriodicity($data->services_id);

                $recharge = Sale::getLastRecharge(
                                        $data->msisdn,
                                        $endDate->toDateTimeString()
                                    );

                $decayDate = Carbon::createFromFormat('Y-m-d H:i:s', $data->date_reg)
                                    //->addMonths(3)
                                    ->addDays(89 + $timeAlta->days)
                                    ->endOfDay();

                if(empty($recharge) && $decayDate->timestamp <= $endDate->timestamp){
                    $decay90 []= $data;
                    $totalR->forget($key);

                    HistoryDC2::createRecord(
                                    $data->msisdn,
                                    'D90',
                                    $decayDate->toDateTimeString(),
                                    $endDate->toDateTimeString(),
                                    false
                                );
                }
            }

            $this->output->writeln(
                'decay: '.count($decay90)
            );

            //calculando churn90
            $churn90 = [];
            foreach($totalR as $key => $data){
                $recharge = Sale::getLastRecharge(
                                        $data->msisdn,
                                        $endDate->toDateTimeString()
                                    );

                if(!empty($recharge)){
                    $timeAlta = Service::getPeriodicity(
                                $recharge->services_id
                            );

                    $churnDate = Carbon::createFromFormat(
                                        'Y-m-d H:i:s',
                                        $recharge->date_reg
                                    )
                                    //->addMonths(3)
                                    ->addDays(89 + $timeAlta->days)
                                    ->endOfDay();

                    if($churnDate->timestamp <= $endDate->timestamp){
                        $churn90 []= $data;
                        $totalR->forget($key);

                        HistoryDC2::createRecord(
                                        $data->msisdn,
                                        'C90',
                                        $churnDate->toDateTimeString(),
                                        $endDate->toDateTimeString(),
                                        false
                                    );
                    }
                } 
            }

            $this->output->writeln(
                'churn90: '.count($churn90)
            );

            //calculando active90
            $active90 = [];
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
                                    //->addMonths(3)
                                    ->addDays(89 + $timeAlta->days)
                                    ->endOfDay();

                if($activeDate->timestamp >= $endDate->timestamp){
                    $active90 []= $data;
                    $totalR->forget($key);

                    $isRec = HistoryDC2::isReactivation($data->msisdn);
                    HistoryDC2::createRecord(
                                    $data->msisdn,
                                    'A90',
                                    $endDate->toDateTimeString(),
                                    $endDate->toDateTimeString(),
                                    $isRec
                                );
                }
            }

            $this->output->writeln(
                'active90: '.count($active90)
            );

            $eop = ($totalReg - count($decay90)) - count($churn90);
            $aop = $eop / 2;

            $saveData = [
                'total_reg' => $totalReg,
                'A90' => count($active90),
                'C90' => count($churn90),
                'CB90' => count($decay90) + count($churn90),
                'CN90' => (count($decay90) + count($churn90)) - 0,
                'D90' => count($decay90),
                'REC' => 0,
                'BOP' => 0,
                'EOP' => $eop,
                'AOP' => $aop,
                'type' => 'HBB',
                'date_reg' => $endDate->toDateTimeString(),
                'status' => 'A'
            ];

            MetricsBi2::create($saveData)->save();
        }
    }
}
