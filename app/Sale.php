<?php

namespace App;

use App\APIKey;
use App\ClientNetwey;
use App\CoordinateChanges;
use App\Inventory;
use App\Organization;
use App\Pack;
use App\Product;
use App\Service;
use App\FiberZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sale extends Model
{
  protected $table = 'islim_sales';

  protected $fillable = [
    'id',
    'services_id',
    'concentrators_id',
    'assig_pack_id',
    'inv_arti_details_id',
    'api_key',
    'users_email',
    'packs_id',
    'order_altan',
    'unique_transaction',
    'codeAltan',
    'type',
    'id_point',
    'description',
    'amount',
    'amount_net',
    'com_amount',
    'msisdn',
    'conciliation',
    'lat',
    'lng',
    'position',
    'date_reg',
    'sale_type',
    'from',
    'status',
    'is_migration',
    'typePayment'
  ];

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
      $obj = new Sale;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function existDN($msisdnTransit = false)
  {
    if ($msisdnTransit) {
      return self::getConnect('R')
        ->select('id', 'date_reg', 'msisdn')
        ->where([
          ['type', 'P'],
          ['msisdn', $msisdnTransit]
        ])
        ->first();
    }
    return null;
  }

  public static function getConnectFDB($typeCon = false)
  {
    if ($typeCon) {
      return DB::connection($typeCon == 'W' ? 'netwey-w' : 'netwey-r')
        ->table('islim_sales');
    }
  }

  public static function getSales($status, $type)
  {
    $sales = Sale::select(
      'id',
      'services_id',
      'concentrators_id',
      'assig_pack_id',
      'inv_arti_details_id',
      'api_key',
      'users_email',
      'packs_id',
      'order_altan',
      'unique_transaction',
      'codeAltan',
      'type',
      'id_point',
      'description',
      'amount',
      'amount_net',
      'com_amount',
      'msisdn',
      'conciliation',
      'lat',
      'lng',
      'date_reg',
      'status',
      'user_locked',
      'from',
      'sale_type'
    )
      ->whereIn('status', $status)
      ->whereIn('type', $type)->get();

    $ids = array();
    foreach ($sales as $sale) {
      $sale->apikey        = APIKey::getAPIKey($sale->api_key);
      $sale->services_name = Service::select('title')->where(['id' => $sale->services_id])->first()->title;
      $sale->pack          = Pack::find($sale->packs_id);
      $article             = Inventory::find($sale->inv_arti_details_id);
      if (isset($article)) {
        $article->parent = Product::find($article->inv_article_id);
      }

      $sale->article = $article;
      $sale->client  = ClientNetwey::getClient($sale->msisdn);
      $ids[]         = $sale->id;
    }
    return ['sales' => $sales, 'ids' => $ids];
  }

  public static function getSale($email, $status, $type, $conciliation = null)
  {
    $sales = Sale::select('id', 'services_id', 'concentrators_id', 'assig_pack_id', 'inv_arti_details_id', 'api_key', 'users_email', 'packs_id', 'order_altan', 'unique_transaction', 'codeAltan', 'type', 'id_point', 'description', 'amount', 'amount_net', 'com_amount', 'msisdn', 'conciliation', 'lat', 'lng', 'date_reg', 'status')->where(['users_email' => $email])->whereIn('status', $status)->whereIn('type', $type);
    if (!empty($conciliation)) {
      $sales = $sales->where(['conciliation' => $conciliation]);
    }
    $amount = $sales->sum('amount');
    $sales  = $sales->get();
    $ids    = array();
    foreach ($sales as $sale) {
      $sale->apikey        = APIKey::getAPIKey($sale->api_key);
      $sale->services_name = Service::select('title')->where(['id' => $sale->services_id])->first()->title;
      $sale->pack          = Pack::find($sale->packs_id);
      $article             = Inventory::where(['id' => $sale->inv_arti_details_id])->first();
      if (isset($article)) {
        $article->parent = Product::find($article->inv_article_id);
      }

      $sale->article = $article;
      $sale->client  = ClientNetwey::getClient($sale->msisdn);
      $ids[]         = $sale->id;
    }
    return ['sales' => $sales, 'ids' => $ids, 'amount' => $amount];
  }

  public static function getSaleReport($type = null, $supervisor = null, $seller = null, $date_ini = null, $date_end = null, $saleStatus = null, $concentrator = null, $unique_transaction = null, $msisdn = null, $service = null, $product = null, $conciliation = null, $serviceability = null, $org = null, $userprofile = null, $userEmail = null, $typebuy = null)
  {
    $userprofile = empty($userprofile) ? session('user')->profile->type : $userprofile;

    // $orgUser = !empty(session('user')) ? session('user')->id_org : false;

    // if (!$orgUser) {
    //     $datau = User::getConnect('R')->select('id_org')->where('email', $userEmail)->first();
    //     if (!empty($datau)) {
    //         $orgUser = $datau->id_org;
    //     }

    // }
    /*$queryuser = '(CASE WHEN '.
    '(islim_sales.users_email IS NOT NULL) THEN '.
    '(SELECT '.
    'CONCAT(islim_users.name, " ", islim_users.last_name) FROM '.
    'islim_users WHERE '.
    'islim_users.email = islim_sales.users_email) '.
    'ELSE islim_sales.users_email END) AS user_name';*/

    $queryuser = 'CONCAT(coo.name, " ", coo.last_name) AS user_name';

    $querycoord = '(CASE WHEN ' .
      '(coo.parent_email IS NOT NULL) THEN ' .
      '(SELECT ' .
      'CONCAT(islim_users.name, " ", islim_users.last_name) FROM ' .
      'islim_users WHERE ' .
      'islim_users.email = coo.parent_email) ' .
      'ELSE ' .
      '(SELECT ' .
      'CONCAT(islim_users.name, " ", islim_users.last_name) FROM ' .
      'islim_users WHERE ' .
      'islim_users.email = islim_sales.users_email) END) AS coord_name';

    $querycocnentrator = '(CASE WHEN ' .
      '(islim_sales.concentrators_id IS NOT NULL) THEN ' .
      '(SELECT ' .
      'islim_concentrators.name FROM ' .
      'islim_concentrators WHERE ' .
      'islim_concentrators.id = islim_sales.concentrators_id) ' .
      'ELSE islim_sales.concentrators_id END) AS concentrator';

    $querypack = '(CASE WHEN ' .
      '(islim_sales.packs_id IS NOT NULL) THEN ' .
      '(SELECT ' .
      'islim_packs.title FROM ' .
      'islim_packs WHERE ' .
      'islim_packs.id = islim_sales.packs_id) ' .
      'ELSE islim_sales.packs_id END) AS pack';

    $saleAmount = "(CASE WHEN (islim_sales.amount = 0) THEN (SELECT b.amount FROM islim_sales as b WHERE b.unique_transaction = islim_sales.unique_transaction AND b.type = 'V') ELSE islim_sales.amount END) as amount";

    //$report = DB::table('islim_sales')->select(
    $report = sale::getConnect('R')->select(
      'islim_sales.unique_transaction',
      'islim_sales.date_reg',
      'islim_sales.msisdn',
      'islim_inv_arti_details.iccid',
      'islim_inv_arti_details.imei',
      'islim_inv_articles.title AS article',
      //'islim_sales.amount',
      'islim_sales.conciliation',
      'islim_sales.id',
      'islim_services.title AS service',
      'islim_services.description AS service_desc',
      'islim_clients.dni',
      'islim_clients.name AS client_name',
      'islim_clients.last_name AS client_lname',
      'islim_clients.phone_home AS client_phone',
      'islim_clients.phone AS client_phone2',
      'islim_client_netweys.serviceability',
      'islim_client_netweys.lat',
      'islim_client_netweys.date_buy',
      'islim_client_netweys.lng',
      'islim_client_netweys.price_remaining',
      'islim_client_netweys.type_buy',
      'islim_client_netweys.total_debt',
      'islim_sales.type AS type',
      'islim_sales.order_altan',
      'islim_sales.codeAltan',
      'islim_dts_organizations.business_name',
      'coo.parent_email',
      'coo.platform',
      'islim_sales.date_init815',
      'islim_sales.date_end815',
      DB::raw($querypack),
      DB::raw($querycocnentrator),
      DB::raw($queryuser),
      DB::raw($querycoord),
      DB::raw($saleAmount)
    )
      ->leftJoin('islim_users as coo', 'coo.email', '=', 'islim_sales.users_email')
      ->join('islim_services', 'islim_services.id', '=', 'islim_sales.services_id')
      ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', '=', 'islim_sales.msisdn')
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
      ->orderBy('islim_sales.date_reg');

    $amount = sale::getConnect('R')
      ->join('islim_services', 'islim_services.id', '=', 'islim_sales.services_id')
      ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', '=', 'islim_sales.msisdn')
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
      ->orderBy('islim_sales.date_reg');

    if (isset($type) && !empty($type)) {
      if ($type == 'ups' || $type == 'P') {
        $report = $report->where('islim_sales.type', '=', 'P');
        /*$report = $report->where(function($query){
        $query->where('islim_sales.type', '=', 'P')
        ->orWhere('islim_sales.type', '=', 'V');
        });*/

        $report = $report->where([
          ['islim_clients.name', '!=', 'TEMPORAL'],
          //['islim_clients.last_name', '!=', 'TEMPORAL']
        ]);

        //$report = $report->where('islim_sales.type', 'P');
        //$report = $report->groupBy('islim_sales.unique_transaction');
        $report = $report->where('islim_client_netweys.status', 'A');
        /*$report = $report->where([
        ['islim_sales.amount', '>', 0],
        ['islim_client_netweys.status','A']
        ]);*/

        $amount = $amount->where('islim_sales.type', '=', 'P');
        /*$amount = $amount->where(function($query){
        $query->where('islim_sales.type', '=', 'P')
        ->orWhere('islim_sales.type', '=', 'V');
        });*/

        $amount = $amount->where([
          ['islim_clients.name', '!=', 'TEMPORAL'],
          //['islim_clients.last_name', '!=', 'TEMPORAL']
        ]);

        $amount = $amount->where('islim_client_netweys.status', 'A');
        //$amount = $amount->groupBy('islim_sales.unique_transaction');
        /*$amount = $amount->where([
      ['islim_sales.amount', '>', 0],
      ['islim_client_netweys.status','A']
      ]);*/
      }
      if ($type == 'recharges' || $type == 'R') {
        $report = $report->where('islim_sales.type', '=', 'R');
        $amount = $amount->where('islim_sales.type', '=', 'R');
      }
    }

    if (!empty($typebuy) && $type == 'ups') {
      $report = $report->where('islim_client_netweys.type_buy', $typebuy);
    }

    /*
    PREPARAMOS LOS FILTROS DE USUARIOS
     **/

    $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

    $filtersUser = array();
    $filtersOrgs = 0;
    if ((isset($supervisor) && !empty($supervisor)) && (isset($seller) && !empty($seller))) {
      if ($seller == 'UN_VALOR_QUE_INDIQUE_QUE_TRAIGA_SOLO_REGISTROS_DEL_COORDINADOR') {
        if ($userprofile == "master") {
          if (empty($org)) {
            $filtersUser = ['u.parent_email' => $supervisor, 'u.email' => $supervisor];
          } else {
            $filtersUser = ['u.parent_email' => $supervisor, 'u.email' => $supervisor, 'u.id_org' => $org]; //
            // Log::info('1');
          }
        } else {
          $filtersUser = ['u.parent_email' => $supervisor, 'u.email' => $supervisor]; //, 'u.id_org' => $orgUser
          // Log::info('2');
          $filtersOrgs = 1;
        }
      } else {
        if ($userprofile == "master") {
          if (empty($org)) {
            $filtersUser = ['u.parent_email' => $supervisor, 'u.email' => $seller];
          } else {
            $filtersUser = ['u.parent_email' => $supervisor, 'u.email' => $seller, 'u.id_org' => $org]; //
            // Log::info('3');
          }
        } else {
          $filtersUser = ['u.parent_email' => $supervisor, 'u.email' => $seller]; // , 'u.id_org' => $orgUser
          // Log::info('4');
          $filtersOrgs = 1;
        }
      }
    } else {
      if (isset($supervisor) && !empty($supervisor)) {
        if ($userprofile == "master") {
          if (empty($org)) {
            $filtersUser = ['u.parent_email' => $supervisor];
          } else {
            $filtersUser = ['u.parent_email' => $supervisor, 'u.id_org' => $org]; //
            // Log::info('5');
          }
        } else {
          $filtersUser = ['u.parent_email' => $supervisor]; //, 'u.id_org' => $orgUser
          $filtersOrgs = 1;
          // Log::info('6');
        }
      } else {
        if (isset($seller) && !empty($seller)) {
          if ($userprofile == "master" || $userprofile == "operation") {
            if (empty($org)) {
              $filtersUser = ['u.email' => $seller];
            } else {
              $filtersUser = ['u.email' => $seller, 'u.id_org' => $org]; //
              // Log::info('7');
            }
          } else {
            $filtersUser = ['u.email' => $seller]; //, 'u.id_org' => $orgUser
            $filtersOrgs = 1;
            // Log::info('8');
          }
        }
      }
    }

    /*
    PREPARAMOS LOS FILTROS DE VENTAS
     **/

    if (count($filtersUser) > 0) {
      $report = $report->leftJoin('islim_users as u', function ($join) {
        $join->on('u.email', '=', 'islim_sales.users_email');
      })->where($filtersUser);
      $amount = $amount->leftJoin('islim_users as u', function ($join) {
        $join->on('u.email', '=', 'islim_sales.users_email');
      })->where($filtersUser);
      if ($filtersOrgs == 1) {
        $report = $report->where(function ($query) use ($orgs) {
          $query->whereIn('u.id_org', $orgs->pluck('id'))
            ->orWhereNull('u.id_org');
        });
        $amount = $amount->where(function ($query) use ($orgs) {
          $query->whereIn('u.id_org', $orgs->pluck('id'))
            ->orWhereNull('u.id_org');
        });
      }
    } else {
      if ($userprofile != "master") {
        $report = $report->leftJoin('islim_users as u', function ($join) {
          $join->on('u.email', '=', 'islim_sales.users_email');
        })->where(function ($query) use ($orgs) {
          $query->whereIn('u.id_org', $orgs->pluck('id'))
            ->orWhereNull('u.id_org');
        });
        $amount = $amount->leftJoin('islim_users as u', function ($join) {
          $join->on('u.email', '=', 'islim_sales.users_email');
        })->where(function ($query) use ($orgs) {
          $query->whereIn('u.id_org', $orgs->pluck('id'))
            ->orWhereNull('u.id_org');
        });
        // Log::info('9');
      } else {
        if (!empty($org)) {
          $report = $report->leftJoin('islim_users as u', function ($join) {
            $join->on('u.email', '=', 'islim_sales.users_email');
          })->where('u.id_org', $org);
          $amount = $amount->leftJoin('islim_users as u', function ($join) {
            $join->on('u.email', '=', 'islim_sales.users_email');
          })->where('u.id_org', $org);
          // Log::info('10');
        } else {
          $report = $report->leftJoin('islim_users as u', function ($join) {
            $join->on('u.email', '=', 'islim_sales.users_email');
          });
        }
      }
    }

    // if (count($filtersUser) > 0) {
    //     $report = $report->leftJoin('islim_users as u', function ($join) {$join->on('u.email', '=', 'islim_sales.users_email');})->where($filtersUser);
    //     $amount = $amount->leftJoin('islim_users as u', function ($join) {$join->on('u.email', '=', 'islim_sales.users_email');})->where($filtersUser);
    // } else {
    //         $report = $report->leftJoin('islim_users as u', function ($join) {$join->on('u.email', '=', 'islim_sales.users_email');});
    //         $amount = $amount->leftJoin('islim_users as u', function ($join) {$join->on('u.email', '=', 'islim_sales.users_email');});
    // }

    // if (empty($org)) {
    //     $report = $report->whereIn('u.id_org',$orgs->pluck('id'));
    //     $amount = $amount->whereIn('u.id_org',$orgs->pluck('id'));
    // }
    // else{
    //     $report = $report->where('u.id_org',$org);
    //     $amount = $amount->where('u.id_org',$org);
    // }

    $report = $report->leftJoin('islim_dts_organizations', function ($join) {
      $join->on('islim_dts_organizations.id', '=', 'u.id_org');
    });

    if (isset($date_ini) && !empty($date_ini)) {
      $date_ini = $date_ini . ' 00:00:00';
    }

    if (isset($date_end) && !empty($date_end)) {
      $date_end = $date_end . ' 23:59:59';
    }

    if (!empty($date_ini) && !empty($date_end)) {
      $report = $report->whereBetween('islim_sales.date_reg', [$date_ini, $date_end]);
      $amount = $amount->whereBetween('islim_sales.date_reg', [$date_ini, $date_end]);
    } else {
      if (!empty($date_ini)) {
        $report = $report->where('islim_sales.date_reg', '>=', $date_ini);
        $amount = $amount->where('islim_sales.date_reg', '>=', $date_ini);
      } else {
        if (!empty($date_end)) {
          $report = $report->where('islim_sales.date_reg', '<=', $date_end);
          $amount = $amount->where('islim_sales.date_reg', '<=', $date_end);
        }
      }
    }

    if (count($saleStatus) > 0) {
      $report = $report->whereIn('islim_sales.status', $saleStatus);
      $amount = $amount->whereIn('islim_sales.status', $saleStatus);
    }

    if ((isset($concentrator) && !empty($concentrator))) {
      $report = $report->where('islim_sales.concentrators_id', $concentrator);
      $amount = $amount->where('islim_sales.concentrators_id', $concentrator);
    }

    if (isset($unique_transaction) && !empty($unique_transaction)) {
      $report = $report->where('islim_sales.unique_transaction', '=', $unique_transaction);
      $amount = $amount->where('islim_sales.unique_transaction', '=', $unique_transaction);
    }

    if (isset($msisdn) && !empty($msisdn)) {
      $report = $report->where('islim_sales.msisdn', '=', $msisdn);
      $amount = $amount->where('islim_sales.msisdn', '=', $msisdn);
    }

    if (isset($service) && !empty($service)) {
      $report = $report->where('islim_services.id', '=', $service);
      $amount = $amount->where('islim_services.id', '=', $service);
    }

    if (isset($product) && !empty($product)) {
      $report = $report->where('islim_inv_articles.id', '=', $product);
      $amount = $amount->where('islim_inv_articles.id', '=', $product);
    }

    if (isset($conciliation) && !empty($conciliation)) {
      $report = $report->where('islim_sales.conciliation', '=', $conciliation);
      $amount = $amount->where('islim_sales.conciliation', '=', $conciliation);
    }

    if (isset($serviceability) && !empty($serviceability)) {
      $report = $report->where('islim_client_netweys.serviceability', '=', $serviceability);
      $amount = $amount->where('islim_client_netweys.serviceability', '=', $serviceability);
    }

    //  $query = vsprintf(str_replace('?', '%s', $report->toSql()), collect($report->getBindings())->map(function ($binding) {
    //     return is_numeric($binding) ? $binding : "'{$binding}'";
    // })->toArray());

    // Log::info($query);

    return ['sales' => $report->distinct()->get(), 'amount' => $amount->sum('amount')];
  }

  //Nuevo metodo para obtener reporte de altas
  public static function getSaleReportUps($filter = [])
  {
    if (is_array($filter) && count($filter)) {
      $datau = User::getConnect('R')->select('id_org')
        ->where([['email', $filter['user']], ['status', 'A']])->first();

      if (!empty($datau)) {
        $saleAmount = "(CASE WHEN (islim_sales.amount = 0 AND islim_sales.is_migration = 'N')
                                THEN (SELECT b.amount FROM islim_sales as b
                                      WHERE b.unique_transaction = islim_sales.unique_transaction AND b.type = 'V'
                                     )
                                ELSE islim_sales.amount END
                               ) as amount";

        $report = self::getConnectFDB('R')
          ->select(
            'islim_sales.unique_transaction',
            'islim_sales.date_reg',
            'islim_sales.msisdn',
            'islim_sales.is_migration',
            'islim_sales.conciliation',
            'islim_sales.sale_type',
            'islim_sales.from',
            'islim_sales.user_locked',
            'islim_sales.typePayment',
            'islim_clients.name AS client_name',
            'islim_clients.last_name AS client_lname',
            'islim_clients.phone_home AS client_phone',
            'islim_clients.phone AS client_phone2',
            'islim_clients.campaign',
            'islim_client_netweys.lat',
            'islim_client_netweys.lng',
            'islim_client_netweys.type_buy',
            'islim_users.name as user_name',
            'islim_users.last_name as user_last_name',
            'islim_users.email as user_email',
            'coo.name as coord_name',
            'coo.last_name as coord_last_name',
            'coo.email as coord_email',
            'islim_packs.title as pack',
            'islim_inv_articles.title AS article',
            'islim_inv_arti_details.iccid',
            'islim_inv_arti_details.imei',
            'islim_services.title AS service',
            'islim_dts_organizations.business_name',
            'inst.name as installer_name',
            'inst.last_name as installer_last_name',
            'inst.email as installer_email',
            'inst_sel.name as sellerf_name',
            'inst_sel.last_name as sellerf_last_name',
            'inst_sel.email as sellerf_email',
            'islim_fiber_zone.name AS zone_name',
            DB::raw($saleAmount)
          )
          ->selectRaw('CONCAT(islim_billings.serie,"-",islim_billings.id) as billing')
          ->join('islim_client_netweys', 'islim_client_netweys.msisdn', 'islim_sales.msisdn')
          ->join('islim_clients', 'islim_clients.dni', 'islim_client_netweys.clients_dni')
          ->join('islim_users', 'islim_users.email', '=', 'islim_sales.users_email')
          ->leftJoin('islim_users as coo', 'coo.email', '=', 'islim_users.parent_email')
          ->leftJoin('islim_dts_organizations', 'islim_dts_organizations.id', 'islim_users.id_org')
          ->leftJoin('islim_packs', 'islim_packs.id', 'islim_sales.packs_id')
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', 'islim_sales.msisdn')
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->join('islim_services', 'islim_services.id', 'islim_sales.services_id')
          ->leftJoin('islim_billings', 'islim_billings.sales_unique_transaction', 'islim_sales.unique_transaction')
          ->leftJoin('islim_installations', 'islim_installations.msisdn', 'islim_sales.msisdn')
          ->leftJoin('islim_users as inst', 'inst.email', '=', 'islim_installations.installer')
          ->leftJoin('islim_users as inst_sel', 'inst_sel.email', '=', 'islim_installations.seller')
          ->leftJoin('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_client_netweys.id_fiber_zone')
          ->where([
            ['islim_sales.type', 'P'],
            ['islim_clients.name', '!=', 'TEMPORAL'],
          ])
          ->whereIn('islim_sales.status', ['E', 'A'])
          ->whereIn('islim_client_netweys.status', ['A', 'S']);
        //->orderBy('islim_sales.date_reg');

        //Filtros del reporte
        $report = self::getFiltersUA($report, $filter, $datau);

        return $report;
      }
    }

    return [];
  }

  //Nuevo metodo para obtener reporte de recargas
  public static function getSaleReportRecharge($filter = [])
  {
    if (is_array($filter) && count($filter)) {
      $datau = User::getConnect('R')->select('id_org')->where([['email', $filter['user']], ['status', 'A']])->first();

      if (!empty($datau)) {
        //getConnect('R')
        $report = self::getConnectFDB('R')
          ->select(
            'islim_sales.unique_transaction',
            'islim_sales.date_reg',
            'islim_sales.msisdn',
            'islim_sales.is_migration',
            'islim_sales.conciliation',
            'islim_sales.amount',
            'islim_sales.sale_type',
            'islim_clients.name AS client_name',
            'islim_clients.last_name AS client_lname',
            'islim_clients.phone_home AS client_phone',
            'islim_clients.phone AS client_phone2',
            'islim_client_netweys.lat',
            'islim_client_netweys.lng',
            'islim_client_netweys.type_buy',
            'islim_users.name as user_name',
            'islim_users.last_name as user_last_name',
            'islim_users.email as user_email',
            'islim_inv_articles.title AS article',
            'islim_inv_arti_details.iccid',
            'islim_inv_arti_details.imei',
            'islim_services.title AS service',
            'islim_dts_organizations.business_name',
            'islim_concentrators.name as concentrator',
            'islim_oxxo_sales.folio',
            'inst.name as installer_name',
            'inst.last_name as installer_last_name',
            'inst.email as installer_email',
            'islim_fiber_zone.name as zone_name'
          )
          ->selectRaw('CONCAT(islim_billings.serie,"-",islim_billings.id) as billing')
          ->join('islim_client_netweys', 'islim_client_netweys.msisdn', 'islim_sales.msisdn')
          ->join('islim_clients', 'islim_clients.dni', 'islim_client_netweys.clients_dni')
          ->leftJoin('islim_users', 'islim_users.email', '=', 'islim_sales.users_email')
          ->leftJoin('islim_concentrators', 'islim_concentrators.id', 'islim_sales.concentrators_id')
          ->leftJoin('islim_dts_organizations', 'islim_dts_organizations.id', 'islim_users.id_org')
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', 'islim_sales.msisdn')
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->join('islim_services', 'islim_services.id', 'islim_sales.services_id')
          ->leftJoin('islim_oxxo_sales', 'islim_oxxo_sales.sale_id', 'islim_sales.id')
          ->leftJoin('islim_billings', 'islim_billings.sales_unique_transaction', 'islim_sales.unique_transaction')
          ->leftJoin('islim_installations', 'islim_installations.msisdn', 'islim_sales.msisdn')
          ->leftJoin('islim_users as inst', 'inst.email', '=', 'islim_installations.installer')
          ->leftJoin('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_client_netweys.id_fiber_zone')
          ->where([
            ['islim_sales.type', 'R'],
          ])
          ->whereIn('islim_sales.status', ['E', 'A'])
          ->whereIn('islim_client_netweys.status', ['A', 'S']);
        //->orderBy('islim_sales.date_reg');

        //Filtros del reporte
        $report = self::getFiltersUA($report, $filter, $datau);

        return $report;
      }
    }

    return [];
  }

  //Nuevo metodo para obtener reporte de ventas
  public static function getSaleReportAll($filter = [])
  {
    if (is_array($filter) && count($filter)) {
      $datau = User::getConnect('R')->select('id_org')->where([['email', $filter['user']], ['status', 'A']])->first();

      if (!empty($datau)) {
        $saleAmount = "(CASE WHEN (islim_sales.amount = 0)
                                THEN (SELECT b.amount FROM islim_sales as b
                                      WHERE b.unique_transaction = islim_sales.unique_transaction AND b.type = 'V'
                                     )
                                ELSE islim_sales.amount END
                               ) as amount";

        $isPhoneRef = "IF(referred_dn IS NULL,'N','Y') as isPhoneRef";

        $report = self::getConnectFDB('R')
          ->select(
            'islim_sales.unique_transaction',
            'islim_sales.date_reg',
            'islim_sales.msisdn',
            'islim_sales.order_altan',
            'islim_sales.codeAltan',
            'islim_sales.type',
            'islim_sales.sale_type',
            'islim_sales.from',
            'islim_clients.name AS client_name',
            'islim_clients.last_name AS client_lname',
            'islim_clients.phone_home AS client_phone',
            'islim_clients.campaign',
            'islim_users.name as user_name',
            'islim_users.last_name as user_last_name',
            'inst.name as installer_name',
            'inst.last_name as installer_last_name',
            'islim_inv_articles.title AS article',
            'islim_services.title AS service',
            'islim_concentrators.name as concentrator',
            'islim_packs.title as pack',
            'islim_fiber_zone.name as zone_name',
            DB::raw($saleAmount),
            DB::raw($isPhoneRef),
            'islim_client_netweys.referred_dn as phoneRefBy'

          )
          ->join('islim_client_netweys', 'islim_client_netweys.msisdn', 'islim_sales.msisdn')
          ->join('islim_clients', 'islim_clients.dni', 'islim_client_netweys.clients_dni')
          ->leftJoin('islim_users', 'islim_users.email', '=', 'islim_sales.users_email')
          ->leftJoin('islim_concentrators', 'islim_concentrators.id', 'islim_sales.concentrators_id')
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', 'islim_sales.msisdn')
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->join('islim_services', 'islim_services.id', 'islim_sales.services_id')
          ->leftJoin('islim_packs', 'islim_packs.id', 'islim_sales.packs_id')
          ->leftJoin('islim_installations', 'islim_installations.msisdn', 'islim_sales.msisdn')
          ->leftJoin('islim_users as inst', 'inst.email', '=', 'islim_installations.installer')
          ->leftJoin('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_client_netweys.id_fiber_zone')
          ->where('islim_clients.name', '!=', 'TEMPORAL')
          ->whereIn('islim_sales.status', ['E', 'A'])
          ->whereIn('islim_client_netweys.status', ['A', 'S']);
        //->orderBy('islim_sales.date_reg');

        if (!empty($filter['type'])) {
          if ($filter['type'] == 'ups') {
            $report = $report->where('islim_sales.type', 'P');
          } else {
            $report = $report->where('islim_sales.type', 'R');
          }
        } else {
          $report = $report->where('islim_sales.type', '!=', 'V');
        }

        // $query = vsprintf(str_replace('?', '%s', $report->toSql()), collect($report->getBindings())->map(function ($binding) {
        //         return is_numeric($binding) ? $binding : "'{$binding}'";
        //     })->toArray());

        //Log::alert($query);


        //Filtros del reporte
        $report = self::getFiltersUA($report, $filter, $datau);

        return $report;
      }
    }

    return [];
  }

  //Nuevo metodo para obtener reporte de concentradores
  public static function getSaleReportConcentrator($filter = [])
  {
    if (is_array($filter) && count($filter)) {
      $datau = User::getConnect('R')->select('id_org')->where([['email', $filter['user']], ['status', 'A']])->first();

      if (!empty($datau)) {
        $saleAmount = "(CASE WHEN (islim_sales.amount = 0)
                                THEN (SELECT b.amount FROM islim_sales as b
                                      WHERE b.unique_transaction = islim_sales.unique_transaction AND b.type = 'V'
                                     )
                                ELSE islim_sales.amount END
                               ) as amount";

        $report = self::getConnect('R')
          ->select(
            'islim_sales.unique_transaction',
            'islim_sales.date_reg',
            'islim_sales.msisdn',
            'islim_sales.sale_type',
            'islim_sales.conciliation',
            'islim_sales.type',
            'islim_inv_articles.title AS article',
            'islim_services.title AS service',
            'islim_concentrators.name as concentrator',
            'islim_packs.title as pack',
            DB::raw($saleAmount)
          )
          ->leftJoin('islim_concentrators', 'islim_concentrators.id', 'islim_sales.concentrators_id')
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', 'islim_sales.msisdn')
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->join('islim_services', 'islim_services.id', 'islim_sales.services_id')
          ->join('islim_client_netweys', 'islim_client_netweys.msisdn', 'islim_sales.msisdn')
          ->join('islim_clients', 'islim_clients.dni', 'islim_client_netweys.clients_dni')
          ->leftJoin('islim_packs', 'islim_packs.id', 'islim_sales.packs_id')
          ->where('islim_clients.name', '!=', 'TEMPORAL')
          ->whereIn('islim_sales.status', ['E', 'A'])
          ->whereIn('islim_client_netweys.status', ['A', 'S'])
          ->orderBy('islim_sales.date_reg');

        if (!empty($filter['type'])) {
          if ($filter['type'] == 'ups') {
            $report = $report->where('islim_sales.type', 'P');
          } else {
            $report = $report->where('islim_sales.type', 'R');
          }
        } else {
          $report = $report->where('islim_sales.type', '!=', 'V');
        }

        //Filtros del reporte
        $report = self::getFiltersUA($report, $filter, $datau);

        return $report;
      }
    }

    return [];
  }

  /*Metodo para ejecutar filtros del reporte de altas y recargas*/
  private static function getFiltersUA($collection, $filter, $datau = false)
  {
    //Organizacion
    if ($filter['user_profile'] != 'master' || !empty($filter['org'])) {
      $orgid = !empty($filter['org']) ? $filter['org'] : $datau->id_org;

      $collection = $collection->where('islim_users.id_org', $orgid);
    }

    //Coordinadores
    if (!empty($filter['supervisor'])) {
      $sub = User::getConnect('R')->select('email')
        ->where('parent_email', $filter['supervisor'])
        ->get()
        ->pluck('email');

      $sub[] = $filter['supervisor'];

      $collection = $collection->whereIn('islim_sales.users_email', $sub);
    }

    //Tipo de linea
    if (!empty($filter['type_line'])) {
      $collection = $collection->where('islim_sales.sale_type', $filter['type_line']);
    }

    //Vendedores
    if (!empty($filter['seller'])) {
      $collection = $collection->where('islim_sales.users_email', $filter['seller']);
    }

    //Fecha
    if (!empty($filter['date_ini']) && !empty($filter['date_end'])) {
      $collection = $collection->where([
        ['islim_sales.date_reg', '>=', $filter['date_ini'] . ' 00:00:00'],
        ['islim_sales.date_reg', '<=', $filter['date_end'] . ' 23:59:59'],
      ]);
    } elseif (!empty($filter['date_ini'])) {
      $collection = $collection->where('islim_sales.date_reg', '>=', $filter['date_ini'] . ' 00:00:00');
    } elseif (!empty($filter['date_end'])) {
      $collection = $collection->where('islim_sales.date_reg', '<=', $filter['date_end'] . ' 23:59:59');
    }

    //Servicio o Plan
    if (!empty($filter['service'])) {
      $collection = $collection->where('islim_services.id', $filter['service']);
    }

    //Producto
    if (!empty($filter['product'])) {
      $collection = $collection->where('islim_inv_articles.id', $filter['product']);
    }

    //Tipo de compra
    if (!empty($filter['type_buy'])) {
      $collection = $collection->where('islim_client_netweys.type_buy', $filter['type_buy']);
    }

    //Conciliación
    if (!empty($filter['conciliation'])) {
      $collection = $collection->where('islim_sales.conciliation', $filter['conciliation']);
    }

    //Servicialidad
    if (!empty($filter['serviceability'])) {
      $collection = $collection->where('islim_client_netweys.serviceability', $filter['serviceability']);
    }

    //Concentrador
    if (!empty($filter['concentrator'])) {
      $collection = $collection->where('islim_sales.concentrators_id', $filter['concentrator']);
    }

    if (!empty($filter['coverage_area'])) {
      $collection = $collection->where('islim_fiber_zone.id', $filter['coverage_area']);
    }

    return $collection;
  }

  // Data del reposte para base recargadora (BI)
  // Date = m/Y
  public static function reportRechargeBase($date, $type_sale = 'H')
  {
    $clients = Sale::getConnect('R')
      ->select(
        'islim_sales.msisdn',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_clients.email',
        'islim_clients.dni',
        'islim_clients.date_reg'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->where([
        [DB::raw("DATE_FORMAT(islim_sales.date_reg, '%m/%Y')"), $date],
        ['islim_sales.status', 'A'],
        ['islim_sales.type', 'R'],
        ['islim_sales.sale_type', $type_sale],
      ])
      ->groupBy('islim_sales.msisdn');

    return $clients;
  }

  // Data del reporte de ventas online
  // date_ini = dd/mm/yyyy date_end= dd/mm/yyyy
  public static function getSalesForReportOS($date_ini, $date_end, $time_ini, $time_end)
  {
    $dini = substr($date_ini, 6, 4) . "-" . substr($date_ini, 3, 2) . "-" . substr($date_ini, 0, 2) . " 00:00:00";
    $dend = substr($date_end, 6, 4) . "-" . substr($date_end, 3, 2) . "-" . substr($date_end, 0, 2) . " 23:59:59";

    $tini = str_replace(":", "", $time_ini) . "00";
    $tend = str_replace(":", "", $time_end) . "59";

    $int_tini = intval($tini);
    $int_tend = intval($tend);

    $tini = $time_ini . ":00";
    $tend = $time_end . ":59";

    if ($int_tini < $int_tend) {
      $OP = "AND";
    } else {
      $OP = "OR";
    }

    $clients = Sale::select(
      'islim_clients.name as Nombre',
      'islim_clients.last_name as Apellido',
      'islim_clients.phone as Telefono',
      'islim_clients.email as Email',
      'islim_clients.date_reg as Fecha_Registro',
      'islim_inv_articles.title as Equipo_Comprado',
      'islim_services.title as Plan',
      DB::raw('DATE_FORMAT(islim_webpay.date, "%d/%m/%Y %H:%I:%S") as Fecha_Compra'),
      'islim_orders_details.id_ordens as Nro_Orden',
      'islim_sales.msisdn as MSISDN',
      'islim_cars_detail.address as Direccion_Entrega',
      DB::raw('IF(islim_client_netweys.status="A","Activo",islim_ordens_status.status) as Estado'),
      DB::raw('IF(islim_client_netweys.status="A", DATE_FORMAT(islim_sales.date_reg, "%d/%m/%Y %H:%I:%S" ), "") as Fecha_Activacion'),
      DB::raw('IF(islim_client_netweys.status="A",DATEDIFF(islim_sales.date_reg, islim_webpay.date),"") as Dias_en_Activar')
    )
      ->leftJoin(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        '=',
        'islim_sales.msisdn'
      )
      ->leftJoin(
        'islim_clients',
        'islim_clients.dni',
        '=',
        'islim_client_netweys.clients_dni'
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        '=',
        'islim_sales.inv_arti_details_id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        '=',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        '=',
        'islim_sales.services_id'
      )
      ->join(
        'islim_orders_details',
        'islim_orders_details.msisdn',
        '=',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_ordens',
        'islim_ordens.id',
        '=',
        'islim_orders_details.id_ordens'
      )
      ->join(
        'islim_cars_detail',
        'islim_cars_detail.id',
        '=',
        'islim_orders_details.car_detail'
      )
      ->join(
        'islim_ordens_status',
        'islim_ordens_status.id_ordens',
        '=',
        'islim_orders_details.id_ordens'
      )
      ->join(
        'islim_webpay',
        'islim_webpay.order_id',
        '=',
        'islim_orders_details.id_ordens'
      )
      ->where([
        ['islim_webpay.date', '>=', $dini],
        ['islim_webpay.date', '<=', $dend],
        ['islim_sales.status', 'A'],
        ['islim_sales.type', 'P'],
        ['islim_sales.id_point', 'dmd@dmdgroup.net'],

      ])
      ->where(DB::raw('(TIME(islim_webpay.date) >= "' . $tini . '" ' . $OP . ' TIME(islim_webpay.date) <= "' . $tend . '")'), true)
      ->groupBy('islim_sales.msisdn');

    return $clients;
  }

  public static function getSalesNotActive()
  {
    $sub = DB::raw('(SELECT id FROM islim_sales as b WHERE (b.status = "A" OR b.status = "E") AND b.type = "P" AND b.unique_transaction = islim_sales.unique_transaction)');

    $query = Sale::select(
      'islim_sales.date_reg',
      'islim_sales.msisdn',
      'islim_inv_arti_details.imei',
      'islim_inv_arti_details.id',
      'islim_inv_articles.title',
      'islim_users.name',
      'islim_users.last_name'
    )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        '=',
        'islim_sales.inv_arti_details_id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        '=',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_users',
        'islim_users.email',
        '=',
        'islim_sales.users_email'
      )
      ->where('islim_sales.type', 'V')
      ->where(function ($query) {
        $query->orWhere('islim_sales.status', 'A')
          ->orWhere('islim_sales.status', 'E');
      })
      ->whereNull($sub);

    return $query;
  }

  public static function getSalesNotActiveReport($filters = null)
  {
    $sub = DB::raw('(SELECT id FROM islim_sales as b WHERE (b.status = "A" OR b.status = "E") AND b.type = "P" AND b.unique_transaction = islim_sales.unique_transaction)');

    $query = Sale::getConnect('R')->select(
      'islim_sales.date_reg',
      'islim_sales.unique_transaction',
      'islim_sales.msisdn',
      'islim_users.name',
      'islim_users.last_name',
      'coo.name as namecoo',
      'coo.last_name as lastnamecoo',
      'islim_dts_organizations.business_name',
      'islim_inv_articles.title',
      'islim_services.title as service'
    )
      ->join(
        'islim_users',
        'islim_users.email',
        '=',
        'islim_sales.users_email'
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        '=',
        'islim_sales.inv_arti_details_id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        '=',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        '=',
        'islim_sales.services_id'
      )
      ->leftJoin(
        'islim_users as coo',
        'coo.email',
        '=',
        'islim_users.parent_email'
      )
      ->leftJoin(
        'islim_dts_organizations',
        'islim_dts_organizations.id',
        '=',
        'islim_users.id_org'
      )
      ->where('islim_sales.type', 'V')
      ->whereNull($sub)
      ->where(function ($query) {
        $query->orWhere('islim_sales.status', 'A')
          ->orWhere('islim_sales.status', 'E');
      });

    //Filtros del reporte
    if (!empty($filters) && is_array($filters)) {
      if (!empty($filters['org'])) {
        $query = $query->where('islim_users.id_org', $filters['org']);
      } else {
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $query = $query->whereIn('islim_users.id_org', $orgs->pluck('id'));
      }

      if (!empty($filters['sup'])) {
        $query = $query->where(function ($query) use ($filters) {
          $query->where('islim_users.parent_email', $filters['sup'])
            ->orWhere('islim_users.email', $filters['sup']);
        });
      }

      if (!empty($filters['sell'])) {
        $query = $query->where('islim_users.email', $filters['sell']);
      }

      if (!empty($filters['con'])) {
        $query = $query->where('islim_sales.conciliation', $filters['con']);
      }

      if (!empty($filters['ser'])) {
        $query = $query->where('islim_sales.services_id', $filters['ser']);
      }

      if (!empty($filters['pro'])) {
        $query = $query->where('islim_inv_articles.id', $filters['pro']);
      }

      if (!empty($filters['db']) && !empty($filters['de'])) {
        $query = $query->whereBetween('islim_sales.date_reg', [$filters['db'] . ' 00:00:00', $filters['de'] . ' 23:59:59']);
      }

      if (empty($filters['db']) && !empty($filters['de'])) {
        $query = $query->where('islim_sales.date_reg', '<=', $filters['de'] . ' 23:59:59');
      }

      if (!empty($filters['db']) && empty($filters['de'])) {
        $query = $query->where('islim_sales.date_reg', '>=', $filters['db'] . ' 00:00:00');
      }
    }

    return $query;
  }

  public static function getSalesActiveReport($filters = null)
  {

    $query = Sale::getConnect('R')->select(
      'islim_sales.date_reg',
      'islim_sales.unique_transaction',
      'islim_sales.msisdn',
      'islim_users.name',
      'islim_users.last_name',
      'coo.name as namecoo',
      'coo.last_name as lastnamecoo',
      'islim_dts_organizations.business_name',
      'islim_inv_articles.title',
      'islim_services.title as service',
      'islim_clients.name as cliname',
      'islim_clients.last_name as clilastname',
      'islim_clients.email',
      'islim_clients.phone_home',
      'islim_clients.phone',
      'sa.date_reg as date_sale'
    )
      ->leftJoin('islim_sales as sa', function ($join) {
        $join->on('sa.unique_transaction', '=', 'islim_sales.unique_transaction')
          ->where('sa.type', 'V')
          ->where(function ($query) {
            $query->orWhere([['sa.status', 'E'], ['sa.status', 'A']]);
          });
      })
      ->join(
        'islim_users',
        'islim_users.email',
        '=',
        'islim_sales.users_email'
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        '=',
        'islim_sales.inv_arti_details_id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        '=',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_services',
        'islim_services.id',
        '=',
        'islim_sales.services_id'
      )
      ->leftJoin(
        'islim_users as coo',
        'coo.email',
        '=',
        'islim_users.parent_email'
      )
      ->leftJoin(
        'islim_dts_organizations',
        'islim_dts_organizations.id',
        '=',
        'islim_users.id_org'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        '=',
        'islim_sales.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        '=',
        'islim_client_netweys.clients_dni'
      )
      ->where('islim_sales.type', 'P')
      ->where(function ($query) {
        $query->orWhere('islim_sales.status', 'A')
          ->orWhere('islim_sales.status', 'E');
      });

    //Filtros del reporte
    if (!empty($filters) && is_array($filters)) {
      if (!empty($filters['org'])) {
        $query = $query->where('islim_users.id_org', $filters['org']);
      } else {
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $query = $query->whereIn('islim_users.id_org', $orgs->pluck('id'));
      }

      if (!empty($filters['sup'])) {
        $query = $query->where(function ($query) use ($filters) {
          $query->where('islim_users.parent_email', $filters['sup'])
            ->orWhere('islim_users.email', $filters['sup']);
        });
      }

      if (!empty($filters['sell'])) {
        $query = $query->where('islim_users.email', $filters['sell']);
      }

      if (!empty($filters['con'])) {
        $query = $query->where('islim_sales.conciliation', $filters['con']);
      }

      if (!empty($filters['ser'])) {
        $query = $query->where('islim_sales.services_id', $filters['ser']);
      }

      if (!empty($filters['pro'])) {
        $query = $query->where('islim_inv_articles.id', $filters['pro']);
      }

      if (!empty($filters['db']) && !empty($filters['de'])) {
        $query = $query->whereBetween('islim_sales.date_reg', [$filters['db'] . ' 00:00:00', $filters['de'] . ' 23:59:59']);
      }

      if (empty($filters['db']) && !empty($filters['de'])) {
        $query = $query->where('islim_sales.date_reg', '<=', $filters['de'] . ' 23:59:59');
      }

      if (!empty($filters['db']) && empty($filters['de'])) {
        $query = $query->where('islim_sales.date_reg', '>=', $filters['db'] . ' 00:00:00');
      }
    }

    return $query;
  }

  public static function payChangeCoord($msisdn = false)
  {
    if ($msisdn) {
      $cod = explode(',', ENV('OFFERT_COORD'));

      if (!empty($cod)) {
        $client = ClientNetwey::select('n_update_coord')
          ->where('msisdn', $msisdn)
          ->first();

        if (!empty($client)) {
          if ($client->n_update_coord < 2) {
            return -1;
          }

          $data = self::select('id')
            ->where([
              ['msisdn', $msisdn],
              ['status', 'A'],
              ['date_reg', '>=', '2020-02-05'],
            ])
            ->whereIn('codeAltan', $cod)
            ->orderBy('date_reg', 'DESC')
            ->first();

          if (!empty($data)) {
            $isUse = CoordinateChanges::select('id')
              ->where('sale_id', $data->id)
              ->first();

            if (empty($isUse)) {
              return $data->id;
            }
          }
        }
      }
    }

    return null;
  }

  public static function getConsuption($filters = [])
  {
    $query = self::getConnect('R')->select(
      'islim_sales.msisdn',
      DB::raw('sum(islim_estado_consumo.consu_me_rgu_d) as consuption'),
      'islim_services.title',
      'islim_sales.codeAltan',
      'islim_estado_consumo.offer_name',
      'islim_sales.date_reg',
      'islim_estado_consumo.date_sup_be',
      'islim_estado_consumo.date_sup_en',
      DB::raw('count(islim_estado_consumo.msisdn) as days'),
      'islim_sales.type'
    )
      ->leftJoin(
        'islim_estado_consumo',
        function ($q) {
          $q->on('islim_estado_consumo.msisdn', 'islim_sales.msisdn')
            ->where([
              ['islim_estado_consumo.offer_id', DB::raw('islim_sales.codeAltan')],
              ['islim_estado_consumo.date_sup_be', DB::raw('DATE_FORMAT(islim_sales.date_reg, "%Y-%m-%d")')],
              ['islim_estado_consumo.consu_me_rgu_d', '>', 0],
              ['islim_estado_consumo.offer_name', 'NOT LIKE', '%throttling%'],
            ]);
        }
      )
      ->join(
        'islim_services',
        'islim_services.id',
        'islim_sales.services_id'
      )
      ->where(function ($w) {
        $w->where('islim_sales.type', 'P')
          ->orWhere('islim_sales.type', 'R');
      });

    if (is_array($filters) && count($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $query = $query->whereBetween(
          'islim_sales.date_reg',
          [$filters['dateb'], $filters['datee']]
        );
      }

      if (!empty($filters['dateb']) && empty($filters['datee'])) {
        $query = $query->where('islim_sales.date_reg', '>=', $filters['dateb']);
      }

      if (empty($filters['dateb']) && !empty($filters['datee'])) {
        $query = $query->where('islim_sales.date_reg', '<=', $filters['datee']);
      }

      if (!empty($filters['msisdn'])) {
        $query = $query->where('islim_sales.msisdn', $filters['msisdn']);
      }
    }

    $query = $query->groupBy(
      'islim_estado_consumo.date_sup_be',
      'islim_estado_consumo.offer_id',
      'islim_sales.msisdn'
      //'islim_estado_consumo.msisdn'
    )
      ->orderBy('islim_sales.date_reg', 'DESC');
    //->orderBy('islim_sales.msisdn', 'DESC');

    return $query;
  }

  public static function getLastRecharge($msisdn = false, $date_limit = false)
  {
    if ($msisdn) {
      $data = self::getConnect('R')
        ->select('msisdn', 'date_reg', 'services_id')
        ->where([
          ['msisdn', $msisdn],
          ['type', 'R'],
        ])
        ->whereIn('status', ['A', 'E']);

      if ($date_limit) {
        $data->where('date_reg', '<=', $date_limit);
      }

      return $data->orderBy('date_reg', 'DESC')->first();
    }

    return null;
  }
  /*Super Sim*/

  private static function recharge_SuperSim($filters = false, $data = false, $dateStar_static = false)
  {

    $Dn_prev     = 'vacio';
    $cont        = 0;
    $dataOKClear = array();
    $datarev0    = array();
    $TempName    = '';
    $TempMail    = '';
    // Log::info("DATA: " . $data);
    foreach ($data as $dat) {

      /*Primero recorrido*/
      if ($Dn_prev == 'vacio') {
        if ($dat->is_alta) {

          if (!in_array($dat->msisdn, $datarev0)) {
            array_push($datarev0, $dat->msisdn);
            $Dn_prev  = $dat->msisdn;
            $TempName = $dat->nameVendedor;
            $TempMail = $dat->mailvendedor;
            //   Log::info("DN: " . $dat->msisdn);
          }
          // Log::info("ALTA-> : " .$dat);
        }
        /* END Primer recorrido*/
      } else {

        if ($dat->is_alta) {

          if (!in_array($dat->msisdn, $datarev0)) {
            array_push($datarev0, $dat->msisdn);
            $TempName = $dat->nameVendedor;
            $TempMail = $dat->mailvendedor;
            $Dn_prev  = $dat->msisdn;
            $cont     = 0;
          }
          // Log::info("DN: " . $dat->msisdn);
          // Log::info("ALTA-> : " .$dat);
        } else {
          // Log::info("RECARGA-> : " .$dat);
          if (in_array($dat->msisdn, $datarev0)) {
            $fecha = date("YmdHis", strtotime($dat->date_reg));
            if ($fecha >= $filters['dateStar_valid']/*&&
          $fecha <= $filters['dateEnd']*/) {

              if ($Dn_prev == $dat->msisdn) {
                $cont++;
              }

              $dat->rownum_sales = $cont;

              //Log::info("dat-> : " . $dat);

              /*3er y 4to registro*/
              if (
                $dat->rownum_sales >= 3 &&
                $dat->rownum_sales <= 4
              ) {
                $dat->nameVendedor = $TempName;
                $dat->mailvendedor = $TempMail;
                array_push($dataOKClear, $dat);
              }
            }
          }
        }
      }
      // Log::info("Ban_valid-> : " . $Ban_valid);
    }
    return $dataOKClear;
  }

  public static function getSuper_Sim($filters = false)
  {
    if ($filters) {
      ini_set('max_execution_time', 180);
      $dateStar_static = date("Y-m-d H:i:s", strtotime('2021-05-17 00:00:00'));

      $info = self::getConnect('R')
        ->select('islim_sales.msisdn')
        ->join(
          'islim_services',
          function ($join) {
            $join->on('islim_services.id', '=', 'islim_sales.services_id');
          }
        )
        ->leftJoin(
          'islim_pack_prices',
          'islim_pack_prices.service_id',
          '=',
          'islim_services.id'
        )
        ->join(
          'islim_arti_packs',
          'islim_arti_packs.pack_id',
          '=',
          'islim_pack_prices.pack_id'
        )
        ->join(
          'islim_inv_articles',
          function ($join) {
            $join->on('islim_inv_articles.id', '=', 'islim_arti_packs.inv_article_id');
          }
        );
      /*Tipo simCard*/

      if (!empty($filters['DN'])) {
        $info = $info->where('islim_sales.msisdn', $filters['DN']);
      }

      $info = $info->where([
        ['islim_sales.sale_type', 'T'],
        ['islim_sales.type', 'P'],
        ['islim_inv_articles.category_id', '2'],
        [DB::raw('CAST(islim_sales.date_reg AS DATETIME)'), '>=', $dateStar_static],
        [DB::raw('CAST(islim_sales.date_reg AS DATETIME)'), '<=', $filters['dateEnd']],
      ])->get();

      // Log::info("ALTAS: " . $info);

      /*RECARGAS*/
      if (!empty($info)) {

        /*
        DB::raw('ROW_NUMBER() OVER(Partition by islim_sales.msisdn ORDER BY islim_sales.msisdn) AS rownum_sales'),

        https://stackoverflow.com/questions/1895110/row-number-in-mysql

        //Alternativa a ROW_NUMBER

        DB::raw('@row_num := IF(@prev_value=islim_sales.msisdn,@row_num+1,1) AS rownum_sales'),
        DB::raw('@prev_value := islim_sales.msisdn'),
         */

        DB::statement(DB::raw('SET @row_num_ini := 0'));
        //DB::statement(DB::raw('SET @row_num := 1'));
        //DB::statement(DB::raw('SET @prev_value := ""'));
        $infoR = self::getConnect('R')
          ->select(
            'islim_sales.msisdn',
            DB::raw('CONCAT(islim_clients.name, " ", islim_clients.last_name) AS nameClient'),
            'islim_clients.email AS mailClient',
            DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) AS nameVendedor'),
            'islim_sales.users_email AS mailvendedor',
            'islim_sales.amount',
            DB::raw('CONCAT(islim_services.title, " - ", islim_services.description) AS servicio'),
            DB::raw('@row_num_ini AS rownum_sales'),
            'islim_sales.date_reg AS date_reg',
            'islim_sales.id',
            'islim_sales.type',
            'islim_sales.id_point',
            DB::raw("(CASE WHEN (islim_sales.type = 'P') THEN 1 ELSE 0 END) AS is_alta")
          )
          ->join(
            'islim_services',
            function ($join) {
              $join->on('islim_services.id', '=', 'islim_sales.services_id');
            }
          )
          ->join(
            'islim_client_netweys',
            'islim_client_netweys.msisdn',
            '=',
            'islim_sales.msisdn'
          )
          ->join(
            'islim_clients',
            'islim_clients.dni',
            '=',
            'islim_client_netweys.clients_dni'
          )
          ->leftJoin(
            'islim_users',
            'islim_users.email',
            '=',
            'islim_sales.users_email'
          )
          ->where([
            ['islim_sales.sale_type', 'T'],
            [DB::raw('CAST(islim_sales.date_reg AS DATETIME)'), '>=', $dateStar_static],
            [DB::raw('CAST(islim_sales.date_reg AS DATETIME)'), '<=', $filters['dateEnd']],
          ])
          ->whereIn('islim_sales.msisdn', $info)
          ->whereIn('islim_sales.type', ['P', 'R']);

        $info = $infoR->orderBy('islim_sales.msisdn', 'DESC')
          ->orderBy('islim_sales.type', 'DESC')
          ->orderBy('islim_sales.date_reg', 'ASC')->get();
        //Log::info("RECARGAS: " . $info);
        if (!empty($info)) {

          if ($filters['dateStar'] < $dateStar_static) {
            $dateStar_static = (date("YmdHis", strtotime('20210517000000')));
            $dateStar_valid  = $dateStar_static;
          } else {
            $dateStar_valid = $filters['dateStar'];
          }
          //$filter = new \stdClass;
          $filter = array(
            'dateStar_valid' => $dateStar_valid,
            'dateEnd'                        => $filters['dateEnd']
          );
          /*print_r($info->toSql()); exit;*/
          $info2 = self::recharge_SuperSim($filter, $info, $dateStar_static);

          return $info2;
        }
      }
      return null;
    }
    return null;
  }
  /*END Super Sim*/

  public static function getTimeRecharge($msisdn = false)
  {
    if ($msisdn) {
      $infoR = self::getConnect('R')
        ->select(DB::raw('DATEDIFF(NOW(),islim_sales.date_reg) as dias_recharge'))
        ->where([
          ['islim_sales.msisdn', $msisdn],
          ['islim_sales.type', 'R'],
          ['islim_sales.status', '!=', 'T']
        ])
        ->orderBy('islim_sales.msisdn', 'DESC')
        ->orderBy('islim_sales.date_reg', 'DESC')
        ->first();
      //Log::info($infoR);
      return $infoR;
    } else {
      return null;
    }
  }

  public static function getDTUpsWithConsumptionsDataReport($filters = [])
  {

    $data = self::getConnect('R')
      ->select(
        'islim_sales.msisdn',
        'islim_sales.date_reg as Fecha_Alta',
        'islim_cdr_data_consumo.date as Fecha_Consumo',
        'islim_cdr_data_consumo.consumo as Consumo'
      )
      ->join('islim_cdr_data_consumo', 'islim_cdr_data_consumo.msisdn', '=', 'islim_sales.msisdn')
      ->whereIn('islim_sales.status', ['A'])
      ->whereIn('islim_sales.sale_type', ['H', 'M', 'MH'])
      ->whereIn('islim_sales.type', ['P']);

    if (is_array($filters)) {
      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data->whereBetween('islim_sales.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data->where('islim_sales.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data->where('islim_sales.date_reg', '<=', $filters['dateEnd']);
      }
    }

    // print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
    // exit;

    $data = $data->orderBy('islim_sales.date_reg', 'ASC')->get();
    return $data;
  }

  public static function getTotalUpsWithConsumptions($filters = [])
  {

    $data = self::getConnect('R')
      ->select(
        'islim_sales.msisdn',
        'islim_sales.date_reg as Fecha_Alta',
        'islim_cdr_data_consumo.date as Fecha_Consumo',
        'islim_cdr_data_consumo.consumo as Consumo'
      )
      ->join('islim_cdr_data_consumo', 'islim_cdr_data_consumo.msisdn', '=', 'islim_sales.msisdn')
      ->whereIn('islim_sales.status', ['A'])
      ->whereIn('islim_sales.sale_type', ['H', 'M', 'MH'])
      ->whereIn('islim_sales.type', ['P']);

    if (is_array($filters)) {
      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data->whereBetween('islim_sales.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data->where('islim_sales.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data->where('islim_sales.date_reg', '<=', $filters['dateEnd']);
      }
    }

    return $data->count();
  }

  public static function getTotalDebtFromSellers($filters = [])
  {
    $data = self::getConnect('R')
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_sales.users_email'
      )
      ->where('islim_sales.status', 'E')
      ->whereIn('islim_users.status', ['A', 'D']);

    if (count($filters)) {
      if (!empty($filters['user'])) {
        $data = $data->where([
          ['islim_users.parent_email', $filters['user']],
          ['islim_sales.users_email', '!=', $filters['user']]
        ]);
      }

      if (!empty($filters['date_end'])) {
        $data = $data->where('islim_sales.date_reg', '<', $filters['date_end']);
      }

      if (!empty($filters['date_begin'])) {
        $data = $data->where('islim_sales.date_reg', '>=', $filters['date_begin']);
      }
    }

    $data = $data->sum('islim_sales.amount');

    return $data;
  }

  public static function getSalesCountByDate($user, $sellers = [], $articles = [], $dateB, $dateE)
  {
    return self::getConnect('R')
      ->select('islim_sales.id')
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_sales.inv_arti_details_id'
      )
      ->whereIn('islim_inv_arti_details.inv_article_id', $articles)
      ->whereIn('islim_sales.status', ['A', 'E'])
      ->where(function ($q) use ($sellers, $user) {
        $q->whereIn('users_email', $sellers)
          ->orWhere('users_email', $user);
      })
      ->where([
        ['islim_sales.type', 'P'],
        ['islim_sales.date_reg', '>=', $dateB],
        ['islim_sales.date_reg', '<=', $dateE],
      ])
      ->count();
  }

  public static function getUpsPeriod($startDate, $endDate, $type = null)
  {
    $altas = self::select(
      'islim_sales.msisdn'
    )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_sales.msisdn'
      )
      ->where([
        ['islim_sales.date_reg', '>=', $startDate],
        ['islim_sales.date_reg', '<=', $endDate],
        ['islim_sales.type', 'P']
      ]);
    if (!empty($type)) {
      $altas = $altas->where([
        ['islim_sales.sale_type', $type]
      ]);
    }
    $altas = $altas->whereIn('islim_sales.status', ['A', 'E'])
      ->whereIn('islim_client_netweys.status', ['A', 'S'])
      ->groupBy('islim_sales.msisdn')
      ->get();

    return $altas;
  }

  /**
   * [getSalesByuser Obtiene las ventas realizadas por un usuario especifico]
   * @param  [type] $email  [description]
   * @param  [type] $status [description]
   * @return [type]         [description]
   */
  public static function getSalesByuser($email, $status)
  {
    return self::getConnect('R')
      ->select('id', 'amount', 'date_reg')
      ->where([
        ['status', $status],
        ['users_email', $email],
        ['amount', '>', 0]
      ]);
  }

  /**
   * Metodo para obtener totales de ventas (Abonos o normales)
   * @param Array $filters
   *
   * @return App\\Models\Sale
   */
  public static function getTotalSalesByType($filters = [])
  {
    $whtI = DB::raw('(SELECT id FROM islim_sales_installments AS i WHERE i.unique_transaction = islim_sales.unique_transaction AND (i.status = "P" OR i.status = "F"))');

    $data = self::getConnect('R')
      ->select(
        DB::raw('COUNT(islim_sales.users_email) AS total_sales'),
        DB::raw('SUM(islim_sales.amount) AS total_mount')
      )
      ->where([
        ['islim_sales.users_email', $filters['user']],
        ['islim_sales.type', 'V']
      ])
      ->whereIn('islim_sales.status', ['A', 'E']);

    if (!empty($filters['type'])) {
      $data = $data->where('islim_sales.sale_type', $filters['type']);
    }

    if (!empty($filters['dateB'])) {
      $data = $data->where('islim_sales.date_reg', '>=', $filters['dateB']);
    }

    if (!empty($filters['dateE'])) {
      $data = $data->where('islim_sales.date_reg', '<=', $filters['dateE']);
    }

    if ($filters['whtI']) {
      $data = $data->whereNull($whtI);
    } else {
      $data = $data->whereNotNull($whtI);
    }

    return $data->groupBy('islim_sales.users_email')
      ->first();
  }
}
