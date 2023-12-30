<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HistoryInventoryStatus extends Model
{
  protected $table = 'islim_history_status_inventory';

  protected $fillable = [
    'users_email',
    'inv_arti_details_id',
    'date_reg',
    'status',
    'motivo_rechazo',
    'url_evidencia',
    'color_destino',
    'userAutorizador'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\HistoryInventoryStatus
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

  public static function rejectChangeStatus($articId){
    return self::getConnect('W')
                ->where([
                  ['status', 'C'],
                  ['inv_arti_details_id', $articId]
                ])
                ->update([
                  'status' => 'R',
                  'motivo_rechazo' => 'Rechazado por movimiento a bodega merma (tarea programada)'
                ]);
  }
}