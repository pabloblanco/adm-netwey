<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SellerWare extends Model
{
    protected $table = 'islim_seller_ware';

    protected $fillable = [
        'id_ware', 'email', 'status', 'date_reg'
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
            $obj = new SellerWare;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}