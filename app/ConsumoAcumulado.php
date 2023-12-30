<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumoAcumulado extends Model
{
  protected $table    = 'islim_consumos_acumulados';
  protected $fillable = [
    'id',
    'msisdn',
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
      $obj = new ConsumoAcumulado;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
