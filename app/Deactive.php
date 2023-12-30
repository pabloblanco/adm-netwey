<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Deactive extends Model
{
    protected $table = 'islim_deactive';

    protected $fillable = [
        'msisdn',
        'response',
        'date_inactive',
        'date_reg',
        'status',
        'from'
    ];

    public $timestamps = false;

     /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Deactive
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
