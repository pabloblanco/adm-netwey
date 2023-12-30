<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TelmovPay extends Model
{
  protected $table = 'islim_telmovpay';

  protected $fillable = [
    'id',
    'dni',
    'msisdn',
    'initial_amount',
    'total_amount',
    'sale_id',
    'seller_name',
    'cve_seller',
    'cve_branch',
    'branch_name',
    'status',
    'date_reg',
    'date_process',
    'cve_solicitud',
    'date_enganche'];

  public $timestamps = false;

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\TelmovPay
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new TelmovPay;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getReport($filters = [])
  {
    $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

    $data = self::getConnect('R')
      ->select(
        'islim_telmovpay.msisdn',
        'islim_telmovpay.initial_amount',
        'islim_telmovpay.total_amount',
        'islim_telmovpay.status',
        'islim_telmovpay.date_reg',
        'islim_telmovpay.date_process',
        DB::raw('CONCAT(
        IFNULL(seller.name,"")," ",
        IFNULL(seller.last_name,"") ) AS nameSellerFull'),
        DB::raw('CONCAT(
        IFNULL(coord.name,"")," ",
        IFNULL(coord.last_name,"") ) AS nameCoordFull'),
        DB::raw('CONCAT(
        IFNULL(islim_clients.name,"")," ",
        IFNULL(islim_clients.last_name,"") ) AS nameClientFull')
      )
      ->leftJoin(
        'islim_sales',
        'islim_sales.msisdn',
        'islim_telmovpay.msisdn'
      )
      ->leftJoin(
        'islim_users as seller',
        function ($join) use ($orgs) {
          $join->on('seller.email', '=', 'islim_sales.users_email')
            ->whereIn('seller.id_org', $orgs->pluck('id'));
        }
      )
      ->leftJoin(
        'islim_users as coord',
        function ($join) use ($orgs) {
          $join->on('coord.email', '=', 'seller.parent_email')
            ->whereIn('coord.id_org', $orgs->pluck('id'));
        }
      )
      ->leftJoin(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_telmovpay.msisdn'
      )
      ->leftJoin(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->where([
        ['islim_telmovpay.status', '!=', 'T'],
        ['islim_sales.type', 'P'],
      ]);

    if (count($filters)) {
      if (!empty($filters['status'])) {
        $data->where('islim_telmovpay.status', $filters['status']);
      }

      if (!empty($filters['seller'])) {
        $data->where('seller.email', $filters['seller']);
      }

      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data->whereBetween('islim_telmovpay.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data->where('islim_telmovpay.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data->where('islim_telmovpay.date_reg', '<=', $filters['dateEnd']);
      }

      if (!empty($filters['coord'])) {
        $sub = User::select('email')
          ->where('parent_email', $filters['coord'])
          ->get()
          ->pluck('email');

        $sub[] = $filters['coord'];

        $data->whereIn('seller.email', $sub);
      }
    }

    return $data->get();
  }
}
