<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reports extends Model {
    protected $table = 'islim_reports';

    protected $fillable = [
        'name_report',
        'email',
        'download_url',
        'filters',
        'user_profile',
        'user',
        'status',
        'date_reg'
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
            $obj = new Reports;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}


