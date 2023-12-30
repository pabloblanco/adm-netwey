<?php

namespace App\Http\Controllers;

use App\FiberInstallation;
use App\Helpers\CommonHelpers;
use App\Inventory;
use App\KPISDismissal;
use App\Paguitos;
use App\TelmovPay;
use App\Reports;
use App\SellerInventoryTrack;
use App\UserLocked;
use App\FiberZone;
use Carbon\Carbon;
use DataTables;
use Illuminate\Http\Request;

class ReportsThreeController extends Controller
{
  //Retorna vista para reporte de usuarios bloqueados
  public static function lockedUsers(Request $request)
  {
    $html = view('pages.ajax.report.locked_users')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  //Genera dataTable para reporte de usuarios bloqueados
  public function getUsersLDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = UserLocked::getReport($request->all());

      return DataTables::eloquent($data)
        ->editColumn('name_user', function ($data) {
          return $data->name_user . ' ' . $data->last_name_user;
        })
        ->editColumn('name_dolockuser', function ($data) {
          return $data->name_dolockuser . ' ' . $data->last_name_dolockuser;
        })
        ->editColumn('name_dounlockuser', function ($data) {
          if (!empty($data->name_dounlockuser)) {
            return $data->name_dounlockuser . ' ' . $data->last_name_dounlockuser;
          }

          return 'N/A';
        })
        ->editColumn('date_locked', function ($data) {
          return date('d-m-Y H:i:s', strtotime($data->date_locked));
        })
        ->editColumn('date_unlocked', function ($data) {
          if (!empty($data->date_unlocked)) {
            return date('d-m-Y H:i:s', strtotime($data->date_unlocked));
          }

          return 'N/A';
        })
        ->editColumn('days', function ($data) {
          if (!empty($data->date_unlocked)) {
            $dateb = Carbon::createFromFormat('Y-m-d H:i:s', $data->date_unlocked);
            $datea = Carbon::createFromFormat('Y-m-d H:i:s', $data->date_locked);

            return $dateb->diffInDays($datea);
          }

          return 'N/A';
        })
        ->make(true);
    }
  }

  //Guarda solicitud para generación de reporte de usaurios bloqueados
  public function downloadgetUsersLDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_usuarios_bloqueados';

      unset($inputs['emails']);
      unset($inputs['_token']);

      $report->filters      = json_encode($inputs);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
  }

  /*******************************************************************/
  /*Pedido Solicitado*/
  public function getInventoryTracks()
  {
    $html = view('pages.ajax.report.inventory_tracks')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDNInInventory(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q)) {
        $numbers = Inventory::select('msisdn')
          ->where([
            ['status', '!=', 'T'],
            ['msisdn', 'like', $request->q . '%']])
          ->limit(10);

        $numbers = $numbers->get();

        return response()->json(array('success' => true, 'dns' => $numbers));
      }

      return response()->json(array('success' => false));
    }
  }

  public function getDTInventoryTracks(Request $request)
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

      $data = SellerInventoryTrack::getInventoryTracks($filters);

      return DataTables::of($data)
        ->make(true);

    }
  }

  public function getDtInventoryTracksDetails(Request $request)
  {

    $report = SellerInventoryTrack::getInventoryTracksDetails($request->id);

    $html = '

        <table id="" class="table table-striped dataTable no-footer my-0 p-0 comps-detail" role="grid" aria-describedby="" style=" margin-left:5.915% !important; width: CALC(100% - 5.915%) !important;">
            <thead>
                <tr role="row" style="background:#FFF">
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Fecha
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Origen
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Destino
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Ejecutado por
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Observación
                    </th>
                </tr>
            </thead>
            <tbody>
        ';

    foreach ($report as $key => $item) {

      $html .= '<tr role="row" class="odd">';
      $html .= '<td>' . $item->date_reg . '</td>';
      if (!empty($item->origin_user)) {
        $html .= '<td>' . $item->origin_user . '</td>';
      } else {
        $html .= '<td>' . $item->origin_wh . '</td>';
      }

      if (!empty($item->destination_user)) {
        $html .= '<td>' . $item->destination_user . '</td>';
      } else {
        $html .= '<td>' . $item->destination_wh . '</td>';
      }

      if (!empty($item->assigned_by)) {
        $html .= '<td>' . $item->assigned_by . '</td>';
      } else {
        $html .= '<td> SISTEMA </td>';
      }

      $html .= '<td>' . $item->comment . '</td>';
      $html .= '</td>';
    }

    $html .= '</tbody></table>';

    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function downloadDTInventoryTracks(Request $request)
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

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_movimiento_inventario';

      unset($filters['_token']);

      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }

  //Retorna la vista de inventario en las bodegas de merma
  public function mermaInventory(Request $request)
  {
    $html = view('pages.ajax.report.inventory_merma')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  //Retorna el datatable para la consulta de inventario en merma
  public function getInventoryMermaDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = Inventory::getReportMerma($request->all());

      return DataTables::of($data)
        ->editColumn('origin_name', function ($data) {
          return !empty($data->origin_name) ? $data->origin_name : 'S/I';
        })
        ->editColumn('assigned_name', function ($data) {
          return !empty($data->assigned_name) ? $data->assigned_name : 'S/I';
        })
        ->editColumn('date_reg', function ($data) {
          if (!empty($data->date_reg)) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $data->date_reg)
              ->format('Y-m-d H:i');
          }

          return 'S/I';
        })
        ->make(true);
    }
  }

  //Guarda solicitud de reporte para inventario en bodegas merma
  public function downloadgetInventoryMermaDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_inventario_merma';

      unset($inputs['emails']);
      unset($inputs['_token']);

      $report->filters      = json_encode($inputs);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }

  /*******************************************************************/
  /*Instalaciones de fibra*/
  public function getFiberInstallations()
  {
    $coverage = FiberZone::getfiberZone();
    $html = view('pages.ajax.report.fiber_installations', compact('coverage'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDNFiberInstallations(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->q)) {
        $numbers = FiberInstallation::select('msisdn')
          ->where([
            ['status', '!=', 'T'],
            ['msisdn', 'like', '%' . $request->q . '%']])
          ->limit(10);

        $numbers = $numbers->get();

        return response()->json(array('success' => true, 'dns' => $numbers));
      }

      return response()->json(array('success' => false));
    }
  }

  public function getDTFiberInstallations(Request $request)
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

      $data = FiberInstallation::getFiberInstallations($filters);

      return DataTables::of($data)
        ->make(true);

    }
  }

  public function getDTFiberInstallationsDetails(Request $request)
  {

    $report = FiberInstallation::getFiberInstallationsDetails($request->group_install);

    $html = '

        <table id="" class="table table-striped dataTable no-footer my-0 p-0 comps-detail" role="grid" aria-describedby="" style=" margin-left:5.915% !important; width: CALC(100% - 5.915%) !important;">
            <thead>
                <tr role="row" style="background:#FFF">
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Instalador
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Teléfono Instalador
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Fecha Pre-Venta
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Fecha Reprogramacion
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Estado
                    </th>
                </tr>
            </thead>
            <tbody>
        ';

    foreach ($report as $key => $item) {

      $html .= '<tr role="row" class="odd">';
      $html .= '<td>' . $item->installer . '</td>';
      $html .= '<td>' . $item->installer_phone . '</td>';
      $html .= '<td>' . $item->date_instalation . '</td>';
      if (!empty($item->date_rescheduling)) {
        $html .= '<td>' . $item->date_rescheduling . '</td>';
      } else {
        $html .= '<td>N/A</td>';
      }

      switch ($item->status) {
        case 'A':$html .= '<td>En proceso</td>';
          break;
        case 'R':$html .= '<td>Reprogramado</td>';
          break;
        case 'P':$html .= '<td>Instalado</td>';
          break;
        case 'T':$html .= '<td>Eliminado</td>';
          break;
      }
      $html .= '</tr>';
    }

    $html .= '</tbody></table>';

    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function downloadDTFiberInstallations(Request $request)
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

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_instalaciones_de_fibra';

      unset($filters['_token']);

      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }

  /*KPI Articulos Perdidos*/
  public function getDTKPIDismissal(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      $data    = KPISDismissal::getDTKPI($filters);
      return DataTables::of($data)
        ->make(true);
    }
  }

  public function downloadDTKPIDismissal(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_kpi_articulos_perdidos';

      unset($filters['_token']);

      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
    return response()->json(array('error' => true));
  }

  /*******************************************************************/
  //Paguitos
  public function paguitos(Request $request)
  {
    $html = view('pages.ajax.report.paguitos')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getPaguitosDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      //Validando que vengan los dos rangos de fechas y formateando fecha
      $filters = CommonHelpers::validateDate($filters);
      $data    = Paguitos::getReport($filters);

      return DataTables::of($data)
        ->editColumn('nameCoordFull', function ($data) {
          if (!empty($data->nameCoordFull)) {
            return $data->nameCoordFull;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('nameSellerFull', function ($data) {
          if (!empty($data->nameSellerFull)) {
            return $data->nameSellerFull;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('nameClientFull', function ($data) {
          if (!empty($data->nameClientFull)) {
            return $data->nameClientFull;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('initial_amount', function ($data) {
          return '$' . round($data->initial_amount, 2);
        })
        ->editColumn('total_amount', function ($data) {
          return '$' . round($data->total_amount, 2);
        })
        ->editColumn('init_amount', function ($data) {
          return '$' . round(($data->total_amount - $data->initial_amount), 2);
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
  public function downloadPaguitosReport(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();
      $inputs = CommonHelpers::validateDate($inputs);

      $report = Reports::getConnect('W');

      $report->name_report = 'report_financiamiento_paguitos';

      unset($inputs['emails']);
      unset($inputs['_token']);

      $report->filters      = json_encode($inputs);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
  }

  //END Paguitos


  // ========== START Reporte de fibra por estatus
  public function getFiberInstallationByStatus(Request $request)
  {
    $html = view('pages.ajax.report.fiber_installation_by_status')->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getInstallationReportByStatus(Request $request){

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

      $data = FiberInstallation::getFiberInstallationsReportByStatus($filters);
      return DataTables::of($data)
        ->make(true);

    }
  }

  public function downloadReportInstallationsByStatus(Request $request){
    if($request->isMethod('post')){
        $filters = $request->all();

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

        $report = Reports::getConnect('W');
        $report->name_report = 'reporte_instalacion_fibra_estatus';

        unset($filters['_token']);

        $report->filters = json_encode($filters);
        $report->user_profile = session('user')->profile->type;
        $report->user = session('user')->email;
        $report->status = 'C';
        $report->date_reg = date('Y-m-d H:i:s');

        $report->save();
        return response()->json(array('error' => false));
    }
}

  // ========== END Reporte de fibra por estatus



  /*******************************************************************/
  //TelmovPay
  public function telmovPay(Request $request)
  {
    $html = view('pages.ajax.report.telmov-pay')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getTelmovPayDt(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      //Validando que vengan los dos rangos de fechas y formateando fecha
      $filters = CommonHelpers::validateDate($filters);
      $data    = TelmovPay::getReport($filters);

      return DataTables::of($data)
        ->editColumn('nameCoordFull', function ($data) {
          if (!empty($data->nameCoordFull)) {
            return $data->nameCoordFull;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('nameSellerFull', function ($data) {
          if (!empty($data->nameSellerFull)) {
            return $data->nameSellerFull;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('nameClientFull', function ($data) {
          if (!empty($data->nameClientFull)) {
            return $data->nameClientFull;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('initial_amount', function ($data) {
          return '$' . round($data->initial_amount, 2);
        })
        ->editColumn('total_amount', function ($data) {
          return '$' . round($data->total_amount, 2);
        })
        ->editColumn('init_amount', function ($data) {
          return '$' . round(($data->total_amount - $data->initial_amount), 2);
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
  public function downloadTelmovPayReport(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();
      $inputs = CommonHelpers::validateDate($inputs);

      $report = Reports::getConnect('W');

      $report->name_report = 'report_financiamiento_telmov_pay';

      unset($inputs['emails']);
      unset($inputs['_token']);

      $report->filters      = json_encode($inputs);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('error' => false));
    }
  }

  //END TelmovPay

}
