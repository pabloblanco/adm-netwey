<?php

namespace App;

use App\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

//use Log;

class TempCar extends Model
{
  protected $table = 'islim_temp_car';

  protected $fillable = [
    'transaction',
    'client_dni',
    'address_send',
    'key',
    'cod_estafeta',
    'price_sending',
    'date_reg',
    'date_update',
    'status'];

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
      $obj = new TempCar;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getSalesReport($filters = [])
  {
    $data = self::getConnect('R')->select(
      'islim_temp_car.transaction',
      'islim_clients.name',
      'islim_clients.last_name',
      'islim_clients.email',
      'islim_clients.phone_home',
      'islim_clients.rfc',
      'islim_clients.dni',
      'islim_clients.require_invoice',
      //'islim_ninety_nine_minutes.order99',
      'islim_ordens.ordNbr as order99',
      'islim_ninety_nine_minutes.price as amount_del',
      'islim_ninety_nine_minutes.url_pdf',
      'islim_ordens.id as order',
      'islim_ordens.date',
      'islim_orders_details.msisdn',
      'islim_temp_car_detail.price_pack',
      'islim_packs.title',
      'islim_client_netweys.status as status_dn'
    )
      ->join(
        'islim_temp_car_detail',
        'islim_temp_car_detail.transaction',
        'islim_temp_car.transaction'
      )
      ->join(
        'islim_packs',
        'islim_packs.id',
        'islim_temp_car_detail.id_pack'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_temp_car.client_dni'
      )
      ->join(
        'islim_ninety_nine_minutes',
        'islim_ninety_nine_minutes.id_temp_car',
        'islim_temp_car.id'
      )
      ->leftJoin('islim_ordens', function ($query) {
        $query->on('islim_ordens.ordNbr', 'islim_ninety_nine_minutes.order99')
          ->whereIn('islim_ordens.status', ['A', 'P']);
      })
      ->leftJoin(
        'islim_orders_details',
        'islim_orders_details.id_ordens',
        'islim_ordens.id'
      )
      ->leftJoin(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_orders_details.msisdn'
      )
      ->where([
        ['islim_temp_car.status', 'P'],
        ['islim_temp_car_detail.status', 'A'],
        ['islim_temp_car.key', $filters['key']]])
      ->whereIn('islim_ninety_nine_minutes.status', ['A', 'S']);

    if (is_array($filters) && count($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $data = $data->whereBetween(
          'islim_ordens.date',
          [
            date('Y-m-d', strtotime($filters['dateb'])) . ' 00:00:00',
            date('Y-m-d', strtotime($filters['datee'])) . ' 23:59:59',
          ]
        );
      }

      if (empty($filters['dateb']) && !empty($filters['datee'])) {
        $data = $data->where(
          'islim_ordens.date',
          '<=',
          date('Y-m-d', strtotime($filters['datee'])) . ' 23:59:59'
        );
      }

      if (!empty($filters['dateb']) && empty($filters['datee'])) {
        $data = $data->where(
          'islim_ordens.date',
          '>=',
          date('Y-m-d', strtotime($filters['dateb'])) . ' 00:00:00'
        );
      }
    }

    $data = $data->get();

    foreach ($data as $key => $sale) {
      if (!empty($sale->order99)) {
        $status = OrderStatus::getConnect('R')->select('description', 'status')
          ->where('reference', $sale->order99)
          ->orderBy('id', 'DESC')
          ->first();
        if (!empty($status)) {
          if (is_array($filters) && count($filters) && !empty($filters['status'])) {
            if ($status->status != $filters['status']) {
              $data->pull($key);
              continue;
            }
          }

          $sale->description = $status->description;
        }
      }
    }

    return $data;
  }

  public static function getReportAPISales($filters = [])
  {
    $data = self::getConnect('R')->select(
      'islim_temp_car.transaction',
      'islim_dts_organizations.business_name',
      'islim_users.email',
      'islim_users.name',
      'islim_users.last_name',
      'islim_inv_articles.title as product',
      'islim_packs.pack_type',
      'islim_packs.title',
      'islim_services.title as service',
      'islim_orders_details.msisdn',
      'islim_clients.name as name_client',
      'islim_clients.last_name as last_name_client',
      'islim_clients.email as email_client',
      'islim_clients.phone_home',
      'islim_ordens.sub_monto',
      'islim_ordens.monto_envio',
      'islim_prova_delivery.folio as folio_pro',
      'islim_ninety_nine_minutes.order99 as folio_99',
      'islim_ninety_nine_minutes.postal_code as postal_code_99',
      'islim_ninety_nine_minutes.state as state_99',
      'islim_ninety_nine_minutes.locality as city_99',
      'islim_ninety_nine_minutes.neighborhood as colony_99',
      'islim_ninety_nine_minutes.municipality as municipality_99',
      'islim_voywey_delivery.folio as folio_voy',
      'islim_voywey_delivery.postal_code as postal_code_v',
      'islim_voywey_delivery.state as state_v',
      'islim_voywey_delivery.city as city_v',
      'islim_voywey_delivery.colony as colony_v',
      'islim_voywey_delivery.municipality as municipality_v',
      'islim_ordens.id as order id',
      'islim_ordens.cod_prom',
      'islim_ordens.discount',
      'islim_ordens.date as sale_date',
      'islim_sales.date_reg as del_date',
      DB::raw('DATEDIFF(islim_sales.date_reg, islim_ordens.date) as active_days'),
      'islim_sales.status',
      DB::raw('(select islim_ordens_status.description from islim_ordens_status where islim_ordens_status.id_ordens = islim_ordens.id order by islim_ordens_status.id DESC limit 1) as last_status'),
      DB::raw('(select islim_ordens_status.date from islim_ordens_status where islim_ordens_status.id_ordens = islim_ordens.id order by islim_ordens_status.id DESC limit 1) as date_status')
    )
      ->join(
        'islim_temp_car_detail',
        'islim_temp_car_detail.transaction',
        'islim_temp_car.transaction'
      )
      ->leftJoin(
        'islim_prova_delivery',
        function ($join) {
          $join->on('islim_prova_delivery.id_temp_car', 'islim_temp_car.id')
            ->where('islim_prova_delivery.status', 'S');
        }
      )
      ->leftJoin(
        'islim_ninety_nine_minutes',
        function ($join) {
          $join->on('islim_ninety_nine_minutes.id_temp_car', 'islim_temp_car.id')
            ->where('islim_ninety_nine_minutes.status', 'S');
        }
      )
      ->leftJoin(
        'islim_voywey_delivery',
        function ($join) {
          $join->on('islim_voywey_delivery.id_temp_car', 'islim_temp_car.id')
            ->where('islim_voywey_delivery.status', 'S');
        }
      )
      ->join(
        'islim_ordens',
        function ($join) {
          $join->on('islim_ordens.ordNbr', 'islim_prova_delivery.folio')
            ->orWhere('islim_ordens.ordNbr', DB::raw('islim_ninety_nine_minutes.order99'))
            ->orWhere('islim_ordens.ordNbr', DB::raw('islim_voywey_delivery.folio'));
        }
      )
      ->join(
        'islim_orders_details',
        'islim_orders_details.id_ordens',
        'islim_ordens.id'
      )
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_ordens.seller_email'
      )
      ->join(
        'islim_dts_organizations',
        'islim_dts_organizations.id',
        'islim_users.id_org'
      )
      ->join(
        'islim_packs',
        'islim_packs.id',
        'islim_temp_car_detail.id_pack'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_orders_details.id_articles'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_orders_details.id_details'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_ordens.client_id'
      )
      ->leftJoin(
        'islim_sales',
        function ($join) {
          $join->on('islim_sales.msisdn', 'islim_orders_details.msisdn')
            ->where('islim_sales.type', 'P');
        }
      )
      ->where('islim_temp_car_detail.status', 'A');

