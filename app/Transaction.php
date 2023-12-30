<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'islim_transaction';

    protected $fillable = [
        'name_client',
        'address',
        'name_article',
        'quantity',
        'status',
        'codigozip',
        'colonia',
        'id_ordens',
        'city',
        'state',
        'state_estafeta',
        'id_estafeta'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Transaction
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Transaction;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getDelivery($order = false){
        if($order){
            return self::getConnect('R')
                        ->select(
                            'city',
                            'colonia',
                            'state',
                            'codigozip',
                            'address'
                        )
                        ->where('id_ordens', $order)
                        ->first();
        }

        return null;
    }
}
