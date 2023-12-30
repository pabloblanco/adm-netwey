<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BillingMasiveFile extends Model {
	protected $table = 'islim_billing_masive_file';

	protected $fillable = [
		'id',
        'status',
        'date_reg'
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
            $obj = new BillingMasiveFile;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}