    if (count($filters)) {
      if (!empty($filters['org_sale'])) {
        $data->where('islim_dts_organizations.id', $filters['org_sale']);
      }

      if (!empty($filters['type_line'])) {
        $data->where('islim_packs.pack_type', $filters['type_line']);
      }

      if (!empty($filters['date_ini']) && !empty($filters['date_end'])) {
        $data->where([
          ['islim_ordens.date', '>=', $filters['date_ini'] . ' 00:00:00'],
          ['islim_ordens.date', '<=', $filters['date_end'] . ' 23:59:59'],
        ]);
      } elseif (!empty($filters['date_ini'])) {
        $data->where('islim_ordens.date', '>=', $filters['date_ini'] . ' 00:00:00');
      } elseif (!empty($filter['date_end'])) {
        $data->where('islim_ordens.date', '<=', $filters['date_end'] . ' 23:59:59');
      }
    }

    return $data;
  }

  public static function getSalesJelou($filters = [])
  {
    $keys_Jelou = explode(',', env('KEY_JELOU'));
    //$hoy        = date('Y-m-d H:i:s');
    //$hoy        = new \DateTime("now");
    //$DateSales = new \DateTime($RegLog->date_reg);
    //$Dias = $DateSales->diff($hoy)->format('%d');
    /*

    IF(expression ,expr_true, expr_false);

    DB::raw('( CASE
    WHEN SUBSTRING(islim_ordens.ordNbr,1,3) = "VOY" THEN "VoyWey"
    WHEN SUBSTRING(islim_ordens.ordNbr,1,3) = "NET" THEN "Prova"
    ELSE "99Minutos"
    END )  AS operadorLogistico'),*/
    /*  DB::raw('( CASE
    WHEN SUBSTRING(islim_ordens.ordNbr,1,3) = "VOY" THEN islim_deferred_payment.date_update
    WHEN SUBSTRING(islim_ordens.ordNbr,1,3) = "NET" THEN islim_ordens.date
    ELSE islim_ordens.date
    END )  AS date_conciliado'),*/
    /*  DB::raw('( CASE
    WHEN SUBSTRING(islim_ordens.ordNbr,1,3) = "VOY" THEN islim_deferred_payment.type_payment
    WHEN SUBSTRING(islim_ordens.ordNbr,1,3) = "NET" THEN "CARD"
    ELSE "CARD"
    END )   AS type_payment'),
    DB::raw(' ( IF (SUBSTRING(islim_ordens.ordNbr,1,3) = "VOY",  IF (islim_deferred_payment.status = "C", "Si","No"), "Si")) AS conciliado'),
    );

    ->selectRaw('islim_ordens.date AS date_conciliado')
    ->selectRaw('CONCAT("CARD") AS type_payment')

     */
    $nameClientFull = DB::raw('CONCAT(
        IFNULL(islim_clients.name,"")," ",
        IFNULL(islim_clients.last_name,"") ) AS nameClient');

    $PaymentCARD = DB::raw('CONCAT("CARD") AS type_payment');
    $YesConciliado = DB::raw('CONCAT("Si") AS conciliado');
    $lastStatus = DB::raw('(SELECT islim_ordens_status.description FROM islim_ordens_status WHERE islim_ordens_status.id_ordens = islim_ordens.id ORDER BY islim_ordens_status.id DESC limit 1) AS status_ord');
    $YesEntregado = DB::raw('CONCAT("Entregado") AS status_ord');
    $dateInRut = DB::raw('DATEDIFF(now(),islim_ordens.date) AS days_Lastsales');

    //Status finales
    $status99_ELO = array("'6'", "'12'", "'19'");
    $status99 = implode(',', $status99_ELO);

    $statusProva_ELO = array("'5'");
    $statusProva = implode(',', $statusProva_ELO);

    $statusVoy_ELO = array("'5'");
    $statusVoy = implode(',', $statusVoy_ELO);

    $statusAll_ELO = array("'5'", "'6'", "'12'", "'19'");
    $statusAll = implode(',', $statusAll_ELO);

    //Dato de la fecha
    if ($filters['typeDate'] == "send") {
      //Fecha de entrega
      $dateFilter = 'islim_ordens_status.date';
    } elseif ($filters['typeDate'] == "high") {
      //Alta del cliente
      $dateFilter = 'islim_client_netweys.date_reg';
    } else {
      //init (creacion de la orden)
      if ($filters['operador'] == '99' || $filters['operador'] == 'prova') {
        $dateFilter = 'islim_ordens.date';
      } else {
        $dateFilter = DB::raw("(SELECT islim_ordens_status.date
        FROM islim_ordens_status
        WHERE islim_ordens_status.status = '1'
        AND islim_ordens_status.reference = islim_ordens.ordNbr
        ORDER BY islim_ordens_status.id DESC LIMIT 1)");
      }
    }

/////////////////////////////////////////////////////////////////
    /// 99MIN
    ///
    /*99MIN Entrega completa*/
    if (empty($filters['operador']) || $filters['operador'] == '99') {
      $AddressEntrega99 = DB::raw('CONCAT(
        IFNULL(islim_ninety_nine_minutes.postal_code,""),", ",
        IFNULL(islim_ninety_nine_minutes.route,""),", ",
        IFNULL(islim_ninety_nine_minutes.municipality,""),", ",
        IFNULL(islim_ninety_nine_minutes.locality,""),", ",
        IFNULL(islim_ninety_nine_minutes.neighborhood,""),", ",
        IFNULL(islim_ninety_nine_minutes.state,""),", ",
        IFNULL(islim_ninety_nine_minutes.street_number,""),", ",
        IFNULL(islim_ninety_nine_minutes.internal_number,""),". >Nota: ",
        IFNULL(islim_ninety_nine_minutes.notes,"") ) AS address_delivery');
    }

    if ((empty($filters['operador']) || $filters['operador'] == '99') &&
      (empty($filters['conciliado']) || $filters['conciliado'] == 'SI') &&
      $filters['deliveryFull'] == 'SI') {

      /* if ($filters['viewFail'] != 'true') {
      Log::info('FALSE');
      } else {
      Log::info('TRUE');
      }*/
      if (filter_var($filters['viewFail'], FILTER_VALIDATE_BOOLEAN)) {
        //Muestro los folios entregados pero aun no han sido activados posiblemente xq no entregaron el DN
        //
        $data = self::getConnect('R')
          ->select(
            DB::raw('CONCAT("S/N") AS msisdn'),
            DB::raw('CONCAT("Por activar...") AS statusDN'),
            'islim_inv_articles.artic_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            DB::raw('CONCAT("99 Minutos") AS courier'),
            $YesEntregado,
            'islim_ordens.date AS date_sales',
            'islim_ninety_nine_minutes.state AS state_delivery',
            $AddressEntrega99,
            'islim_orders_details.price AS mount',
            $PaymentCARD,
            'islim_ordens_status.date AS date_delivery',
            DB::raw('CONCAT("99 Minutos") AS operadorLogistico'),
            'islim_ordens.date AS date_conciliado',
            $YesConciliado,
            DB::raw('DATEDIFF(islim_ordens_status.date, islim_ordens.date) AS days_Lastsales'),
            DB::raw('CONCAT("Por activar...") AS release_date')
          )
          ->join(
            'islim_ninety_nine_minutes',
            'islim_ninety_nine_minutes.id_temp_car',
            'islim_temp_car.id')
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_ninety_nine_minutes.order99')
              ->where('islim_ordens.status', 'P');
          })
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->join('islim_ordens_status', function ($join) use ($status99) {
            $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
              ->whereRaw('islim_ordens_status.status IN(' . $status99 . ')')
              ->limit(1);
          })
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);
      } else {
        //Folios entregados y segun estan activos
        $data = self::getConnect('R')
          ->select(
            'islim_orders_details.msisdn',
            'islim_client_netweys.status AS statusDN',
            'islim_client_netweys.dn_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            DB::raw('CONCAT("99 Minutos") AS courier'),
            $YesEntregado,
            'islim_ordens.date AS date_sales',
            'islim_ninety_nine_minutes.state AS state_delivery',
            $AddressEntrega99,
            'islim_orders_details.price AS mount',
            $PaymentCARD,
            'islim_ordens_status.date AS date_delivery',
            DB::raw('CONCAT("99 Minutos") AS operadorLogistico'),
            'islim_ordens.date AS date_conciliado',
            $YesConciliado,
            DB::raw('DATEDIFF(islim_ordens_status.date, islim_ordens.date) AS days_Lastsales'),
            'islim_sales.date_reg AS release_date'
          )
          ->join(
            'islim_ninety_nine_minutes',
            'islim_ninety_nine_minutes.id_temp_car',
            'islim_temp_car.id')
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_ninety_nine_minutes.order99')
              ->where('islim_ordens.status', 'P');
          })
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNotNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->join('islim_client_netweys', function ($join) {
            $join->on('islim_client_netweys.msisdn', '=', 'islim_orders_details.msisdn')
              ->where('islim_client_netweys.status', '!=', 'T');
          })
          ->join('islim_ordens_status', function ($join) use ($status99) {
            $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
              ->whereRaw('islim_ordens_status.status IN(' . $status99 . ')')
              ->limit(1);
          })
          ->join('islim_sales', function ($join) {
            $join->on('islim_sales.msisdn', '=', 'islim_orders_details.msisdn')
              ->where('islim_sales.type', 'P');
          })
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);
      }
    } else {
      /*99MIN Aun en tramite*/
      if ((empty($filters['operador']) || $filters['operador'] == '99') &&
        (empty($filters['conciliado']) || $filters['conciliado'] == 'SI') &&
        $filters['deliveryFull'] == 'NO') {
        $data = self::getConnect('R')
          ->select(
            DB::raw('CONCAT("S/N") AS msisdn'),
            DB::raw('CONCAT("S/N") AS statusDN'),
            'islim_inv_articles.artic_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            DB::raw('CONCAT("99 Minutos") AS courier'),
            $lastStatus,
            'islim_ordens.date AS date_sales',
            'islim_ninety_nine_minutes.state AS state_delivery',
            $AddressEntrega99,
            'islim_orders_details.price AS mount',
            $PaymentCARD,
            DB::raw('CONCAT("En camino...") AS date_delivery'),
            DB::raw('CONCAT("99 Minutos") AS operadorLogistico'),
            'islim_ordens.date AS date_conciliado',
            $YesConciliado,
            $dateInRut,
            DB::raw('CONCAT("S/N") AS release_date')
          )
          ->join(
            'islim_ninety_nine_minutes',
            'islim_ninety_nine_minutes.id_temp_car',
            'islim_temp_car.id')
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_ninety_nine_minutes.order99')
              ->where('islim_ordens.status', 'A');
          })
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);
      }
    }
