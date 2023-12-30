<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FiberServiceZone extends Model
{
    protected $table = 'islim_fiber_service_zone';

	protected $fillable = [
		'id',
		'fiber_zone_id',
		'service_id',
		'service_pk',
		'date_modified',
		'status'
    ];

    public $timestamps = false;


	/**
	* Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
	* @param String $typeCon
	*
	* @return App\ConsumoAcumulado
	*/
	public static function getConnect($typeCon = false)
	{
		if ($typeCon) {
			$obj = new self;
			$obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
			return $obj;
		}
		return null;
	}
}
