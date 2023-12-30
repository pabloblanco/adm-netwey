<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Sale;

class CDRDataView extends Model
{
    protected $table = 'islim_cdr_data_view';

    public $timestamps = false;

    public static function getConsumptionDN($msisdn = false, $filters = []){
    	if($msisdn){
            $data = self::select(
                'islim_cdr_data_view.msisdn as msisdn',
                'islim_cdr_data_view.cust_local_start_datetime as datetime_transaction'             
            )
            ->selectRaw('DATE(islim_cdr_data_view.cust_local_start_datetime) as date_transaction')
            ->selectRaw('SUM(islim_cdr_data_view.rate_usage) as consuption')
            ->where([
                    ['islim_cdr_data_view.msisdn',$msisdn]                                
                ]
            );

            if(is_array($filters) && count($filters)){
                if(!empty($filters['dateB'])){
                    $data = $data->where('islim_cdr_data_view.cust_local_start_datetime', '>=', $filters['dateB']);
                }
            }

            $data->groupBy(DB::raw('DATE(islim_cdr_data_view.cust_local_start_datetime)'));

            //Log::info($data->toSql());
            return $data->get();

           
    	}

    	return [];
    }


     public static function getConsumptionDNDetails($msisdn, $date){
       
            $data = self::select(
                'islim_cdr_data_view.msisdn as msisdn',
                'islim_cdr_data_view.cust_local_start_datetime as datetime_transaction_start',
                'islim_cdr_data_view.fu_charging_offerid as codeAltan',
                'islim_cdr_data_view.rate_usage as consuption',
                'islim_cdr_data_view.ratinggroup as consuption_type'
            )
            ->selectRaw('TIME(islim_cdr_data_view.cust_local_start_datetime) as time_transaction_start')
            ->selectRaw('TIME(islim_cdr_data_view.cust_local_end_datetime) as time_transaction_end')          
            ->where([
                    ['islim_cdr_data_view.msisdn',$msisdn]                                
                ]
            )
            ->where(DB::raw('DATE(islim_cdr_data_view.cust_local_start_datetime)'), '=', $date)
            ->orderBy('islim_cdr_data_view.cust_local_start_datetime','DESC');

            // Log::info("msisdn: ".$msisdn);
            // Log::info("date: ".$date);
            // Log::info("dataquery --> ".$data->toSql());

            $data = $data->get();

           if($data){
                foreach ($data as $key => $item) {
                    if (!empty($item->codeAltan) &&  strlen(trim($item->codeAltan))>0 ) {
                        $codeAltan=explode(';', $item->codeAltan); 
                    
                        $sales=Sale::selectRaw('DATE(islim_sales.date_reg) as date_activation')
                        ->selectRaw('DATE(DATE_ADD(islim_sales.date_reg, INTERVAL islim_periodicities.days + 1 DAY )) as date_expired')
                        ->selectRaw('CONCAT(`islim_services`.`title`, " | ",`islim_services`.`description`) as description')
                        ->join('islim_services','islim_services.id','islim_sales.services_id')
                        ->join('islim_periodicities','islim_periodicities.id','islim_services.periodicity_id')
                        ->where([
                            ['islim_sales.msisdn',$item->msisdn],
                            ['islim_sales.codeAltan',$codeAltan[0]],
                            ['islim_sales.status','A']
                        ])
                        ->whereIn('islim_sales.type',['P','R'])
                        ->where('islim_sales.date_reg','<=',$item->datetime_transaction_start)                    
                        ->whereRaw('DATE(DATE_ADD(islim_sales.date_reg, INTERVAL islim_periodicities.days + 1 DAY )) >= ?',[$item->datetime_transaction_start])
                        ->orderBy('islim_sales.date_reg','DESC');

                        // Log::info("msisdn: ".$item->msisdn);
                        // Log::info("codeAltan: ".$item->codeAltan);
                        // Log::info("datetime_transaction_start: ".$item->datetime_transaction_start);

                        // Log::info("sales (".$key.")--> ".$sales->toSql());

                        $sales=$sales->first();
                        
                        if($sales){
                            $item->date_activation=$sales->date_activation;
                            $item->date_expired=$sales->date_expired;
                            $item->service=$sales->description;                       
                        }
                    }

                }
            }



            return $data;
        
    }
}