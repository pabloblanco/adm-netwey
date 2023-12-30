<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TelephoneCompany extends Model
{
  protected $table = 'islim_telephone_companys';

  protected $fillable = [
    'id',
    'name',
    'status',
    'date_reg'];

  protected $primaryKey = 'id';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\TelephoneCompany
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

  public static function getListCompany()
  {
    return self::getConnect('R')
      ->select(
        'id',
        'name')
      ->where('status', 'A')
      ->get();

  }

}
