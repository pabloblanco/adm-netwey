<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlimService extends Model {
	protected $table = 'islim_blim_services';

	protected $fillable = [
		'id', 'name', 'description', 'sku', 'status', 'price','date_register', 'date_modified'
    ];
    
    public $timestamps = true;
    public $incrementing = true;
    protected $primaryKey  = 'id';

    const CREATED_AT = 'date_register';
    const UPDATED_AT = 'date_modified';	

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new BlimService;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}