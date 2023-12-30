<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ADB_portability_errors extends Model
{
  protected $table = 'islim_soap_portability_errors';

  protected $fillable = [
    'code_error',
    'description'];
  protected $primaryKey = 'code_error';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\ADB_portability_details
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ADB_portability_errors;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
