<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TheftOrLoss extends Model
{
  protected $table = 'islim_theft_Loss_msisdn';

  protected $fillable = [
    'id',
    'msisdn',
    'orderId',
    'date_reg',
    'date_active',
    'user',
    'status'];

  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new TheftOrLoss;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function isReport($msisdn = false)
  {
    if ($msisdn) {
      return self::select('id')->where([['msisdn', $msisdn], ['status', 'A']])->first();
    }

    return null;
  }

  public static function resume($msisdn = false, $user)
  {
    if ($msisdn) {
      return self::getConnect('W')
        ->where([
          ['msisdn', $msisdn],
          ['status', 'A'],
        ])
        ->update([
          'status'      => 'I',
          'user'        => $user,
          'date_active' => date('Y-m-d H:i:s'),
        ]);
    }
  }
}