/////////////////////////////////////////////////////////////////
    // PROVA
    //
    if (empty($filters['operador']) || $filters['operador'] == 'prova') {
      $AddressEntregaProva = DB::raw('CONCAT(
        IFNULL(islim_prova_delivery.postal_code,""),", ",
        IFNULL(islim_prova_delivery.state,""),", ",
        IFNULL(islim_prova_delivery.city,""),", ",
        IFNULL(islim_prova_delivery.colony,""),", ",
        IFNULL(islim_prova_delivery.municipality,""),", ",
        IFNULL(islim_prova_delivery.street,""),", ",
        IFNULL(islim_prova_delivery.ext_number,""),". >Nota:  ",
        IFNULL(islim_prova_delivery.notes,"") ) AS address_delivery');
    }
    /*PROVA Entrega completa*/
    if ((empty($filters['operador']) || $filters['operador'] == 'prova') &&
      (empty($filters['conciliado']) || $filters['conciliado'] == 'SI') &&
      $filters['deliveryFull'] == 'SI') {
      if (filter_var($filters['viewFail'], FILTER_VALIDATE_BOOLEAN)) {
        //Muestro los folios entregados pero aun no han sido activados posiblemente xq no entregaron el DN
        //
        $dataProva = self::getConnect('R')
          ->select(
            DB::raw('CONCAT("S/N") AS msisdn'),
            DB::raw('CONCAT("Por activar...") AS statusDN'),
            'islim_inv_articles.artic_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            DB::raw('CONCAT("Prova") AS courier'),
            $YesEntregado,
            'islim_ordens.date AS date_sales',
            'islim_prova_delivery.state AS state_delivery',
            $AddressEntregaProva,
            'islim_orders_details.price AS mount',
            $PaymentCARD,
            'islim_ordens_status.date AS date_delivery',
            DB::raw('CONCAT("Prova") AS operadorLogistico'),
            'islim_ordens.date AS date_conciliado',
            $YesConciliado,
            DB::raw('DATEDIFF(islim_ordens_status.date, islim_ordens.date) AS days_Lastsales'),
            DB::raw('CONCAT("Por activar...") AS release_date')
          )
          ->join(
            'islim_prova_delivery',
            'islim_prova_delivery.id_temp_car',
            'islim_temp_car.id')
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_prova_delivery.folio')
              ->where('islim_ordens.status', 'P');
          })
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->join('islim_ordens_status', function ($join) use ($statusProva) {
            $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
              ->whereRaw('islim_ordens_status.status IN(' . $statusProva . ')')
              ->limit(1);
          })
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);
      } else {
        $dataProva = self::getConnect('R')
          ->select(
            'islim_orders_details.msisdn',
            'islim_client_netweys.status AS statusDN',
            'islim_client_netweys.dn_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            DB::raw('CONCAT("Prova") AS courier'),
            $YesEntregado,
            'islim_ordens.date AS date_sales',
            'islim_prova_delivery.state AS state_delivery',
            $AddressEntregaProva,
            'islim_orders_details.price AS mount',
            $PaymentCARD,
            'islim_ordens_status.date AS date_delivery',
            DB::raw('CONCAT("Prova") AS operadorLogistico'),
            'islim_ordens.date AS date_conciliado',
            $YesConciliado,
            DB::raw('DATEDIFF(islim_ordens_status.date, islim_ordens.date) AS days_Lastsales'),
            'islim_sales.date_reg AS release_date'
          )
          ->join(
            'islim_prova_delivery',
            'islim_prova_delivery.id_temp_car',
            'islim_temp_car.id')
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_prova_delivery.folio')
              ->where('islim_ordens.status', 'P');
          })
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNotNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->join('islim_client_netweys', function ($join) {
            $join->on('islim_client_netweys.msisdn', '=', 'islim_orders_details.msisdn')
              ->where('islim_client_netweys.status', '!=', 'T');
          })
          ->join('islim_ordens_status', function ($join) use ($statusProva) {
            $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
              ->whereRaw('islim_ordens_status.status IN(' . $statusProva . ')')
              ->limit(1);
          })
          ->join('islim_sales', function ($join) {
            $join->on('islim_sales.msisdn', '=', 'islim_orders_details.msisdn')
              ->where('islim_sales.type', 'P');
          })
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);
      }
      if (!empty($data)) {
        $data = $data->union($dataProva);
      } else {
        $data = $dataProva;
      }
    } else {
      /*PROVA Aun en tramite*/
      if ((empty($filters['operador']) || $filters['operador'] == 'prova') &&
        (empty($filters['conciliado']) || $filters['conciliado'] == 'SI') &&
        $filters['deliveryFull'] == 'NO') {
        $dataProva = self::getConnect('R')
          ->select(
            DB::raw('CONCAT("S/N") AS msisdn'),
            DB::raw('CONCAT("S/N") AS statusDN'),
            'islim_inv_articles.artic_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            DB::raw('CONCAT("Prova") AS courier'),
            $lastStatus,
            'islim_ordens.date AS date_sales',
            'islim_prova_delivery.state AS state_delivery',
            $AddressEntregaProva,
            'islim_orders_details.price AS mount',
            $PaymentCARD,
            DB::raw('CONCAT("En camino...") AS date_delivery'),
            DB::raw('CONCAT("Prova") AS operadorLogistico'),
            'islim_ordens.date AS date_conciliado',
            $YesConciliado,
            $dateInRut,
            DB::raw('CONCAT("S/N") AS release_date')
          )
          ->join(
            'islim_prova_delivery',
            'islim_prova_delivery.id_temp_car',
            'islim_temp_car.id')
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_prova_delivery.folio')
              ->where('islim_ordens.status', 'A');
          })
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);

        if (!empty($data)) {
          $data = $data->union($dataProva);
        } else {
          $data = $dataProva;
        }
      }
    }
