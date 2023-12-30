<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Sale;
use App\Service;
use App\MetricsBi2;
use App\Deactive;
use App\HistoryDC2;
use Carbon\Carbon;

class KPIReportMBB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kpiMBB';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta cron para calcular kpis version 2.0 de MBB';

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
        $lastMetrcis = MetricsBi2::getLastMetric('T');

        //Primer mes a calcular 07/2020
        
        $today = Carbon::now();
        //$today = Carbon::createFromFormat('Y-m-d H:i:s', '2020-08-23 12:00:00');
        //Primer dia del mes
        $begin = Carbon::createFromFormat(
                            'Y-m-d H:i:s', 
                            !empty($lastMetrcis) ? $lastMetrcis->date_reg : $today->format('Y-m-d H:i:s')
                        )
                        ->startOfMonth();

        if(!empty($lastMetrcis)){
            $begin->addMonth();
        }

        //Validación para que no calcule meses futuros
        if((int)$today->format('Ym') <= (int)$begin->format('Ym')){
            if(!empty($lastMetrcis)){
                $lastMetrcis->status = 'T';
                $lastMetrcis->save();
            }

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
                        ['islim_sales.sale_type', 'T']
                    ])
                    ->whereIn('islim_sales.type',['P', 'R'])
                    ->whereIn('islim_sales.status',['A', 'E'])
                    ->where(function($q){
                        $q->whereIn('islim_client_netweys.status',['A', 'S'])
                          ->orWhereIn('islim_client_netweys.msisdn', Deactive::select('msisdn')->where([['status', 'A']]));
                    })
                    //->whereIn('islim_client_netweys.status',['A', 'S'])
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
        //NOTA: Esto se debe ejecutar luego de calcular los active para que tome solo los recuperados del mes
        $contRec = HistoryDC2::getClientsByTag(['REC'], 'T')->get()->count();
        
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
                        ['islim_sales.sale_type', 'T']
                    ])
                    ->whereIn('islim_sales.status',['A', 'E'])
                    ->whereIn('islim_client_netweys.status',['A', 'S'])
                    ->groupBy('islim_sales.msisdn')
                    ->get();

        $totalReg = $totalReg->count();
        $lastEOP = !empty($lastMetrcis) ? $lastMetrcis->EOP : 0;
        $eop = ($lastEOP + $totalReg) - ((count($decay90) + count($churn90)) - $contRec);

        $aop = ($eop + $lastEOP) / 2;

        $saveData = [
            'total_reg' => $totalReg,
            'A90' => count($active90),
            'C90' => count($churn90),
            'CB90' => count($decay90) + count($churn90),
            'CN90' => (count($decay90) + count($churn90)) - $contRec,
            'D90' => count($decay90),
            'REC' => $contRec,
            'BOP' => $lastEOP,
            'EOP' => $eop,
            'AOP' => $aop,
            'type' => 'T',
            'date_reg' => $endDate->toDateTimeString(),
            'status' => 'A'
        ];

        MetricsBi2::create($saveData)->save();
    }
}
