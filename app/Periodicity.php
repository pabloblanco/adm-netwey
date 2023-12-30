<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Periodicity extends Model {
	protected $table = 'islim_periodicities';

	protected $fillable = [
		'id', 'periodicity', 'price_fee', 'status'
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
            $obj = new Periodicity;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}