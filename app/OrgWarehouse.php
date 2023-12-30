<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrgWarehouse extends Model {
	 protected $table = 'islim_wh_org';

	protected $fillable = [
        'id',
        'id_wh',
        'id_org'
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
            $obj = new OrgWarehouse;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}