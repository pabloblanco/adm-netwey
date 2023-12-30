<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProvaDelivery extends Model
{
    protected $table = 'islim_prova_delivery';

    protected $fillable = [
        'id_car',
        'id_temp_car',
        'deposit',
        'postal_code',
        'state',
        'city',
        'colony',
        'municipality',
        'street',
        'ext_number',
        'int_number',
        'notes',
        'transaction',
        'folio',
        'courier_g',
        'price',
        'status',
        'date_reg'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\ProvaDelivery
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ProvaDelivery;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getDelivery($folio = false){
        if($folio){
            return self::getConnect('R')
                        ->select(
                            'street',
                            'colony',
                            'state'
                        )
                        ->where('folio', $folio)
                        ->whereIn('status', ['S', 'A'])
                        ->first();
        }

        return null;
    }
}
