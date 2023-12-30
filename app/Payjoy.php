<?php

namespace App;

use App\Organization;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Payjoy extends Model
{
  protected $table = 'islim_payjoy';

  protected $fillable = [
    'id',
    'dni',
    'msisdn',
    'pack',
    'amount',
    'total_amount',
    'phone_payjoy',
    'customer_id',
    'customer_name',
    'monthly_cost',
    'weekly_cost',
    'finance_id',
    'months',
    'status',
    'date_reg',
    'date_process'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Payjoy
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Payjoy;
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
        'islim_payjoy.msisdn',
        'islim_payjoy.amount',
        'islim_payjoy.total_amount',
        'islim_payjoy.finance_id',
        'islim_payjoy.status',
        'islim_payjoy.date_reg',
        'islim_payjoy.date_process',
        'seller.name as seller_name',
        'seller.last_name as seller_last_name',
        'coord.name as coord_name',
        'coord.last_name as coord_last_name',
        'islim_clients.name as client_name',
        'islim_clients.last_name as client_last_name'
      )
      ->leftJoin(
        'islim_sales',
        'islim_sales.msisdn',
        'islim_payjoy.msisdn'
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
        'islim_payjoy.msisdn'
      )
      ->leftJoin(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->where([
        ['islim_payjoy.status', '!=', 'T'],
        ['islim_sales.type', 'P'],
      ]);

    if (count($filters)) {
      if (!empty($filters['status'])) {
        $data->where('islim_payjoy.status', $filters['status']);
      }

      if (!empty($filters['seller'])) {
        $data->where('seller.email', $filters['seller']);
      }

      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $data->whereBetween(
          'islim_payjoy.date_reg',
          [
            date('Y-m-d', strtotime($filters['dateb'])) . ' 00:00:00',
            date('Y-m-d', strtotime($filters['datee'])) . ' 23:59:59',
          ]);
      }

      if (empty($filters['dateb']) && !empty($filters['datee'])) {
        $data->where(
          'islim_payjoy.date_reg',
          '<=',
          date('Y-m-d', strtotime($filters['datee'])) . ' 23:59:59'
        );
      }

      if (!empty($filters['dateb']) && empty($filters['datee'])) {
        $data->where(
          'islim_payjoy.date_reg',
          '>=',
          date('Y-m-d', strtotime($filters['dateb'])) . ' 00:00:00'
        );
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

    return $data;
  }
}
