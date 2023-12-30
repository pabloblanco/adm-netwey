<?php
/*LJPD*/
namespace App\Http\Controllers;

use App\ADB_portability_details;
use App\Client;
use App\ClientNetwey;
use App\Helpers\CommonHelpers;
use App\Inventory;
use App\Inv_reciclers;
use App\Portability;
use App\Portability_exportacion;
use App\Portability_log;
use App\ProfileDetail;
use App\Reports;
use App\Sale;
use App\TelephoneCompany;
use App\User;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PortabilityController extends Controller
{
/***************** Importacion de portabilidades ******************************/
  public function portability_importacion()
  {
    $query  = CommonHelpers::getOptionColumn('islim_portability', 'status');
    $status = array();

    foreach ($query as $keydate) {
      switch ($keydate) {
        case 'A':
          array_push($status, array('code' => 'A', 'description' => 'Activo'));
          break;
        case 'C':
          array_push($status, array('code' => 'C', 'description' => 'Cancelado'));
          break;
        case 'P':
          array_push($status, array('code' => 'P', 'description' => 'Procesado'));
          break;
        case 'S':
          array_push($status, array('code' => 'S', 'description' => 'Solicitud Netwey'));
          break;
        case 'W':
          array_push($status, array('code' => 'W', 'description' => 'En proceso Netwey'));
          break;
        case 'E':
          array_push($status, array('code' => 'E', 'description' => 'Error'));
          break;
        case 'SS':
          array_push($status, array('code' => 'SS', 'description' => 'En proceso ADB'));
          break;
        case "IS":
          array_push($status, array('code' => 'IS', 'description' => 'Incidencia ADB'));
          break;
        case 'SA':
          array_push($status, array('code' => 'SA', 'description' => 'En proceso Altan'));
          break;
      }
    }
    $html = view('pages.ajax.portabilidad.importaciones', compact('status'))->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTPortImportPeriod(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      $filters = CommonHelpers::validateDate($filters);

      $data = Portability::getDTPotabilityPeriod($filters);
//Log::info('data: '.$data);
      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
        })
        ->editColumn('status', function ($c) {
          if (!empty($c->status)) {

            switch ($c->status) {
              case "A":
                return 'Activo';
                break;
              case "C":
                return 'Cancelado';
                break;
              case "P":
                return 'Procesado';
                break;
              case "S":
                return 'Solicitud Netwey';
                break;
              case "W":
                return 'En proceso Netwey';
                break;
              case "E":
                return 'Error';
                break;
              case "SS":
                return 'En proceso ADB';
                break;
              case "IS":
                return 'Incidencia ADB';
                break;
              case "SA":
                return 'En proceso Altan';
                break;
              default:
                return 'Desconocido';
                break;
            }
          }
          return 'N/A';
        })
        ->addColumn('Observation', function ($c) {
          if (isset($c->Observation) && !empty($c->Observation)) {
            return $c->Observation;
          } else {
            return 'N/A';
          }
        })
        ->addColumn('details_error', function ($c) {
          if (isset($c->details_error) && !empty($c->details_error)) {
            $searchText = array("'", "`");
            $textError  = str_replace($searchText, '', $c->details_error);
            return $textError;
          } else {
            return 'N/A';
          }
        })
        ->addColumn('portID', function ($c) {
          if (!empty($c->portID)) {
            return $c->portID;
          } else {
            return 'N/A';
          }
        })
        ->addColumn('boton_disable', function ($c) {
          return $c->boton_disable;
        })
        ->addColumn('latest_soap', function ($c) {
          return $c->latest_soap;
        })
        ->make(true);
    }
    return redirect()->route('root');

  }
  public function PortImportUpdateItem(Request $request)
  {
    /*El proceso de actualizacion de datos de portabilidad se realiza de forma asincrona por medio de command:updatePortability. Actualizacion de Agosto 2021 Luisj*/

    $dnTransitorio = $request->get('dnTransitorio');
    $dnaPortar     = $request->get('dnaPortar');

    $sale_id = $request->get('sale_id');

    $errors = '';

    //verifico que el Dn a portar no este en clientes
    $ban14_2 = ClientNetwey::existDN($dnaPortar);

    if (!empty($ban14_2)) {
      $dniCli = ClientNetwey::getDNIClient($dnaPortar);
      if (!empty($dniCli)) {
        $infoCli = Client::getInfoClient($dniCli->clients_dni);
        if (!empty($infoCli)) {
          $errors = 'El DN a portar: ' . $dnaPortar . ' esta registrado en Netwey con el siguiente Cliente. Dni: ' . $dniCli->clients_dni . ' - ' . $infoCli->name . ' ' . $infoCli->last_name . ' - ' . $infoCli->email;
        } else {
          $errors = 'El DN a portar: ' . $dnaPortar . ' esta registrado en BD como cliente Netwey.';
        }
      }

      Portability_log::setLogPotability($request->get('id'), 'islim_client_netweys - Gui', 'ERROR', $errors);
    }

    $ban13 = Sale::existDN($dnTransitorio);

    if (!empty($ban13->id)) {
      // Log::info("Ban13: " . $ban13->id . ' Sale: ' . $sale_id . 'DN' . $ban13->msisdn);
      if ($ban13->id != $sale_id) {
        $errors = 'El DN transitorio: ' . $dnTransitorio . ' esta bajo la orden de venta: ' . $ban13->id . ' el cual no corresponde con la alta registrada en la portabilidad, la orden de venta registrada : ' . $sale_id;

        Portability_log::setLogPotability($request->get('id'), 'islim_sales - Gui', 'ERROR', $errors);
      }
    }

    $id_portability  = $request->get('id');
    $Obs_portability = '';

    if (!empty($request->get('obser'))) {
      if ($request->get('obser') != 'false') {
        $Obs_portability = $request->get('obser');
      }
    }

    try {
      DB::table('islim_portability')->where('id', $id_portability)->update(['date_process' => date('Y-m-d H:i:s')]);

      if ($Obs_portability != '') {
        DB::table('islim_portability')->where('id', $id_portability)->update(['Observation' => $Obs_portability]);
      }

      if ($errors == '') {
        DB::table('islim_portability')->where('id', $id_portability)->update(['status' => 'S', 'details_error' => '']);

        Portability_log::setLogPotability($id_portability, 'islim_portability - Solicitud de proceso');

        return response()->json(['success' => true]);
      } else {
        DB::table('islim_portability')->where('id', $id_portability)->update(['status' => 'E', 'details_error' => $errors]);

        return response()->json([
          'success' => false,
          'error'   => $errors . '. Verifique la informacion antes de continuar.']);
      }
    } catch (Exception $e) {
      Portability_log::setLogPotability($id_portability, 'islim_portability - gui', 'ERROR', $e->getMessage());
      return response()->json([
        'success' => false,
        'error'   => $e]);
    }
  }

  public function PortImportSetObservation(Request $request)
  {
    $id_portability  = $request->get('id');
    $msj_portability = $request->get('msj');
    try {
      DB::table('islim_portability')->where('id', $id_portability)->update(['date_process' => date('Y-m-d H:i:s'), 'status' => 'C', 'Observation' => $msj_portability]);
      return response()->json(['success' => true]);
    } catch (Exception $e) {
      return response()->json(['error' => $e]);
    }
  }

  public function getDTPortImportDownload(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $status  = $filters['status'];
      $filters = CommonHelpers::validateDate($filters);
      //Log::info( $filters);
      $filters['status'] = $status;
      $report            = new Reports;
      $report            = Reports::getConnect('W');

      $report->name_report = 'report_portability_import';

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
   * [getDetailsSoap Retorna la informacion de los mensajes de portabilidad soap]
   * @param  Request $request [recibe el PortID]
   * @return [type]           [Informacion detallada de los mensajes]
   */
  public function getDetailsSoap(Request $request)
  {

    if ($request->isMethod('post')) {
      $filters = $request->all();
      $data    = ADB_portability_details::getInfoDetail($filters['portID']);

      return DataTables::of($data)
        ->editColumn('messageID_type', function ($c) {
          if (!empty($c->messageID_type)) {

            switch ($c->messageID_type) {
              case "S":
                return 'Enviado';
                break;
              case "R":
                return 'Recibido';
                break;
              default:
                return 'Desconocido';
                break;
            }
          }
          return 'N/A';
        })
        ->make(true);
    }
    return redirect()->route('root');

  }
/**
 * [PortImportSetCancelSoapItem Procesa la peticion de cancelacion de portabilidad ante ADB]
 * @param Request $request [description]
 */
  public function PortImportSetCancelSoapItem(Request $request)
  {
    $msisdn = $request->get('msisdn');
    $portID = $request->get('portID');

    $data = ADB_portability_details::getInfoForCancel($portID, $msisdn);

    if (!empty($data)) {
      return response()->json(['success' => true]);
    } else {
      return response()->json(['success' => false]);
    }
  }
  /**
   * [PortImportSetNewNIP Realiza actualizacion de codigo NIP]
   * @param Request $request [description]
   */
  public function PortImportSetNewNIP(Request $request)
  {
    $id_portability = $request->get('id');
    $newNIP         = $request->get('newNIP');
//Como se cambio el codigo NIP se actualiza la fecha de registro
    try {
      DB::table('islim_portability')->where('id', $id_portability)->update([
        'status'       => 'A',
        'nip'          => $newNIP,
        'date_process' => date('Y-m-d H:i:s'),
        'date_reg'     => date('Y-m-d H:i:s'),
        'Observation'  => 'NIP actualizado por: ' . session('user')->email]);
      return response()->json(['success' => true, 'error' => '']);
    } catch (Exception $e) {
      return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
    return response()->json(['success' => false, 'error' => '0']);
  }
  /**
   * [getStatusResult retorna si ultimo status del resultado de portabilidad es procesado por ALTAN]
   * @param  Request $request [description]
   * @return [type]           [description]
   */
  public function getStatusResult(Request $request)
  {
    $portID = $request->get('portID');
    try {
      $result = DB::table('islim_soap_portability_result')
        ->where([['portID', $portID],
          ['status', 'PA']])
        ->first();
      if (!empty($result)) {
        //dd($result['0']->status);
        //exit();
        //if ($result['0']->status == 'PA') {
        return response()->json(['success' => true, 'error' => '']);
        //}
      }
      return response()->json(['success' => false, 'error' => 'Portabilidad no ha sido procesada por Altan']);
    } catch (Exception $e) {
      return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
    return response()->json(['success' => false, 'error' => '0']);
  }

  public function PortImportSetReprocessInADB(Request $request)
  {
    $ID = $request->get('id');
    try {
      DB::table('islim_portability')->where('id', $ID)
        ->update(['status' => 'A',
          'date_process'     => date('Y-m-d H:i:s'),
          'Observation'      => 'Reprocesado por: ' . session('user')->email,
          'details_error'    => null, 'portID'      => null,
          'latest_soap'      => null, 'update_soap' => null,
          'boton_disable'    => 'N']);

      return response()->json(['success' => true, 'error' => '']);

    } catch (Exception $e) {
      return response()->json(['success' => false, 'error' => $e->getMessage()]);
    }
    return response()->json(['success' => false, 'error' => '0']);
  }
/**************** END importacion de portabilidades ***************************/

  public function portability_exportacion()
  {
    $query    = CommonHelpers::getOptionColumn('islim_soap_portability_result', 'result');
    $typePort = array();

    foreach ($query as $keydate) {
      array_push($typePort, array('code' => $keydate, 'description' => $keydate));
    }
    $html = view('pages.ajax.portabilidad.exportaciones', compact('typePort'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function getDTPortExportPeriod(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      $filters = CommonHelpers::validateDate($filters);

      $data = Portability_exportacion::getDTPotabilityPeriod($filters);

      return DataTables::of($data)
        ->editColumn('dni_client', function ($c) {
          return !empty($c->dni_client) ? $c->dni_client : 'S/N';
        })
        ->editColumn('NameClient', function ($c) {
          return !empty($c->NameClient) ? $c->NameClient : 'S/N';
        })
        ->editColumn('sales_id', function ($c) {
          return !empty($c->sales_id) ? $c->sales_id : 'S/N';
        })
        ->editColumn('sales_date', function ($c) {
          return !empty($c->sales_date) ? $c->sales_date : 'S/N';
        })
        ->editColumn('id', function ($c) {
          if (!empty($c->port_date)) {
            $date1 = new \DateTime($c->port_date);
            $hoy   = new \DateTime("now");
            $Dias  = $date1->diff($hoy)->format('%d');

            if ($Dias < 15) {
              //Son los 15 dias permitidos para solicitar reversos
              return true;
            }
            return false;
          } else {
            return false;
          }
        })
        ->editColumn('status', function ($c) {
          if (!empty($c->status)) {

            switch ($c->status) {
              case "PN":
                return 'Procesado Netwey';
                break;
              case "PA":
                return 'Procesado Altan';
                break;
              case "C":
                return 'Notificado por ADB';
                break;
              case "E":
                return 'Error';
                break;
            }
          }
          return 'N/A';
        })
        ->make(true);
    }
    return redirect()->route('root');

  }

  public function getDTPortExportDownload(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters  = $request->all();
      $typePort = $filters['typePort'];
      $filters  = CommonHelpers::validateDate($filters);
      //Log::info( $filters);
      $filters['typePort'] = $typePort;
      $report              = new Reports;
      $report              = Reports::getConnect('W');

      $report->name_report = 'report_portability_export';

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
####### Nueva portacion desde call center ######
  /**
   * [viewFromNewPortability Crea el formulario de pre-solicitud de portabilidad]
   * @param  Request $request [recibo el DN que envio el call center]
   * @return [type]           [regreso el formulario a ser visto en el tab]
   */
  public function viewFromNewPortability(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      if (session('DNTrans') != null) {
        $DNTrans = session('DNTrans');
      } else {
        $DNTrans = trim((String) $filters['msisdn']);
      }
      $idSales = Sale::existDN($DNTrans);
      $salesP  = false;

      if (!empty($idSales)) {
        $salesP = true;
      }

      $inprocess = Portability::inProcess($DNTrans);
      session(['DNTrans' => $DNTrans]);
      $permitirPort = "Lo sentimos, para realizar esta acción requieres ser un ejecutivo o un supervisor de Call-Center";
      $infoJefe     = null;
      $sinSuperior  = null;
      if (empty($inprocess)) {
        //Significa que no hay portabilidades con el dn transitorio que es el que tiene actualmente. En este caso luego de verificar el DN y continuar se debe solicitar la contrasena del supervisor de call-center
        //Si es ejecutivo, indico quien es el supervisor para que pida la contrasena del mismo.

        $isSupervisor = null;
        $isEjecutive  = ProfileDetail::getUserCallCenter(13, session('user')->email);

        if (!empty($isEjecutive)) {
          //es un ejecutivo, busco el supervisor de este usuario
          if (!empty(session('user')->parent_email)) {
            $isSupervisor = ProfileDetail::getUserCallCenter(21, session('user')->parent_email);

            if (!empty($isSupervisor)) {
              $permitirPort = null;
              $infoJefe     = $isSupervisor;
            }
          } else {
            //El ejecutivo no tiene supervisor
            $sinSuperior  = "El usuario: ( " . session('user')->email . " ) requiere tener un Supervisor de Call-Center para poder continuar el proceso de solicitud de portabilidad.";
            $permitirPort = null;
          }
        } else {
          //Reviso si es supervisor de call center
          $isSupervisor = ProfileDetail::getUserCallCenter(21, session('user')->email);
          if (!empty($isSupervisor)) {
            $permitirPort = null;
          }
        }

        if (empty($isSupervisor) && empty($isEjecutive)) {
          //No es ni ejecutivo ni supervisor, reviso si es un super usuario
          $isSuper = ProfileDetail::getUserCallCenter(1, session('user')->email);
          if (!empty($isSuper)) {
            $permitirPort = null;
          }
        }
      }
      $html = view('pages.ajax.portabilidad.preProcessNewPortability', compact('DNTrans', 'inprocess', 'salesP', 'permitirPort', 'infoJefe', 'sinSuperior'))->render();
      return response()->json(['success' => true, 'htmlCode' => $html, 'numError' => 0]);}
    return redirect()->route('root');
  }

/**
 * [portChekingDNPort se revisa el dn a portar]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function portChekingDNPort(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      $dnPort  = $filters['dnPort'];
      //Debo revisar que el DN a portar no este en inventario
      $invPort = Inventory::existDN($dnPort);
      if (empty($invPort)) {
        //El Dn no existe en inventario
        session(['dnPort' => $dnPort]);
        return ['success' => true, 'msg' => 'El msisdn ' . $dnPort . ' esta permitido utilizarse como numero a portar. Puedes verificar otro DN o continuar el proceso de portación'];
      } else {
        //Hay registros previos del DN a portar en netwey, se debe estudiar el caso si se puede reciclar o no.

        $infoReciclers           = array();
        $infoReciclers['msisdn'] = $dnPort;

        $permit = array("RECICLER", "DN_NOTFOUND_ALTAN", "DN_NOT_CLIENT");

        $analisis = Inv_reciclers::Verify_msisdn_recicler($infoReciclers, 'call_center');

        if (in_array($analisis['code'], $permit)) {
          //se reciclo y se permite continuar
          session(['dnPort' => $dnPort]);

          return ['success' => true, 'msg' => 'El msisdn ' . $dnPort . ' se envio a reciclar, ya que existia registros previos, puedes continuar el proceso de solicitud de portación'];

        } else {
          $msg = "";
          if (!empty($analisis['msg'])) {
            $msg = ' +Detalles: ' . $analisis['msg'];
          }
          return ['success' => false, 'msg' => 'El msisdn ' . $dnPort . ' se envio a reciclar, ya que existia registros previos, pero requiere de revisión para continuar el proceso de solicitud de portación con este DN.' . $msg];
        }
        return ['success' => false, 'msg' => 'Existe registros del DN ' . $dnPort . ' en el sistema. (' . $invPort->status . ')'];
      }
    }
    return redirect()->route('root');
  }

/**
 * [chekingSupervisor Valida la contrasena del supervisor de call center para procesar la peticion de portabilidad iniciada por el ejecutivo de call center]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function chekingSupervisor(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $parent = User::select('password')->where(['email' => session('user')->parent_email, 'status' => 'A'])->first();

      if (!empty($parent)) {

        if (Hash::check($request->pass, $parent->password)) {
          return response()->json([
            'success' => true,
            'msg'     => 'authorize',
          ]);
        } else {
          return response()->json([
            'success' => false,
            'msg'     => 'Clave errada',
          ]);
        }
      } else {
        return response()->json([
          'success' => false,
          'msg'     => 'Supervisor no encontrado',
        ]);
      }
    }
    return redirect()->route('root');
  }

/**
 * [portSuccessNew Formulario de portacion con el DN transitorio y a portar verificados solo de registrar los datos faltantes de solicitud]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function portSuccessNew(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $DNTrans     = session('DNTrans');
      $DNPort      = session('dnPort');
      $infoCompany = TelephoneCompany::getListCompany();

      $html = view('pages.ajax.portabilidad.requestPortability', compact('DNTrans', 'DNPort', 'infoCompany'))->render();
      return response()->json(['success' => true, 'htmlCode' => $html, 'numError' => 0]);
    }
    return redirect()->route('root');
  }

/**
 * [sendFromNewPortability Registro la informacion ya procesada y que es enviada para ser portada]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function sendFromNewPortability(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters  = $request->all();
      $DNTrans  = session('DNTrans');
      $dnPort   = session('dnPort');
      $operator = $filters['operator'];
      $PortNIP  = trim($filters['PortNIP']);

      $Register = Portability::newPortability($DNTrans, $dnPort, $operator, $PortNIP);
      if ($Register['success']) {
        return ['success' => true];
      } else {
        return ['success' => false, 'msg' => $Register['msg']];
      }
    }
    return redirect()->route('root');
  }
}
