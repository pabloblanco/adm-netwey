<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeliveryAddress extends Model
{
  protected $table = 'islim_user_delivery_address';

  protected $primaryKey = 'id';

  protected $fillable = [
    'email',
    'street',
    'colony',
    'municipality',
    'state',
    'postal_code',
    'ext_number',
    'int_number',
    'reference',
    'user_reg',
    'date_reg',
    'status'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\UserDeliveryAddress
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

  public static function deleteReg($email){
    return self::getConnect('W')
                ->where([
                  ['status', 'A'],
                  ['email', $email]
                ])
                ->update(['status' => 'T']);
  }

  public static function getActiveAddress($email){
    return self::getConnect('R')
                ->select(
                  'islim_user_delivery_address.street',
                  'islim_user_delivery_address.colony',
                  'islim_user_delivery_address.municipality',
                  'islim_user_delivery_address.state',
                  'islim_user_delivery_address.postal_code',
                  'islim_user_delivery_address.ext_number',
                  'islim_user_delivery_address.int_number',
                  'islim_user_delivery_address.reference'
                )
                ->where([
                  ['email', $email],
                  ['status', 'A']
                ])
                ->first();
  }
}
