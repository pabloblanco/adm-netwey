<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingConcept extends Model {
	protected $table = 'islim_billing_concepts';

	protected $fillable = [
		'id', 'nro_identification', 'description', 'unit_key', 'unit', 'service_id','pack_id', 'product_key', 'shipping', 'is_financed'
    ];
    
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';


    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new BillingConcept;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}