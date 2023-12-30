<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NinetyNineMinutes extends Model
{
    protected $table = 'islim_ninety_nine_minutes';

    protected $fillable = [
        'id_car',
        'postal_code',
        'route',
        'neighborhood',
        'municipality',
        'state',
        'street_number',
        'internal_number',
        'notes',
        'sub_locality',
        'delivery_type',
        'vehicle_type',
        'received_order',
        'order99',
        'url_pdf',
        'price',
        'status',
        'date_reg'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\NinetyNineMinutes
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new NinetyNineMinutes;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getDelivery($folio = false){
        if($folio){
            return self::getConnect('R')
                        ->select(
                            'route',
                            'neighborhood',
                            'state'
                        )
                        ->where('order99', $folio)
                        ->whereIn('status', ['S', 'A'])
                        ->first();
        }

        return null;
    }
}
