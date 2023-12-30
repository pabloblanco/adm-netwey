<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SuggestedOrder extends Model
{
  protected $table = 'islim_suggested_order';

  protected $fillable = [
    'user_reg',
    'date_reg',
    'status'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   * 
   * @return App\SuggestedOrder
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SuggestedOrder;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
