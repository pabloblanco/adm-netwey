<?php

namespace App\Http\Controllers;

use App\AssignedSales;
use App\Helpers\APIProva;
use App\Helpers\CommonHelpers;
use App\Inventory;
use App\Inv_preAssigne;
use App\KPISDismissal;
use App\LowEvidences;
use App\LowRequest;
use App\PayInstallment;
use App\Reports;
use App\SaleInstallmentDetail;
use App\SellerInventory;
use App\SellerInventoryTrack;
use App\User;
use App\UserDeliveryAddress;
use DataTables;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LowController extends Controller
{
  /**
   * [ViewListRequest Carga la vista inicial]
   */
  public function ViewListRequest()
  {
    $html = view('pages.ajax.low.listRequest')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

/**
 * [getUserFilterLow Busca los nombres de los usuarios que solicitaron baja]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getUserFilterLow(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $name = $request->input('name');
      if (!empty($name)) {
        $users = LowRequest::GetUsersSearchList($name);
        return response()->json(array('success' => true, 'users' => $users));
      }
      return response()->json(array('success' => false));
    }
  }

/**
 * [getDTListRequestLow Solicitud de bajas]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getDTListRequestLow(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      $filters = CommonHelpers::validateDate($filters);
      $data    = LowRequest::GetListRequest($filters);

      return DataTables::of($data)
        ->addColumn('id', function ($c) {
          return $c->id;
        })
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : 'S/N';
        })
        ->editColumn('cash_request', function ($c) {
          return !empty($c->cash_request) ? '$ ' . $c->cash_request : '$ 0';
        })
        ->editColumn('days_cash_request', function ($c) {
          return !empty($c->days_cash_request) ? $c->days_cash_request : '0';
        })
        ->editColumn('article_request', function ($c) {
          return !empty($c->article_request) ? '$ ' . $c->article_request : '$ 0';
        })
        ->editColumn('cash_abonos', function ($c) {
          return !empty($c->cash_abonos) ? '$ ' . $c->cash_abonos : '$ 0';
        })
        ->editColumn('cant_abonos', function ($c) {
          return !empty($c->cant_abonos) ? $c->cant_abonos : '0';
        })
        ->editColumn('cash_total', function ($c) {
          return !empty($c->cash_total) ? '$ ' . $c->cash_total : '$ 0';
        })
        ->addColumn('cant_evidenci', function ($c) {
          return !empty($c->cant_evidenci) ? $c->cant_evidenci : '0';
        })->make(true);
    }
    return redirect()->route('root');
  }

/**
 * [getRequestDownload Solicitud de descargar archivo]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getRequestDownload(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $filters = CommonHelpers::validateDate($filters);
      //Log::info( $filters);

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'user_low_request';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }

/**
 * [getEvidenceRequestLow Obtiene la lista de archivos de evidencia de una solicitud]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getEvidenceRequestLow(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters   = $request->all();
      $Evidences = LowEvidences::getEvidence($filters['idLow']);
      return DataTables::of($Evidences)
        ->addColumn('id', function ($c) {
          return $c->id;
        })->make(true);
    }
    return redirect()->route('root');
  }

/**
 * [setRejectionLow Se rechazo la solicitud de baja]
 * @param Request $request [description]
 */
  public function setRejectionLow(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters         = $request->all();
      $idLow           = $filters['id'];
      $motiveRejectLow = $filters['msj'];
      $data            = LowRequest::SetUpdateRequest($idLow, 'reject', $motiveRejectLow);
      return response()->json(['success' => true, 'msg' => 'Se ha guardado el motivo del rechazo de la baja']);
    }
    return redirect()->route('root');
  }

/**
 * [setAceptLow Se acepta la baja del usuario]
 * @param Request $request [description]
 */
  public function setAceptLow(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters           = $request->all();
      $idLow             = $filters['id'];
      $filters['status'] = 'R';

      $mailLow = LowRequest::GetRequest($filters);

      if (!empty($mailLow)) {
        $mailLow = $mailLow->user_dismissal;
      } else {
        return response()->json(['success' => false, 'msg' => "No pudo ser procesado la peticion!"]);
      }

      $infoUser = User::getConnect('R')
        ->where('email', $mailLow)
        ->first();

      if (!empty($infoUser)) {
        if (empty($infoUser->parent_email)) {
          //NO tiene supervisor
          $subordinados = User::getConnect('R')
            ->where('parent_email', $mailLow)
            ->first();

          if (!empty($subordinados)) {
            //Tiene al menos un usuario por debajo
            return response()->json(['success' => false, 'msg' => "El usuario " . $mailLow . " posee otros usuarios que supervisa y no se puede asignar automaticamente a otro responsable"]);
          }
        } else {
          //Usuario con supervisor

          //Se debe reasignar los usuarios que este por debajo se pasan al superior inmediato que este activo
          //
          //Se verifica que el superior este activo caso contrario no se puede asignar y se cancela
          $infoSup = User::getConnect('R')
            ->where('email', $infoUser->parent_email)
            ->first();

          $banReasinarUser = false;
          if (!empty($infoSup)) {
            if ($infoSup->status == 'A') {
              $banReasinarUser = true;
            }
          }
          $subordinados = User::getConnect('R')
            ->where('parent_email', $mailLow)
            ->get();

          if (!empty($subordinados) && $banReasinarUser) {
            $banReasinarUser = true;
          } elseif (!empty($subordinados)) {
            return response()->json(['success' => false, 'msg' => "El usuario " . $infoUser->parent_email . " es el superior de " . $mailLow . " y no se encuentra activo para realizar de forma automatica la recepcion de usuarios."]);
          }
          //
          //Antes de cambiar de superior se debe rechazar la recepcion de dinero
          //
          if (!empty($subordinados)) {
            foreach ($subordinados as $userInferior) {
              $salesEfecti = AssignedSales::getListAssigneSaleLow($mailLow, $userInferior->email);
              $salesAbono  = PayInstallment::getInstallmentReceptionLow($mailLow, $userInferior->email);

              $reasonReject = "Rechazada la recepcion de dinero debido a que " . $mailLow . " se encuentra en proceso de baja de Netwey";

              if (!empty($salesEfecti)) {
                foreach ($salesEfecti as $itemEfecti) {
                  AssignedSales::aceptReceptionVULow(
                    $itemEfecti->id,
                    $mailLow,
                    [
                      'date_reject' => date('Y-m-d H:i:s'),
                      'reason'      => $reasonReject,
                      'view'        => 'N',
                      'status'      => 'I']);
                }
              }
              if (!empty($salesAbono)) {
                foreach ($salesAbono as $itemAbono) {
                  $reports = PayInstallment::getListReception($itemAbono->id_report, $mailLow);
                  $date    = date('Y-m-d H:i:s');

                  foreach ($reports as $sale) {
                    PayInstallment::updateRecptionStatus($sale->id, [
                      'status'      => 'R',
                      'date_update' => $date,
                      'date_acept'  => null,
                      'reason'      => $reasonReject,
                      'view'        => 'N']);

                    SaleInstallmentDetail::updateRecptionStatus($sale->sale_installment_detail, [
                      'conciliation_status' => 'CV',
                      'date_update'         => $date]);
                  }
                }
              }
            }
          }
          if ($banReasinarUser && !empty($subordinados)) {
            //Se le pasan los subordinados al superior del usaurio que se esta dando de baja
            User::setUpdateParentUsers($mailLow, $infoUser->parent_email);
          }
          //se actualiza el status del usuario que se esta dando de baja
          User::setStatusLowAcept($mailLow, 'D');
          //actualizo la tabla de solicitud de bajas
          LowRequest::SetUpdateRequest($idLow, 'acept');
        }
        return response()->json(['success' => true, 'msg' => "Usuario " . $mailLow . " ha sido dado de baja exitosamente!"]);
      } else {
        return response()->json(['success' => false, 'msg' => "Usuario " . $mailLow . " no pudo ser procesado!"]);
      }
    }
    return redirect()->route('root');
  }

  //Reporte de bajas
  public function ViewListReport(Request $request)
  {
    $title    = " Reporte de bajas";
    $showPlus = true;
    $query    = CommonHelpers::getOptionColumn('islim_request_dismissal', 'status');
    $status   = array();

    foreach ($query as $keydate) {
      switch ($keydate) {
        case 'R':
          array_push($status, array('code' => 'R', 'description' => 'Solicitada'));
          break;
        case 'P':
          array_push($status, array('code' => 'P', 'description' => 'En proceso'));
          break;
        case 'F':
          array_push($status, array('code' => 'F', 'description' => 'Finalizada'));
          break;
        case 'D':
          array_push($status, array('code' => 'D', 'description' => 'Rechazada'));
          break;
      }
    }
    $html = view('pages.ajax.low.listInProcess_finish', compact('title', 'showPlus', 'status'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

/**
 * [getDTListLowReport Consulta de reporte de bajas]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getDTListLowReport(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      $filters = CommonHelpers::validateDate($filters);
      $data    = LowRequest::GetListProcess($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : 'S/N';
        })
        ->editColumn('date_step1', function ($c) {
          return !empty($c->date_step1) ? date("d-m-Y H:i:s", strtotime($c->date_step1)) : 'S/N';
        })
        ->editColumn('date_step2', function ($c) {
          return !empty($c->date_step2) ? date("d-m-Y H:i:s", strtotime($c->date_step2)) : 'S/N';
        })
        ->editColumn('cash_total', function ($c) {
          return !empty($c->cash_total) ? '$ ' . $c->cash_total : '$ 0';
        })
        ->editColumn('article_request', function ($c) {
          return !empty($c->article_request) ? '$ ' . $c->article_request : '$ 0';
        })
        ->editColumn('cash_request', function ($c) {
          return !empty($c->cash_request) ? '$ ' . $c->cash_request : '$ 0';
        })
        ->editColumn('cash_abonos', function ($c) {
          return !empty($c->cash_abonos) ? '$ ' . $c->cash_abonos : '$ 0';
        })
        ->editColumn('reason_deny', function ($c) {
          return !empty($c->reason_deny) ? $c->reason_deny : 'S/N';
        })
        ->editColumn('status', function ($c) {
          if (!empty($c->status)) {

            switch ($c->status) {
              case "R":
                return 'Solicitada';
                break;
              case "P":
                return 'En proceso';
                break;
              case "F":
                return 'Finalizada';
                break;
              case "D":
                return 'Rechazada';
                break;

              default:
                return 'Status Desconocido';
                break;
            }
          }
          return 'N/A';
        })
        ->editColumn('discounted_amount', function ($c) {
          return !empty($c->discounted_amount) ? '$ ' . $c->discounted_amount : 'N/A';
        })
        ->editColumn('mount_liquidacion', function ($c) {
          return !empty($c->mount_liquidacion) ? '$ ' . $c->mount_liquidacion : 'N/A';
        })
        ->editColumn('residue_amount', function ($c) {
          return !empty($c->residue_amount) ? '$ ' . $c->residue_amount : '$ 0';
        })
        ->editColumn('cash_discount_total', function ($c) {
          return !empty($c->cash_discount_total) ? '$ ' . $c->cash_discount_total : '$ 0';
        })
        ->editColumn('date_liquidacion', function ($c) {
          return !empty($c->date_liquidacion) ? $c->date_liquidacion : 'N/A';
        })
        ->make(true);
    }
    return redirect()->route('root');
  }
  public function getDTListReportDownload(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters    = $request->all();
      $statusCash = $filters['statusCash'];
      $filters    = CommonHelpers::validateDate($filters);
      //Log::info( $filters);

      $filters['statusCash'] = $statusCash;
      $report                = new Reports;
      $report                = Reports::getConnect('W');

      $report->name_report = 'user_low_report';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }

//Vista de carga de finiquitos de bajas
  public function ViewListLowFinish(Request $request)
  {
    $title    = " bajas por finiquitar";
    $viewFile = true;

    $html = view('pages.ajax.low.listInProcess_finish', compact('title', 'viewFile'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }
/**
 * [getDTListLowFiniquite consulta de finiquitos de bajas]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getDTListLowFiniquite(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters              = $request->all();
      $filters              = CommonHelpers::validateDate($filters);
      $filters['statusLow'] = 'P'; //P=Baja en proceso
      $data                 = LowRequest::GetListProcess($filters);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : 'S/N';
        })
        ->editColumn('date_step1', function ($c) {
          return !empty($c->date_step1) ? date("d-m-Y H:i:s", strtotime($c->date_step1)) : 'S/N';
        })
        ->editColumn('cash_total', function ($c) {
          return !empty($c->cash_total) ? '$ ' . $c->cash_total : '$ 0';
        })
        ->editColumn('residue_amount', function ($c) {
          return !empty($c->residue_amount) ? '$ ' . $c->residue_amount : '$ 0';
        })
        ->editColumn('cash_discount_total', function ($c) {
          return !empty($c->cash_discount_total) ? '$ ' . $c->cash_discount_total : '$ 0';
        })

        ->make(true);
    }
    return redirect()->route('root');
  }

  /**
   * [getDTListFiniquiteDownload Descargar bajas en finiquito]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function getDTListFiniquiteDownload(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters              = $request->all();
      $statusCash           = $filters['statusCash'];
      $filters['statusLow'] = 'P'; //P=Baja en proceso
      $filters              = CommonHelpers::validateDate($filters);
      //Log::info( $filters);

      $filters['statusCash'] = $statusCash;
      $report                = new Reports;
      $report                = Reports::getConnect('W');

      $report->name_report = 'user_low_process';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }

  public function setUpFiniquite(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      //Columnas: email, monto_liquidacion, fecha_finiquito, monto_descontado
      $linePost   = 0;
      $postUpdate = 0;
      $mailFail   = '';
      if ($request->hasFile('file_csv')) {
        $data = Excel::load($request->file('file_csv')->getRealPath(), function ($reader) {})->get();

        foreach ($data as $row) {
          $linePost++;

          $numValid1 = true;
          if (empty($row->monto_liquidacion)) {
            if ($row->monto_liquidacion == 0) {
              $numValid1 = true;
            } else {
              $numValid1 = false;
            }
          }

          $numValid2 = true;
          if (empty($row->monto_descontado)) {
            if ($row->monto_descontado == 0) {
              $numValid2 = true;
            } else {
              $numValid2 = false;
            }
          }
          if (empty($row->email) || !$numValid2
            || empty($row->fecha_finiquito) || !$numValid1
            || count($row) != 4) {
            return ['success' => false, 'msg' => "Formato erroneo. Error en linea (" . $linePost . ")." . PHP_EOL . "Verifica que el archivo cuenta con las columnas:" . PHP_EOL . " [ email, monto_liquidacion, fecha_finiquito, monto_descontado ] " . PHP_EOL . "Por favor corrige el archivo y vuelve a intentar."];
          }
          $regFinish = LowRequest::getConnect('W')
            ->where([['user_dismissal', $row->email],
              ['status', 'P']])
            ->orderBy('date_reg', 'DESC')
            ->first();

          if (!empty($regFinish)) {
            $postUpdate++;
            $fechanew = $row->fecha_finiquito;
            if (str_contains($row->fecha_finiquito, "/")) {
              $fechanew = str_replace("/", "-", $fechanew);
            } elseif (str_contains($row->fecha_finiquito, ".")) {
              $fechanew = str_replace(".", "-", $fechanew);
            }
            $timestamp = strtotime($fechanew);
            $fecha     = date("Y-m-d", $timestamp);

            $regFinish->date_liquidacion  = $fecha;
            $regFinish->mount_liquidacion = $row->monto_liquidacion;
            $regFinish->discounted_amount = $row->monto_descontado;
            $regFinish->status            = 'F';
            $regFinish->date_step2        = date('Y-m-d H:i:s');
            $regFinish->user_finish       = session('user')->email;
            $regFinish->save();

            //Se debe retirar el inventario que quedo asociado al vendedor y se pasa a bodega merma//
            $msjLow = "Removido por baja de usuario";
            //obtengo los equipo activos que estan asignados
            $invActive = SellerInventory::getInventaryAssigne($row->email);

            //Se quita las presignaciones
            Inv_preAssigne::setRemovePreassigneLow($row->email);
            if (!empty($invActive) && $invActive->count() > 0) {
              //Se elimina el inventario
              SellerInventory::setRemoveInventoryLow($row->email, $msjLow);

              //se cambio de bodega
              foreach ($invActive as $itemInv) {
                //print_r($itemInv->inv_arti_details_id);
                //tabla de restreo de movimiento
                SellerInventoryTrack::setInventoryTrack(
                  $itemInv->inv_arti_details_id,
                  $row->email,
                  null,
                  null,
                  env('WH_MERMA_LOW'),
                  session('user')->email,
                  $msjLow
                );
                //Nuevo bodega donde estara el equipo
                Inventory::setNewWarehouseLow($itemInv->inv_arti_details_id, $msjLow);
              }
            }
            //Se actualiza el usuario a eliminado logico
            User::setStatusLowAcept($row->email, 'T');

            //Eliminando dirección en prova
            UserDeliveryAddress::deleteReg($row->email);
            APIProva::deleteUser($row->email);

          } else {
            $mailFail .= $row->email . ', ';
          }
        }
        return ['success' => true, 'msg' => "Se realizo finiquito de " . $postUpdate . " de " . $linePost . " registros cargados en el archivo.", "EmailNoprocess" => !empty($mailFail) ? "Los siguientes email no estan en status de procesado para finiquito: " . $mailFail : ""];
      }
      return ['success' => false, 'msg' => "Se trato de cargar un archivo invalido"];
    }
    return redirect()->route('root');
  }

  //Listado KPI de descuento de equipos perdidos
  public function ViewListKPIDismissal(Request $request)
  {

    // $last_periodo = KPISDismissal::getConnect('R')
    //               ->select(
    //                 DB::raw('YEAR(MAX(DATE(CONCAT(year,"-",month,"-01")))) as year'),
    //                 DB::raw('MONTH(MAX(DATE(CONCAT(year,"-",month,"-01")))) as month')
    //               )
    //               ->where('status','A')
    //               ->first();

    $years = KPISDismissal::getConnect('R')
      ->select(
        DB::raw('DISTINCT(year) as year')
      )
      ->where('status', 'A')
      ->get();

    $months = KPISDismissal::getConnect('R')
      ->select(
        DB::raw('DISTINCT(month) as month')
      )
      ->where('status', 'A')
      ->get();

    $html = view('pages.ajax.low.listKPIDismissal', compact('years', 'months'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getMonthsAvailables(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      if (!empty($request->year)) {

        $months = KPISDismissal::getConnect('R')
          ->select(
            DB::raw('DISTINCT(month) as month')
          )
          ->where('year', $request->year)
          ->where('status', 'A')
          ->get();

        return ['success' => true, 'months' => $months];
      }
      return ['success' => false, 'msg' => "Ocurrio un error", 'req' => $request->year];
    }
    return redirect()->route('root');
  }

}