/////////////////////////////////////////////////////////////////
    //VOYWEY
    //
    if (empty($filters['operador']) || $filters['operador'] == 'voy') {
      $AddressEntregaVoy = DB::raw('CONCAT(
        IFNULL(islim_voywey_delivery.postal_code,""),", ",
        IFNULL(islim_voywey_delivery.state,""),", ",
        IFNULL(islim_voywey_delivery.city,""),", ",
        IFNULL(islim_voywey_delivery.colony,""),", ",
        IFNULL(islim_voywey_delivery.municipality,""),", ",
        IFNULL(islim_voywey_delivery.street,""),", ",
        IFNULL(islim_voywey_delivery.ext_number,""),", ",
        IFNULL(islim_voywey_delivery.int_number,""),". >Nota: ",
        IFNULL(islim_voywey_delivery.notes,"") ) AS address_delivery');

      $Operador = DB::raw('CONCAT("VoyWey") AS operadorLogistico');
      $days_LastSalesVoy = DB::raw("DATEDIFF(islim_ordens_status.date,
        (SELECT islim_ordens_status.date
        FROM islim_ordens_status
        WHERE islim_ordens_status.status = '1'
        AND islim_ordens_status.reference = islim_ordens.ordNbr
        LIMIT 1 ) )
        AS days_Lastsales");

      $date_createVoy = DB::raw("(SELECT islim_ordens_status.date FROM islim_ordens_status
        WHERE islim_ordens_status.status = '1' AND islim_ordens_status.reference = islim_ordens.ordNbr LIMIT 1 ) AS date_sales");

    }
    //Se agrego diferenciacion en Voywey externo e interno
    /*VOYWEY Entrega completa*/
    if ((empty($filters['operador']) || $filters['operador'] == 'voy') &&
      $filters['deliveryFull'] == 'SI') {

      ##############
      //
      //Subconsulta de VoyweyInterno completas (Pagos diferidos)
      //
      if (filter_var($filters['viewFail'], FILTER_VALIDATE_BOOLEAN)) {
        //Muestro los folios entregados pero aun no han sido activados posiblemente xq no entregaron el DN
        //
        $voyInterno = self::getConnect('R')
          ->select(
            DB::raw('CONCAT("S/N") AS msisdn'),
            DB::raw('CONCAT("Por activar...") AS statusDN'),
            'islim_inv_articles.artic_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            'islim_voywey_delivery.courier_g AS courier',
            $YesEntregado,
            $date_createVoy,
            'islim_voywey_delivery.state AS state_delivery',
            $AddressEntregaVoy,
            'islim_orders_details.price AS mount',
            'islim_deferred_payment.type_payment  AS type_payment',
            'islim_ordens_status.date AS date_delivery',
            $Operador,
            DB::raw('IF (islim_deferred_payment.status = "C", islim_deferred_payment.date_update,"S/N") AS date_conciliado'),
            DB::raw('IF (islim_deferred_payment.status = "C", "Si","No")  AS conciliado'),
            $days_LastSalesVoy,
            DB::raw('CONCAT("Por activar...") AS release_date')
          )
          ->join('islim_voywey_delivery', function ($join) {
            $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
              ->where('islim_voywey_delivery.courier_g', "voywey")
              ->where('islim_voywey_delivery.type_payment', "D");
          })
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
              ->where('islim_ordens.status', 'P');
          })
          ->join('islim_deferred_payment',
            'islim_deferred_payment.order_id',
            'islim_ordens.id')
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->join('islim_ordens_status', function ($join) use ($statusVoy) {
            $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
              ->whereRaw('islim_ordens_status.status IN(' . $statusVoy . ')')
              ->limit(1);
          })
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou);
      } else {
        //Entregados y activados correctamente
        $voyInterno = self::getConnect('R')
          ->select(
            'islim_orders_details.msisdn',
            'islim_client_netweys.status AS statusDN',
            'islim_client_netweys.dn_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            'islim_voywey_delivery.courier_g AS courier',
            $YesEntregado,
            $date_createVoy,
            'islim_voywey_delivery.state AS state_delivery',
            $AddressEntregaVoy,
            'islim_orders_details.price AS mount',
            'islim_deferred_payment.type_payment  AS type_payment',
            'islim_ordens_status.date AS date_delivery',
            $Operador,
            DB::raw('IF (islim_deferred_payment.status = "C", islim_deferred_payment.date_update,"S/N") AS date_conciliado'),
            DB::raw('IF (islim_deferred_payment.status = "C", "Si","No")  AS conciliado'),
            $days_LastSalesVoy,
            'islim_sales.date_reg AS release_date'
          )
          ->join('islim_voywey_delivery', function ($join) {
            $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
              ->where('islim_voywey_delivery.courier_g', "voywey")
              ->where('islim_voywey_delivery.type_payment', "D");
          })
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
              ->where('islim_ordens.status', 'P');
          })
          ->join('islim_deferred_payment',
            'islim_deferred_payment.order_id',
            'islim_ordens.id')
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNotNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->join('islim_client_netweys', function ($join) {
            $join->on('islim_client_netweys.msisdn', '=', 'islim_orders_details.msisdn')
              ->where('islim_client_netweys.status', '!=', 'T');
          })
          ->join('islim_ordens_status', function ($join) use ($statusVoy) {
            $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
              ->whereRaw('islim_ordens_status.status IN(' . $statusVoy . ')')
              ->limit(1);
          })
          ->join('islim_sales', function ($join) {
            $join->on('islim_sales.msisdn', '=', 'islim_orders_details.msisdn')
              ->where('islim_sales.type', 'P');
          })
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);
      }

      if (!empty($filters['conciliado'])) {
        if ($filters['conciliado'] == 'NO') {
          $voyInterno = $voyInterno->where('islim_deferred_payment.status', 'A');
        } else {
          $voyInterno = $voyInterno->where('islim_deferred_payment.status', 'C');
        }
      } else {
        $voyInterno = $voyInterno->whereIn('islim_deferred_payment.status', ['C', 'A']);
      }

      if ($filters['conciliado'] != 'NO') {
        //
        //Subconsulta de VoyweyInterno completas (Pagos mercado pago)
        //
        if (filter_var($filters['viewFail'], FILTER_VALIDATE_BOOLEAN)) {
          //Muestro los folios entregados pero aun no han sido activados posiblemente xq no entregaron el DN
          //
          $voyInternoMP = self::getConnect('R')
            ->select(
              DB::raw('CONCAT("S/N") AS msisdn'),
              DB::raw('CONCAT("Por activar...") AS statusDN'),
              'islim_inv_articles.artic_type AS typeDN',
              'islim_inv_articles.sku AS SKU',
              'islim_clients.dni AS dniClient',
              $nameClientFull,
              'islim_clients.phone_home AS telfClient',
              'islim_ordens.ordNbr AS folio',
              'islim_voywey_delivery.courier_g AS courier',
              $YesEntregado,
              $date_createVoy,
              'islim_voywey_delivery.state AS state_delivery',
              $AddressEntregaVoy,
              'islim_orders_details.price AS mount',
              $PaymentCARD,
              'islim_ordens_status.date AS date_delivery',
              $Operador,
              DB::raw("(SELECT islim_ordens_status.date FROM islim_ordens_status WHERE islim_ordens_status.status = '1' AND islim_ordens_status.reference = islim_ordens.ordNbr LIMIT 1 ) AS date_conciliado"),
              $YesConciliado,
              $days_LastSalesVoy,
              DB::raw('CONCAT("Por activar...") AS release_date')
            )
            ->join('islim_voywey_delivery', function ($join) {
              $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
                ->where('islim_voywey_delivery.courier_g', "voywey")
                ->where('islim_voywey_delivery.type_payment', "MP");
            })
            ->join('islim_ordens', function ($join) {
              $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
                ->where('islim_ordens.status', 'P');
            })
            ->join('islim_clients',
              'islim_clients.dni',
              'islim_ordens.client_id')
            ->join('islim_orders_details', function ($join) {
              $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
                ->whereNull('islim_orders_details.msisdn');
            })
            ->join('islim_inv_articles',
              'islim_inv_articles.id',
              'islim_orders_details.id_articles')
            ->join('islim_ordens_status', function ($join) use ($statusVoy) {
              $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
                ->whereRaw('islim_ordens_status.status IN(' . $statusVoy . ')')
                ->limit(1);
            })
            ->where('islim_temp_car.status', 'P')
            ->whereIn('islim_temp_car.key', $keys_Jelou)
            ->where([
              [$dateFilter, '>=', $filters['dateStar']],
              [$dateFilter, '<=', $filters['dateEnd']]]);
        } else {
          $voyInternoMP = self::getConnect('R')
            ->select(
              'islim_orders_details.msisdn',
              'islim_client_netweys.status AS statusDN',
              'islim_client_netweys.dn_type AS typeDN',
              'islim_inv_articles.sku AS SKU',
              'islim_clients.dni AS dniClient',
              $nameClientFull,
              'islim_clients.phone_home AS telfClient',
              'islim_ordens.ordNbr AS folio',
              'islim_voywey_delivery.courier_g AS courier',
              $YesEntregado,
              $date_createVoy,
              'islim_voywey_delivery.state AS state_delivery',
              $AddressEntregaVoy,
              'islim_orders_details.price AS mount',
              $PaymentCARD,
              'islim_ordens_status.date AS date_delivery',
              $Operador,
              DB::raw("(SELECT islim_ordens_status.date FROM islim_ordens_status WHERE islim_ordens_status.status = '1' AND islim_ordens_status.reference = islim_ordens.ordNbr LIMIT 1 ) AS date_conciliado"),
              $YesConciliado,
              $days_LastSalesVoy,
              'islim_sales.date_reg AS release_date'
            )
            ->join('islim_voywey_delivery', function ($join) {
              $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
                ->where('islim_voywey_delivery.courier_g', "voywey")
                ->where('islim_voywey_delivery.type_payment', "MP");
            })
            ->join('islim_ordens', function ($join) {
              $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
                ->where('islim_ordens.status', 'P');
            })
            ->join('islim_clients',
              'islim_clients.dni',
              'islim_ordens.client_id')
            ->join('islim_orders_details', function ($join) {
              $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
                ->whereNotNull('islim_orders_details.msisdn');
            })
            ->join('islim_inv_articles',
              'islim_inv_articles.id',
              'islim_orders_details.id_articles')
            ->join('islim_client_netweys', function ($join) {
              $join->on('islim_client_netweys.msisdn', '=', 'islim_orders_details.msisdn')
                ->where('islim_client_netweys.status', '!=', 'T');
            })
            ->join('islim_ordens_status', function ($join) use ($statusVoy) {
              $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
                ->whereRaw('islim_ordens_status.status IN(' . $statusVoy . ')')
                ->limit(1);
            })
            ->join('islim_sales', function ($join) {
              $join->on('islim_sales.msisdn', '=', 'islim_orders_details.msisdn')
                ->where('islim_sales.type', 'P');
            })
            ->where('islim_temp_car.status', 'P')
            ->whereIn('islim_temp_car.key', $keys_Jelou)
            ->where([
              [$dateFilter, '>=', $filters['dateStar']],
              [$dateFilter, '<=', $filters['dateEnd']]]);
        }
        //
        //Subconsulta de VoyweyExterno completas
        //
        if (filter_var($filters['viewFail'], FILTER_VALIDATE_BOOLEAN)) {
          //Muestro los folios entregados pero aun no han sido activados posiblemente xq no entregaron el DN
          //
          $voyExterno = self::getConnect('R')
            ->select(
              DB::raw('CONCAT("S/N") AS msisdn'),
              DB::raw('CONCAT("Por activar...") AS statusDN'),
              'islim_inv_articles.artic_type AS typeDN',
              'islim_inv_articles.sku AS SKU',
              'islim_clients.dni AS dniClient',
              $nameClientFull,
              'islim_clients.phone_home AS telfClient',
              'islim_ordens.ordNbr AS folio',
              'islim_voywey_delivery.courier_g AS courier',
              $YesEntregado,
              $date_createVoy,
              'islim_voywey_delivery.state AS state_delivery',
              $AddressEntregaVoy,
              'islim_orders_details.price AS mount',
              $PaymentCARD,
              'islim_ordens_status.date AS date_delivery',
              $Operador,
              DB::raw("(SELECT islim_ordens_status.date FROM islim_ordens_status WHERE islim_ordens_status.status = '1' AND islim_ordens_status.reference = islim_ordens.ordNbr LIMIT 1) AS date_conciliado"),
              $YesConciliado,
              $days_LastSalesVoy,
              DB::raw('CONCAT("Por activar...") AS release_date')
            )
            ->join('islim_voywey_delivery', function ($join) {
              $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
                ->where('islim_voywey_delivery.courier_g', "voywey-externo");
            })
            ->join('islim_ordens', function ($join) {
              $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
                ->where('islim_ordens.status', 'P');
            })
            ->join('islim_clients',
              'islim_clients.dni',
              'islim_ordens.client_id')
            ->join('islim_orders_details', function ($join) {
              $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
                ->whereNull('islim_orders_details.msisdn');
            })
            ->join('islim_inv_articles',
              'islim_inv_articles.id',
              'islim_orders_details.id_articles')
            ->join('islim_ordens_status', function ($join) use ($statusVoy) {
              $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
                ->whereRaw('islim_ordens_status.status IN(' . $statusVoy . ')')
                ->limit(1);
            })
            ->where('islim_temp_car.status', 'P')
            ->whereIn('islim_temp_car.key', $keys_Jelou)
            ->where([
              [$dateFilter, '>=', $filters['dateStar']],
              [$dateFilter, '<=', $filters['dateEnd']]]);
        } else {
          $voyExterno = self::getConnect('R')
            ->select(
              'islim_orders_details.msisdn',
              'islim_client_netweys.status AS statusDN',
              'islim_client_netweys.dn_type AS typeDN',
              'islim_inv_articles.sku AS SKU',
              'islim_clients.dni AS dniClient',
              $nameClientFull,
              'islim_clients.phone_home AS telfClient',
              'islim_ordens.ordNbr AS folio',
              'islim_voywey_delivery.courier_g AS courier',
              $YesEntregado,
              $date_createVoy,
              'islim_voywey_delivery.state AS state_delivery',
              $AddressEntregaVoy,
              'islim_orders_details.price AS mount',
              $PaymentCARD,
              'islim_ordens_status.date AS date_delivery',
              $Operador,
              DB::raw("(SELECT islim_ordens_status.date FROM islim_ordens_status WHERE islim_ordens_status.status = '1' AND islim_ordens_status.reference = islim_ordens.ordNbr LIMIT 1) AS date_conciliado"),
              $YesConciliado,
              $days_LastSalesVoy,
              'islim_sales.date_reg AS release_date'
            )
            ->join('islim_voywey_delivery', function ($join) {
              $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
                ->where('islim_voywey_delivery.courier_g', "voywey-externo");
            })
            ->join('islim_ordens', function ($join) {
              $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
                ->where('islim_ordens.status', 'P');
            })
            ->join('islim_clients',
              'islim_clients.dni',
              'islim_ordens.client_id')
            ->join('islim_orders_details', function ($join) {
              $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
                ->whereNotNull('islim_orders_details.msisdn');
            })
            ->join('islim_inv_articles',
              'islim_inv_articles.id',
              'islim_orders_details.id_articles')
            ->join('islim_client_netweys', function ($join) {
              $join->on('islim_client_netweys.msisdn', '=', 'islim_orders_details.msisdn')
                ->where('islim_client_netweys.status', '!=', 'T');
            })
            ->join('islim_ordens_status', function ($join) use ($statusVoy) {
              $join->on('islim_ordens_status.id_ordens', '=', 'islim_ordens.id')
                ->whereRaw('islim_ordens_status.status IN(' . $statusVoy . ')')
                ->limit(1);
            })
            ->join('islim_sales', function ($join) {
              $join->on('islim_sales.msisdn', '=', 'islim_orders_details.msisdn')
                ->where('islim_sales.type', 'P');
            })
            ->where('islim_temp_car.status', 'P')
            ->whereIn('islim_temp_car.key', $keys_Jelou)
            ->where([
              [$dateFilter, '>=', $filters['dateStar']],
              [$dateFilter, '<=', $filters['dateEnd']]]);
        }
      }

      //
      //Uniones del VoyWey completados
      //
      if (!empty($filters['currier'])) {
        //Miro los currier
        if ($filters['currier'] == 'EX' && $filters['conciliado'] != 'NO') {
          if (!empty($data)) {
            $data = $data->union($voyExterno);
          } else {
            $data = $voyExterno;
          }
        } else {
          if (!empty($data)) {
            $data = $data->union($voyInterno);
          } else {
            $data = $voyInterno;
          }
          if ($filters['conciliado'] != 'NO') {
            $data = $data->union($voyInternoMP);
          }
        }
      } else {
        //Los dos curriers
        if ($filters['conciliado'] != "NO") {
          // $dataProcess = $voyInterno->union($voyExterno);

          if (!empty($data)) {
            $data = $data->union($voyInterno);
          } else {
            $data = $voyInterno;
          }
          $data = $data->union($voyInternoMP);
          $data = $data->union($voyExterno);

        } else {
          if (!empty($data)) {
            $data = $data->union($voyInterno);
          } else {
            $data = $voyInterno;
          }
        }
      }
      //
      //END Uniones del VoyWey completados
      //
      ##############
    } else {
      /*VOYWEY Aun en tramite*/
      if ((empty($filters['operador']) || $filters['operador'] == 'voy') &&
        $filters['deliveryFull'] == 'NO') {

        //
        //Subconsulta de VoyWey interno que estan en tramite (Pagos diferidos)
        //

        $voyInterno = $dataVoy = self::getConnect('R')
          ->select(
            DB::raw('CONCAT("S/N") AS msisdn'),
            DB::raw('CONCAT("S/N") AS statusDN'),
            'islim_inv_articles.artic_type AS typeDN',
            'islim_inv_articles.sku AS SKU',
            'islim_clients.dni AS dniClient',
            $nameClientFull,
            'islim_clients.phone_home AS telfClient',
            'islim_ordens.ordNbr AS folio',
            'islim_voywey_delivery.courier_g AS courier',
            $lastStatus,
            $date_createVoy,
            'islim_voywey_delivery.state AS state_delivery',
            $AddressEntregaVoy,
            'islim_orders_details.price AS mount',
            'islim_deferred_payment.type_payment  AS type_payment',
            DB::raw('CONCAT("En camino...") AS date_delivery'),
            $Operador,
            DB::raw('IF (islim_deferred_payment.status = "C", islim_deferred_payment.date_update,"S/N")  AS date_conciliado'),
            DB::raw('IF (islim_deferred_payment.status = "C", "Si","No") AS conciliado'),
            $dateInRut,
            DB::raw('CONCAT("S/N") AS release_date')
          )
          ->join('islim_voywey_delivery', function ($join) {
            $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
              ->where('islim_voywey_delivery.courier_g', "voywey")
              ->where('islim_voywey_delivery.type_payment', "D");
          })
          ->join('islim_ordens', function ($join) {
            $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
              ->where('islim_ordens.status', 'A');
          })
          ->join('islim_deferred_payment',
            'islim_deferred_payment.order_id',
            'islim_ordens.id')
          ->join('islim_clients',
            'islim_clients.dni',
            'islim_ordens.client_id')
          ->join('islim_orders_details', function ($join) {
            $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
              ->whereNull('islim_orders_details.msisdn');
          })
          ->join('islim_inv_articles',
            'islim_inv_articles.id',
            'islim_orders_details.id_articles')
          ->where('islim_temp_car.status', 'P')
          ->whereIn('islim_temp_car.key', $keys_Jelou)
          ->where([
            [$dateFilter, '>=', $filters['dateStar']],
            [$dateFilter, '<=', $filters['dateEnd']]]);

        if (!empty($filters['conciliado'])) {
          if ($filters['conciliado'] == 'NO') {
            $voyInterno = $voyInterno->where('islim_deferred_payment.status', 'A');
          } else {
            $voyInterno = $voyInterno->where('islim_deferred_payment.status', 'C');
          }
        } else {
          $voyInterno = $voyInterno->whereIn('islim_deferred_payment.status', ['C', 'A']);
        }

        if ($filters['conciliado'] != 'NO') {
          //
          //Subconsulta de VoyWey Interno que estan en tramite y pagaron con Mercado Pago
          //
          $voyInternoMP = $dataVoy = self::getConnect('R')
            ->select(
              DB::raw('CONCAT("S/N") AS msisdn'),
              DB::raw('CONCAT("S/N") AS statusDN'),
              'islim_inv_articles.artic_type AS typeDN',
              'islim_inv_articles.sku AS SKU',
              'islim_clients.dni AS dniClient',
              $nameClientFull,
              'islim_clients.phone_home AS telfClient',
              'islim_ordens.ordNbr AS folio',
              'islim_voywey_delivery.courier_g AS courier',
              $lastStatus,
              $date_createVoy,
              'islim_voywey_delivery.state AS state_delivery',
              $AddressEntregaVoy,
              'islim_orders_details.price AS mount',
              $PaymentCARD,
              DB::raw('CONCAT("En camino...") AS date_delivery'),
              $Operador,
              'islim_ordens.date AS date_conciliado',
              $YesConciliado,
              $dateInRut,
              DB::raw('CONCAT("S/N") AS release_date')
            )
            ->join('islim_voywey_delivery', function ($join) {
              $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
                ->where('islim_voywey_delivery.courier_g', "voywey")
                ->where('islim_voywey_delivery.type_payment', "MP");
            })
            ->join('islim_ordens', function ($join) {
              $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
                ->where('islim_ordens.status', 'A');
            })
            ->join('islim_clients',
              'islim_clients.dni',
              'islim_ordens.client_id')
            ->join('islim_orders_details', function ($join) {
              $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
                ->whereNull('islim_orders_details.msisdn');
            })
            ->join('islim_inv_articles',
              'islim_inv_articles.id',
              'islim_orders_details.id_articles')
            ->where('islim_temp_car.status', 'P')
            ->whereIn('islim_temp_car.key', $keys_Jelou)
            ->where([
              [$dateFilter, '>=', $filters['dateStar']],
              [$dateFilter, '<=', $filters['dateEnd']]]);

          //
          //Subconsulta de VoyWey externo que estan en tramite
          //
          $voyExterno = $dataVoy = self::getConnect('R')
            ->select(
              DB::raw('CONCAT("S/N") AS msisdn'),
              DB::raw('CONCAT("S/N") AS statusDN'),
              'islim_inv_articles.artic_type AS typeDN',
              'islim_inv_articles.sku AS SKU',
              'islim_clients.dni AS dniClient',
              $nameClientFull,
              'islim_clients.phone_home AS telfClient',
              'islim_ordens.ordNbr AS folio',
              'islim_voywey_delivery.courier_g AS courier',
              $lastStatus,
              $date_createVoy,
              'islim_voywey_delivery.state AS state_delivery',
              $AddressEntregaVoy,
              'islim_orders_details.price AS mount',
              $PaymentCARD,
              DB::raw('CONCAT("En camino...") AS date_delivery'),
              $Operador,
              'islim_ordens.date AS date_conciliado',
              $YesConciliado,
              $dateInRut,
              DB::raw('CONCAT("S/N") AS release_date')
            )
            ->join('islim_voywey_delivery', function ($join) {
              $join->on('islim_voywey_delivery.id_temp_car', '=', 'islim_temp_car.id')
                ->where('islim_voywey_delivery.courier_g', "voywey-externo");
            })
            ->join('islim_ordens', function ($join) {
              $join->on('islim_ordens.ordNbr', '=', 'islim_voywey_delivery.folio')
                ->where('islim_ordens.status', 'A');
            })
            ->join('islim_clients',
              'islim_clients.dni',
              'islim_ordens.client_id')
            ->join('islim_orders_details', function ($join) {
              $join->on('islim_orders_details.id_ordens', '=', 'islim_ordens.id')
                ->whereNull('islim_orders_details.msisdn');
            })
            ->join('islim_inv_articles',
              'islim_inv_articles.id',
              'islim_orders_details.id_articles')
            ->where('islim_temp_car.status', 'P')
            ->whereIn('islim_temp_car.key', $keys_Jelou)
            ->where([
              [$dateFilter, '>=', $filters['dateStar']],
              [$dateFilter, '<=', $filters['dateEnd']]]);
        }

        //
        //Uniones del VoyWey en proceso
        //
        //$dataInProcess = "";
        if (!empty($filters['currier'])) {
          if ($filters['currier'] == 'EX' && $filters['conciliado'] != 'NO') {
            if (!empty($data)) {
              $data = $data->union($voyExterno);
            } else {
              $data = $voyExterno;
            }
          } else {
            //Voywey Interno
            if (!empty($data)) {
              $data = $data->union($voyInterno);
            } else {
              $data = $voyInterno;
            }
            if ($filters['conciliado'] != 'NO') {
              $data = $data->union($voyInternoMP);
            }
          }
        } else {
          //Los dos curriers
          if ($filters['conciliado'] != "NO") {
            //$dataInProcess = $voyInterno->union($voyExterno);

            if (!empty($data)) {
              $data = $data->union($voyInterno);
            } else {
              $data = $voyInterno;
            }
            $data = $data->union($voyInternoMP);
            $data = $data->union($voyExterno);

          } else {
            if (!empty($data)) {
              $data = $data->union($voyInterno);
            } else {
              $data = $voyInterno;
            }
          }
        }
        //
        //END Uniones del VoyWey en proceso
        //
      }
    }
/////////////////////////////////////////////////////////////////

    /*   $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
    return is_numeric($binding) ? $binding : "'{$binding}'";
    })->toArray());
    Log::info($query);
     */
    return $data->get();
  }
}
