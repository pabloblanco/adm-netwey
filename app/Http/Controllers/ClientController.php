<?php

namespace App\Http\Controllers;

use App\CDRDataConsDetails;
use App\Client;
use App\ClientNetwey;
use App\ClientsUpdateCall;
use App\CompensationBonus;
use App\ConsumoAcumuladoDetails;
use App\ConsumoAcumuladoTotal;
use App\CoordinateChanges;
use App\Deactive;
use App\FiberInstallation;
use App\HbbMobility;
use App\Helpers\APIAltan;
use App\Helpers\APIClient;
use App\Helpers\ValidateString;
use App\Incidencia;
use App\InfoDevice;
use App\Inventory;
use App\Mail\RetentionMail;
use App\Migrations;
use App\mobility;
use App\PackPrices;
use App\PreDesactivate;
use App\Promontion;
use App\Reports;
use App\RetentionActivates;
use App\RetentionReasons;
use App\Sale;
use App\SalesBlim;
use App\SellerInventory;
use App\Service;
use App\SimSwap;
use App\Suspend;
use App\SuspendedByAdmin;
use App\TheftOrLoss;
use App\User;
use Carbon\Carbon;
use DataTables;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
  public function getClientProfile($msisdn = false)
  {
    if ($msisdn) {
      $data = APIClient::getClient($msisdn);

      //Marca si el dn esta en prediactive
      $isPre = PreDesactivate::select('status')
        ->where([
          ['msisdn', $msisdn],
        ])
        ->orderBy('date_reg', 'DESC')
        ->first();

      if (!empty($isPre) && $isPre->status == 'P') {
        return response()->json([
          'status' => 'success',
          'msisdn' => [
            'isPreDesactivate' => true,
            'period_serv_days' => 7],
        ]);
      }

      if (!empty($data) && is_object($data) && $data->success) {
        $data->data->isPreDesactivate = false;

        $data->data->activeBuyChangeCoord = empty(Sale::payChangeCoord($msisdn));
        $period_serv_days                 = 7;

        if (!empty($data->data->service_id)) {
          $periodicity = Service::getPeriodicity($data->data->service_id);
          if ($periodicity) {
            $period_serv_days = $periodicity->days;
          }
        }

        $data->data->period_serv_days = $period_serv_days;

        return response()->json([
          'status' => 'success',
          'msisdn' => $data->data,
        ]);
      }

      return response()->json([
        'status'  => 'error',
        'msisdn'  => (!empty($data) && is_object($data)) ? $data->data : $data,
        'message' => (!empty($data) && is_object($data)) ? $data->data->msg : 'Error consultando profile',
      ]);
    }
  }

  public function barring($msisdn = false)
  {
    if (User::hasPermission(session('user')->email, 'CLA-SPS')) {
      if ($msisdn) {
        return APIAltan::doRequest('barring', $msisdn);
      }
    } else {
      return response()->json(array('status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación', 'numError' => 002));
    }
  }

  public function unbarring($msisdn = false)
  {
    if (User::hasPermission(session('user')->email, 'CLA-SPS')) {
      if ($msisdn) {
        return APIAltan::doRequest('unbarring', $msisdn);
      }
    } else {
      return response()->json(array('status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación', 'numError' => 002));
    }
  }

  public function reduceDeactivate($msisdn = false)
  {
    if ($msisdn) {
      $res = APIAltan::removeSuplementary($msisdn, env('NAV_NOCTURNA'));

      if (!$res['error']) {
        return response()->json([
          'status'  => 'success',
          'message' => 'Servicio de reducción desactivado exitosamente.',
        ]);
      }

      return response()->json([
        'status'  => 'error',
        'message' => 'No se pudo quitar el servicio de reducción.',
      ]);
    }
  }

  /*DEPRECATED Consulta el profile de un Dn dado, por medio de la clase "APIAltan" que se conecta a la api de altan*/
  protected function profile($msisdn)
  {
    if (isset($msisdn)) {
      $profile = APIAltan::doRequest('profile', $msisdn);
      $profile = json_decode($profile);

      /*Si no se pudo obtener un JSON de la respuesta de altan retorna un error*/
      if (!is_object($profile)) {
        return response()->json(['status' => 'error', 'message' => 'Ocurrio un error consultando el profile']);
      }

      /*Cosulta si el DN esta en prediactive*/
      $isPre = PreDesactivate::select('status')
        ->where([
          ['msisdn', $msisdn],
        ])
        ->orderBy('date_reg', 'DESC')
        ->first();

      //Marca si el dn esta en prediactive
      if (!empty($isPre) && $isPre->status == 'P') {
        $profile->isPreDesactivate = true;
      } else {
        $profile->isPreDesactivate = false;
      }

      //Consulta imei del arcitulo
      $art = Inventory::select('imei')->where('msisdn', $msisdn)->first();
      if (!empty($art)) {
        $profile->imei = $art->imei;
      }

      /*Si el estatus es suspend y esta en la tabla suspends se muestra el mensaje suspendido por inactividad (Suspenciones en base a churn90)*/
      if (strtolower($profile->msisdn->status) == 'suspend') {
        $isMov = mobility::where([['msisdn', $msisdn], ['status', 'A']])
          ->first();

        $isReport = TheftOrLoss::isReport($msisdn);

        if (!empty($isReport)) {
          $profile->msisdn->status = 'suspendTOL';
        } elseif (!empty($isMov)) {
          $profile->msisdn->status = 'suspendMov';
        } else {
          $isInactive = Suspend::where('msisdn', $msisdn)->first();

          if (!empty($isInactive)) {
            $profile->msisdn->status = 'suspendInactive';
          }
        }
      }

      $profile->activeBuyChangeCoord = empty(Sale::payChangeCoord($msisdn));

      return response()->json($profile);
    }
  }

  /*Suspendo un dn si el usuario tiene el permiso para hacerlo*/
  protected function suspend($msisdn)
  {
    if (User::hasPermission(session('user')->email, 'CLA-ASL')) {
      if (isset($msisdn)) {
        return APIAltan::doRequest('suspend', $msisdn);
      }
    } else {
      return response()->json(array('status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación', 'numError' => 002));
    }
  }

  /*Suspendiendo por robo o extravío*/
  public function suspendTheftorLost(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax() && !empty($request->msisdn)) {
      if (!User::hasPermission(session('user')->email, 'CLA-ASL')) {
        return response()->json(array('status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación', 'numError' => 002));
      }

      $msisdn = $request->msisdn;

      $profile = APIAltan::doRequest('profile', $msisdn);
      $profile = json_decode($profile);

      /*Si no se pudo obtener un JSON de la respuesta de altan retorna un error*/
      if (is_object($profile) && $profile->status == 'success') {
        if (strtolower($profile->msisdn->status) != 'suspend') {
          $res = APIAltan::doRequest('suspend', $msisdn);
          $res = json_decode($res);

          if (!is_object($res) || $res->status != 'success') {
            return response()->json(array('status' => 'error', 'message' => 'No se pudo suspender el DN'));
          }
        }

        TheftOrLoss::getConnect('W')->insert([
          'msisdn'   => $msisdn,
          'date_reg' => date('Y-m-d H:i:s'),
          'user'     => session('user')->email,
          'status'   => 'A',
        ]);

        return response()->json(array('status' => 'success'));
      }
    }

    return response()->json(array('status' => 'error', 'message' => 'No se pudo suspender el DN'));
  }

  /*Activa un dn si el usuario tiene el permiso para hacerlo*/
  protected function activate($msisdn)
  {
    if (User::hasPermission(session('user')->email, 'CLA-ASL')) {
      if (isset($msisdn)) {
        $response = APIAltan::doRequest('activate', $msisdn);

        //$obj = json_decode($response);

        //if (!empty($obj) && $obj->status == 'success') {
        ClientNetwey::where('msisdn', $msisdn)->update(['status' => 'A']);
        Suspend::where([['msisdn', $msisdn], ['status', 'A']])->update(['status' => 'T']);
        TheftOrLoss::resume($msisdn, session('user')->email);
        //}

        return $response;
      }
    } else {
      return response()->json(array('status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación', 'numError' => 002));
    }
  }

  /*Coloca en prediactive un dn si el usuario tiene el permiso para hacerlo*/
  protected function preDesactivate($msisdn)
  {
    if (!empty($msisdn)) {
      if (User::hasPermission(session('user')->email, 'CLA-PDL')) {
        $pre = APIAltan::doRequest('preDesactivate', $msisdn);
        $pre = json_decode($pre);

        if (is_object($pre) && $pre->status == 'success') {
          PreDesactivate::saveStatus($msisdn, 'P', $pre->orderId);
          return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Ocurrio un error pre-desactivando la linea']);
      }
      return response()->json(['status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación']);
    }
  }

  /*Reactiva un dn si el usuairo tiene el permiso para hacerlo*/
  protected function reactivate($msisdn)
  {
    if (!empty($msisdn)) {
      if (User::hasPermission(session('user')->email, 'CLA-PDL')) {
        $react = APIAltan::doRequest('reactivate', $msisdn);
        $react = json_decode($react);

        if (!is_object($react) && $react->status == 'success') {
          PreDesactivate::saveStatus($msisdn, 'R', $react->orderId);
          return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error', 'message' => 'Ocurrio un error re-activando la linea']);
      }
      return response()->json(['status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación']);
    }
  }

  /*Optiene los datos de un cliente, su venta, y financiamiento en caso de ser a credito la compra -> la info se muestra en el tab detalles*/
  protected function getclients($msisdns = null, $dnis = null)
  {
    $clients = ClientNetwey::select(
      'islim_client_netweys.msisdn',
      'islim_client_netweys.clients_dni',
      'islim_client_netweys.service_id',
      'islim_client_netweys.address',
      'islim_client_netweys.type_buy',
      'islim_client_netweys.periodicity',
      'islim_client_netweys.num_dues',
      'islim_client_netweys.paid_fees',
      'islim_client_netweys.unique_transaction',
      'islim_client_netweys.serviceability',
      'islim_client_netweys.lat',
      'islim_client_netweys.lng',
      'islim_client_netweys.date_buy',
      'islim_client_netweys.price_remaining',
      'islim_client_netweys.date_reg',
      'islim_client_netweys.date_expire',
      'islim_client_netweys.status',
      'islim_client_netweys.obs',
      'islim_client_netweys.n_update_coord',
      'islim_client_netweys.n_sim_swap',
      'islim_client_netweys.total_debt',
      'islim_client_netweys.credit',
      'islim_client_netweys.dn_type',
      'islim_inv_arti_details.imei',
      'islim_inv_arti_details.inv_article_id',
      'islim_inv_articles.title as art_title',
      'islim_fiber_zone.name as zone_name'
    )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.msisdn',
        'islim_client_netweys.msisdn'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_arti_details.inv_article_id',
        'islim_inv_articles.id'
      )
      ->leftJoin(
        'islim_fiber_zone',
        'islim_fiber_zone.id',
        'islim_client_netweys.id_fiber_zone'
      )
      ->leftJoin('islim_deactive', function ($join) {
        $join->on('islim_deactive.msisdn', '=', 'islim_client_netweys.msisdn')
          ->where([
            ['islim_deactive.status', 'A'],
            ['islim_client_netweys.status', 'T']]);
      });

    /*Si no es un usuario admin se filtra los usuario que se retorna, solo puede ver los que fueron registrados o fueron asignados a el*/
    if (!session('user.platform') == 'admin') {
      $users_email   = User::where('parent_email', session('user.email'))->get(['email'])->pluck('email');
      $users_email[] = session('user.email');
      $client        = Client::whereIn('reg_email', $users_email)
        ->orWhereIn('user_mail', $users_email)
        ->get(['dni'])
        ->pluck('dni');
      $clients = $clients->whereIn('clients_dni', $client);
    }

    /*Filtra por lo(s) dn(s) enviados*/
    if (!empty($msisdns) && is_array($msisdns)) {
      $clients = $clients->whereIn('islim_client_netweys.msisdn', $msisdns);
    }

    /*Filtra por dni*/
    if (!empty($dnis) && is_array($dnis)) {
      $clients = $clients->whereIn('clients_dni', $dnis);
    }

    $clients = $clients->get();

    $user_can_sf = User::hasPermission(session('user')->email, 'CRE-CLI');

    foreach ($clients as $clinet) {
      /*Obtiene el alta del cliente*/
      $alta = Sale::select('services_id', 'packs_id', 'typePayment')
        ->where([
          ['type', 'P'],
          ['msisdn', $clinet->msisdn],
        ])
        ->whereIn('status', ['A', 'E', 'I'])
        ->first();

      /*Si el usuario no tiene permismo para ver financiamiento borra el campo*/
      if (!$user_can_sf) {
        $clinet->type_buy = null;
      }

      // $clinet->plan   = Service::find($clinet->service_id)->title;
      $clinet->client = Client::find($clinet->clients_dni);
      $infoPlan       = Service::getDetailService($clinet->service_id);
      if (!empty($infoPlan)) {
        $clinet->plan  = $infoPlan->title;
        $clinet->speed = $infoPlan->description;
      }

      //Guardando empresa de financiamiento
      if (!empty($alta)) {

        $clinet->typePayment = $alta->typePayment;

        /*Si el alta existe y es una compra a credito se consulta el financiamiento*/
        if ($clinet->type_buy == 'CR') {

          $clinet->financing = PackPrices::select(
            'islim_financing.name',
            'islim_financing.total_amount',
            'islim_financing.amount_financing',
            'islim_financing.SEMANAL',
            'islim_financing.QUINCENAL',
            'islim_financing.MENSUAL'
          )
            ->leftJoin(
              'islim_financing',
              'islim_financing.id',
              'islim_pack_prices.id_financing'
            )
            ->where([
              ['islim_pack_prices.pack_id', $alta->packs_id],
              ['islim_pack_prices.service_id', $alta->services_id],
              ['islim_pack_prices.type', 'CR'],
            ])
            ->first();

        }

        /*
        Obtenemos los datos del plan activo del cliente
        */
        if ($clinet->dn_type == 'F') {
          $infoPlan = Service::getDetailService($alta->services_id);
          if (!empty($infoPlan)) {

            $clinet->service_sell_description  = $infoPlan->title;
            $clinet->service_sell_speed = $infoPlan->description;

          }
        }
      }

      if ($clinet->dn_type == 'H') {

        //Verifico si el DN es migrado o no
        if (Migrations::isMigrations($clinet->msisdn)) {
          $clinet->is_migrated = 'Sí';
        } else {
          $clinet->is_migrated = 'NO';
        }

        $inc = Incidencia::isIncidencia($clinet->msisdn);
        if (!empty($inc)) {
          // Log::info($inc);
          if ($inc->status == 'A') {
            $clinet->status_issue = "Con incidencia";
          } else {
            if ($inc->status == 'F') {
              $clinet->status_issue = "Posible incidencia";
            } else {
              $clinet->status_issue = 'OK';
            }
          }
          $clinet->date_issue = $inc->date_incident;
        } else {
          $clinet->status_issue = 'OK';
        }
        //Log::info('clinet: ' . $clinet);
      }

      $drecharger = Sale::getTimeRecharge($clinet->msisdn);

      if (!empty($drecharger) && !empty($drecharger->dias_recharge)) {
        $clinet->timeRecharge = $drecharger->dias_recharge;
      } else {
        $clinet->timeRecharge = 0;
      }

      //$clinet->timeRecharge = 59;
      //Log::info($clinet->timeRecharge);

      /*Consultando datos del vendedor*/
      $clinet->seller = Sale::select(
        'islim_sales.users_email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_dts_organizations.business_name'
      )
        ->join('islim_users', 'islim_users.email', '=', 'islim_sales.users_email')
        ->leftJoin('islim_dts_organizations', 'islim_dts_organizations.id', '=', 'islim_users.id_org')
        ->where('islim_sales.unique_transaction', $clinet->unique_transaction)
        ->first();

      if (!empty($clinet->client->address)) {
        $clinet->client->address = str_replace('"', ' ', str_replace("'", ' ', $clinet->client->address));
      }

      if (!empty($clinet->client->address_store)) {
        $clinet->client->address_store = str_replace('"', ' ', str_replace("'", ' ', $clinet->client->address_store));
      }

      if (!empty($clinet->client->name)) {
        $clinet->client->name = str_replace('"', ' ', str_replace("'", ' ', $clinet->client->name));
      }

      if (!empty($clinet->client->last_name)) {
        $clinet->client->last_name = str_replace('"', ' ', str_replace("'", ' ', $clinet->client->last_name));
      }

      if (!empty($clinet->client->note)) {
        $clinet->client->note = str_replace('"', ' ', str_replace("'", ' ', $clinet->client->note));
      }

      //Busca el imei y detalles del equipo
      if ($clinet->dn_type == 'T') {

        $dataEquipo = InfoDevice::getModel($clinet->msisdn);
        if (!empty($dataEquipo)) {
          $clinet->imei  = $dataEquipo->imei;
          $clinet->model = $dataEquipo->model;
          $clinet->brand = $dataEquipo->brand;
        }
      } else {
        //Mifi, Hbb, Fibra
        $model          = Inventory::getModelo($clinet->msisdn);
        $clinet->equipo = !empty($model) ? !empty($model->title) ? $model->title : 'S/N' : 'S/N';
        $clinet->brand  = !empty($model) ? !empty($model->brand) ? $model->brand : 'S/N' : 'S/N';
        $clinet->model  = !empty($model) ? !empty($model->model) ? $model->model : 'S/N' : 'S/N';
      }

      $clinet->detc_lat = $clinet->detc_lon = null;

      if ($clinet->dn_type != 'F') {

        //   $clinet->serviceability = 'error data profile';

        /*mobility detec*/
        $mobilityDetect = DB::table('islim_hbb_mobility_detections')
          ->select('detc_lat', 'detc_lon')
          ->where([['msisdn', $clinet->msisdn]])
          ->orderBy('date_reg', 'desc')
          ->first();
        if ($mobilityDetect) {
          $clinet->detc_lat = $mobilityDetect->detc_lat;
          $clinet->detc_lon = $mobilityDetect->detc_lon;
        }
      } else {
        //$clinet->equipo         = $clinet->art_title;
        $clinet->serviceability = "";

        $clinet->total_suspend = DB::connection('netwey-r')->table('islim_history_fiber_suspend')->where('msisdn', $clinet->msisdn)->where('enum', 'ALERT')->count();
        
        //Obtengo las coordenadas de las instalaciones.
        $coordInstall = FiberInstallation::getCoordInstalation($clinet->msisdn);
        if (!empty($coordInstall)) {
          $clinet->lat = $coordInstall->lat;
          $clinet->lng = $coordInstall->lng;
        } else {
          $clinet->lat = "S/I";
          $clinet->lng = "S/I";
        }

      }

      if ($clinet->status == 'A') {
        //Trato los casos en los que en clientes son status A pero tiene prefijo, en este caso en realidad no son DN activos, estan activos por temas de pagos de comisiones, por tanto se consideran aca como DN eliminados. Los DN dados de baja por inactividad en teoria ya se encuentran en status T en clientes
        $search1 = "_PR"; //Portabilidad reverso
        $search2 = "_PI"; //Reciclaje
        $search3 = "_P"; //Portabilidad (exportacion)

        $cadena        = $clinet->msisdn;
        $coincidencia1 = strpos($cadena, $search1);
        $coincidencia2 = strpos($cadena, $search2);
        $coincidencia3 = strpos($cadena, $search3);

        if ($coincidencia1 !== false || $coincidencia2 !== false || $coincidencia3 !== false) {
          $clinet->status = 'T';
        }
      }
    }
    return $clients;
  }

  public function getClientdt(Request $request)
  {
    $clients = $this->getclients($request->msisdns, $request->dnis);

    $user_can_edit = User::hasPermission(session('user')->email, 'CLA-ECL');

    return DataTables::of($clients)
      ->editColumn('name', '{{$client->name}} {{$client->last_name}}')
      ->editColumn('address', function ($client) {
        if (!empty($client->address)) {
          return str_replace('"', ' ', str_replace("'", ' ', $client->address));
        }

        return $client->address;
      })
      ->editColumn('email', '{{$client->email}}')
      ->editColumn('phone_home', '{{$client->phone_home}}')
      ->editColumn('phone_2', function ($client) {
        return !empty($client->client->phone) ? $client->client->phone : 'N/A';
      })
      ->editColumn('msisdn', '{{$msisdn}}')
      ->editColumn('dn_type_l', function ($client) {
        switch ($client->dn_type) {
          case 'T':return "Telefonía";break;
          case 'M':return "MIFI Nacional";break;
          case 'MH':return "MIFI Huella ALTAN";break;
          case 'F':return "Fibra";break;
          default:return "Internet Hogar";break;
        }
      })
      ->editColumn('serviceability', '{{!empty($serviceability)?$serviceability:"N/A"}}')
      ->editColumn('plan', '{{$plan}}')
      ->editColumn('lat', function ($client) {
        return (!empty($client->lat)) ? $client->lat : 'N/A';
      })
      ->editColumn('lng', function ($client) {
        return (!empty($client->lng)) ? $client->lng : 'N/A';
      })
      ->editColumn('imei', '{{$imei}}')
      ->editColumn('status', function (ClientNetwey $client) {
        switch ($client->status) {
          case 'A':
            return "Activo";
          case 'T':
            return "Dado de baja";
          case 'I':
            return "Inactivo";
          case 'S':
            return "Suspendido";
        }
      })
      ->addColumn('canEdit', function () use ($user_can_edit) {
        return $user_can_edit;
      })
      ->make(true);
  }

  public function clientdt($msisdns)
  {
    $clients = $this->getclients(json_decode($msisdns));

    return DataTables::of($clients)
      ->editColumn('name', '{{$client->name}} {{$client->last_name}}')
      ->editColumn('email', '{{$client->email}}')
      ->editColumn('phone_home', '{{$client->phone_home}}')
      ->editColumn('phone_2', function ($client) {
        return !empty($client->client->phone) ? $client->client->phone : 'N/A';
      })
      ->editColumn('msisdn', '{{$msisdn}}')
      ->editColumn('serviceability', '{{$serviceability}}')
      ->editColumn('plan', '{{$plan}}')
      ->editColumn('lat', '{{$lat}}')
      ->editColumn('lng', '{{$lng}}')
      ->editColumn('imei', function (ClientNetwey $client) {
        return Inventory::where('msisdn', $client->msisdn)->first()->imei;
      })
      ->editColumn('status', function (ClientNetwey $client) {
        switch ($client->status) {
          case 'A':
            return "Activo";
          case 'T':
            return "Eliminado";
          case 'I':
            return "Inactivo";
          case 'S':
            return "Suspendido";
        }
      })
      ->addColumn('canEdit', function () {
        return User::hasPermission(session('user')->email, 'CLA-ECL');
      })
      ->toJson();
  }

  /*Vista de consulta clientes*/
  public function view()
  {
    $reasons = RetentionReasons::select('reason')->where('status', 'A')
      ->distinct()->get();

    $subreasons = RetentionReasons::select('id', 'reason', 'sub_reason')->where('status', 'A')
      ->get();

    $html = view('pages.ajax.client.client', compact('reasons', 'subreasons'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Devuelve los dns para el input auto-completado de la vista de clientes*/
  public function getClientsForInput(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q)) {

        $clients = ClientNetwey::getConnect('R')
          ->select('islim_client_netweys.msisdn')
          ->where([
            ['islim_client_netweys.status', '!=', 'T'],
            ['islim_client_netweys.msisdn', 'like', $request->q . '%'],
          ]);
        //->limit(10)->get();
        //
        /*->leftJoin('islim_deactive', function ($join) {
        $join->on('islim_deactive.msisdn', '=', 'islim_client_netweys.msisdn')
        ->where([
        ['islim_deactive.status', 'A'],
        ['islim_client_netweys.status', 'T']]);
        })*/

        $clientsDeactive = Deactive::getConnect('R')
          ->select('islim_deactive.msisdn')
          ->join('islim_client_netweys',
            'islim_client_netweys.msisdn',
            'islim_deactive.msisdn')
          ->where([
            ['islim_deactive.status', 'A'],
            ['islim_client_netweys.status', 'T'],
            ['islim_deactive.msisdn', 'like', $request->q . '%'],
          ]);

        if (!empty($clients)) {
          $clients = $clients->union($clientsDeactive);
        } else {
          $clients = $clientsDeactive;
        }

        $clients = $clients->limit(10)->get();

        return response()->json(array('success' => true, 'clients' => $clients));
      }
    }

    return response()->json(array('success' => false));
  }

  /*Devuelve los nombre para el input auto-completado de nombre de la vista de clientes*/
  public function getClientsByName(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q)) {
        $find = $request->q;
        $name = DB::raw("(CONCAT(name, CONCAT(' ',last_name))) as full_name");

        $clients = Client::select($name, 'dni')
          ->where(function ($query) use ($find) {
            $query->where('name', 'like', $find . '%')
              ->orWhere('last_name', 'like', $find . '%');
          })
          ->join(
            'islim_client_netweys',
            'islim_client_netweys.clients_dni',
            'islim_clients.dni'
          )
          ->limit(10)
          ->get();

        return response()->json(array('success' => true, 'clients' => $clients));
      }
    }

    return response()->json(array('success' => false));
  }

  /*Vista que retorna listado de clientes*/
  public function viewClients(Request $request)
  {
    $msisdns = array();
    $dnis    = array();
    if ($request->hasFile('msisdn_file')) {
      $fileFlag = true;
      if ($request->file('msisdn_file')->isValid()) {
        $file = $request->file('msisdn_file');

        $path      = base_path('uploads');
        $file_name = $file->getClientOriginalName();
        if (!file_exists($path)) {
          mkdir($path, 0777, true);
        }
        $file->move($path, $file_name);

        ini_set('auto_detect_line_endings', true);

        if (($gestor = fopen($path . '/' . $file_name, "r")) !== false) {
          while (($datos = fgetcsv($gestor, 1000, ",")) !== false) {
            foreach ($datos as $item) {
              $msisdns[] = $item;
            }
          }
          fclose($gestor);
        } else {
        }
        ini_set('auto_detect_line_endings', false);

        unlink($path . '/' . $file_name);
      } else {
        $errors[] = 'El archivo no puede ser validado';
      }
    } elseif ($request->has('msisdn_select')) {
      $msisdns = explode(",", (String) $request->msisdn_select);
    } elseif ($request->has('name_select')) {
      $dnis = explode(",", $request->name_select);
    } else {
      return response()->json(array('success' => false));
    }
    /*else{
    //$msisdns = ClientNetwey::where('status','!=','T')->get(['msisdn'])->pluck('msisdn');
    $msisdns = 'ALL_DNS';
    }*/

    $html = view('pages.ajax.client.clientdt', compact('msisdns', 'dnis'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna la tabla de recargas hechas por el cliente seleccionado*/
  public function getviewtables($msisdn)
  {
    $dtclient = ClientNetwey::select(
      'msisdn', 'clients_dni', 'service_id', 'address', 'type_buy', 'periodicity', 'num_dues', 'paid_fees', 'unique_transaction', 'serviceability', 'lat', 'lng', 'date_buy', 'price_remaining', 'date_reg', 'date_expire', 'status', 'obs', 'n_update_coord', 'n_sim_swap', 'total_debt', 'credit', 'dn_type'
      )->where('msisdn', $msisdn)->first();
    $report = $this->getrecharges($msisdn);
    $html   = view('pages.ajax.client.datatable', compact('report', 'msisdn', 'dtclient'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna la tabla de recargas disponibles para el DN seleccionado*/
  public function getviewtablesRecDisp($msisdn)
  {
    $html = view('pages.ajax.client.datatable_recdisp', compact('msisdn'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna tabla de consumos hechos por el DN seleccionado*/
  public function getviewtablesComp($msisdn)
  {
    $dateb = Carbon::now()->subMonth()->format('Y-m-d');
    //$report = CDRDataConsumo::getConsumptionDN($msisdn, ['dateB' => $dateb]);
    //$report2 = ConsumoAcumuladoTotal::getConsumptionDN($msisdn, ['dateB' => $dateb]);
    //$report = $report->union($report2)->get();
    //EstadoConsumo::getConsumptionDN($msisdn, ['dateB' => $dateb]);

    $report = ConsumoAcumuladoTotal::getConsumptionDN($msisdn, ['dateB' => $dateb])->get();
    $html   = view('pages.ajax.client.datatable_comp', compact('report', 'msisdn'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna tabla de servicios blim comprados por el DN seleccionado*/
  public function getviewtablesBlim($msisdn)
  {
    // $dateb =  Carbon::now()->subYear()->format('Y-m-d');
    // $report = SalesBlim::getBlimServicesDN($msisdn, ['dateB' => $dateb]);
    $html = view('pages.ajax.client.datatable_blim', compact('msisdn'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna tabla de servicios de retencion activos para el DN seleccionado*/
  public function getviewtablesretentions($msisdn)
  {
    $html = view('pages.ajax.client.datatable_retentions', compact('msisdn'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna detalle de tabla de consumos hechos por el cliente seleccionado y fecha dada*/
  public function getviewCompDetails(Request $request)
  {
    //$report = CDRDataView::getConsumptionDN($msisdn, ['dateB' => $dateb]);
    //$html = view('pages.ajax.client.datatable_comp', compact('report','msisdn'))->render();

    $date         = Carbon::createFromFormat('Y-m-d', $request->date)->toDateString();
    $sttdate      = strtotime($date);
    $sttdatelimit = strtotime("2021-07-16 00:00:00");

    if ($sttdate < $sttdatelimit) {
      $report = CDRDataConsDetails::getConsumptionDNDetails($request->msisdn, $date);

      $html = '

            <table id="" class="table table-striped dataTable no-footer my-0 p-0 comps-detail" role="grid" aria-describedby="" style=" margin-left:5.915% !important; width: CALC(100% - 5.915%) !important;">
                <thead>
                    <tr role="row" style="background:#FFF">
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Rango de Tiempo
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Consumo
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Tipo de Consumo
                        </th>
                    </tr>
                </thead>
                <tbody>
            ';
    } else {
      $report = ConsumoAcumuladoDetails::getConsumptionDNDetails($request->msisdn, $date);

      $html = '

            <table id="" class="table table-striped dataTable no-footer my-0 p-0 comps-detail" role="grid" aria-describedby="" style=" margin-left:5.915% !important; width: CALC(100% - 5.915%) !important;">
                <thead>
                    <tr role="row" style="background:#FFF">
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Rango de Tiempo
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Consumo
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Tipo de Consumo
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Servicio
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Activación
                        </th>
                        <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                            Corte
                        </th>
                    </tr>
                </thead>
                <tbody>
            ';
    }
    foreach ($report as $key => $item) {

      $date_act = 'S/I';
      $date_exp = 'S/I';
      $consumo  = (round(((($item->consuption / 1024) / 1024) / 1024), 2) == 0) ? round(((($item->consuption / 1024) / 1024)), 2) . " MB" : round(((($item->consuption / 1024) / 1024) / 1024), 2) . " GB";
      if (!empty($item->date_activation)) {
        $date_act = Carbon::createFromFormat('Y-m-d', $item->date_activation)->format('d-m-Y');
      }
      if (!empty($item->date_expired)) {
        $date_exp = Carbon::createFromFormat('Y-m-d', $item->date_expired)->format('d-m-Y');
      }

      if (!empty($item->consuption_type)) {
        switch ($item->consuption_type) {
          case 'N':$consuption_type = "Normal";
            break;
          case 'T':$consuption_type = "Throttling";
            break;

            // case '0':
            //     $consuption_type = "Consumo Normal";
            //     break;
            // case '10':
            //     $consuption_type = "Whatsapp";
            //     break;
            // case '13':
            //     $consuption_type = "Youtube";
            //     break;
            // case '14':
            //     $consuption_type = "Twitter + Facebook";
            //     break;
            // case '15':
            //     $consuption_type = "Snapchat + Instagram";
            //     break;
            // case '16':
            //     $consuption_type = "Netflix";
            //     break;
            // case '17':
            //     $consuption_type = "Uber";
            //     break;
            // case '19':
            //     $consuption_type = "Spotify";
            //     break;
            // case '20':
            //     $consuption_type = "Amazon Prime";
            //     break;
            // case '990':
            //     $consuption_type = "Servicialidad";
            //     break;
            //     /*case '998': $consuption_type="DNS"; break;
            // case '999': $consuption_type="DNS y Portal Cautivo"; break;*/
            // case '998':
            //     $consuption_type = "Consumo Normal";
            //     break;
            // case '999':
            //     $consuption_type = "Consumo Normal";
            //     break;
            // default:
            //     $consuption_type = $item->consuption_type;
            //     break;
        }
      } else {
        $consuption_type = "Normal";
      }

      if ($sttdate < $sttdatelimit) {
        $html .= '<tr role="row" class="odd">
                            <td>' . $item->time_transaction_start . ' - ' . $item->time_transaction_end . '</td>
                            <td>' . $consumo . '</td>
                            <td>' . $consuption_type . '</td>
                        </tr>';
      } else {
        $html .= '<tr role="row" class="odd">
                        <td>' . $item->time_transaction_start . ' - ' . $item->time_transaction_end . '</td>
                        <td>' . $consumo . '</td>
                        <td>' . $consuption_type . '</td>
                        <td>' . $item->service . '</td>
                        <td>' . $date_act . '</td>
                        <td>' . $date_exp . '</td>
                    </tr>';
      }
    }

    $html .= '</tbody></table>';

    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna la tabla historica de compensaciones hechas a un dn*/
  public function getviewtablesCompensations($msisdn)
  {
    $report = $this->getCompensationsHistory($msisdn);
    $html   = view('pages.ajax.client.datatable_compensations', compact('report', 'msisdn'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna las recargas hechas por el dn dado*/
  protected function getrecharges($msisdn)
  {
    $date_end = date('Y-m-d');
    $date_ini = date("Y-m-d", strtotime("$date_end - 2 month"));

    /*Esta consulta se puede optimisar*/
    $sales = Sale::getSaleReport(
      'recharges',
      null,
      null,
      null,
      null,
      ['E', 'A'],
      null,
      null,
      $msisdn
    )['sales'];
    return $sales;
  }

  /*Data table de recargas*/
  public function rechargesdt($msisdn)
  {
    $sales = $this->getrecharges($msisdn);
    $cc    = env('OFFERT_COORD');
    $cc    = explode(',', $cc);
    return DataTables::of($sales)
      ->editColumn('unique_transaction', '{{$unique_transaction}}')
      ->editColumn('date_reg', '{{$date_reg}}')
      ->editColumn('user_name', '{{$user_name}}')
      ->editColumn('service', function ($sale) use ($cc) {
        if (in_array($sale->codeAltan, $cc)) {
          if (strtotime($sale->date_reg) >= strtotime('2020-02-05')) {
            $d = CoordinateChanges::select('id')
              ->where([
                ['dn', $sale->msisdn],
                ['sale_id', $sale->id],
              ])
              ->first();

            if (empty($d)) {
              return $sale->service . ' (No Usado)';
            }
          }

          return $sale->service . ' (Usado)';
        }

        return $sale->service;
      })
      ->editColumn('client', '{{$client_name}} {{$client_lname}}')
      ->editColumn('msisdn', '{{$msisdn}}')
      ->editColumn('client_phone', '{{$client_phone}}')
      ->editColumn('amount', '{{$amount}}')
      ->editColumn('concentrator', '{{$concentrator}}')
      ->editColumn('conciliation', '{{$conciliation}}')
      ->toJson();
  }

  /*Data table de compensaciones*/
  public function compensationsdt($msisdn)
  {
    $compensations = $this->getCompensationsHistory($msisdn);

    return DataTables::of($compensations)
      ->editColumn('date_bonus', function ($compensation) {
        return date('Y-m-d', strtotime($compensation->date_bonus));
      })
      ->editColumn('incident_date', function ($compensation) {
        return date('Y-m-d H:i:s', strtotime($compensation->incident_date . "0000"));
      })
      ->toJson();
  }

  /*Data table de blim codes*/
  public function blimdt($msisdn)
  {

    $dateb     = Carbon::now()->subYear()->format('Y-m-d H:i:s');
    $blimCodes = SalesBlim::getBlimCodes($msisdn, ['dateB' => $dateb]);

    return DataTables::of($blimCodes)
      ->editColumn('pin', function ($blimCode) {
        $len = strlen(trim($blimCode->pin)) - 4;
        if ($len > 0) {
          $pin = substr(trim($blimCode->pin), 0, 2) . str_pad("", $len, "*") . substr(trim($blimCode->pin), -2);
        } else {
          $pin = trim($blimCode->pin);
        }

        return $pin;
      })
      ->editColumn('date_reg', function ($blimCode) {
        return date('d-m-Y H:i:s', strtotime($blimCode->date_reg));
      })
      ->editColumn('redeemed', function ($blimCode) {
        switch ($blimCode->redeemed) {
          case 'Y':
            return "Redimido";
          case 'N':
            return "Sin Redimir";
        }
      })
      ->toJson();
  }

  /*Data table de retention activates*/
  public function retentionsdt($msisdn)
  {

    $dateb      = Carbon::now()->subYear()->format('Y-m-d H:i:s');
    $retentions = RetentionActivates::getServicesActivates($msisdn, ['dateB' => $dateb]);

    return DataTables::of($retentions)
      ->toJson();

    // ->editColumn('pin', function($blimCode){
    //                               $len = strlen(trim($blimCode->pin))-4;
    //                               if($len>0)
    //                                   $pin = substr(trim($blimCode->pin), 0,2).str_pad("", $len, "*").substr(trim($blimCode->pin), -2);
    //                               else
    //                                   $pin=trim($blimCode->pin);
    //                               return $pin;
    //                           })
    // ->editColumn('date_reg', function($blimCode){
    //                               return date('d-m-Y H:i:s',strtotime($blimCode->date_reg));
    //                           })
    //                   ->editColumn('redeemed', function($blimCode) {
    //              switch ($blimCode->redeemed){
    //                  case 'Y':
    //                      return "Redimido";
    //                  case 'N':
    //                      return "Sin Redimir";
    //              }
    //       })
  }

  /*
   * Registro de actualizaciones
   */
  private function LogUpdate($request)
  {
    if (strcmp($request->name, $request->origin_name) !== 0) {
      $objupdate              = new ClientsUpdateCall();
      $objupdate->clients_dni = $request->dni;
      $objupdate->users_mail  = session('user')->email;
      $objupdate->date_reg    = date("Y-m-d H:m:s");
      $objupdate->campo       = 'NAME';
      $objupdate->data_last   = $request->origin_name;
      $objupdate->data_new    = $request->name;
      $objupdate->msisdn      = $request->msisdn;
      $objupdate->save();
    }
    if (strcmp($request->last_name, $request->origin_name_last) !== 0) {
      $objupdate              = new ClientsUpdateCall();
      $objupdate->clients_dni = $request->dni;
      $objupdate->users_mail  = session('user')->email;
      $objupdate->date_reg    = date("Y-m-d H:m:s");
      $objupdate->campo       = 'LASTNAME';
      $objupdate->data_last   = $request->origin_name_last;
      $objupdate->data_new    = $request->last_name;
      $objupdate->msisdn      = $request->msisdn;
      $objupdate->save();
    }
    if (strcmp($request->phone, $request->origin_phone) !== 0) {
      $objupdate              = new ClientsUpdateCall();
      $objupdate->clients_dni = $request->dni;
      $objupdate->users_mail  = session('user')->email;
      $objupdate->date_reg    = date("Y-m-d H:m:s");
      $objupdate->campo       = 'PHONE1';
      $objupdate->data_last   = $request->origin_phone;
      $objupdate->data_new    = $request->phone;
      $objupdate->msisdn      = $request->msisdn;
      $objupdate->save();
    }
    if (strcmp($request->phone2, $request->origin_phone2) !== 0) {
      if (strcmp($request->phone2, '') === 0) {
        $request->phone2 = '';
      }
      $objupdate              = new ClientsUpdateCall();
      $objupdate->clients_dni = $request->dni;
      $objupdate->users_mail  = session('user')->email;
      $objupdate->date_reg    = date("Y-m-d H:m:s");
      $objupdate->campo       = 'PHONE2';
      $objupdate->data_last   = $request->origin_phone2;
      $objupdate->data_new    = $request->phone2;
      $objupdate->msisdn      = $request->msisdn;
      $objupdate->save();
    }
    if (strcmp($request->email, $request->origin_email) !== 0) {
      $objupdate              = new ClientsUpdateCall();
      $objupdate->clients_dni = $request->dni;
      $objupdate->users_mail  = session('user')->email;
      $objupdate->date_reg    = date("Y-m-d H:m:s");
      $objupdate->campo       = 'MAIL';
      $objupdate->data_last   = $request->origin_email;
      $objupdate->data_new    = $request->email;
      $objupdate->msisdn      = $request->msisdn;
      $objupdate->save();
    }
    if (strcmp($request->address, $request->origin_address) !== 0) {
      $objupdate              = new ClientsUpdateCall();
      $objupdate->clients_dni = $request->dni;
      $objupdate->users_mail  = session('user')->email;
      $objupdate->date_reg    = date("Y-m-d H:m:s");
      $objupdate->campo       = 'ADDRESS';
      $objupdate->data_last   = $request->origin_address;
      $objupdate->data_new    = $request->address;
      $objupdate->msisdn      = $request->msisdn;
      $objupdate->save();
    }
  }
  /*
   * End registro de actualizaciones
   */

  /*Actualiza datos del cliente*/
  public function update(Request $request)
  {
    if (User::hasPermission(session('user')->email, 'CLA-ECL')) {
      //dd($request);
      if (strcmp($request->mailvalid, "true") == 0) {

        $client             = Client::find($request->dni);
        $client->name       = ValidateString::normaliza($request->name);
        $client->last_name  = ValidateString::normaliza($request->last_name);
        $client->phone_home = $request->phone;

        if (empty($request->phone2)) {
          $client->phone = '';
        } else {
          $client->phone = $request->phone2;
        }

        if (isset($request->email)) {
          $client->email = $request->email;
        }

        if (isset($request->address)) {
          $client->address = $request->address;
        }

        $client->save();
        // dd($request);
        $this->LogUpdate($request);

        return 'El Cliente se ha actualizado con exito';
      }
      return "El correo no esta disponible, por favor intenta con otro";
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }
  /*Verifica que el correo este disponible*/
  public function check_email(Request $request)
  {
    $dni   = $request->get('dni');
    $email = $request->get('mail');

    $ban = Client::disposeMail($email, $dni);
    // dd($ban->mail_count);
    if (is_null($ban) || $ban->mail_count == 0) {
      return response()->json(['success' => true, 'msj' => '✓ Disponible', 'code' => true]);
    } else {
      return response()->json(['success' => true, 'msj' => 'Ø Email no Disponible!', 'code' => false]);
    }
  }
  /* END Verifica que el correo este disponible*/

  /*Retorna los puntos cercanos donde puede recargar saldo*/
  public function getPointsRechargers($lat = false, $lng = false)
  {
    if ($lat && $lng) {
      $maxDist   = env('DISTANCE_POINT', 5);
      $distance  = DB::raw("(acos(sin(radians(" . $lat . ")) * sin(radians(lat)) + cos(radians(" . $lat . ")) * cos(radians(lat)) * cos(radians(" . $lng . ") - radians(lng))) * 6378) as dist");
      $distancec = DB::raw("acos(sin(radians(" . $lat . ")) * sin(radians(lat)) + cos(radians(" . $lat . ")) * cos(radians(lat)) * cos(radians(" . $lng . ") - radians(lng))) * 6378");

      $points = DB::table('islim_point_recharges')
        ->select($distance, 'name', 'lat', 'lng', 'address')
        ->where([[$distancec, '<=', $maxDist], ['status', 'A']])
        ->orderBy('dist', 'asc')
        ->limit(20)
        ->get();
      return json_encode($points);
    }
    return false;
  }

  public function canChangelatlng(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $canChange = Sale::payChangeCoord($request->msisdn);

      if (!empty($canChange)) {
        return response()->json(array('success' => true));
      }
    }

    return response()->json(array('success' => false, 'msg' => 'Debe comprar el servicio cambio de domicilio para poder actualizar las coordenadas.'));
  }

  public function updateB28(Request $request)
  {
    if ($request->isMethod('post') && !empty($request->imei_form) && !empty($request->msisdn_imei)) {
      $val = APIAltan::validImei($request->imei_form);

      if (!$val['error']) {
        if (strtoupper($val['data']->deviceFeatures->band28 == 'SI')) {
          ClientNetwey::where('msisdn', $request->msisdn_imei)
            ->update([
              'is_band_twenty_eight' => 'Y',
              'is_suspend_by_b28'    => 'N',
            ]);

          infoDevice::where([['msisdn', $request->msisdn_imei], ['status', 'A']])
            ->update([
              'imei'         => $request->imei_form,
              'homologated'  => $val['data']->imei->homologated,
              'blocked'      => $val['data']->imei->blocked,
              'volteCapable' => $val['data']->deviceFeatures->volteCapable,
              'model'        => $val['data']->deviceFeatures->model,
              'brand'        => $val['data']->deviceFeatures->brand,
            ]);

          return response()->json([
            'success' => true,
            'msg'     => 'MSISDN actualizado correctamente.',
          ]);
        } else {
          return response()->json([
            'success' => true,
            'msg'     => 'El IMEI no pertenece a un teléfono banda28.',
          ]);
        }
      }
    }

    return response()->json(array('success' => false, 'msg' => 'No se pudo actualizar el estatus de banda28.'));
  }

  public function activateChangeCoord(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax() && !empty($request->msisdn)) {
      $msisdn = $request->msisdn;

      $profile = APIAltan::doRequest('profile', $msisdn);
      $profile = json_decode($profile);

      /*Si no se pudo obtener un JSON de la respuesta de altan retorna un error*/
      if (is_object($profile) && $profile->status == 'success') {
        if (strtolower($profile->msisdn->status) != 'suspend') {
          $res = APIAltan::doRequest('suspend', $msisdn);
          $res = json_decode($res);

          if (!is_object($res) || $res->status != 'success') {
            return response()->json(array('success' => false));
          }
        }

        mobility::where('msisdn', $msisdn)->update(['status' => 'I']);

        mobility::insert([
          'msisdn'          => $msisdn,
          'enb'             => '0',
          'cell_id'         => '0',
          'dateAltanaffect' => date('YmdH'),
          'date_affec'      => date('Y-m-d H:i:s'),
          'status'          => 'A',
          'notas'           => 'Agregado de forma manual',
        ]);

        return response()->json(array('success' => true));
      }
    }

    return response()->json(array('success' => false));
  }

  /*Ejecuta el request de combio de geoposicion de altan, el usuario que lo esta ejecutando debe tener el permiso para poderlo hacer*/
  public function changelatlng(Request $request)
  {
    if (User::hasPermission(session('user')->email, 'CLA-ECO')) {
      try {
        $canChange = Sale::payChangeCoord($request->msisdn);

        if (!empty($canChange)) {
          $data = [
            "lat"    => $request->lat,
            "lng"    => $request->lng,
            "apiKey" => env('API_KEY_ALTAM'),
          ];

          $curl = curl_init();

          curl_setopt_array($curl, array(
            CURLOPT_URL            => env('URL_ALTAM') . "changeLocation/" . $request->msisdn,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING       => "",
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => array(
              "Content-Type: application/json",
              "cache-control: no-cache",
            ),
          ));

          $response = curl_exec($curl);
          $err      = curl_error($curl);

          curl_close($curl);

          if ($err) {
            return response()->json(array('status' => 'error', 'message' => 'Ha ocurrido un error durante la operación (API islim)', 'numError' => 001));
          } else {
            $response = json_decode($response);
            if ($response->status == "success") {
              $nupdates = ClientNetwey::select('msisdn', 'n_update_coord', 'lat', 'lng', 'point')
                ->where('msisdn', $request->msisdn)
                ->first();

              $point = DB::raw("(GeomFromText('POINT(" . $request->lat . " " . $request->lng . ")'))");

              $recordChange             = new CoordinateChanges;
              $recordChange->user_email = session('user.email');
              $recordChange->dn         = $request->msisdn;
              $recordChange->old_lat    = $nupdates->lat;
              $recordChange->old_lng    = $nupdates->lng;
              $recordChange->old_point  = $nupdates->point;
              $recordChange->new_lat    = $request->lat;
              $recordChange->new_lng    = $request->lng;
              $recordChange->new_point  = $point;
              $recordChange->sale_id    = $canChange;
              $recordChange->date_reg   = date('Y-m-d H:i:s');
              $recordChange->save();

              $nupdates->lat            = $request->lat;
              $nupdates->lng            = $request->lng;
              $nupdates->point          = $point;
              $nupdates->n_update_coord = !empty($nupdates->n_update_coord) ? ($nupdates->n_update_coord + 1) : 1;
              $nupdates->save();

              return response()->json(array('status' => 'success', 'message' => 'Los datos se actualizaron correctamente', 'numError' => 0));
            } else {
              return response()->json(array('status' => 'error', 'message' => 'Ha ocurrido un error durante la operación (API islim)', 'numError' => 001));
            }
          }
        } else {
          return response()->json(array('status' => 'success', 'message' => 'Debe comprar el servicio cambio de domicilio para poder actualizar las coordenadas.', 'numError' => 0));
        }
      } catch (\Exception $e) {
        //echo $e->get_message();
        return response()->json(array('status' => 'error', 'message' => 'Ha ocurrido un error durante la operación', 'numError' => 001));
      }
    }
    return response()->json(array('status' => 'error', 'message' => 'Usted no posee permisos para realizar esta operación', 'numError' => 002));
  }

  /*Devuelve la vista para hacer un simswap*/
  public function simSwapInit()
  {
    $html = view('pages.ajax.sim_swap.init')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Ejecuta fase 1 de simsap, verificacion de datos y estatus en el atan del dn del cliente*/
  public function simSwapStep1(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $dn  = $request->dno;
      $res = ["error" => true, "message" => "Ocurrio un error."];

      if (!empty($dn)) {
        $client = ClientNetwey::select(
          'msisdn',
          'islim_client_netweys.clients_dni',
          'islim_clients.address',
          'name',
          'last_name',
          'email',
          'phone_home'
        )
          ->join(
            'islim_clients',
            'islim_clients.dni',
            '=',
            'islim_client_netweys.clients_dni'
          )
          ->where([['status', 'A'], ['msisdn', $dn]])
          ->first();

        if (!empty($client)) {
          /*Consultando profile*/
          $profile = APIAltan::doRequest('profile', $dn);
          $profile = json_decode($profile);

          if (!empty($profile) && $profile->status != 'error') {
            if (!empty($profile->msisdn) && strtolower($profile->msisdn->status) == 'active') {
              if (!empty($profile->msisdn->supplementaryOffers)) {
                $client->line_status = 'Activa';
              } else {
                $client->line_status = 'Expirada';
              }
            } else {
              $client->line_status = 'Suspendida';
            }

            $lastSale = Sale::select('services_id', 'title')
              ->join(
                'islim_services',
                'islim_services.id',
                '=',
                'islim_sales.services_id'
              )
              ->where('msisdn', $dn)
              ->orderBy('islim_sales.date_reg', 'DESC')
              ->first();

            $client->plan  = $lastSale->title;
            $client->iccid = $profile->msisdn->ICCID;

            $html = view('pages.ajax.sim_swap.step1', compact('client'))->render();
            $res  = ["error" => false, "html" => $html];
          } else {
            $res = ["error" => true, "message" => "Error consultando profile del dn"];
          }
        } else {
          $res = ["error" => true, "message" => "Cliente no registrado en Netwey o no esta activo"];
        }
      } else {
        $res = ["error" => true, "message" => "Debe escribir un dn."];
      }
      return response()->json($res);
    }
  }

  /*Ejecuta el simswap*/
  public function simSwapStep2(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $res = ["error" => true, "message" => "Ocurrio un error."];

      if (!empty($request->typeswap) && !empty($request->dnDes) && !empty($request->dno)) {
        //Buscando el cliente al que se le va a hacer el sim swap
        $client = ClientNetwey::select('msisdn')
          ->where([['status', 'A'], ['msisdn', $request->dno]])
          ->first();

        if (!empty($client)) {
          //Consultando profiles de ambos dn
          $profileOr = APIAltan::doRequest('profile', $request->dno);
          $profileOr = json_decode($profileOr);

          $profileDe = APIAltan::doRequest('profile', $request->dnDes);
          $profileDe = json_decode($profileDe);

          //Banderas para validar estatus de los dn
          $statusOr = false;
          $statusDe = false;

          //tipo de sim swap que se est haciendo
          $tipo = $request->typeswap == 'modem' ? 'S+M' : 'S';

          if (!empty($profileOr) && $profileOr->status != 'error' && !empty($profileDe) && $profileDe->status != 'error') {
            if (!empty($profileOr->msisdn) && strtolower($profileOr->msisdn->status) == 'active') {
              $statusOr = true;
            }

            if (!empty($profileDe->msisdn) && empty($profileDe->msisdn->redirect) && empty($profileDe->msisdn->supplementaryOffers) && strtolower($profileDe->msisdn->status) != 'active') {
              $statusDe = true;
            }

            //Si ambos dn estan actos para el sim swap
            if ($statusOr && $statusDe) {
              //Consultando dn en el inventario
              $dest = Inventory::select('imei')->where('msisdn', $request->dnDes)->first();
              $org  = Inventory::select('imei')->where('msisdn', $request->dno)->first();

              //Si es sim swap con modem y no esta en el inventario no se puede hacer el cambio
              if ($request->typeswap == 'modem' && (empty($request->imei) || empty($org))) {
                $res = ["error" => true, "message" => "Modem no registrado en el sistema"];
              } else {
                //Haciendo request de sim swap
                $sw = APIAltan::simSwap($request->dno, $profileDe->msisdn->ICCID);
                $cc = (string) json_encode($sw);
                Log::info('log de sim swap: ' . $cc);

                if (!$sw['error']) {
                  $sw = json_decode($sw['data']);
                  if (!empty($sw) && is_object($sw) && $sw->status == 'success') {
                    //Datos de respaldo sim swap
                    $dataSwap = [
                      "iccid_origin"  => $profileOr->msisdn->ICCID . 'F',
                      "msisdn_origin" => $request->dno,
                      "iccid_dest"    => $profileDe->msisdn->ICCID . 'F',
                      "msisdn_dest"   => $request->dnDes,
                      "tipo"          => $tipo,
                      "id_order"      => $sw->orderId,
                      "date_reg"      => date('Y-m-d H:i:s'),
                    ];

                    //Actualizando datos de origen por los de destino
                    if ($tipo == 'S+M') {
                      Inventory::where('msisdn', $request->dno)
                        ->update([
                          'iccid' => $profileDe->msisdn->ICCID,
                          'imei'  => $request->imei, //$dest->imei
                        ]);
                      $dataSwap['imei_origin'] = $org->imei;
                      $dataSwap['imei_dest']   = $request->imei;
                    } else {
                      Inventory::where('msisdn', $request->dno)
                        ->update([
                          'iccid' => $profileDe->msisdn->ICCID,
                        ]);
                    }

                    SimSwap::create($dataSwap)->save();

                    //Si existe en el inventario el destino se elimina
                    if (!empty($dest)) {
                      Inventory::where('msisdn', $request->dnDes)->delete();
                    }

                    //Contando sim swap realizados al cliente
                    $client->n_sim_swap = empty($client->n_sim_swap) ? 1 : ($client->n_sim_swap + 1);
                    $client->save();

                    $res = ["error" => false];
                  } else {
                    $res = ["error" => true, "message" => "Ocurrio un error al intentar hacer el sim swap en el servicio, por favor intente mas tarde."];
                  }
                } else {
                  $res = ["error" => true, "message" => "Ocurrio un error al intentar hacer el sim swap, por favor intente mas tarde."];
                }
              }
            } else {
              $res = ["error" => true, "message" => "Los estatus del los dn no son los indicados para hacer sim swap"];
            }
          } else {
            $res = ["error" => true, "message" => "Error consultando los profiles"];
          }
        } else {
          $res = ["error" => true, "message" => "Cliente no registrado en Netwey o no esta activo"];
        }
      } else {
        $res = ["error" => true, "message" => "No se pudo procesar el sim swap."];
      }

      return response()->json($res);
    }
  }

  /*Verifica si el simswap ya fue hecho por altan por medio de un profile y la tabla simswap que guarda los datos del simswap*/
  public function verifySwap(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $dn  = $request->dnv;
      $res = ["error" => true, "message" => "Ocurrio un error."];

      if (!empty($dn)) {
        $client = ClientNetwey::select(
          'msisdn',
          'islim_client_netweys.clients_dni',
          'islim_clients.address',
          'name',
          'last_name',
          'email',
          'phone_home'
        )
          ->join(
            'islim_clients',
            'islim_clients.dni',
            '=',
            'islim_client_netweys.clients_dni'
          )
          ->where([['status', 'A'], ['msisdn', $dn]])
          ->first();

        $swap = SimSwap::select('iccid_dest')
          ->where('msisdn_origin', $dn)
          ->orderBy('date_reg', 'DESC')
          ->first();

        if (!empty($swap) && !empty($client)) {
          $profile = APIAltan::doRequest('profile', $dn);
          $profile = json_decode($profile);

          if (!empty($profile) && $profile->status != 'error' && !empty($profile->msisdn)) {
            $sim = 'NOT_OK';

            if ($profile->msisdn->ICCID . 'F' == $swap->iccid_dest) {
              $sim = 'OK';
            }

            $client->iccid = $profile->msisdn->ICCID;

            $html = view('pages.ajax.sim_swap.verifySwap', compact('sim', 'client'))->render();
            $res  = ["error" => false, "html" => $html];
          } else {
            $res = ["error" => true, "message" => "Error consultando profile del dn"];
          }
        } else {
          $res = ["error" => true, "message" => "Al dn no se le ha hecho sim swap"];
        }
      } else {
        $res = ["error" => true, "message" => "Debe escribir un dn."];
      }
      return response()->json($res);
    }
  }

  /*Vista de articulos vendidos pero no activos (Retail)*/
  public function articBuy()
  {
    $html = view('pages.ajax.client.artic_not_active')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  /*Data table de articulos vendidos pero no activos (Retail)*/
  public function articBuyDT(Request $request)
  {
    $artics = Sale::getSalesNotActive();

    return DataTables::eloquent($artics)
      ->editColumn('seller', '{{$name}} {{$last_name}}')
      ->editColumn('date', function ($artic) {
        return date("d-m-Y", strtotime($artic->date_reg));
      })
      ->make(true);
  }

  /*Guarda la solicitud del reportes de articulos vendidos pero no activos para que luego sea ejecutado por el cron de reportes*/
  public function articBuyDW(Request $request)
  {
    if ($request->isMethod('post')) {

      $report = new Reports;

      $report->name_report = 'reporte_articulos_no_activos';
      $report->email       = $request->emails;

      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
  }

  /*Vista de servicialidad*/
  public function serviciability()
  {
    $html = view('pages.ajax.client.servicability')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  /*Consulta la servicialidad dada una longitud y una latitud por medio de altan*/
  public function getServiciability(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->lat) && !empty($request->lon)) {
        $res = [
          'error'   => false,
          'message' => 'Zona apta para venta de internet móvil nacional, sujeto a cobertura de roaming en México.',
        ];

        $serv = APIAltan::doRequest('serviciability', false, $request->lat, $request->lon);
        $serv = json_decode($serv);

        $servMH = APIAltan::doRequest('serviciability', false, $request->lat, $request->lon, true);
        $servMH = json_decode($servMH);

        if (!is_object($serv) || !is_object($servMH)) {
          return response()->json([
            'error'   => true,
            'message' => 'Ocurrio un error consultando servicialidad.',
          ]);
        }

        if ($serv->status == 'success' && $servMH->status == 'success') {
          $res['message'] = 'Zona apta para venta de internet hogar hasta ' . $this->_getWide($serv->service) . ' mbps, internet móvil e internet móvil nacional.';
        } elseif ($serv->status == 'success' && $servMH->status != 'success') {
          $res['message'] = 'Zona apta para venta de internet hogar hasta ' . $this->_getWide($serv->service) . ' mbps e internet móvil nacional.';
        } elseif ($serv->status != 'success' && $servMH->status == 'success') {
          $res['message'] = 'Zona apta para venta de internet móvil e internet móvil nacional.';
        }

        return response()->json($res);
      }

      return response()->json([
        'error'   => true,
        'message' => 'Ocurrio un error consultando servicialidad.',
      ]);
    }
  }
  /*Retorna la vista de la tabla de promociones del cliente */
  public function servicespromociones($msisdn)
  {
    //$report = $this->getPromociones($msisdn);
    $html = view('pages.ajax.client.datatable_promociones', compact('msisdn'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*Retorna las promociones del dn dado*/
  protected function getDTPromociones(Request $request)
  {
    $filtre_date = array(
      'date_end' => date('Y-m-d'),
      'date_ini' => date("Y-m-d", strtotime("date('Y-m-d') - 3 month")),
    );
    /*Esta consulta se puede optimisar*/
    $infoPromontion = Promontion::getPromontionReport($request->msisdn, $filtre_date);
    return DataTables::of($infoPromontion)
      ->editColumn('date_reg', function ($c) {
        return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
      })
      ->make(true);
  }

  /*Consulta estado de salud del servicio dado un DN*/
  public function getHealthNetwork(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->dn)) {
        $health = APIAltan::healthNetworkv3($request->dn);
        if ($health['success'] == true) {
          return $health['data'];
        } else {
          return $health['msg'];
        }
      }
    }
  }

  /*Consulta estado bono de compensacion segun el dn*/
  public function getCompensationsStatus(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->dn)) {
        $compensation = APIAltan::compensationBonus($request->dn);
        if ($compensation['error'] == false) {
          return $compensation['data'];
        } else {
          return $compensation['msg'];
        }
      }
    }
  }

  /*Consulta cambios de coordendas segun el dn*/
  public function getCoordinatesChanges(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->dn)) {

        $coordenadaslist = CoordinateChanges::select('id', 'user_email', 'old_lat', 'old_lng', 'new_lat', 'new_lng', 'date_reg')->where('dn', '=', $request->dn)->orderBy('date_reg', 'DESC')->get();

        if ($coordenadaslist) {
          if (count($coordenadaslist) > 0) {
            $html = '<div class="row justify-content-center">
                            <div class="col-3"><p><strong>Realizada por</strong></p></div>
                            <div class="col-3"><p><strong>Anterior</strong></p></div>
                            <div class="col-3"><p><strong>Nueva</strong></p></div>
                            <div class="col-3"><p><strong>Fecha</strong></p></div>
                          </div>
                          ';

            foreach ($coordenadaslist as $key => $coord) {
              $html .= '<div class="row justify-content-center">
                                <div class="col-3"><p>' . $coord->user_email . '</p></div>
                                <div class="col-3"><p>' . $coord->old_lat . ' , ' . $coord->old_lng . '</p></div>
                                <div class="col-3"><p>' . $coord->new_lat . ' , ' . $coord->new_lng . '</p></div>
                                <div class="col-3"><p>' . $coord->date_reg . '</p></div>
                              </div>
                              ';
            }
          } else {
            $html = '<div class="row justify-content-center">
                            <div class="col-12"><p><strong>No se han realizado cambios de coordenadas</strong></p></div>                          </div>
                          ';
          }
        } else {
          $html = '<div class="row justify-content-center">
                            <div class="col-12"><p><strong>No se han realizado cambios de coordenadas</strong></p></div>                          </div>
                          ';
        }
        return response()->json(array('error' => false, 'html' => $html));
      }
      return response()->json([
        'error'   => true,
        'message' => 'Ocurrio un error.',
      ]);
    }
  }

  /*Consulta historico de compensaciones segun el dn*/
  public function getCompensationsHistory($msisdn)
  {
    /*$date_end = date('Ymd');
    $date_ini = date("Ymd", strtotime("$date_end - 12 month"));*/

    $compensations = CompensationBonus::getCompensationHistory(
      $msisdn
    );
    return $compensations;
  }

  /*Crea registro en historico de suspensiones para un DN (suspension hechas en call center)*/
  public function setSuspendedHistory(Request $request)
  {
    try {
      $susp_history = new SuspendedByAdmin;
      $susp_history->getConnect('W');
      $susp_history->msisdn     = $request->msisdn;
      $susp_history->user_email = session('user')->email;
      $susp_history->date_reg   = date('Y-m-d H:i:s');
      $susp_history->save();

      return response()->json([
        'error'   => false,
        'message' => 'historico creado con exitosamente.',
      ]);

    } catch (QueryException $e) {
      return response()->json([
        'error'   => true,
        'message' => 'Ocurrio un error creado histórico de suspension.',
      ]);
    }
  }

  /*Consulta detalle de suspension por mobilidad de un dn*/
  public function getSuspensionDetails(Request $request)
  {

    $dn = $request->dn;
    //$dn='524494742183';

    $susp_det = HbbMobility::where('msisdn', $dn)
      ->orderBy('id', 'DESC')
      ->first();

    if ($susp_det) {
      return response()->json([
        'error'      => false,
        'date'       => date("d-m-Y H:i:s", strtotime($susp_det->date_file . '0000')),
        'distance'   => round($susp_det->distance, 2),
        'consuption' => round($susp_det->tf_mob_aprox, 2),
      ]);
    } else {
      return response()->json([
        'error'   => true,
        'message' => 'Ocurrio un error.',
      ]);
    }
  }

  private function altanRetentionActive($msisdn, Service $service, $reason_id, $is_auth = false)
  {
    $res = APIAltan::retentionActive($msisdn, $service->codeAltan);
    //Log::alert((String) json_encode($res));
    $response = [];
    $response['success']=false;

    if ($res['error'] == false) {
      $data = json_decode($res['data']);
      if ($data->status == 'success') {
        $sale                     = Sale::getConnect('W');
        $sale->services_id        = $service->id;
        $sale->concentrators_id   = env('RET_CONCENTRATOR_ID', '1');
        $sale->api_key            = env('API_KEY_ALTAM');
        $sale->order_altan        = $data->orderId;
        $sale->unique_transaction = uniqid('RET-');
        $sale->codeAltan          = $service->codeAltan;
        $sale->type               = 'SR';
        $sale->id_point           = 'RETENTION';
        $sale->description        = $service->title;
        $sale->amount             = 0;
        $sale->amount             = 0;
        $sale->com_amount         = 0;
        $sale->msisdn             = $msisdn;
        $sale->date_reg           = $data->effectiveDate;
        $sale->save();

        $ret_activate               = RetentionActivates::getConnect('W');
        $ret_activate->msisdn       = $msisdn;
        $ret_activate->user_creator = session('user')->email;
        if ($is_auth) {
          $ret_activate->user_autorization = session('user')->parent_email;
        }

        $ret_activate->services_id = $service->id;
        $ret_activate->reason_id   = $reason_id;
        $ret_activate->sales_id    = $sale->id;
        $ret_activate->save();


        $response['success']=true;
        $response['msg'] = 'Servicio de retención activado con exito';
      }
      else{
        Log::error((String) json_encode($data));
        $response['msg'] = $data->message;
      }
    }
    return $response;
  }

  /*envia notificaciones por la activacion del servicio de retencion*/
  private function activateRetentionServiceNotify($email, $info)
  {
    try {
      Mail::to($email)->send(new RetentionMail($info));
    } catch (\Exception $e) {
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL            => 'https://secure.netwey.com.mx/Webhook/sendSMS',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => '',
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => 'POST',
      CURLOPT_POSTFIELDS     => array('msisdn' => $info['msisdn'], 'sms_type' => 'G', 'concentrator_id' => '1', 'sms_attrib' => 'Disculpa las molestias que te ocasionamos, NETWEY te ha compensado con ' . $info['GB'] . ' con vigencia de ' . $info['time'] . ', los GB están abiertos a cualquier tipo de navegación.'),
      CURLOPT_HTTPHEADER     => array(
        'Cookie: PHPSESSID=5ufiug6b71c6nssnrdaj5bm0t5',
      ),
    ));
    $response = curl_exec($curl);

    curl_close($curl);
  }
  /*activa servicio de retencion a un dn*/
  public function activateRetentionService(Request $request)
  {
    $msg       = "";
    $limite    = intval(SellerInventory::getTotalPermision(session('user')->email, 'RET-LAS')); //limite de activaciones del agente
    $cantmonth = RetentionActivates::getCantMonth(session('user')->email, 'P'); //cantidad de activaciones en los ultimos 30 dias agente

    $timeLastActDN = RetentionActivates::getTimeLastActivateByDN($request->msisdn);
    $cantMonthDN   = RetentionActivates::getCantMonthByDN($request->msisdn);
    $err           = 0;
    if ($timeLastActDN >= 0 && $timeLastActDN <= 24) {
      $err = 1;
      $msg = "Este DN ya cuenta con una activación en las ultimas 24 horas";
    }

    if ($cantMonthDN >= env('ACT_RET_LIMIT', 2)) {
      $err = 1;
      $msg = "Este DN ya cuenta con el máximo de activaciones permitidas en el mes";
    }

    $service     = Service::getService($request->retservice, 'A');
    $periodicity = Service::getPeriodicity($request->retservice);
    $client      = ClientNetwey::getClient($request->msisdn);

    $infodata = array(
      'GB'        => $service->gb . 'gb',
      'time'      => $periodicity->days . ' dias',
      'name'      => $client->client->name,
      'last_name' => $client->client->last_name,
      'msisdn'    => $request->msisdn,
    );

    if ($err == 0) {
      if ($cantmonth < $limite) {
        //permite activar aun no supera su limite mensual
        $res = self::altanRetentionActive($request->msisdn, $service, $request->retsubreason);
        if ($res['success']) {
          self::activateRetentionServiceNotify($client->client->email, $infodata);
          return response()->json([
            'status' => 'success',
            'msg'    => $res['msg'],
          ]);
        }
        $msg = "Ocurrió un error al procesar tu solicitud, ".$res['msg'];
      } else {
        if (!empty(session('user')->parent_email)) {
          $perm = User::hasPermission(session('user')->email, 'RET-ASE'); //politica de activaciones con autorizacion
          if ($perm) {
            if (!$request->has('pass')) {
              $parent = User::select('name', 'last_name')->where(['email' => session('user')->parent_email, 'status' => 'A'])->first();

              if (!empty($parent)) {
                $nameprnt = trim($parent->name . " " . $parent->last_name);
              }

              return response()->json([
                'status'      => 'success',
                'action'      => 'authorize',
                'name_parent' => $nameprnt,
              ]);
            } else {
              $parent = User::select('password')->where(['email' => session('user')->parent_email, 'status' => 'A'])->first();
              if ($parent) {
                if (Hash::check($request->pass, $parent->password)) {

                  $limites    = intval(SellerInventory::getTotalPermision(session('user')->parent_email, 'RET-LAS')); //limite de activaciones del supervisor
                  $cantmonths = RetentionActivates::getCantMonth(session('user')->parent_email, 'P'); //cantidad de activaciones en los ultimos 30 dias del supervisor

                  if ($cantmonths < $limites) {
                    //permite activar aun no supera su limite mensual
                    $res = self::altanRetentionActive($request->msisdn, $service, $request->retsubreason, true);
                    if ($res['success']) {
                      self::activateRetentionServiceNotify($client->client->email, $infodata);
                      return response()->json([
                        'status' => 'success',
                        'msg'    => $res['msg'],
                      ]);
                    }
                    $msg = "Ocurrió un error al procesar tu solicitud, ".$res['msg'];
                  } else {
                    $msg = "Tu supervisor no puede autorizar mas servicios de retención, ya llego a su limite mensual";
                  }
                } else {
                  $msg = "Clave de supervisor invalida";
                }
              } else {
                $msg = "Clave de supervisor invalida";
              }
            }
          }
        } else {
          $msg = "No puedes activar mas servicios de retención, ya llegaste a tu limite mensual";
        }
      }
    }
    return response()->json([
      'status' => 'error',
      'msg'    => $msg,
    ]);
  }

  //retornar un entero verificando los ultimos tres o dos caracteres de una cadena
  private function _getWide($wide)
  {
    $wide = substr($wide, strlen($wide) - 3, strlen($wide));
    if (is_numeric($wide)) {
      return (int) $wide;
    } else {
      $wide = substr($wide, strlen($wide) - 2, strlen($wide));
      if (is_numeric($wide)) {
        return (int) $wide;
      } else {
        $wide = substr($wide, strlen($wide) - 1, strlen($wide));
        if (is_numeric($wide)) {
          return (int) $wide;
        }
      }
    }
    return 0;
  }
}
