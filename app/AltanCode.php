<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AltanCode extends Model
{
    protected $table = 'islim_altan_codes';

	protected $fillable = [
        'services_id', 'codeAltan', 'supplementary', 'status'
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
            $obj = new AltanCode;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getAltanCodes ($status) {
        if(isset($status)) {
            return AltanCode::where(['status' => $status])->get();
        } else {
            return AltanCode::all();
        }
    }

    public static function getAltanCode ($status) {
    }

    public static function getAltanCodesByService ($service, $status) {
        $altan = AltanCode::where('services_id', $service);
        if(isset($status)) {
            $altan = $altan->where(['status' => $status])->get();
        } else {
            $altan = $altan->all();
        }
        return $altan;
    }

    public static function getAltanCodeByCode ($code) {
        return AltanCode::where('codeAltan', $code)->first();
    }

}