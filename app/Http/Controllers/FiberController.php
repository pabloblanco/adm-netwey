<?php

namespace App\Http\Controllers;

use App\FiberCityZone;
use App\FiberZone;
use App\Helpers\API815;
use App\Reports;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class FiberController extends Controller
{
  public function ViewListFiber()
  {
    $html = view('pages.ajax.fiber.listZones')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function ListZone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $data = FiberZone::getfiberZone($filters);
      return DataTables::of($data)
        ->editColumn('name', function ($c) {
          return !empty($c->name) ? $c->name : 'S/N';
        })
        ->editColumn('url_api', function ($c) {
          return !empty($c->url_api) ? $c->url_api : 'S/N';
        })
        ->editColumn("configuration.owner", function ($c) {
          if (!empty($c->configuration['owner'])) {
            switch ($c->configuration['owner']) {
              case "N":
                return 'Netwey';
                break;
              case "V":
                return 'Velocom';
                break;
              default:
                return 'Owner no definido';
                break;
            }
          }
        })
        ->editColumn("configuration.collector", function ($c) {
          if (!empty($c->configuration['collector'])) {
            switch ($c->configuration['collector']) {
              case "I":
                return 'Instalador';
                break;
              case "V":
                return 'Vendedor';
                break;
              default:
                return 'collector no definido';
                break;
            }
          }
        })
        ->make(true);
    }
    return redirect()->route('root');
  }

  public function chekingZone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      if (!empty($filters['id'])) {

        $zona       = FiberZone::getfiberZone($filters);
        $disponible = false;
        if (count($zona) > 0) {

          $disponible = API815::verifyEndPointFiberZone($zona[0]->url_api);
        }
        if ($disponible) {
          $credencial = API815::verifyEndPointCredencial($filters['id']);

          if ($credencial) {
            return response()->json(['success' => true, 'title' => 'Endpoint OK', 'msg' => 'El endPoint ' . $zona[0]->url_api . ' funciona correctamente!', 'icon' => 'success']);
          }
          return response()->json(['success' => false, 'title' => 'Conexion fallo', 'msg' => 'El endPoint ' . $zona[0]->url_api . ' responde al llamado pero no se puede autenticar de forma correcta, por favor verificar el usuario y contrasena!', 'icon' => 'error']);
        } else {
          return response()->json(['success' => false, 'title' => 'Endpoint fallo', 'msg' => 'El endPoint ' . $zona[0]->url_api . ' no esta disponible, por favor verifique que la url este correcta!', 'icon' => 'error']);
        }
      }
      return response()->json(['success' => false, 'title' => 'Endpoint fallo', 'msg' => 'El endPoint no se puede verificar, intente mas tarde', 'icon' => 'error']);
    }
    return redirect()->route('root');
  }

  public function getDownloadZones(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'report_fiber_zone';

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
    return redirect()->route('root');
  }

  public function EncriptPass($inputs)
  {
    if ($inputs['password'] != $inputs['password2']) {
      $inputs['password'] = Crypt::encryptString($inputs['password']);
    }
    return $inputs;
  }

  public function DesEncriptPass($inputs)
  {
    if (!empty($inputs['password'])) {
      $inputs['password'] = Crypt::decryptString($inputs['password']);
    }
    return $inputs;
  }
  public function createZone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs  = $request->all();
      $newzone = FiberZone::registerZone($inputs);

      return response()->json(array('success' => true, 'msg' => 'Zona registrada exitosamente!', 'icon' => 'success'));
    }
    return redirect()->route('root');
  }

  public function updateZone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();
      if (!empty($inputs['id'])) {

        $inputs = self::EncriptPass($inputs);
        $inputs = self::DesEncriptPass($inputs);

        $updateZone = FiberZone::UpdateZone($inputs['id'], $inputs);
        return response()->json(array('success' => true, 'msg' => 'Zona actualizada exitosamente!', 'icon' => 'success'));
      }
      return response()->json(array('success' => false, 'msg' => 'No se pudo actualizar los datos de la zona!', 'icon' => 'error'));
    }
    return redirect()->route('root');
  }

  public function deleteZone(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();
      if (!empty($inputs['id'])) {
        $removezone = FiberZone::RemoverZone($inputs['id']);
        return response()->json(array('success' => true, 'title' => 'Zona eliminada!', 'msg' => '', 'icon' => 'success'));
      }
      return response()->json(array('success' => false, 'title' => 'Fallo la eliminación!', 'msg' => 'La zona no se pudo eliminar, intente mas tarde', 'icon' => 'error'));
    }
    return redirect()->route('root');
  }

  public function getDetailZona(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();

      if (!empty($inputs['id'])) {
        $data = FiberZone::getfiberZone($inputs);
        if (count($data) > 0) {
          //ENCRIPTACION//
          $valEncript = Crypt::encryptString($data[0]['param']['password']);
          $infoParam  = new \stdClass;
          $infoParam  = $data[0]['param'];

          $infoParam['password'] = $valEncript;
          $data[0]['param']      = $infoParam;
          //ENCRIPTACION//
          return response()->json(array('success' => true, 'msg' => $data[0], 'icon' => 'success'));
        }
      }
      return response()->json(['success' => false, 'title' => 'Problemas al consultar', 'msg' => 'Se presento un problema para consultar los detalles de la zona de fibra, por favor intente mas tarde', 'icon' => 'error']);
    }
    return redirect()->route('root');
  }

  /**************************************************/
  //Mapa de cobertura
  public function ViewSignalFiber()
  {
    $html = view('pages.ajax.fiber.fiberMap')->render();

    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }

  public function listViewMap(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $data = FiberCityZone::getListCityZone();
      return DataTables::of($data)

        ->editColumn("poligono.zoom", function ($c) {
          if (!empty($c->poligono['zoom'])) {
            return $c->poligono['zoom'];
          } else {
            return 'S/N';
          }
        })
        ->editColumn("status", function ($c) {
          if (!empty($c->status)) {
            switch ($c->status) {
              case "A":
                return 'Activo';
                break;
              case "I":
                return 'Inactivo';
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

  public function viewMap(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();
      if (!empty($inputs['id'])) {
        $idMapCiFi = $inputs['id'];
        session(['idMapCiFi' => $idMapCiFi]);
        $infoMap = FiberCityZone::getListCityZone($idMapCiFi);
        if (count($infoMap) > 0) {
          $infoMap = $infoMap[0];
          if (!empty($infoMap['poligono'])) {
            $pointCenter = FiberCityZone::getCoordCenter($infoMap['poligono']['poligono']);
          } else {
            $pointCenter = [];
          }

          $html = view('pages.ajax.fiber.viewMap', compact('infoMap'))->render();
          return response()->json(array('success' => true, 'html' => $html, 'poligono' => $infoMap['poligono'], 'pointCenter' => $pointCenter));
        }
      }
      return response()->json(array('success' => false, 'msg' => 'No se pudo obtener informacion del mapa'));
    }
    return redirect()->route('root');
  }

  public function loadMap(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if ($request->hasFile('filekml') && $request->file('filekml')->isValid()) {

        $inputs    = $request->all();
        $filekml   = $request->file('filekml');
        $fichero   = fopen($filekml, "r");
        $contenido = fread($fichero, filesize($filekml));
        $vowels    = array("\n", "\t");
        $contenido = str_replace($vowels, '', $contenido);

        $DataPoly = '';
        if (!empty($contenido)) {
          $findme   = '<coordinates>';
          $findme2  = '</coordinates>';
          $pos      = strpos($contenido, $findme) + 13;
          $pos2     = strpos($contenido, $findme2);
          $DataPoly = substr($contenido, $pos, $pos2 - $pos);
        }
        /**
         * Se recibe un campo con la siguiente combinacion
         * -98.91667191950921,19.37776513941691,0
         * lng y lat
         **/
        $poligono = array();
        $points   = explode(",0", $DataPoly);
        foreach ($points as $item) {
          if (!empty(trim($item))) {
            $coord = explode(",", trim($item));
            if (count($coord) > 0) {
              $point = array("lng" => floatval($coord[0]), "lat" => floatval($coord[1]));
              array_push($poligono, $point);
            }
          }
        }
        if (!empty($inputs['zoom'])) {
          $zoom = intval($inputs['zoom']);
        } else {
          $zoom = 12;
        }

        $PolyFullData = array("zoom" => $zoom, 'poligono' => $poligono);

        $sav = FiberCityZone::updatePoligono(session('idMapCiFi'), $PolyFullData);
        if ($sav && count($poligono) > 1) {
          if (!empty($poligono)) {
            $pointCenter = FiberCityZone::getCoordCenter($poligono);
          } else {
            $pointCenter = [];
          }
          return response()->json(array('success' => true, 'msg' => 'Datos de zona de cobertura actualizados', 'poligono' => $PolyFullData, 'pointCenter' => $pointCenter, 'id' => session('idMapCiFi')));
        }
        return response()->json(array('success' => false, 'msg' => 'Hubo un problema para procesar el archivo de cobertura'));
      }
    }
    return redirect()->route('root');
  }

  public function updateItemMap(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();
      if (!empty($inputs['id']) && !empty($inputs['status'])) {
        $sav = FiberCityZone::setStatusPoligono($inputs['id'], $inputs['status']);
        return response()->json(array('success' => true, 'msg' => 'Se actualizo el status de la zona de cobertura'));
      }
      return response()->json(array('success' => false, 'msg' => 'Hubo un problema para desactivar la zona de cobertura'));
    }
    return redirect()->route('root');
  }

  public function updateListMap(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $UpdateFiberCity = API815::doRequest('refresh-citys-fiber', 'GET');

      $FiberCity = json_decode($UpdateFiberCity['original'], true);

      $msj = "No hay datos que se puedan actualizar de las ciudades de cobertura de fibra. Intente mas tarde";

      if (isset($FiberCity['data']['eightFifteen']['processFail'])) {
        $dataApiFail = $FiberCity['data']['eightFifteen']['processFail'];
        if (!empty($dataApiFail)) {
          $msj = "Respuesta desde Api815. ";
          foreach ($dataApiFail as $itemApi) {
            $msj .= $itemApi . '. ';
          }
        }
      }
      if ($UpdateFiberCity['success']) {
        $msj = "Listado de ciudad con servicio de fibra actualizados!";

        return response()->json(array('success' => true, 'msg' => $msj, 'icon' => 'success'));
      }
      return response()->json(array('success' => false, 'msg' => $msj, 'icon' => 'warning'));
    }
    return redirect()->route('root');
  }
}
