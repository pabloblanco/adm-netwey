<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Responsible extends Model {
	 protected $table = 'islim_dts_responsibles';

	protected $fillable = [
        'id',
        'id_org',
        'name',
        'phone',
        'email',
        'position',
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
            $obj = new Responsible;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

}