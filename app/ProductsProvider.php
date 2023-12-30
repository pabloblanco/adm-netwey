<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductsProvider extends Model
{
    protected $table = 'islim_providers';

    protected $fillable = [
        'dni',
        'name',
        'rfc',
        'email',
        'business_name',
        'phone',
        'address',
        'responsable',
        'date_reg',
        'status'
    ];

    protected $primaryKey = 'dni';

    protected $keyType = 'string';

    public $incrementing = false;
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ProductsProvider;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}