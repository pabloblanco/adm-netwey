<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientBuyBack extends Model
{
  protected $table = 'islim_client_buy_back';

  protected $fillable = [
    'id',
    'msisdn',
    'answer',
    'acept',
    'comment',
    'is_last',
    'file',
    'date_reg'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\ClientBuyBack
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ClientBuyBack;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function resetLastStatus($dn = false)
  {
    if ($dn) {
      return self::getConnect('W')
        ->where('msisdn', $dn)
        ->update([
          'is_last' => 'N',
        ]);
    }
  }

  public static function getLastContact($dn)
  {
    return self::getConnect('R')
      ->select(
        'answer',
        'acept',
        'comment',
        'date_reg'
      )
      ->where([
        ['msisdn', $dn],
        ['is_last', 'Y'],
      ])
      ->first();
  }
}
