<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DNMigration extends Model
{
  protected $table = 'islim_dns_migrations';

  protected $fillable = [
    'msisdn',
    'status',
    'date_reg'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   * 
   * @return App\DNMigration
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new DNMigration;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getDnAvailable()
  {
    $dn = self::getConnect('R')
      ->select('msisdn')
      ->where('status', 'A')
      ->first();

    if (!empty($dn)) {
      self::setDNBusy($dn->msisdn);
      return $dn;
    }

    return null;
  }

  public static function deleteDN($msisdn)
  {
    return self::getConnect('W')
      ->where('msisdn', $msisdn)
      ->update(['status' => 'T']);
  }
}
