<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetricsDasboard extends Model
{
    protected $table = "islim_metrics_dashboard";
    public $timestamps = false;

    protected $fillable = [
		'id',
		'date',
		'metrics',
		'id_org',
		'type',
		'type_device'
    ];

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\MetricsDasboard
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new MetricsDasboard;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}
