<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LowEvidences extends Model
{
  protected $table = 'islim_documentation_dismissal';

  protected $fillable = [
    'id',
    'url',
    'id_req_dismissal',
    'date_reg',
    'status'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\LowEvidences
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new LowEvidences;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getEvidece Obtiene la evidencia de un solicitud en especifico]
 * @param  [type] $idRequest [id de la solicitud]
 * @return [type]            [description]
 */
  public static function getEvidence($idRequest)
  {
    $data = self::getConnect('R')
      ->select(
        'islim_documentation_dismissal.id',
        'islim_documentation_dismissal.url',
        'islim_documentation_dismissal.date_reg')
      ->where([
        ['islim_documentation_dismissal.status', 'A'],
        ['islim_documentation_dismissal.id_req_dismissal', $idRequest]])
      ->get();
    return $data;
  }
}
