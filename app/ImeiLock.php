<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ImeiLock extends Model
{
    protected $table = 'islim_imei_locks';

    protected $fillable = [
        'msisdn',
        'date_reg'
    ];

    public $timestamps = false;

     /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\ImeiLock
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new self;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
            return $obj;
        }
        return null;
    }
}
