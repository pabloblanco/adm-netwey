<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetricsDasboardB extends Model
{
  protected $table   = "islim_metrics_dashboard_new";
  public $timestamps = false;

  protected $fillable = [
    'id',
    'date',
    'quantity',
    'amount',
    'id_org',
    'type',
    'type_device'];

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\MetricsDasboardB
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new MetricsDasboardB;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getSumMetrics($dateB = false, $dateE = false, $type = false, $device = false)
  {
    if ($type && $device) {
      $data = self::getConnect('R')
        ->select('quantity')
        ->where([
          ['type', $type],
          ['type_device', $device]]);

      if ($dateB) {
        $data->where('date', '>=', $dateB);
      }

      if ($dateE) {
        $data->where('date', '<=', $dateE);
      }

      return $data->sum('quantity');
    }

    return 0;
  }

  public static function getSumByDate($date = false, $type = false, $device = false)
  {
    if ($type && $date) {
      return self::getConnect('R')
        ->select('quantity')
        ->where([
          ['type', $type],
          ['date', $date],
          ['type_device', $device]])
        ->sum('quantity');

    }

    return 0;
  }
}
