<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConsumoAcumuladoTotal extends Model
{
  protected $table    = 'islim_consumos_acumulados_totals';
  protected $fillable = [
    'id',
    'msisdn'];
  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ConsumoAcumuladoTotal;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getConsumptionDN($msisdn = false, $filters = [])
  {
    if ($msisdn) {
      $data = self::select(
        'islim_consumos_acumulados_totals.msisdn as msisdn',
        'islim_consumos_acumulados_totals.date as date_transaction',
        'islim_consumos_acumulados_totals.consumo_real as consuption',
        'islim_consumos_acumulados_totals.consumo_throttling as throttling'
      )
        ->where([
          ['islim_consumos_acumulados_totals.msisdn', $msisdn],
          ['islim_consumos_acumulados_totals.date', '>=', '2021-07-16'],
        ]
        )
        ->where(function ($query) {
          $query->where('islim_consumos_acumulados_totals.consumo_real', '>', '0')
                ->orWhere('islim_consumos_acumulados_totals.consumo_throttling', '>', '0');
        });

      if (is_array($filters) && count($filters)) {
        if (!empty($filters['dateB'])) {
          $data = $data->where('islim_consumos_acumulados_totals.date', '>=', $filters['dateB']);
        }
      }
      //Log::info($data->toSql());
      return $data;

    }

    return [];
  }

}
