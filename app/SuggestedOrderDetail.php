<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuggestedOrderDetail extends Model
{
  protected $table = 'islim_suggested_order_detail';

  protected $fillable = [
    'suggested_order_id',
    'user',
    'articles_id',
    'total_sales',
    'avg_sales',
    'stock',
    'gap',
    'suggested',
    'status'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   * 
   * @return App\SuggestedOrderDetail
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SuggestedOrderDetail;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
