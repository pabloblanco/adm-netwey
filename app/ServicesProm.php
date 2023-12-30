<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServicesProm extends Model
{
    protected $table = 'islim_services_prom';

	protected $fillable = [
        'name',
        'service_id',
        'qty',
        'period_days',
        'max_time',
        'date_reg',
        'status'
    ];

    public $incrementing = true;
    protected $primaryKey  = 'id';
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ServicesProm;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getServicesProm(){
        $data = self::getConnect('R')
                     ->select(
                        'id',
                        'name'                     
                     )
                     ->where('status', 'A');      
        return $data->get();
    }

    /**
     * Metodo para obtener datos de un servicio promocional dado su id
     * @param String $id
     * 
     * @return App\Models\ServicesProm
    */
    public static function getPromByID($id = false){
        if($id){
            return self::getConnect('R')
                        ->where('id', $id)
                        ->first();
        }
        
        return null;
    }
}
