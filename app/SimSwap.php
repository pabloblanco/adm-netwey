<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SimSwap extends Model {
	protected $table = 'islim_sim_swap';

	protected $fillable = [
		'iccid_origin', 'msisdn_origin', 'imei_origin', 'iccid_dest', 'msisdn_dest', 'imei_dest', 'tipo', 'id_order', 'date_reg'
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
            $obj = new SimSwap;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}