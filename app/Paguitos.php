<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Paguitos extends Model
{
  protected $table = 'islim_paguitos';

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
 * @return App\Paguitos
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Paguitos;
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
        'islim_paguitos.msisdn',
        'islim_paguitos.initial_amount',
        'islim_paguitos.total_amount',
        'islim_paguitos.status',
        'islim_paguitos.date_reg',
        'islim_paguitos.date_process',
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
        'islim_paguitos.msisdn'
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
        'islim_paguitos.msisdn'
      )
      ->leftJoin(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->where([
        ['islim_paguitos.status', '!=', 'T'],
        ['islim_sales.type', 'P'],
      ]);

    if (count($filters)) {
      if (!empty($filters['status'])) {
        $data->where('islim_paguitos.status', $filters['status']);
      }

      if (!empty($filters['seller'])) {
        $data->where('seller.email', $filters['seller']);
      }

      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data->whereBetween('islim_paguitos.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data->where('islim_paguitos.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data->where('islim_paguitos.date_reg', '<=', $filters['dateEnd']);
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
