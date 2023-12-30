<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
  protected $table = 'islim_incident_msisdn_affected';

  protected $fillable = [
    'id',
    'msisdn',
    'date_incident',
    'status',
    'date_reg',
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Incidencia
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Incidencia;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

  public static function isIncidencia($msisdn = false)
  {
    if ($msisdn) {
      $mob = self::getConnect('R')
        ->select('msisdn', 'date_incident', 'status')
        ->where('msisdn', $msisdn)
        ->orderBy('msisdn', 'DESC')
        ->orderBy('id', 'DESC')
        ->first();
      if (!empty($mob)) {
        return $mob;
      }
    }
    return null;
  }
}
