<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PayInstallment extends Model
{
  protected $table = 'islim_pay_installments';

  protected $fillable = [
    'sale_installment_detail',
    'amount',
    'id_report',
    'user_process',
    'reason',
    'view',
    'date_reg',
    'date_nom',
    'date_reg',
    'date_acept',
    'date_update',
    'alert_orange_send',
    'alert_red_send',
    'status'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new PayInstallment;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getLastReportPay($saleDetailId = false, $filters = [])
  {
    if ($saleDetailId) {
      $data = self::getConnect('R')->select(
        'id',
        'date_reg',
        'date_acept',
        'date_update',
        'status'
      )
        ->where([
          ['status', '!=', 'T'],
          ['sale_installment_detail', $saleDetailId]])
        ->orderBy('date_update', 'DESC')
        ->first();

      if (count($filters)) {
        if (!empty($filters['alert'])) {
          $now   = time();
          $dater = strtotime($data->date_reg);

          if ($filters['alert'] == 'R') {
            if (strtotime('+ 12 hours', $dater) >= $now) {
              return null;
            }

          }

          if ($filters['alert'] == 'O') {
            if (strtotime('+ 6 hours', $dater) < $now || strtotime('+ 12 hours', $dater) >= $now) {
              return null;
            }

          }

          if ($filters['alert'] == 'B') {
            if (strtotime('+ 6 hours', $dater) <= $now) {
              return null;
            }

          }
        }
      }

      return $data;
    }

    return null;
  }

  public static function getAlertReport()
  {
    $diff = DB::raw("TIME_FORMAT(TIME(TIMEDIFF(CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City'),islim_pay_installments.date_reg)), '%H')");

    $consult = DB::raw("(TIME_FORMAT(TIME(TIMEDIFF(CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City'),islim_pay_installments.date_reg)), '%H')) as diff");

    $data = self::getConnect('R')->select(
      'islim_pay_installments.id',
      'islim_pay_installments.date_reg',
      'islim_pay_installments.amount',
      'islim_pay_installments.alert_orange_send',
      'islim_pay_installments.alert_red_send',
      'seller.name as name_seller',
      'seller.last_name as last_name_seller',
      'coord.name as name_coord',
      'coord.last_name as last_name_coord',
      'islim_sales_installments_detail.unique_transaction',
      $consult
    )
      ->join(
        'islim_sales_installments_detail',
        'islim_sales_installments_detail.id',
        'islim_pay_installments.sale_installment_detail'
      )
      ->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales_installments_detail.unique_transaction'
      )
      ->join(
        'islim_users as seller',
        'seller.email',
        'islim_sales_installments.seller'
      )
      ->join(
        'islim_users as coord',
        'coord.email',
        'islim_sales_installments.coordinador'
      )
      ->where([
        ['islim_pay_installments.status', 'V'],
        [$diff, '>=', 6]])
      ->get();

    return $data;
  }

  public static function getDebUser($user = false)
  {
    if ($user) {
      return self::getConnect('R')->select('islim_pay_installments.amount')
        ->join(
          'islim_sales_installments_detail',
          'islim_sales_installments_detail.id',
          'islim_pay_installments.sale_installment_detail'
        )
        ->join(
          'islim_sales_installments',
          'islim_sales_installments.unique_transaction',
          'islim_sales_installments_detail.unique_transaction'
        )
        ->where([
          ['islim_pay_installments.status', 'C'],
          ['islim_sales_installments.coordinador', $user]])
        ->sum('islim_pay_installments.amount');
    }

    return 0;
  }

  public static function getDetailDeb($user = false)
  {
    if ($user) {
      return self::getConnect('R')->select(
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_sales_installments.msisdn',
        'islim_sales_installments.unique_transaction',
        'islim_sales_installments_detail.n_quote as quote',
        'islim_pay_installments.amount',
        'islim_pay_installments.date_update as date',
        'islim_pay_installments.id as pay',
        'islim_packs.title as pack',
        'islim_services.title as service',
        'islim_inv_articles.title as artic'
      )
        ->join(
          'islim_sales_installments_detail',
          'islim_sales_installments_detail.id',
          'islim_pay_installments.sale_installment_detail'
        )
        ->join(
          'islim_sales_installments',
          'islim_sales_installments.unique_transaction',
          'islim_sales_installments_detail.unique_transaction'
        )
        ->join(
          'islim_clients',
          'islim_clients.dni',
          'islim_sales_installments.client_dni'
        )
        ->join(
          'islim_services',
          'islim_services.id',
          'islim_sales_installments.service_id'
        )
        ->join(
          'islim_packs',
          'islim_packs.id',
          'islim_sales_installments.pack_id'
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.msisdn',
          'islim_sales_installments.msisdn'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_pay_installments.status', 'C'],
          ['islim_sales_installments.coordinador', $user]])
        ->get();
    }

    return [];
  }

  public static function getGroupDetailDeb($user = false)
  {
    if ($user) {
      return self::getConnect('R')->select(
        'islim_sales_installments.unique_transaction',
        DB::raw('SUM(islim_pay_installments.amount) as amount'),
        'islim_pay_installments.date_acept',
        'islim_pay_installments.id_report',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.email'
      )
        ->join(
          'islim_sales_installments_detail',
          'islim_sales_installments_detail.id',
          'islim_pay_installments.sale_installment_detail'
        )
        ->join(
          'islim_sales_installments',
          'islim_sales_installments.unique_transaction',
          'islim_sales_installments_detail.unique_transaction'
        )
        ->join(
          'islim_users',
          'islim_users.email',
          'islim_sales_installments.seller'
        )
        ->where([
          ['islim_pay_installments.status', 'C'],
          ['islim_sales_installments.coordinador', $user]])
        ->groupBy('islim_pay_installments.id_report')
        ->get();
    }

    return [];
  }

  public static function getDetailReport($report = false)
  {
    if ($report) {
      return self::getConnect('R')->select(
        'islim_pay_installments.amount',
        'islim_sales_installments.date_reg_alt',
        'islim_sales_installments.msisdn',
        'islim_sales_installments.unique_transaction',
        'islim_sales_installments_detail.n_quote as quote',
        'islim_packs.title as pack',
        'islim_services.title as service',
        'islim_inv_articles.title as artic',
        'islim_inv_articles.artic_type'
      )
        ->join(
          'islim_sales_installments_detail',
          'islim_sales_installments_detail.id',
          'islim_pay_installments.sale_installment_detail'
        )
        ->join(
          'islim_sales_installments',
          'islim_sales_installments.unique_transaction',
          'islim_sales_installments_detail.unique_transaction'
        )
        ->join(
          'islim_services',
          'islim_services.id',
          'islim_sales_installments.service_id'
        )
        ->join(
          'islim_packs',
          'islim_packs.id',
          'islim_sales_installments.pack_id'
        )
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.msisdn',
          'islim_sales_installments.msisdn'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where('islim_pay_installments.id_report', $report)
        ->get();
    }

    return [];
  }

  public static function getDebInst($user = false)
  {
    if ($user) {
      return self::getConnect('W')->select(
        'islim_pay_installments.id',
        'islim_pay_installments.amount',
        'islim_pay_installments.sale_installment_detail',
        'islim_sales_installments.unique_transaction',
        'islim_config_installments.quotes'
      )
        ->join(
          'islim_sales_installments_detail',
          'islim_sales_installments_detail.id',
          'islim_pay_installments.sale_installment_detail'
        )
        ->join(
          'islim_sales_installments',
          'islim_sales_installments.unique_transaction',
          'islim_sales_installments_detail.unique_transaction'
        )
        ->join(
          'islim_config_installments',
          'islim_config_installments.id',
          'islim_sales_installments.config_id'
        )
        ->where([
          ['islim_pay_installments.status', 'C'],
          ['islim_sales_installments.coordinador', $user]])
        ->orderBy('islim_sales_installments.date_reg_alt', 'ASC')
        ->get();
    }

    return [];
  }

  public static function getTotalDebtByUser($email)
  {
    return PayInstallment::getConnect('R')
      ->select('islim_pay_installments.amount')
      ->join(
        'islim_sales_installments_detail',
        'islim_sales_installments_detail.id',
        'islim_pay_installments.sale_installment_detail'
      )
      ->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales_installments_detail.unique_transaction'
      )
      ->where([
        ['islim_pay_installments.status', 'C'],
        ['islim_sales_installments.coordinador', $email]])
      ->sum('islim_pay_installments.amount');
  }

  /**
   * Metodo para obtener listado de pagos en abono cobrados y los datos de sus vendedores
   */

  public static function getInstallmentReceptionLow($UserLow, $User)
  {
    return self::getConnect('R')
      ->select(
        'islim_pay_installments.id_report',
        'islim_sales_installments_detail.amount',
        'islim_sales_installments_detail.n_quote',
        'islim_pay_installments.date_update',
        'islim_users.email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_sales_installments.msisdn'
      )
      ->join(
        'islim_sales_installments_detail',
        'islim_sales_installments_detail.id',
        'islim_pay_installments.sale_installment_detail'
      )
      ->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales_installments_detail.unique_transaction'
      )
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_sales_installments.seller'
      )
      ->whereIn('islim_sales_installments.status', ['P', 'F'])
      ->where([
        ['islim_sales_installments.coordinador', $UserLow],
        ['islim_sales_installments.seller', $User],
        ['islim_sales_installments_detail.conciliation_status', 'V'],
        ['islim_sales_installments_detail.status', 'A'],
        ['islim_pay_installments.status', 'V']])
      ->get();
  }

  /**
   * Metodo para obtener listado de pagos en abono dado un id de reporte y un usuario
   * @param String $report
   * @param String $user
   *
   * @return App\Models\PayInstallment
   */
  public static function getListReception($report, $user)
  {
    return self::getConnect('R')
      ->select(
        'islim_pay_installments.id',
        'islim_pay_installments.sale_installment_detail',
        'islim_sales_installments_detail.unique_transaction'
      )
      ->join(
        'islim_sales_installments_detail',
        'islim_sales_installments_detail.id',
        'islim_pay_installments.sale_installment_detail'
      )
      ->join(
        'islim_sales_installments',
        'islim_sales_installments.unique_transaction',
        'islim_sales_installments_detail.unique_transaction'
      )
      ->where([
        ['islim_pay_installments.status', 'V'],
        ['islim_sales_installments_detail.status', 'A'],
        ['islim_sales_installments_detail.conciliation_status', 'V'],
        ['islim_pay_installments.id_report', $report],
        ['islim_sales_installments.coordinador', $user],
      ])
      ->get();
  }
/**
 * Metodo actualizar estatus de recepción de un pago en abono
 * @param String $id
 * @param Array $data
 *
 * @return App\Models\PayInstallment
 */
  public static function updateRecptionStatus($id, $data = [])
  {
    return self::getConnect('W')
      ->where('id', $id)
      ->update($data);
  }
}
