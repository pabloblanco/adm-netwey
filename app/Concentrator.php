<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concentrator extends Model {
	protected $table = 'islim_concentrators';

	protected $fillable = [
        'id',
        'name',
        'rfc',
        'email',
        'dni',
        'business_name',
        'phone',
        'address',
        'balance',
        'commissions',
        'date_reg',
        'status',
        'postpaid',
        'amount_alert',
        'amount_allocate',
        'id_channel'
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
            $obj = new Concentrator;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getConcentrators(){
        return self::getConnect('R')
                    ->select('id', 'name', 'business_name', 'balance')
                    ->where('status', 'A')
                    ->get();
    }
}