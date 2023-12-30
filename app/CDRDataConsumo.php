<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CDRDataConsumo extends Model
{
  protected $table    = 'islim_cdr_data_consumo';
  protected $fillable = [
    'id',
    'msisdn'];
  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new CDRDataConsumo;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getConsumptionDN($msisdn = false, $filters = [])
  {
    if ($msisdn) {
      $data = self::select(
        'islim_cdr_data_consumo.msisdn as msisdn',
        'islim_cdr_data_consumo.date as date_transaction',
        'islim_cdr_data_consumo.consumo_real as consuption'
      )
        ->where([
          ['islim_cdr_data_consumo.msisdn', $msisdn],
          ['islim_cdr_data_consumo.consumo_real', '>', '0'],
        ]
        )
        ->where('islim_cdr_data_consumo.date', '>=', '2020-10-05')
        ->where('islim_cdr_data_consumo.date', '<', '2021-07-16');

      if (is_array($filters) && count($filters)) {
        if (!empty($filters['dateB'])) {
          $data = $data->where('islim_cdr_data_consumo.date', '>=', $filters['dateB']);
        }
      }
      //Log::info($data->toSql());
      //return $data->get();
      return $data;

    }

    return [];
  }

}
