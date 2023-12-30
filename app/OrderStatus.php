<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderStatus extends Model {
	protected $table = 'islim_ordens_status';
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\OrderStatus
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new OrderStatus;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getLastStatus($order = false){
    	if($order){
    		return self::getConnect('R')
    					->select('description')
    					->where('id_ordens', $order)
    					->orderBy('id', 'DESC')
    					->first();
    	}

    	return null;
    }
}