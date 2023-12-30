<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServerIP extends Model
{
	protected $table = 'islim_ip_servers';

	protected $fillable = [
        'api_key', 'ip', 'date_reg', 'status',
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
            $obj = new ServerIP;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}