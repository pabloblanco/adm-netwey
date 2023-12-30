<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DeactiveMasivesDetails extends Model
{
  protected $table = 'islim_deactive_masives_details';

  protected $fillable = [
    'id',
    'date_reg',
    'msisdn',
    'status_line',
    'response_altan',
    'altan_order',
    'obs_status',
    'prc_status',
    'prc_date'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Deactive
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
