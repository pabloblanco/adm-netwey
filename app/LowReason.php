<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LowReason extends Model
{
  protected $table = 'islim_reason_dismissal';

  protected $fillable = [
    'id',
    'reason',
    'date_reg',
    'status'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\LowReason
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new LowReason;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
