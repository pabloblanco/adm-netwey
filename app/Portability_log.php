<?php
/*
Autor: Ing. LuisJ
Agosto 2021
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class Portability_log extends Model
{
  protected $table    = 'islim_portability_logs';
  protected $fillable = [
    'id',
    'portability_id',
    'Item_update',
    'date_update',
    'status',
    'details_error'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Portability_log;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function setLogPotability($id, $table, $status = 'OK', $detailsError = false)
  {
    $item                 = self::getConnect('W');
    $item->portability_id = $id;
    $item->date_update    = date('Y-m-d H:i:s');
    $item->Item_update    = $table;
    $item->status         = $status;
    if ($detailsError) {
      $item->details_error = $detailsError;
    }
    $item->save();
    return true;
  }
}
