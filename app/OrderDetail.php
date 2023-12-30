<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
  protected $table = 'islim_orders_details';

  protected $fillable = [
    'id', 'periodicity', 'price_fee', 'status', 'msisdn', 'address', 'reference',
  ];

  public $timestamps = false;
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new OrderDetail;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getAddress_active description]
 * @param  boolean $folio [Numero de folio de la orden]
 * @return [type]         [Direccion en la cual se activa el modem]
 */
  public static function getAddress_active($folio = false)
  {
    if ($folio) {

      return self::getConnect('R')
        ->select(
          'islim_orders_details.address'
        )
        ->orWhere('islim_orders_details.reference', 'like', '%' . $folio . '%')
        ->first();
    }
    return null;
  }
}
