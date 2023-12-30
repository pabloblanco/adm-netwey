<?php

namespace App;

use App\Client;
use App\FiberClientZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
  protected $table = 'islim_clients';

  protected $fillable = [
    'dni',
    'user_mail',
    'reg_email',
    'name',
    'last_name',
    'address',
    'birthday',
    'email',
    'phone_home',
    'phone',
    'social',
    'campaign',
    'date_reg',
    'note',
    'contact_date',
    'isFiber'
  ];

  protected $primaryKey = 'dni';

  public $incrementing = false;

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
      $obj = new Client;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  /*
   *Retorna el nombre, apellido y email del cliente
   */
  public static function getInfoClient($dni)
  {
    return self::getConnect('R')
      ->select('islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.email')
      ->where('dni', $dni)
      ->first();
  }

  /*chequea disponibilidad de correo
   * modifica a minuscula el correo antes de comparar
   */
  public static function disposeMail($mail, $dni)
  {
    $info = Client::select(DB::raw('count(*) as mail_count'))
      ->where('dni', '!=', $dni)
      ->whereRaw('lower(email) = "' . strtolower($mail) . '"')
      ->first();
    return $info;
  }

  public static function getReport($seller = [], $date_ini = null, $date_end = null, $org = null, $islead = false)
  {
    $report = Client::getConnect('R')
      ->leftJoin('islim_users', 'islim_users.email', '=', 'islim_clients.reg_email')
      ->leftJoin('islim_users AS coo', 'coo.email', '=', 'islim_users.parent_email')
      ->leftJoin('islim_dts_organizations AS o', 'o.id', '=', 'islim_users.id_org')
      ->leftJoin('islim_client_netweys', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
      ->select(
        'islim_clients.date_reg',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.email',
        'islim_clients.phone_home',
        'islim_clients.address',
        'islim_clients.note',
        'islim_clients.contact_date',
        'islim_clients.campaign',
        'islim_users.name AS seller_name',
        'islim_users.last_name AS seller_last_name',
        'islim_client_netweys.msisdn',
        'o.business_name',
        'coo.name as name_coord',
        'coo.last_name as last_name_coord'
      )
      ->whereNull('islim_client_netweys.msisdn')
      ->orderBy('islim_clients.date_reg');

    if (!empty($seller) && count($seller)) {
      $report = $report->whereIn('islim_clients.reg_email', $seller);
    }

    if ($islead) {
      $tablereg = 'islim_clients.date_reg';
    } else {
      $tablereg = 'islim_client_netweys.date_reg';
    }

    if (!empty($date_ini) && !empty($date_end)) {
      $report = $report->whereBetween($tablereg, [$date_ini . ' 00:00:00', $date_end . ' 23:59:59']);
    } else {
      if (!empty($date_ini)) {
        $report = $report->where($tablereg, '>=', $date_ini . ' 00:00:00');
      }

      if (!empty($date_end)) {
        $report = $report->where($tablereg, '<=', $date_end . ' 23:59:59');
      }

    }

    if (!empty($org)) {
      $sellers = User::getConnect('R')->select('email')->where([['status', 'A'], ['id_org', $org]])->get()->pluck('email');
      $report  = $report->whereIn('islim_clients.reg_email', $sellers);
    }

    return $report; //->get();
  }

  public static function getClientsUnSoldRecordsForReportsOS($date_ini, $date_end)
  {
    $dini = substr($date_ini, 6, 4) . "-" . substr($date_ini, 3, 2) . "-" . substr($date_ini, 0, 2) . " 00:00:00";
    $dend = substr($date_end, 6, 4) . "-" . substr($date_end, 3, 2) . "-" . substr($date_end, 0, 2) . " 23:59:59";

    $wp = DB::raw("(select w.dn from islim_webpay as w where w.status in ('A','E'))");
    $mp = DB::raw("(SELECT DISTINCT(o.client_id) from islim_ordens as o where o.id in (SELECT m.order_id from islim_mercado_pago as m where m.order_id is not NULL and m.type = 'S' and m.status = 'approved'))");

    $clients = Client::getConnect('R')->select('name as Nombre', 'last_name as Apellido', 'email as Email', 'phone_home as Telefono', 'address_store as Direccion', DB::raw('DATE_FORMAT(date_reg,"%d/%m/%Y %H:%I:%S") as Fecha_Registro'), 'islim_cars.campaign as Campaña')
      ->leftJoin('islim_cars', 'islim_clients.dni', 'islim_cars.ine')
      ->where([
        ['date_reg', '>=', $dini],
        ['date_reg', '<=', $dend],
      ])
      ->whereNull('user_mail')
      ->whereNull('reg_email')
      ->whereNotNull('email')
      ->whereNotIn('dni', [$wp])
      ->whereNotIn('dni', [$mp])
      ->groupBy('dni');

    return $clients;
  }

  public static function getClientsUnSoldRecordsForReportCron()
  {

    $date = date('d/m/Y', strtotime("-3 days"));
    $dini = substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2) . " 00:00:00";
    $dend = substr($date, 6, 4) . "-" . substr($date, 3, 2) . "-" . substr($date, 0, 2) . " 23:59:59";

    $wp = DB::raw("(select w.dn from islim_webpay as w where w.status in ('A','E'))");
    $mp = DB::raw("(SELECT DISTINCT(o.client_id) from islim_ordens as o where o.id in (SELECT m.order_id from islim_mercado_pago as m where m.order_id is not NULL and m.type = 'S' and m.status = 'approved'))");

    $clients = Client::getConnect('R')->select('name as Nombre', 'last_name as Apellido', 'email as Email', 'phone_home as Telefono', 'address_store as Direccion', 'colony_store as Colonia', 'city_store as Ciudad', 'state_store as Estado', 'cp_store as Cod_Postal', DB::raw('DATE_FORMAT(date_reg,"%d/%m/%Y %H:%I:%S") as Fecha_Registro'))
      ->where([
        ['date_reg', '>=', $dini],
        ['date_reg', '<=', $dend],
      ])
      ->whereNull('user_mail')
      ->whereNull('reg_email')
      ->whereNotNull('email')
      ->whereNotIn('dni', [$wp])
      ->whereNotIn('dni', [$mp])
      ->groupBy('dni');

    return $clients->get();
  }

  public static function setPKuser815($dni, $pkuser, $id_fiber_zone)
  {
    $previus = self::getConnect('R')
      ->select('islim_fiber_client_zone.id',
        'islim_fiber_client_zone.pk_user')
      ->join('islim_fiber_client_zone',
        'islim_fiber_client_zone.dni_client',
        'islim_clients.dni')
      ->where([['islim_fiber_client_zone.dni_client', $dni],
        ['islim_fiber_client_zone.fiber_zone_id', $id_fiber_zone],
      ])
      ->first();

    if (!empty($previus)) {
      //Actualizando
      return FiberClientZone::updateClientZone($dni, $pkuser, $id_fiber_zone);

    } else {
      //Creo un nuevo registro
      FiberClientZone::registerNewClientZone($dni, $pkuser, $id_fiber_zone);

      return self::getConnect('W')
        ->where('dni', $dni)
        ->update([
          'isFiber' => 'Y']);
    }
  }
}
