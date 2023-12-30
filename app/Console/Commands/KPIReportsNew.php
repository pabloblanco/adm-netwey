<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
//use Illuminate\Support\Facades\DB;
use App\Sale;
use App\Service;
use App\MetricsBi2;
use App\HistoryDC2;
use App\ClientNetwey;
use Carbon\Carbon;

class KPIReportsNew extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:kpisNew {type?}'; //hbb, mbb, mifi, fibra

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta cron para calcular kpis V3.0 de HBB, mbb, mifi';

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

        $this->output->writeln('Inicia '.date('Y-m-d H:i:s'));

        $type = 'H';
        if(!empty($this->argument('type'))){
            switch ($this->argument('type')) {
                case 'HBB': $type = 'H'; break;
                case 'MBB': $type = 'T'; break;
                case 'T': $type = 'T'; break;
                case 'MIFI': $type = 'M'; break;
                case 'M': $type = 'M'; break;
                case 'MIFIH': $type = 'MH'; break;
                case 'MH': $type = 'MH'; break;
                case 'FIBRA': $type = 'F'; break;
                case 'F': $type = 'F'; break;
                default: $type = 'H'; break;
            }
        }


        //Validación para que no calcule meses futuros
        MetricsBi2::deleteFutures($type);

        $lastMetrcis = MetricsBi2::getLastMetric($type);

        if(!empty($lastMetrcis)){

            $today = Carbon::now();
            //Primer dia del mes
            $beginDate = Carbon::createFromFormat('Y-m-d H:i:s', $lastMetrcis->date_reg)
                            ->startOfMonth()
                            ->addMonth();

            $beginDateCompare = $today->copy()->startOfMonth()->subMonth();

            //el calculo solo se realiza si la ultima metrica corresponde a la del mes anterior al actual, en caso contrario se debe realizar el calculo usando el metodo anterior la V2.0

            if((int)$beginDateCompare->format('Ymd') == (int)$beginDate->format('Ymd')){

                //ultimo dia del mes
                $endDate = $beginDate->copy()->endOfMonth();


                //Dns a analizar
                $totalR = ClientNetwey::select(
                            'islim_client_netweys.msisdn',
                            'islim_client_netweys.date_cd90'
                        )->where([
                            ['islim_client_netweys.date_cd90', '>=', $beginDate->toDateTimeString()],
                            ['islim_client_netweys.dn_type', $type]
                        ])
                        ->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->get();

                $this->output->writeln(
                    'msisdns a analizar para la fecha '.$endDate->toDateTimeString().': '.$totalR->count()
                );

                //calculando Churn, Decay y active 90
                $decay90 = [];
                $churn90 = [];
                $active90 = [];

                foreach($totalR as $key => $data){

                    //$this->output->writeln('msisdn: '.$data->msisdn);

                    $createHist = 0;

                    $cd90Date = Carbon::createFromFormat('Y-m-d', $data->date_cd90)
                                        ->endOfDay();
                    $isRec = false;

                    if($cd90Date->timestamp <= $endDate->timestamp &&
                       $cd90Date->format('Y-m') == $beginDate->format('Y-m')){

                        $recharge = Sale::getLastRecharge(
                            $data->msisdn,
                            $endDate->toDateTimeString()
                        );

                        if(empty($recharge)){  // decay
                            $type_estatus = 'D90';
                            $decay90 []= $data;
                        }
                        else{ //churn
                            $type_estatus = 'C90';
                            $churn90 []= $data;
                        }
                        $date_ev = $cd90Date;
                        $createHist = 1;
                    }
                    else{ // activos
                        if($cd90Date->timestamp >= $endDate->timestamp){
                            $active90 [] = $data;
                            $isRec = HistoryDC2::isReactivation($data->msisdn);
                            $type_estatus = 'A90';
                            $date_ev = $endDate;
                            $createHist = 1;
                        }
                    }

                    if( $createHist == 1){
                        HistoryDC2::createRecord(
                            $data->msisdn,
                            $type_estatus,
                            $date_ev->toDateTimeString(),
                            $endDate->toDateTimeString(),
                            $isRec
                        );
                    }

                    if(($key % 10000) == 0){
                        $this->output->writeln('key ('.$key.'): '.date('Y-m-d H:i:s'));
                    }

                }

                $this->output->writeln('decay90: '.count($decay90));
                $this->output->writeln('churn90: '.count($churn90));
                $this->output->writeln('active90: '.count($active90));

                //recuperados del mes
                $contRec = HistoryDC2::getClientsByTag(['REC'], $type)->get()->count();

                $totalReg = Sale::getUpsPeriod($beginDate->toDateTimeString(),$endDate->toDateTimeString(),$type)->count();

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
                    'type' => $type == 'H'?'HBB':$type,
                    'date_reg' => $endDate->toDateTimeString(),
                    'status' => 'A'
                ];

                MetricsBi2::create($saveData)->save();

                //$this->output->writeln($saveData);
                $this->output->writeln('---------------------------------');

            }
            else{
               $this->output->writeln("Se debe carcular con proceso anterior V2.0");
            }
        }
        else{
            $this->output->writeln("Se debe carcular con proceso anterior V2.0");
        }

        $this->output->writeln('Culmina '.date('Y-m-d H:i:s'));

    }
}
