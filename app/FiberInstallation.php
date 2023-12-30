<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FiberInstallation extends Model
{
  protected $table = 'islim_installations';

  protected $fillable = [
    'id',
    'clients_dni',
    'address_instalation',
    'pack_id',
    'service_id',
    'inv_article_id',
    'price',
    'seller',
    'installer',
    'date_instalation',
    'paid',
    'is_migration',
    'status',
    'date_reg',
    'user_mod',
    'date_mod',
    'date_install',
    'date_paid',
    'msisdn',
    'route',
    'house_number',
    'colony',
    'municipality',
    'reference',
    'lat',
    'lng',
    'father_install',
    'num_rescheduling',
    'group_install',
    'id_fiber_zone',
    'id_state',
    'id_fiber_city'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\FiberInstallation
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new FiberInstallation;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getFiberInstallations($filters = [])
  {

    /*Selecciono los grupos de instalacion que cumplen con los filtros*/

    $inst_groups = self::getConnect('R')
      ->select('group_install');

    if (is_array($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $inst_groups->whereBetween('date_instalation', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $inst_groups->where('date_instalation', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $inst_groups->where('date_instalation', '<=', $filters['datee']);
      }

      if (!empty($filters['msisdn_select'])) {
        $inst_groups->whereIn('msisdn', $filters['msisdn_select']);
      }

      if (!empty($filters['status'])) {
        $inst_groups->where('status', $filters['status']);
      }
    }

    $inst_groups = $inst_groups->get();

    /*Selecciono el maximo id de cada grupo para obtener el registro mas actual del historico*/

    $reg_acts = self::getConnect('R')
      ->select('id', 'group_install', DB::raw('MAX(id) as max_id'))
      ->whereIn('group_install', $inst_groups->pluck('group_install'))
      ->groupBy('group_install')
      ->get();

    $data = self::getConnect('R')
      ->select(
        'islim_installations.id',
        'islim_installations.group_install',
        'islim_installations.msisdn',
        'islim_installations.reason_delete',
        DB::raw('CONCAT(islim_clients.name, " ", islim_clients.last_name) AS client'),
        'islim_clients.email as client_email',
        'islim_clients.phone_home as client_phone',
        'islim_installations.address_instalation as address_instalation',
        DB::raw('CONCAT(user_seller.name, " ", user_seller.last_name) AS seller'),
        DB::raw('CONCAT(user_installer.name, " ", user_installer.last_name) AS installer'),
        'user_installer.phone as installer_phone',
        'islim_installations.date_instalation as date_presell',
        'islim_installations.date_install',
        'islim_installations.paid',
        'islim_installations.status',
        'islim_installations.num_rescheduling',
        'islim_fiber_zone.name AS zone_name'
      )
      ->join('islim_clients', 'islim_clients.dni', 'islim_installations.clients_dni')
      ->join('islim_users as user_seller', 'user_seller.email', 'islim_installations.seller')
      ->join('islim_users as user_installer', 'user_installer.email', 'islim_installations.installer')
      ->leftJoin('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_installations.id_fiber_zone')
      ->whereIn('islim_installations.id', $reg_acts->pluck('max_id'))
      ->groupBy('islim_installations.group_install');

    // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
    //       return is_numeric($binding) ? $binding : "'{$binding}'";
    //   })->toArray());

    // Log::info($query);

    //print_r($query);

    if (!empty($filters['status'])) {
      $data->where('islim_installations.status', $filters['status']);
    }
    if (!empty($filters['coverage_area'])) {
      $data->where('islim_fiber_zone.id', $filters['coverage_area']);
    }

    $data = $data->get();
    return $data;
  }

  public static function getFiberInstallationsDetails($group_install)
  {
    $data = self::getConnect('R')
      ->select(
        DB::raw('CONCAT(user_installer.name, " ", user_installer.last_name) AS installer'),
        'user_installer.phone as installer_phone',
        'islim_installations.date_instalation',
        'islim_installations.status',
        'islim_installations2.date_instalation as date_rescheduling'
      )
      ->leftJoin(
        'islim_installations as islim_installations2', function ($join) {
          $join->on('islim_installations.id', '=', 'islim_installations2.father_install');
        }
      )
      ->join('islim_users as user_installer', 'user_installer.email', 'islim_installations.installer')
      ->where('islim_installations.group_install', $group_install);

    return $data->orderBy('islim_installations.id', 'DESC')->get();

  }

  public static function getFiberInstallationsReport($filters = [])
  {
    /*Selecciono los grupos de instalacion que cumplen con los filtros*/

    $inst_groups = self::getConnect('R')
      ->select('group_install');

    if (is_array($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $inst_groups->whereBetween('date_instalation', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $inst_groups->where('date_instalation', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $inst_groups->where('date_instalation', '<=', $filters['datee']);
      }

      if (!empty($filters['msisdn_select'])) {
        $inst_groups->whereIn('msisdn', $filters['msisdn_select']);
      }

      if (!empty($filters['status'])) {
        $inst_groups->where('status', $filters['status']);
      }

    }

    $inst_groups = $inst_groups->get();

    $data = self::getConnect('R')
      ->select(
        'islim_installations.group_install as id_proccess',
        'islim_installations.msisdn',
        DB::raw('CONCAT(islim_clients.name, " ", islim_clients.last_name) AS client'),
        'islim_clients.email as client_email',
        'islim_clients.phone_home as client_phone',
        'islim_installations.address_instalation as address_instalation',
        DB::raw('CONCAT(user_seller.name, " ", user_seller.last_name) AS seller'),
        DB::raw('CONCAT(user_installer.name, " ", user_installer.last_name) AS installer'),
        'user_installer.phone as installer_phone',
        'islim_installations.date_instalation as date_presell',
        'islim_installations.date_install',
        'islim_installations.paid',
        'islim_installations.status',
        'islim_installations2.date_instalation as date_rescheduling',
        'islim_installations.num_rescheduling',
        'islim_fiber_zone.name AS zone_name'
      )
      ->join('islim_clients', 'islim_clients.dni', 'islim_installations.clients_dni')
      ->join('islim_users as user_seller', 'user_seller.email', 'islim_installations.seller')
      ->join('islim_users as user_installer', 'user_installer.email', 'islim_installations.installer')
      ->leftJoin(
        'islim_installations as islim_installations2', function ($join) {
          $join->on('islim_installations.id', '=', 'islim_installations2.father_install');
        }
      )
      ->leftJoin('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_installations.id_fiber_zone')
      ->where('islim_installations.status', '<>', 'T')
      ->whereIn('islim_installations.group_install', $inst_groups->pluck('group_install'))
      ->orderBy('islim_installations.group_install', 'ASC')->orderBy('islim_installations.id', 'ASC');

    if (!empty($filters['status'])) {
      $data->where('islim_installations.status', $filters['status']);
    }

    if (!empty($filters['coverage_area'])) {
      $data->where('islim_fiber_zone.id', $filters['coverage_area']);
    }

    $data = $data->get();

    return $data;
  }

  public static function getCoordInstalation($msisdn)
  {
    $data = self::getConnect('R')
      ->select(
        'islim_installations.lat',
        'islim_installations.lng')
      ->where('islim_installations.msisdn', $msisdn)
      ->first();
    return $data;
  }

  public static function getDetailInstalation($dni_client, $id_fiber_zone)
  {
    $data = self::getConnect('R')
      ->select(
        'islim_installations.id',
        'islim_installations.pack_id',
        'islim_installations.service_id',
        'islim_installations.price',
        'islim_installations.installer',
        'islim_installations.id_fiber_city')
      ->where([
        ['islim_installations.clients_dni', $dni_client],
        ['islim_installations.status', 'A'],
        ['islim_installations.id_fiber_zone', $id_fiber_zone]])
      ->first();
    return $data;
  }

  public static function setInstallStatus($id, $status, $msisdn = null, $dateInstall = false)
  {
    self::getConnect('W')
      ->where('id', $id)
      ->update([
        'status'       => $status,
        'msisdn'       => $msisdn,
        'date_install' => $dateInstall]);
  }


  public static function getFiberInstallationsReportByStatus($filters){
    $data = self::getConnect('R')
      ->select(
        'islim_installations.id',
        'islim_installations.group_install',
        'islim_installations.msisdn',
        DB::raw('CONCAT(islim_clients.name, " ", islim_clients.last_name) AS client'),
        'islim_installations.date_instalation', //Fecha de la cita
        'islim_client_netweys.date_reg as date_activation',//Fecha en la que se activo el Serv
        DB::raw('CONCAT(user_seller.name, " ", user_seller.last_name) AS seller'),
        'islim_installations.colony',
        'islim_fiber_zone.name AS zone_name',
        'islim_inv_arti_details.imei as mac',
        'islim_installations.num_rescheduling',
        'islim_installations.date_reg', //Fecha en la que se realizo la venta 
        DB::raw('TIMESTAMPDIFF (DAY, islim_installations.date_reg, NOW() ) as antiquity'),
        'islim_installations.status'
      )
      ->join('islim_clients', 'islim_clients.dni', 'islim_installations.clients_dni')
      ->join('islim_users as user_seller', 'user_seller.email', 'islim_installations.seller')
      ->leftJoin('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', 'islim_installations.msisdn')
      ->Join('islim_client_netweys', 'islim_client_netweys.clients_dni', 'islim_installations.clients_dni')
      ->leftJoin('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_installations.id_fiber_zone');
    
      if (!empty($filters['status'])) {
        $data->where('islim_installations.status', $filters['status']);
      }
      
      if (!empty($filters['dateFilter'] ) && $filters['dateFilter'] == "date_sell" ) {
        $data->where('islim_installations.date_instalation', ">=",$filters['dateb']);
        $data->where('islim_installations.date_instalation', "<=",$filters['datee']);
      }
      if (!empty($filters['dateFilter'] ) && $filters['dateFilter'] == "date_activation" ) {
        $data->where('islim_client_netweys.date_reg', ">=",$filters['dateb']);
        $data->where('islim_client_netweys.date_reg', "<=",$filters['datee']);
      }

      $data->groupBy('islim_installations.group_install');
      return $data->get();
  }

}
