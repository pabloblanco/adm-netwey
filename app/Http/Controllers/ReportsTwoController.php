<?php

namespace App\Http\Controllers;

use App\ClientsUpdateCall;
use App\ConsumoAcumuladoDetails;
use App\CoordinateChanges;
use App\Coppel;
use App\DeferredPayment;
use App\HbbMobilityDetections;
use App\Helpers\APIvoyWey;
use App\Helpers\CommonHelpers;
use App\Migrations;
use App\Payjoy;
use App\Reports;
use App\RetentionActivates;
use App\Sale;
use App\StockProvaDetail;
use App\SuspendedByAdmin;
use App\TempCar;
use App\User;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;

class ReportsTwoController extends Controller
{
  public function consumption()
  {
    $html = view('pages.ajax.report.consumption')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTConsumption(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();

        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();

        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])
          ->endOfDay()
          ->addMonth()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
      }

      $data = Sale::getConsuption($filters);

      return DataTables::eloquent($data)
        ->editColumn('consuption', function ($c) {
          return !empty($c->consuption) ? round(((($c->consuption / 1024) / 1024) / 1024), 2) : 'S/I';
        })
        ->editColumn('offer_name', function ($c) {
          return !empty($c->offer_name) ? $c->offer_name : 'S/I';
        })
        ->editColumn('date_reg', function ($c) {
          return Carbon::createFromFormat('Y-m-d H:i:s', $c->date_reg)
            ->format('Y-m-d');
        })
        ->editColumn('date_sup_en', function ($c) {
          return !empty($c->date_sup_en) ? Carbon::createFromFormat(
            'Y-m-d',
            $c->date_sup_en
          )
            ->format('Y-m-d') : 'S/I';
        })
        ->editColumn('days', function ($c) {
          return !empty($c->days) ? $c->days : 'S/I';
        })
        ->editColumn('type', function ($c) {
          return $c->type == 'P' ? 'Alta' : 'Recarga';
        })
        ->make(true);
    }
  }

  public function downloadDTConsumption(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->addMonth()
          ->endOfDay()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->format('Y-m-d H:i:s');
        //->toDateTimeString();

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->format('Y-m-d H:i:s');
        //->toDateTimeString();
      }

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_consumo';

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }

    return response()->json(array('error' => true));
  }

  public function consumptionCDR()
  {
    $html = view('pages.ajax.report.consumption_cdr')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTConsumptionCDR(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();

        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();

        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])
          ->endOfDay()
          ->addMonth()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
      }

      // $totalRecrods = CDRDataConsDetails::getTotalconsuption($filters);
      // $data         = CDRDataConsDetails::getConsuptionV2($filters);

      $totalRecrods = ConsumoAcumuladoDetails::getTotalconsuption($filters);
      $data = ConsumoAcumuladoDetails::getConsuptionV2($filters);

      return DataTables::of($data)
        ->editColumn('consuption', function ($c) {
          return !empty($c->consuption) ? round(((($c->consuption / 1024) / 1024) / 1024), 2) : '0';
        })
        ->editColumn('throttling', function ($c) {
          return !empty($c->throttling) ? round(((($c->throttling / 1024) / 1024) / 1024), 2) : '0';
        })
        ->editColumn('date_reg', function ($c) {
          return Carbon::createFromFormat('Y-m-d H:i:s', !empty($c->date_reg) ? $c->date_reg : $c->date_reg_rec)
            ->format('Y-m-d');
        })
        ->editColumn('date_sup_en', function ($c) {
          return !empty($c->date_sup_en) ? Carbon::createFromFormat(
            'Y-m-d',
            $c->date_sup_en
          )
            ->format('Y-m-d') :
          Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $c->date_reg_rec
          )
            ->addDays($c->period + 1)
            ->format('Y-m-d');
        })
        ->editColumn('days', function ($c) {
          return !empty($c->days) ? $c->days : '0';
        })
        ->editColumn('type', function ($c) {
          switch ($c->type) {
            case 'P':return 'Alta';break;
            case 'R':return 'Recarga';break;
            case 'SR':return 'Retención';break;
            default:return '';break;
          }
        })
        ->skipPaging()
        ->setTotalRecords($totalRecrods)
        ->toJson();
    }
  }

  public function downloadDTConsumptionCDR(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->addMonth()
          ->endOfDay()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->format('Y-m-d H:i:s');
      }

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_consumo_cdr';

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }

    return response()->json(array('error' => true));
  }
  /*******************************************************************/
  public function gracePeriod()
  {
    $html = view('pages.ajax.report.grace_period')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTGracePeriod(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $filters = $this->validateDate($filters);

      //$totalRecrods = HbbMobilityDetections::getTotalGracePeriods($filters);
      $data = HbbMobilityDetections::getGracePeriodDataReport($filters);
      //$data = CDRDataConsDetails::getConsuptionV2($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg . "0000")) : '';
        })
        ->make(true);
    }
  }

  public function downloadDTGracePeriod(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {

      $filters = $request->all();

      $filters = $this->validateDate($filters);

      $grace_period = HbbMobilityDetections::getGracePeriodDataReport($filters);

      $data[] = ['MSISDN', 'Inicio Periodo de Gracia', 'Coordenada de Activación', 'Coordenada de Uso', 'Distancia (Km)', 'Vendedor', 'Estatus'];

      foreach ($grace_period as $grace_period) {

        $date_pg = date("d-m-Y H:i:s", strtotime($grace_period->date_pg . "0000"));

        $data[] = [
          $grace_period->msisdn,
          $date_pg,
          $grace_period->point_act,
          $grace_period->point_pg,
          $grace_period->distance,
          $grace_period->vendor,
          ($grace_period->status == 'A' ? "Activo" : "Inactivo")];
      }

      $url = CommonHelpers::saveFile('/public/reports', 'grace_period', $data, 'grace_period_report_' . date('d-m-Y'));

      return response()->json(array('url' => $url));

    }
  }
  /*******************************************************************/
  public function servicesRetention()
  {
    $html = view('pages.ajax.report.services_retention')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTRetentionPeriod(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $filters = $this->validateDate($filters);

      //$totalRecrods = HbbMobilityDetections::getTotalGracePeriods($filters);
      $data = RetentionActivates::getDTRetentionPeriodDataReport($filters);
      //$data = CDRDataConsDetails::getConsuptionV2($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
        })
        ->make(true);
    }
  }
  public function downloadDTServicesRetention(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $filters = $request->all();

      $filters = $this->validateDate($filters);

      $services_retention = RetentionActivates::getDTRetentionPeriodDataReport($filters);

      $data[] = ['MSISDN', 'Servicio', 'usuario creador', 'usuario autorizado', 'Motivo', 'Sub-Motivo', 'Fecha de creacion'];

      foreach ($services_retention as $services_retention) {

        $date_reg = date("d-m-Y H:i:s", strtotime($services_retention->date_reg));

        $data[] = [
          $services_retention->msisdn,
          $services_retention->service,
          $services_retention->user_creator,
          $services_retention->user_autorization,
          $services_retention->reason,
          $services_retention->sub_reason,
          $date_reg];
      }

      $url = CommonHelpers::saveFile('/public/reports', 'services_retention', $data, 'services_retention_report_' . date('d-m-Y'));

      return response()->json(array('url' => $url));

    }
  }

  /*******************************************************************/

  public function payjoy(Request $request)
  {
    $html = view('pages.ajax.report.payjoy')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getPayjoyDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = Payjoy::getReport($request->all());

      return DataTables::eloquent($data)
        ->editColumn('coordinador', function ($data) {
          if (!empty($data->coord_name)) {
            return $data->coord_name . ' ' . $data->coord_last_name;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('seller', function ($data) {
          if (!empty($data->seller_name)) {
            return $data->seller_name . ' ' . $data->seller_last_name;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('client', function ($data) {
          if (!empty($data->client_name)) {
            return $data->client_name . ' ' . $data->client_last_name;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('amount', function ($data) {
          return '$' . round($data->amount, 2);
        })
        ->editColumn('total_amount', function ($data) {
          return '$' . round($data->total_amount, 2);
        })
        ->editColumn('init_amount', function ($data) {
          return '$' . round(($data->total_amount - $data->amount), 2);
        })
        ->editColumn('date_reg', function ($data) {
          return date('d-m-Y H:i:s', strtotime($data->date_reg));
        })
        ->editColumn('date_process', function ($data) {
          return !empty($data->date_process) ? date('d-m-Y H:i:s', strtotime($data->date_process)) : 'N/A';
        })
        ->editColumn('status', function ($data) {
          return $data->status == 'A' ? 'Notificado' : 'Asociado';
        })
        ->make(true);

    }
  }

  public function downloadPayjoyReport(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_financiamiento_payjoy';

      unset($inputs['emails']);
      unset($inputs['_token']);

      $report->filters = json_encode($inputs);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
  }

  /*******************************************************************/

  public function coordinates()
  {
    $html = view('pages.ajax.report.coordinates')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getCoordinatesDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = CoordinateChanges::getReport($request->all());

      return DataTables::eloquent($data)
        ->editColumn('client', function ($data) {
          return $data->client_name . ' ' . $data->client_last_name;
        })
        ->editColumn('user', function ($data) {
          return $data->user_name . ' ' . $data->user_last_name;
        })
        ->editColumn('date_reg', function ($data) {
          return date('d-m-Y H:i:s', strtotime($data->date_reg));
        })
        ->make(true);
    }
  }

  public function downloadCoordinatesReport(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_coordenadas';

      unset($inputs['emails']);
      unset($inputs['_token']);

      $report->filters = json_encode($inputs);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
  }
  /*
  Actualizacion de datos del cliente desde el call center
   */
  public function clientsupdate()
  {
    $html = view('pages.ajax.report.clients_updateCenter')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }
  public function getDTClientsUpdateCall(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $filters = $this->validateDate($filters);

      $data = ClientsUpdateCall::getDTUpdatePeriodDataReport($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
        })
        ->make(true);
    }
  }
  public function downloadDTClientsUpdateCall(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $filters = $request->all();

      $filters = $this->validateDate($filters);

      $data_update = ClientsUpdateCall::getDTUpdatePeriodDataReport($filters);

      $data[] = ['ID', 'DN cliente', 'usuario responsable', 'Fecha de actualizacion', 'Campo modificado', 'Info original', 'Info actualizada'];

      foreach ($data_update as $data_update) {

        $date_reg = date("d-m-Y H:i:s", strtotime($data_update->date_reg));

        $data[] = [
          $data_update->id,
          $data_update->msisdn,
          $data_update->users_mail,
          $date_reg,
          $data_update->campo,
          $data_update->data_last,
          $data_update->data_new];
      }

      $url = CommonHelpers::saveFile('/public/reports', 'clients_update', $data, 'clients_updateCall_report_' . date('d-m-Y'));

      return response()->json(array('url' => $url));

    }
  }
  /*
  END Actualizacion de datos del cliente desde el call center
   */

  /* validacion de fechas de filtro de busqueda */
  private function validateDate($filters)
  {
    //Validando que vengan los dos rangos de fechas y formateando fecha
    if (empty($filters['dateStar']) && empty($filters['dateEnd'])) {
      $filters['dateStar'] = Carbon::now()
        ->format('Y-m-d H:i:s');

      $filters['dateEnd'] = Carbon::now()
        ->addMonth()
        ->format('Y-m-d H:i:s');
    } elseif (empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
      $filters['dateEnd'] = Carbon::createFromFormat('d-m-Y', $filters['dateEnd'])
        ->endOfDay()
        ->toDateTimeString();

      $filters['dateStar'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateEnd'])
        ->subMonth()
        ->startOfDay()
        ->toDateTimeString();
    } elseif (empty($filters['dateEnd']) && !empty($filters['dateStar'])) {
      $filters['dateStar'] = Carbon::createFromFormat('d-m-Y', $filters['dateStar'])
        ->startOfDay()
        ->toDateTimeString();

      $filters['dateEnd'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateStar'])
        ->endOfDay()
        ->addMonth()
        ->toDateTimeString();
    } else {
      $filters['dateStar'] = Carbon::createFromFormat('d-m-Y', $filters['dateStar'])
        ->startOfDay()
        ->toDateTimeString();

      $filters['dateEnd'] = Carbon::createFromFormat('d-m-Y', $filters['dateEnd'])
        ->endOfDay()
        ->toDateTimeString();
    }

    $filters['dateStar'] = (date("Ymd000000", strtotime($filters['dateStar'])));
    $filters['dateEnd'] = (date("Ymd235959", strtotime($filters['dateEnd'])));
    return $filters;
  }
  /* END validacion de fechas de filtro de busqueda */
  /*Reporte voywey Nomina*/
  public function voyweynomina()
  {
    $html = view('pages.ajax.voywey.voywey_nomina')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTvoywey_nomina(Request $request)
  {
    $filters = $request->all();

    $filters = $this->validateDate($filters);

    $inputapi = [
      'date_inicio' => $filters['dateStar'],
      'date_fin' => $filters['dateEnd']];

    $current_page = 1;
    $dataInfo = APIvoyWey::nomina($inputapi, $current_page);

    if ($dataInfo['success']) {
      $arrayData = array();
      $arrayData = $dataInfo['data']->data->data;

      while ($dataInfo['data']->data->next_page_url != null) {
        $current_page++;
        //Log::info($current_page);

        $dataInfo = APIvoyWey::nomina($inputapi, $current_page);

        for ($i = 0; $i < count($dataInfo['data']->data->data); $i++) {
          array_push($arrayData, $dataInfo['data']->data->data[$i]);
        }
      }

      return DataTables::of($arrayData)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
        })
        ->editColumn('date_del', function ($c) {
          return !empty($c->date_del) ? date("d-m-Y H:i:s", strtotime($c->date_del)) : '';
        })
        ->editColumn('MP_transaction', function ($c) {
          return !empty($c->MP_transaction) ? $c->MP_transaction : 'S/N';
        })
        ->editColumn('DN', function ($c) {
          return !empty($c->DN) ? $c->DN : 'S/N';
        })
        ->make(true);
    }
  }

  public function downloadDTvoywey_nomina(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $filters = $this->validateDate($filters);
      //if(!empty($inputs['emails'])){
      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_nomina_voywey';

      $report->email = session('user')->email;

      unset($filters['_token']);

      $inputapi = [
        'date_inicio' => $filters['dateStar'],
        'date_fin' => $filters['dateEnd']];

      $report->filters = json_encode($inputapi);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }

    return response()->json(array('success' => false));
  }
  /*END Reporte voywey Nomina*/
  /* Reporte voywey conciliacion*/
  public function voyweyconciliacion()
  {
    $html = view('pages.ajax.voywey.voywey_conciliacion')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }
  public function getDTvoywey_conciliacion(Request $request)
  {
    $filters = $request->all();

    $filters = $this->validateDate($filters);

    $inputapi = [
      'date_inicio' => $filters['dateStar'],
      'date_fin' => $filters['dateEnd']];

    $dataInfo = APIvoyWey::conciliacion($inputapi);

    $current_page = 1;
    $dataInfo = APIvoyWey::conciliacion($inputapi, $current_page);

    if ($dataInfo['success']) {
      $arrayData = array();
      $arrayData = $dataInfo['data']->data->data;

      while ($dataInfo['data']->data->next_page_url != null) {
        $current_page++;

        $dataInfo = APIvoyWey::conciliacion($filters, $current_page);

        for ($i = 0; $i < count($dataInfo['data']->data->data); $i++) {
          array_push($arrayData, $dataInfo['data']->data->data[$i]);
        }
      }
      //Log::info($arrayData);
      return DataTables::of($arrayData)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
        })
        ->editColumn('date_del', function ($c) {
          return !empty($c->date_del) ? date("d-m-Y H:i:s", strtotime($c->date_del)) : '';
        })
        ->editColumn('hrs_desde_entrega', function ($c) {
          return !empty($c->hrs_desde_entrega) ? round($c->hrs_desde_entrega / 24, 0, PHP_ROUND_HALF_DOWN) : '0';
        })
        ->make(true);
    }
  }
  public function downloadDTvoywey_conciliacion(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $filters = $this->validateDate($filters);
      //if(!empty($inputs['emails'])){
      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_conciliacion_voywey';

      $report->email = session('user')->email;

      unset($filters['_token']);

      $inputapi = [
        'date_inicio' => $filters['dateStar'],
        'date_fin' => $filters['dateEnd']];

      $report->filters = json_encode($inputapi);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }

  /* End Reporte voywey conciliacion*/

  /* Reporte voywey inventory */
  public function voyweyinventory()
  {
    $html = view('pages.ajax.voywey.voywey_inventory')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }
  public function getDTvoywey_inventory(Request $request)
  {

    $inputs = $request->all();
    /*
    para generacion de reporte -> makeReportInventory
    para la vista -> inventory
     */
    $current_page = 1;
    $dataInfo = APIvoyWey::inventory($inputs, $current_page);

    if ($dataInfo['success']) {

      $arrayData = array();
      $arrayData = $dataInfo['data']->data;

      while ($dataInfo['data']->next_page_url != null) {
        $current_page++;

        $dataInfo = APIvoyWey::inventory($inputs, $current_page);

        for ($i = 0; $i < count($dataInfo['data']->data); $i++) {
          array_push($arrayData, $dataInfo['data']->data[$i]);
        }
      }

      return DataTables::of($arrayData)
        ->editColumn('detail', function ($c) {
          return json_encode($c->detail);
        })
        ->editColumn('detail_warehouse', function ($c) {
          return json_encode($c->detail_warehouse);
        })
        ->make(true);
    }
  }
  public function getDTvoywey_inventory_detail(Request $request)
  {

    $inputs = $request->all();
    $current2_page = 1;
    $dataInfo = APIvoyWey::inventoryDetail($inputs, $current2_page);

    if ($dataInfo['success']) {
      $arrayData = array();
      $arrayData = $dataInfo['data']->data->data;

      while ($dataInfo['data']->data->next_page_url != null) {
        $current2_page++;

        $dataInfo = APIvoyWey::inventoryDetail($inputs, $current2_page);

        for ($i = 0; $i < count($dataInfo['data']->data->data); $i++) {
          array_push($arrayData, $dataInfo['data']->data->data[$i]);
        }
      }

      return DataTables::of($arrayData)
        ->make(true);
    }
  }
  public function downloadDTvoywey_inventory(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_inventario_voywey';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }
  /* END Reporte voywey inventory */

  /* Reporte voywey ventas jelou */

  public function voyweySalesjelou()
  {
    $query = DeferredPayment::getStatus();
    $status = array();

    foreach ($query as $keydate) {
      switch ($keydate) {
        case 'P':
          array_push($status, array('code' => 'P', 'description' => 'En transito'));
          break;
        case 'A':
          array_push($status, array('code' => 'A', 'description' => 'Pendiente de deposito'));
          break;
        case 'D':
          array_push($status, array('code' => 'D', 'description' => 'Rechazado'));
          break;
        case 'T':
          array_push($status, array('code' => 'T', 'description' => 'Eliminado'));
          break;
        case 'C':
          array_push($status, array('code' => 'C', 'description' => 'Orden finalizada'));
          break;
        default:
          array_push($status, array('code' => 'X', 'description' => 'No disponible'));
          break;
      }
    }

    $html = view('pages.ajax.voywey.voywey_salesJelou', compact('status'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTvoywey_salesjelou(Request $request)
  {

    $filters = $request->all();
    $status = $filters['status'];
    $filters = $this->validateDate($filters);

    $inputapi = [
      'dateStar' => $filters['dateStar'],
      'dateEnd' => $filters['dateEnd'],
      'status' => $status];

    $data_DefPayment = DeferredPayment::getData_DeferredPayment($inputapi);

    if (!empty($data_DefPayment)) {
      foreach ($data_DefPayment as $RegJeoVoy) {
        $RegJeoVoy = DeferredPayment::getDetail_repartidorByOrden($RegJeoVoy->OrderVoy, $RegJeoVoy);
      }
    }
    return DataTables::of($data_DefPayment)
      ->editColumn('Fecha', function ($culumn) {
        return !empty($culumn->Fecha) ? date("d-m-Y H:i:s", strtotime($culumn->Fecha)) : '';
      })
      ->editColumn('Fecha_Activacion', function ($culumn) {
        return !empty($culumn->Fecha_Activacion) ? date("d-m-Y H:i:s", strtotime($culumn->Fecha_Activacion)) : 'N/A';
      })
      ->editColumn('Codigo', function ($culumn) {
        if (!empty($culumn->Codigo)) {
          return $culumn->Codigo;
        } else {
          return 'N/A';
        }
      })
      ->editColumn('FormaPago', function ($culumn) {
        if ($culumn->FormaPago == 'CASH') {
          return "Efectivo";
        } else {
          return 'Tarjeta';
        }
      })
      ->editColumn('Dias_en_Activar', function ($culumn) {
        if ($culumn->status_client == 'A') {
          return $culumn->Dias_en_Activar != 0 ? $culumn->Dias_en_Activar . ' día(s)' : '0 días';
        } else {

          $date1 = date_create(date("Y-m-d", strtotime($culumn->Fecha)));
          $date2 = date_create(date("Y-m-d"));

          $resultado = $date1->diff($date2);
          return $resultado->format('%a día(s)');
        }
      })
      ->editColumn('ClienteMail', function ($culumn) {
        if (!empty($culumn->ClienteMail)) {
          return $culumn->ClienteMail;
        } else {
          return 'N/A';
        }

      })
      ->editColumn('MSISDN', function ($culumn) {
        if (!empty($culumn->MSISDN)) {
          return $culumn->MSISDN;
        } else {
          return 'N/A';
        }

      })
      ->editColumn('status', function ($culumn) {
        if (!empty($culumn->status)) {
          switch ($culumn->status) {
            case 'P':
              return 'En transito';
              break;
            case 'A':
              return 'Pendiente de deposito';
              break;
            case 'D':
              return 'Rechazado';
              break;
            case 'T':
              return 'Eliminado';
              break;
            case 'C':
              return 'Orden finalizada';
              break;
            default:
              return 'No disponible';
              break;
          }
        } else {
          return 'N/A';
        }
      })
      ->make(true);
    // }
  }
  public function downloadDTvoywey_salesjelou(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $filters = $this->validateDate($filters);

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_SaleJelou_voywey';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }
  /* END Reporte voywey ventas jelou */

  /*******************************************************************/
  public function migration()
  {
    $html = view('pages.ajax.report.migration')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTMigration(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $filters = $this->validateDate($filters);

      //$totalRecrods = HbbMobilityDetections::getTotalGracePeriods($filters);
      $data = Migrations::getDTMigrationsDataReport($filters);
      //$data = CDRDataConsDetails::getConsuptionV2($filters);

      // return DataTables::of($data)
      //     ->editColumn('date_reg', function ($c) {
      //         return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
      //     })
      //     ->make(true);
      // return DataTables::of($data)->make(true);

      return DataTables::of($data)
        ->editColumn('artic_type', function ($c) {
          switch ($c->artic_type) {
            case 'H':return 'Hogar';break;
            case 'T':return 'Telefonia';break;
            case 'M':return 'MIFI';break;
            default:return 'Hogar';break;
          }
        })
        ->make(true);
    }
  }
  public function downloadDTMigration(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      if (!empty($filters['dateStar'])) {
        $filters['dateStar'] = Carbon::createFromFormat('d-m-Y', $filters['dateStar'])
          ->startOfDay()
          ->toDateTimeString();
      }

      if (!empty($filters['dateEnd'])) {
        $filters['dateEnd'] = Carbon::createFromFormat('d-m-Y', $filters['dateEnd'])
          ->endOfDay()
          ->toDateTimeString();
      }
      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_migracion';

      $report->email = session('user')->email;

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }

    return response()->json(array('success' => false));
  }

  /*************************************************/
  /*Report super Sim*/
  public function super_sim()
  {
    $html = view('pages.ajax.report.super_sim')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTsuper_sim(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $imputDN = $filters['DN'];
      $filters = $this->validateDate($filters);

      $input = [
        'dateStar' => $filters['dateStar'],
        'dateEnd' => $filters['dateEnd'],
        'DN' => $imputDN];

      $datos = Sale::getSuper_Sim($input);
      //Log::info("datos: " . $datos[0]);
      //return DataTables::of($datos ? [$datos] : [])
      //
      return DataTables::of($datos)
        ->editColumn('mailClient', function ($culumn) {
          if (!empty($culumn->mailClient)) {
            return $culumn->mailClient;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('nameVendedor', function ($culumn) {
          if (!empty($culumn->nameVendedor)) {
            return $culumn->nameVendedor;
          } else {
            return $culumn->id_point;
          }
        })
        ->editColumn('mailvendedor', function ($culumn) {
          if (!empty($culumn->mailvendedor)) {
            return $culumn->mailvendedor;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('date_reg', function ($culumn) {
          return !empty($culumn->date_reg) ? date("d-m-Y H:i:s", strtotime($culumn->date_reg)) : 'N/A';
        })
        ->make(true);
    }
  }
  public function downloadDTsuper_sim(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $imputDN = $filters['DN'];
      $filters = $this->validateDate($filters);
      $filters['DN'] = $imputDN;

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_SuperSim';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }
  /*END Report super Sim*/
  /*************************************************/

  /*************************************************/
  /*Report Altas con consumos*/
  public function upsWithConsumptions()
  {
    $html = view('pages.ajax.report.ups_with_consumptions')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTUpsWithConsumptions(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $filters['dateStar'] = (date("Y-m-d 00:00:00", strtotime($filters['dateStar'])));
      $filters['dateEnd'] = (date("Y-m-d 23:59:59", strtotime($filters['dateEnd'])));

      $data = Sale::getDTUpsWithConsumptionsDataReport($filters);

      return DataTables::of($data)
        ->editColumn('Fecha_Alta', function ($c) {
          return !empty($c->Fecha_Alta) ? date("d-m-Y H:i:s", strtotime($c->Fecha_Alta)) : '';
        })
        ->editColumn('Fecha_Consumo', function ($c) {
          return !empty($c->Fecha_Consumo) ? date("d-m-Y", strtotime($c->Fecha_Consumo)) : '';
        })
        ->editColumn('Consumo', function ($c) {
          return !empty($c->Consumo) ? round($c->Consumo / 1024 / 1024, 2) : '0';
        })
        ->make(true);
    }
  }

  public function downloadDTUpsWithConsumptions(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $filters['dateStar'] = (date("Y-m-d 00:00:00", strtotime($filters['dateStar'])));
      $filters['dateEnd'] = (date("Y-m-d 23:59:59", strtotime($filters['dateEnd'])));

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_altas_con_consumos';

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }

    return response()->json(array('error' => true));
  }

  /*******************************************************************/
  /*Historico de suspensiones*/
  public function suspendedHistory()
  {
    $html = view('pages.ajax.report.suspended_history')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTSuspendedHistory(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])
          ->endOfDay()
          ->addMonth()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
      }

      //$totalRecrods = ConsumoAcumuladoDetails::getTotalconsuption($filters);
      $data = SuspendedByAdmin::getSuspendedHistory($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return Carbon::createFromFormat('Y-m-d H:i:s', $c->date_reg)
            ->format('Y-m-d H:i:s');
        })
        ->make(true);

    }
  }

  public function downloadDTSuspendedHistory(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->addMonth()
          ->endOfDay()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->format('Y-m-d H:i:s');
      }

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_historico_suspensiones';

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }

  /*******************************************************************/
  /*Ventas Coppel*/
  public function coppelSales()
  {
    $html = view('pages.ajax.report.coppel_sales')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTCoppelSales(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])
          ->endOfDay()
          ->addMonth()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
      }

      $data = Coppel::getCoppelSales($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return Carbon::createFromFormat('Y-m-d H:i:s', $c->date_reg)
            ->format('Y-m-d H:i:s');
        })
        ->editColumn('status', function ($c) {
          switch ($c->status) {
            case 'I':
              return "En Proceso";
            case 'S':
              return "Procesado";
            case 'EA':
              return "Con Error de Altan";
            case 'EC':
              return "Con Error de Coppel";
            case 'T':
              return "Eliminado";
          }
        })
        ->make(true);

    }
  }

  public function downloadDTCoppelSales(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->addMonth()
          ->endOfDay()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->format('Y-m-d H:i:s');
      }

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_ventas_coppel';

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }
/*********************************************************/
/*Ventas de JELOU*/
  public function getFromSalesJelou()
  {
    $html = view('pages.ajax.report.salesJelou')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTJelouSales(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      $typeDate = $filters['typeDate'];
      $operador = $filters['operador'];
      $conciliado = $filters['conciliado'];
      $deliveryFull = $filters['deliveryFull'];
      $currier = $filters['currier'];
      $viewFail = $filters['listFail'];

      $filters = $this->validateDate($filters);

      $input = [
        'typeDate' => $filters['typeDate'],
        'dateStar' => $filters['dateStar'],
        'dateEnd' => $filters['dateEnd'],
        'operador' => $operador,
        'conciliado' => $conciliado,
        'deliveryFull' => $deliveryFull,
        'currier' => $currier,
        'viewFail' => $viewFail];

      $datos = TempCar::getSalesJelou($input);
      //Log::info("datos: " . $datos[0]);
      //return DataTables::of($datos ? [$datos] : [])
      //
      /*
      ->addColumn('operadorLogistico', function ($culumn) {
      $folio = substr($culumn->folio, 0, 3);
      if ($folio == "VOY") {
      return "VoyWey";
      } else {
      if ($folio == "NET") {
      return "Prova";
      } else {
      return "99Min";
      }
      }
      })
       */
      return DataTables::of($datos)
        ->editColumn('statusDN', function ($c) {
          switch ($c->statusDN) {
            case 'A':
              return "Activo";
              break;
            case 'I':
              return "Inactivo";
              break;
            case 'S':
              return "Suspendido";
              break;
            case 'S/N':
              return "S/N";
              break;
            case 'Por activar...':
              return "Por activar...";
              break;
            default:
              return "Status desconocido";
              break;
          }
        })
        ->editColumn('typeDN', function ($c) {
          switch ($c->typeDN) {
            case 'H':
              return "HBB";
              break;
            case 'M':
              return "Mifi";
              break;
            case 'MH':
              return "Mifi Huella";
              break;
            default:
              return "Tipo desconocido";
              break;
          }
        })
        ->editColumn('telfClient', function ($c) {
          if (!empty($c->telfClient)) {
            return $c->telfClient;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('date_sales', function ($c) {
          if (!empty($c->date_sales)) {
            if ($c->date_sales != 'S/N' && $c->date_sales != 'En camino...' && $c->date_sales != 'Por activar...') {
              $date = date_create($c->date_sales);
              return date_format($date, "d-m-Y H:i:s");
            }
            return $c->date_sales;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('date_delivery', function ($c) {
          if (!empty($c->date_delivery)) {
            if ($c->date_delivery != 'S/N' && $c->date_delivery != 'En camino...' && $c->date_delivery != 'Por activar...') {
              $date = date_create($c->date_delivery);
              return date_format($date, "d-m-Y H:i:s");
            }
            return $c->date_delivery;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('date_conciliado', function ($c) {
          if (!empty($c->date_conciliado)) {
            if ($c->date_conciliado != 'S/N' && $c->date_conciliado != 'En camino...' && $c->date_conciliado != 'Por activar...') {
              $date = date_create($c->date_conciliado);
              return date_format($date, "d-m-Y H:i:s");
            }
            return $c->date_conciliado;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('release_date', function ($c) {
          if (!empty($c->release_date)) {
            if ($c->release_date != 'S/N' && $c->release_date != 'En camino...' && $c->release_date != 'Por activar...') {
              $date = date_create($c->release_date);
              return date_format($date, "d-m-Y H:i:s");
            }
            return $c->release_date;
          } else {
            return 'N/A';
          }
        })
        ->make(true);
    }
  }

  public function downloadDTJelouSales(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $filters = $request->all();
      $typeDate = $filters['typeDate'];
      $operador = $filters['operador'];
      $conciliado = $filters['conciliado'];
      $deliveryFull = $filters['deliveryFull'];
      $currier = $filters['currier'];
      $viewFail = $filters['listFail'];

      $filters = $this->validateDate($filters);

      $Filterinput = [
        'typeDate' => $filters['typeDate'],
        'dateStar' => $filters['dateStar'],
        'dateEnd' => $filters['dateEnd'],
        'operador' => $operador,
        'conciliado' => $conciliado,
        'deliveryFull' => $deliveryFull,
        'currier' => $currier,
        'viewFail' => $viewFail];

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_JelouSales';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters = json_encode($Filterinput);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }
/*END Ventas de JELOU*/
/*********************************************************/

/*******************************************************************/
  /*Pedido Solicitado*/
  public function getOrderRequest()
  {
    $html = view('pages.ajax.report.order_request')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTOrderRequest(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])
          ->endOfDay()
          ->addMonth()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->toDateTimeString();
      }

      $data = StockProvaDetail::getOrderRequest($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return Carbon::createFromFormat('Y-m-d H:i:s', $c->date_reg)->format('d-m-Y H:i:s');
        })
        ->editColumn('status', function ($c) {
          $estatus = "";
          switch ($c->status) {
            case 'A':$estatus = "Sin Procesar";break;
            case 'E':$estatus = "Con Error";break;
            case 'P':$estatus = "Asignado a Coordinador";break;
            case 'AS':$estatus = "Asignado a Regional";break;
            case 'PR':$estatus = "Reciclaje";break;
            case 'T':$estatus = "Eliminado";break;
          }
          return $estatus;
        })
        ->editColumn('recicler_status', function ($c) {
          if (empty($c->recicler_status)) {
            return "N/A";
          } else {
            $estatus = "";
            switch ($c->recicler_status) {
              case 'C':$estatus = "Creado";break;
              case 'F':$estatus = "Procesado sufijo";break;
              case 'P':$estatus = "Agregado a inventario";break;
              case 'M':$estatus = "Solicitud manual";break;
              case 'E':$estatus = "Error";break;
              case 'R':$estatus = "Rechazado";break;
            }
            return $estatus;
          }
        })
        ->editColumn('last_user_action', function ($c) {
          if (!empty($c->last_user_action)) {
            return $c->last_user_action;
          } else {
            return "N/A";
          }

        })
        ->editColumn('reg_date_action', function ($c) {
          if (!empty($c->reg_date_action)) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $c->reg_date_action)->format('d-m-Y H:i:s');
          } else {
            return "N/A";
          }

        })
        ->editColumn('coo_date_action', function ($c) {
          if (!empty($c->coo_date_action)) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $c->coo_date_action)->format('d-m-Y H:i:s');
          } else {
            return "N/A";
          }

        })
        ->editColumn('comment', function ($c) {
          if (!empty($c->comment)) {
            return $c->comment;
          } else {
            return "N/A";
          }

        })
        ->make(true);

    }
  }

  public function downloadDTOrderRequest(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::now()
          ->addMonth()
          ->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->subMonth()
          ->startOfDay()
          ->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->addMonth()
          ->endOfDay()
          ->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])
          ->startOfDay()
          ->format('Y-m-d H:i:s');

        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])
          ->endOfDay()
          ->format('Y-m-d H:i:s');
      }

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_pedido_solicitado';

      unset($filters['_token']);

      $report->filters = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user = session('user')->email;
      $report->status = 'C';
      $report->date_reg = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }
  /*********************************************************/

}
