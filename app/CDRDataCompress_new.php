<?php
/*
Elaborado por Luis
Marzo 2021
 */
namespace App;

use Illuminate\Database\Eloquent\Model;

class CDRDataCompress_new extends Model
{
  protected $table = 'islim_cdr_data_compress_new';

  protected $fillable = [
    'cdr_id',
    'pri_identity'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\CDR_DataCompress_new
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new CDRDataCompress_new;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }
}
