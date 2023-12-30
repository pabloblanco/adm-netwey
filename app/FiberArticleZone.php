<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FiberArticleZone extends Model
{
    protected $table = 'islim_fiber_article_zone';

	protected $fillable = [
		'id',
		'fiber_zone_id',
		'article_id',
		'product_pk',
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
