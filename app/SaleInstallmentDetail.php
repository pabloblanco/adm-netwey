<?php

namespace App;

use App\Organization;
use App\PayInstallment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SaleInstallmentDetail extends Model
{
  protected $table = 'islim_sales_installments_detail';

  protected $fillable = [
    'unique_transaction',
    'amount',
    'n_quote',
    'conciliation_status',
    'date_reg',
    'date_update',
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
      $obj = new SaleInstallmentDetail;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getRREReport($filters = [])
  {
    /*
    La subconsulta a la tabla islim_pay_installments es solo para validar de que al menos tenga un reporte de entrega de dinero de vendedor a coordinador.
     */
    $sub = DB::raw('(SELECT count(id) FROM islim_pay_installments WHERE islim_pay_installments.status != "T" AND islim_pay_installments.sale_installment_detail = islim_sales_installments_detail.id)');

    $rres = self::getConnect('R')->select(
      'islim_sales_installments.unique_transaction',
      'islim_sales_installments.msisdn',
      'islim_sales_installments.date_reg_alt as date_sell',
      'islim_dts_organizations.business_name',
      'seller.name as name_seller',
      'seller.last_name as last_name_seller',
      'coord.name as name_coord',
      'coord.last_name as last_name_coord',
      'islim_sales_installments_detail.id',
      'islim_sales_installments_detail.n_quote',
      'islim_sales_installments_detail.amount',
      'islim_sales_installments_detail.date_update'
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
      ->join(
        'islim_dts_organizations',
        'islim_dts_organizations.id',
        'seller.id_org'
      )
      ->where([
        ['islim_sales_installments_detail.status', 'A'],
        [$sub, '>', 0]]);

    if (is_array($filters) && count($filters)) {
      //fechas
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $rres = $rres->whereBetween(
          'islim_sales_installments.date_reg_alt',
          [
            date('Y-m-d', strtotime($filters['dateb'])) . ' 00:00:00',
            date('Y-m-d', strtotime($filters['datee'])) . ' 23:59:59',
          ]);
      }

      if (empty($filters['dateb']) && !empty($filters['datee'])) {
        $rres = $rres->where(
          'islim_sales_installments.date_reg_alt',
          '<=',
          date('Y-m-d', strtotime($filters['datee'])) . ' 23:59:59'
        );
      }

      if (!empty($filters['dateb']) && empty($filters['datee'])) {
        $rres = $rres->where(
          'islim_sales_installments.date_reg_alt',
          '>=',
          date('Y-m-d', strtotime($filters['dateb'])) . ' 00:00:00'
        );
      }

      //Organizacion
      if (!empty($filters['org'])) {
        $rres = $rres->where('islim_dts_organizations.id', $filters['org']);
      } else {
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $rres = $rres->whereIn('islim_dts_organizations.id', $orgs->pluck('id'));
      }

      //Coordinador
      if (!empty($filters['coord'])) {
        $rres = $rres->where('coord.email', $filters['coord']);
      }

      //Vendedor
      if (!empty($filters['seller'])) {
        $rres = $rres->where('seller.email', $filters['seller']);
      }

      //Estatus
      if (!empty($filters['status'])) {
        if ($filters['status'] == 'V') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'V'
          );
        }

        if ($filters['status'] == 'P') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'C'
          );
        }

        if ($filters['status'] == 'A') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'P'
          );
        }

        /*
        El estatus CV serian los reportes de dinero rechazados debido a que el dinero volvio a caer en manos del vendedor pero tiene un registro en la tabla islim_pay_installments por lo tanto hizo un reporte
         */
        if ($filters['status'] == 'I') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'CV'
          );
        }

      }

      //Alert
      if (!empty($filters['alert'])) {
        if ($filters['alert'] == 'G') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'CV'
          );
        }

        if ($filters['alert'] == 'Gr') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'C'
          );
        }

        if ($filters['alert'] == 'B' ||
          $filters['alert'] == 'O' ||
          $filters['alert'] == 'R') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'V'
          );
        }

        if ($filters['alert'] == 'G') {
          $rres = $rres->where(
            'islim_sales_installments_detail.conciliation_status',
            'CV'
          );
        }

      }
    }

    $rres = $rres->get();

    foreach ($rres as $key => $rre) {
      $pay = PayInstallment::getLastReportPay(
        $rre->id,
        count($filters) && !empty($filters['alert']) ? ['alert' => $filters['alert']] : []
      );

      if (!empty($pay)) {
        $rre->date_reg    = $pay->date_reg;
        $rre->status_rre  = $pay->status;
        $rre->date_update = $pay->date_update;
        $rre->date_acept  = $pay->date_acept;
      } else {
        $rres->pull($key);
      }
    }

    return $rres;
  }

  public static function getDetailByTransaction($transaction = false)
  {
    if ($transaction) {
      return self::getConnect('R')->select(
        'n_quote',
        'unique_transaction',
        'amount',
        'conciliation_status',
        'date_reg'
      )
        ->where([
          ['status', 'A'],
          ['unique_transaction', $transaction]])
        ->orderBy('n_quote', 'ASC')
        ->get();
    }

    return [];
  }

  /**
   * Metodo para actualizar detalle de venta en abono
   * @param String $id
   *
   * @return App\Models\SaleInstallmentDetail
   */
  public static function updateRecptionStatus($id, $data = [])
  {
    return self::getConnect('W')
      ->where('id', $id)
      ->update($data);
  }
}
