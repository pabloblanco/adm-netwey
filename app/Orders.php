<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    protected $table = 'islim_ordens';

    protected $fillable = [
        'id', 'client_id', 'ordNbr', 'seller_email'];

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Orders
     */
    public static function getConnect($typeCon = false)
    {
        if ($typeCon) {
            $obj = new Orders;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getMail_vendedor($folio = false){
        if($folio){
            
            return self::getConnect('R')
                        ->select(  
                            'islim_ordens.seller_email'   
                        )    
                        ->where('islim_ordens.ordNbr', $folio)
                        ->first();
        }

        return null;
    }

}
