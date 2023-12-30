<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientFiberFail extends Model
{
  protected $table    = 'islim_fiber_fail';
  protected $fillable = [
    'id',
    'msisdn',
    'dni_client',
    'status',
    'detail_fail',
    'date_process',
    'id_fiber_zone'];

  public $incrementing = false;

  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ClientFiberFail;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getEarring Obtiene un registro pendiente por registrar]
 * @return [type] [description]
 */
  public static function getEarring()
  {
    return self::getConnect('R')
      ->select('id', 'msisdn', 'dni_client', 'id_fiber_zone')
      ->where('status', 'C')
      ->first();
  }

  public static function setFiberFailStatus($id, $status, $detailFail = null)
  {
    self::getConnect('W')
      ->where('id', $id)
      ->update([
        'status'       => $status,
        'detail_fail'  => $detailFail,
        'date_process' => date('Y-m-d H:i:s')]);
  }
}
