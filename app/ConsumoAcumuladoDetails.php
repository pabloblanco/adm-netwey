<?php

namespace App;

use App\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConsumoAcumuladoDetails extends Model
{
  protected $table    = 'islim_consumos_acumulados_details';
  protected $fillable = [
    'id',
    'msisdn'];
  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\ConsumoAcumuladoDetails
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ConsumoAcumuladoDetails;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getConsumptionDNDetails($msisdn, $date)
  {

    $data = self::select(
      'islim_consumos_acumulados_details.msisdn as msisdn',
      'islim_consumos_acumulados_details.date_start as datetime_transaction_start',
      'islim_consumos_acumulados_details.codeAltan as codeAltan',
      'islim_consumos_acumulados_details.consumo as consuption',
      'islim_consumos_acumulados_details.type as consuption_type',
      'islim_consumos_acumulados_details.service_activated as date_activation',
      'islim_consumos_acumulados_details.service_expired as date_expired',
      'islim_consumos_acumulados_details.service_desc as service'
    )
      ->whereNotNull('islim_consumos_acumulados_details.service_desc')
      ->selectRaw('TIME(islim_consumos_acumulados_details.date_start) as time_transaction_start')
      ->selectRaw('TIME(islim_consumos_acumulados_details.date_end) as time_transaction_end')
      ->where([
        ['islim_consumos_acumulados_details.msisdn', $msisdn],
        ['islim_consumos_acumulados_details.date_start', '>=', '2021-07-16 00:00:00'],
      ]
      )
      ->where(DB::raw('DATE(islim_consumos_acumulados_details.date_start)'), '=', $date)
      ->orderBy('islim_consumos_acumulados_details.date_start', 'DESC');

    // Log::info("msisdn: ".$msisdn);
    // Log::info("date: ".$date);
    // Log::info("dataquery --> ".$data->toSql());

    $data = $data->get();

    return $data;

  }

  // public static function getConsuption($filters = [])
  // {
  //     $query = self::select(
  //         'islim_cdr_data_cons_details.msisdn',
  //         DB::raw('sum(islim_cdr_data_cons_details.consumo) as consuption'),
  //         'islim_cdr_data_cons_details.service_desc as title',
  //         'islim_cdr_data_cons_details.codeAltan',
  //         'islim_cdr_data_cons_details.date_start as date_reg',
  //         'islim_cdr_data_cons_details.service_activated as date_sup_be',
  //         'islim_cdr_data_cons_details.service_expired as date_sup_en',
  //         DB::raw('count(distinct(date(islim_cdr_data_cons_details.date_start))) as days'),
  //         'islim_cdr_data_cons_details.service_type as type'
  //     )
  //         ->whereNotNull('islim_cdr_data_cons_details.service_desc')
  //         ->where('islim_cdr_data_cons_details.date_start', '>=', '2020-10-05 00:00:00');

  //     if (is_array($filters) && count($filters)) {
  //         if (!empty($filters['dateb']) && !empty($filters['datee'])) {
  //             $query = $query->whereBetween(
  //                 'islim_cdr_data_cons_details.service_activated',
  //                 [$filters['dateb'], $filters['datee']]
  //             );
  //         }

  //         if (!empty($filters['dateb']) && empty($filters['datee'])) {
  //             $query = $query->where('islim_cdr_data_cons_details.service_activated', '>=', $filters['dateb']);
  //         }

  //         if (empty($filters['dateb']) && !empty($filters['datee'])) {
  //             $query = $query->where('islim_cdr_data_cons_details.service_activated', '<=', $filters['datee']);
  //         }

  //         if (!empty($filters['msisdn'])) {
  //             $query = $query->where('islim_cdr_data_cons_details.msisdn', $filters['msisdn']);
  //         }
  //     }

  //     $query = $query->groupBy(
  //         'islim_cdr_data_cons_details.msisdn',
  //         'islim_cdr_data_cons_details.codeAltan',
  //         'islim_cdr_data_cons_details.service_activated'
  //     )
  //         ->orderBy('islim_cdr_data_cons_details.service_activated', 'DESC');

  //     return $query;

  // }

  public static function getTotalconsuption($filters = [])
  {
    $data = Sale::getConnect('R')
      ->select('msisdn')
      ->whereIn('type', ['R', 'P', 'SR'])
      ->whereIn('status', ['A', 'E'])
      ->whereNotIn('sale_type',['F']);

    if (is_array($filters) && count($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $data->whereBetween('date_reg', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $data->where('date_reg', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $data->where('date_reg', '<=', $filters['datee']);
      }

      if (!empty($filters['msisdn'])) {
        $data->where('msisdn', $filters['msisdn']);
      }
    }

    return $data->count();
  }

  public static function getConsuptionV2($filters = [])
  {
    $data = Sale::getConnect('R')
      ->select(
        'islim_sales.id',
        'islim_sales.msisdn',
        'islim_sales.date_reg as date_reg_rec',
        'islim_sales.codeAltan',
        'islim_sales.type',
        'islim_services.title',
        'islim_services.description',
        'islim_periodicities.days as period'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_sales.services_id'
      )
      ->join(
        'islim_periodicities',
        'islim_periodicities.id',
        'islim_services.periodicity_id'
      )
      ->whereIn('islim_sales.type', ['R', 'P', 'SR'])
      ->whereIn('islim_sales.status', ['A', 'E'])
      ->whereNotIn('islim_sales.sale_type',['F']);

    if (is_array($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $data->whereBetween('islim_sales.date_reg', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $data->where('islim_sales.date_reg', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $data->where('islim_sales.date_reg', '<=', $filters['datee']);
      }

      if (!empty($filters['msisdn'])) {
        $data->where('islim_sales.msisdn', $filters['msisdn']);
      }

      if (!empty($filters['length'])) {
        $data->limit($filters['length'])->skip($filters['start']);
      }
    }

    $data = $data->orderBy('islim_sales.date_reg', 'DESC')->get();

    foreach ($data as $sale) {
      $con = self::getConsuptionByDnAndService($sale->id);

      $sale->consuption = !empty($con) ? $con->consuption : 0;
      $sale->throttling = !empty($con) ? $con->throttling : 0;
      $sale->title .= ' | ' . $sale->description;
      $sale->date_reg    = $sale->date_reg_rec;
      $sale->date_sup_be = !empty($con) ? $con->date_sup_be : null;
      $sale->date_sup_en = !empty($con) ? $con->date_sup_en : null;
      $sale->days        = !empty($con) ? $con->days : 0;
    }

    return $data;
  }

  public static function getConsuptionByDnAndService($sale = false)
  {
    if ($sale) {
      return self::getConnect('R')
        ->select(
          DB::raw('sum(CASE WHEN islim_consumos_acumulados_details.type <> "T" THEN islim_consumos_acumulados_details.consumo END) as consuption'),
          DB::raw('sum(CASE WHEN islim_consumos_acumulados_details.type = "T" THEN islim_consumos_acumulados_details.consumo END) as throttling'),
          'islim_consumos_acumulados_details.date_start as date_reg',
          'islim_consumos_acumulados_details.service_activated as date_sup_be',
          'islim_consumos_acumulados_details.service_expired as date_sup_en',
          DB::raw('count(distinct(date(islim_consumos_acumulados_details.date_start))) as days')
        )
        ->whereNotNull('islim_consumos_acumulados_details.service_desc')
        ->where([
          ['islim_consumos_acumulados_details.sales_id', $sale],
          ['islim_consumos_acumulados_details.date_start', '>=', '2021-07-16 00:00:00'],
        ])
        ->groupBy('islim_consumos_acumulados_details.sales_id')
        ->first();
    }

    return null;
  }
}
