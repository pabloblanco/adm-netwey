<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MetricsBi2 extends Model {
	protected $table = 'islim_metrics_bi_2'; //'islim_metrics_bi_copy';

	protected $fillable = [
        'id',
        'total_reg',
        'C30',
        'A90',
        'C90',
        'CB90',
        'CN90',
        'D90',
        'REC',
        'BOP', 
        'EOP', 
        'AOP',
        'type',
        'date_reg', 
        'status'
    ];
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new MetricsBi2;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getLastMetric($type = 'HBB', $mode = 'W'){
        if($type == 'H')
            $type = 'HBB';
    	return self::getConnect($mode)
    				->where([
    					['status','A'],
    					['type', $type]
    				])
					->orderBy('date_reg', 'DESC')
					->first();
    }

    public static function deleteFutures($type = 'HBB'){

        $lastMetrcis = self::getLastMetric($type);
        if(!empty($lastMetrcis)){
            $today = Carbon::now();
            $begin = Carbon::createFromFormat('Y-m-d H:i:s', $lastMetrcis->date_reg)
                            ->startOfMonth()
                            ->addMonth();
            $aux=0;
            do{
                //Validación para que no calcule meses futuros
                if((int)$today->format('Ym') <= (int)$begin->format('Ym')){
                    $lastMetrcis->status = 'T';
                    $lastMetrcis->save();

                    $begin = $begin->subMonth();
                    $aux=1;
                }
            }
            while((int)$today->format('Ym') <= (int)$begin->format('Ym'));

            if($aux==1)
                sleep(3); //se detiene para sincronizar BDs
        }
        return;
    }

    public static function getMetricByDate($date = false, $type = 'HBB'){
    	if($date){
    		return self::getConnect('R')
    				->where([
    					['status','A'],
    					[DB::raw("DATE_FORMAT(date_reg, '%m/%Y')"), $date],
    					['type', $type]
    				])
					->orderBy('date_reg', 'DESC')
					->first();
    	}
    	return null;
    }


}