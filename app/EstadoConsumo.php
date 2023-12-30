<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EstadoConsumo extends Model
{
    protected $table = 'islim_estado_consumo';

    public $timestamps = false;

    public static function getConsumptionDN($msisdn = false, $fileters = []){
    	if($msisdn){
    		$data = self::select(
    						DB::raw('sum(islim_estado_consumo.consu_me_rgu_d) as consuption'),
    						'islim_services.title',
    						'islim_sales.codeAltan',
	                        'islim_estado_consumo.offer_name',
	                        'islim_estado_consumo.date_sup_be',
                        	'islim_estado_consumo.date_sup_en',
                        	'islim_estado_consumo.date_transaction'
    					)
    					->join(
    						'islim_sales',
    						function($query) use ($msisdn){
    							$query->on(
    									DB::raw('DATE_FORMAT(islim_sales.date_reg, "%Y-%m-%d")'),
    									'islim_estado_consumo.date_sup_be'
    								  )
    								  ->where('islim_sales.msisdn', $msisdn)
    								  ->where(function($wh){
    								  	$wh->where('islim_sales.type', 'P')
    								  	   ->orWhere('islim_sales.type', 'R');
    								  });
    						}
    					)
    					->join(
	                        'islim_services',
	                        'islim_services.id',
	                        'islim_sales.services_id'
	                    )
    					->where([
    						['islim_estado_consumo.msisdn', $msisdn],
    						['islim_estado_consumo.consu_me_rgu_d','>',0],
    						['islim_estado_consumo.offer_name', 'NOT LIKE', '%throttling%']
    					]);

    		if(is_array($fileters) && count($fileters)){
    			if(!empty($fileters['dateB'])){
    				$data = $data->where('islim_estado_consumo.date_transaction', '>=', $fileters['dateB']);
    			}
    		}

    		$data = $data->groupBy(
                            'islim_estado_consumo.date_transaction',
                            'islim_estado_consumo.offer_id',
                            'islim_estado_consumo.date_sup_be'
                        )
                        ->orderBy('islim_estado_consumo.date_transaction', 'DESC')
                        ->orderBy('islim_estado_consumo.date_sup_be', 'DESC');
                        
            return $data->get();
    	}

    	return [];
    }
}