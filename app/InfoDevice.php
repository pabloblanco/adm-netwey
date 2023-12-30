<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InfoDevice extends Model
{
  protected $table = 'islim_info_device';

  protected $fillable = [
    'id',
    'msisdn',
    'imei',
    'homologated',
    'blocked',
    'volteCapable',
    'model',
    'brand',
    'date_reg',
    'status'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new InfoDevice;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
  public static function getModel($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
        ->select(
          'islim_info_device.model',
          'islim_info_device.imei',
          'islim_info_device.brand'
        )
        ->where('msisdn', $msisdn)
        ->first();
    }
    return null;
  }
}
