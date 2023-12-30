<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Inv_preAssigne extends Model
{
  protected $table    = 'islim_inv_assignments_temp';
  protected $fillable = [
    'id',
    'user_email',
    'inv_arti_details_id',
    'status',
    'assigned_by',
    'date_reg',
    'date_status',
    'notification_view'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\Inv_preAssigne
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Inv_preAssigne;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
/**
 * [setRemoveInventoryLow Remueve el inventario que este preasignado al usuario que finalizo el proceso de baja]
 * @param [type] $sellers [correo del usuario que finalizo baja]
 */
  public static function setRemovePreassigneLow($sellers)
  {
    return self::getConnect('W')
      ->where([
        ['user_email', $sellers],
        ['status', 'P']])
      ->update([
        'status' => 'T',
      ]);
  }

}
