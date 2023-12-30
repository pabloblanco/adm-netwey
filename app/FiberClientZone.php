<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FiberClientZone extends Model
{
  protected $table = 'islim_fiber_client_zone';

  protected $fillable = [
    'id',
    'fiber_zone_id',
    'dni_client',
    'pk_user',
    'date_reg',
    'status'];

  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

  public static function registerNewClientZone($dni = false, $pkuser = false, $fiberZone = false)
  {
    if ($dni && $pkuser && $fiberZone) {

      $dataInsertClientZone = array(
        'fiber_zone_id' => $fiberZone,
        'dni_client'    => $dni,
        'pk_user'       => $pkuser,
        'date_reg'      => date('Y-m-d H:i:s'));

      return self::getConnect('W')->insert($dataInsertClientZone);
    }
  }
  public static function updateClientZone($dni = false, $pkuser = false, $fiberZone = false)
  {
    if ($dni && $pkuser && $fiberZone) {
      return self::getConnect('W')
        ->where([['dni_client', $dni],
          ['fiber_zone_id', $fiberZone]])
        ->update([
          'pk_user' => $pkuser]);
    }
  }
}
