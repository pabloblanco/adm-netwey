<?php

namespace App\Http\Controllers;

use App\AltanCode;
use App\BlimService;
use App\Broadband;
use App\Channel;
use App\Concentrator;
use App\FiberServiceZone;
use App\FiberZone;
use App\Helpers\API815;
use App\ListDns;
use App\Pack;
use App\PackPrices;
use App\Periodicity;
use App\Service;
use App\ServiceChanel;
use App\ServicesProm;
use Illuminate\Http\Request;

class ServiceController extends Controller
{

  public function view()
  {
    $periodicities = Periodicity::getConnect('R')->where(['status' => 'A'])->get();
    $broadbands    = Broadband::getConnect('R')->where(['status' => 'A'])->get();
    $services      = Service::getServicesFullData(['A', 'I'], false);
    $concentrators = Concentrator::getConnect('R')->select('id', 'name')->where('status', 'A')->get();

    $channels = Channel::getConnect('R')->select('id', 'name')->where('status', 'A')->get();
    $lists    = ListDns::getConnect('R')->select('name', 'id')->where('status', 'A')->get();
    $listsA   = ListDns::getConnect('R')->select('name', 'id')->where('status', 'A')->where('lifetime', '>', 0)->get();
    // $out = new \Symfony\Component\Console\Output\ConsoleOutput();
    // $out->writeln((String)json_encode($listsA));
    $blimservices = BlimService::getConnect('R')->select('name', 'id')->where('status', 'A')->get();

    $fiberzones = FiberZone::getfiberZone();

    $html = view('pages.ajax.services', compact('services', 'periodicities', 'broadbands', 'concentrators', 'channels', 'lists', 'listsA', 'blimservices', 'fiberzones'))->render();

    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  /*public function show () {
  }*/

  public function store(Request $request)
  {
    try {

      if (
        !empty($request->title) &&
        !empty($request->description) &&
        !empty($request->periodicity_id) &&
        !empty($request->type) &&
        !empty($request->service_type) &&
        !empty($request->status) &&
        ($request->type == 'P' && ((in_array($request->service_type, ['T', 'M', 'MH']) && !empty($request->codeAltanSuplementary)) || ($request->service_type == 'H' && !empty($request->codeAltan) && !empty($request->broadband)) || ($request->service_type == 'F' && !empty($request->serv_fiber_zone)))) ||
        ($request->type == 'A' && ((in_array($request->service_type, ['T', 'M', 'MH']) && !empty($request->codeAltan)) || ($request->service_type == 'H' && !empty($request->codeAltan) && !empty($request->broadband)) || ($request->service_type == 'F' && !empty($request->serv_fiber_zone)))) ||
        ($request->type == 'R' && (($request->service_type == 'H' && !empty($request->codeAltanSuplementary) && !empty($request->broadband))))
      ) {
        $service                 = Service::getConnect('W');
        $service->title          = $request->title;
        $service->description    = $request->description;
        $service->periodicity_id = $request->periodicity_id;
        $service->type           = $request->type;
        $service->service_type   = $request->service_type;

        if (!empty($request->price_pay)) {
          $service->price_pay = $request->price_pay;
        } else {
          $service->price_pay = 0;
        }

        if (!empty($request->gb)) {
          $service->gb = $request->gb;
        } else {
          $service->gb = 0;
        }

        if (!empty($request->sms)) {
          $service->sms = $request->sms;
        } else {
          $service->sms = 0;
        }

        if (!empty($request->min)) {
          $service->min = $request->min;
        } else {
          $service->min = 0;
        }

        if (!empty($request->is_band_twenty_eight)) {
          $service->is_band_twenty_eight = $request->is_band_twenty_eight;
        }

        if ($request->service_type == 'H') {
          $service->broadband = $request->broadband;
        }

        $service->supplementary = 'N';
        /*
         * Solo planes de telefonia y retencion de internet hogar poseen codigo altan suplementarios
         */
        if (($request->type == 'P' && ($request->service_type == 'M' || $request->service_type == 'MH'))
          || ($request->type == 'R' && $request->service_type == 'H')) {
          $service->codeAltan     = $request->codeAltanSuplementary;
          $service->supplementary = 'Y';
        } else {
          $service->codeAltan = $request->codeAltan;
        }
        /**/

        if (!empty($request->plan_type)) {
          $service->plan_type = $request->plan_type;
        }

        if ($request->service_type == 'F') {
          $service->codeAltan = null;
        }

        $service->status   = $request->status;
        $service->date_reg = date('Y-m-d H:i:s');
        $service->save();

        if ($request->service_type == 'F') {
          $arrServFibs = explode(',', $request->serv_fiber_zone);
          foreach ($arrServFibs as $key => $servFib) {
            $servf = explode('-', base64_decode($servFib));
            if (count($servf) == 2) {
              $service_fz                = FiberServiceZone::getConnect('W');
              $service_fz->fiber_zone_id = $servf[0];
              $service_fz->service_id    = $service->id;
              $service_fz->service_pk    = $servf[1];
              $service_fz->status        = $request->status;
              $service_fz->date_modified = date('Y-m-d H:i:s');
              $service_fz->save();
            }
          }
        }

        if ($request->service_type != 'F' && ($request->type == 'A' || ($request->type == 'P' && in_array($request->service_type, ['T', 'M', 'MH'])) || (in_array($request->type, ['R', 'P']) && $request->service_type == 'H'))) {

          if ($request->type == 'A' || ($request->type == 'P' && in_array($request->service_type, ['H', 'T']))) {
            $altan                = AltanCode::getConnect('W');
            $altan->services_id   = $service->id;
            $altan->codeAltan     = $service->codeAltan;
            $altan->supplementary = 'N';
            $altan->status        = $service->status;
            $altan->save();
          }

          if ($request->type != 'A' && $request->service_type != 'F') {
            $altanS                = AltanCode::getConnect('W');
            $altanS->services_id   = $service->id;
            $altanS->codeAltan     = $request->codeAltanSuplementary;
            $altanS->supplementary = 'Y';
            $altanS->status        = $service->status;
            $altanS->save();
          }
        }

        if ($request->type == 'P') {
          if ($request->plan_type == 'G') {
            if (!empty($request->chanels) && count($request->chanels)) {
              foreach ($request->chanels as $chanel) {
                $ser_ch             = ServiceChanel::getConnect('W');
                $ser_ch->id_channel = $chanel;
                $ser_ch->id_service = $service->id;
                $ser_ch->status     = 'A';
                $ser_ch->date_reg   = date('Y-m-d H:i:s');
                $ser_ch->save();
              }

            }

            if (!empty($request->conc) && count($request->conc)) {
              foreach ($request->conc as $conc) {
                $ser_ch                  = ServiceChanel::getConnect('W');
                $ser_ch->id_concentrator = $conc;
                $ser_ch->id_service      = $service->id;
                $ser_ch->status          = 'A';
                $ser_ch->date_reg        = date('Y-m-d H:i:s');
                $ser_ch->save();
              }

            }
          } else {
            if (!empty($request->lists) && count($request->lists)) {
              foreach ($request->lists as $list) {
                $ser_ch              = ServiceChanel::getConnect('W');
                $ser_ch->id_list_dns = $list;
                $ser_ch->id_service  = $service->id;
                $ser_ch->status      = 'A';
                $ser_ch->date_reg    = date('Y-m-d H:i:s');
                $ser_ch->save();
              }
            }
          }
        }
        if ($request->type == 'A') {
          if (!empty($request->listsA)) {
            $ser_ch              = ServiceChanel::getConnect('W');
            $ser_ch->id_list_dns = $request->listsA;
            $ser_ch->id_service  = $service->id;
            $ser_ch->status      = 'A';
            $ser_ch->date_reg    = date('Y-m-d H:i:s');
            $ser_ch->save();
          }
        }

        return response()->json(array('success' => true, 'msg' => 'El servicio ' . $service->title . ' se ha creado con exito', 'numError' => 0));
      }

      return response()->json(array('success' => false, 'msg' => 'Hubo un error creando el servicio', 'numError' => 0));

    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error creando el servicio.', 'errorMsg' => $e, 'numError' => 1));
    }
  }

  public function update(Request $request, $id)
  {
    try {
      if (
        !empty($request->title) &&
        !empty($request->description) &&
        !empty($request->periodicity_id) &&
        !empty($request->type) &&
        !empty($request->service_type) &&
        !empty($request->status) &&
        ($request->type == 'P' && ((in_array($request->service_type, ['T', 'M', 'MH']) && !empty($request->codeAltanSuplementary)) || ($request->service_type == 'H' && !empty($request->codeAltan) && !empty($request->broadband)) || ($request->service_type == 'F' && !empty($request->serv_fiber_zone)))) ||
        ($request->type == 'A' && ((in_array($request->service_type, ['T', 'M', 'MH']) && !empty($request->codeAltan)) || ($request->service_type == 'H' && !empty($request->codeAltan) && !empty($request->broadband)) || ($request->service_type == 'F' && !empty($request->serv_fiber_zone)))) ||
        ($request->type == 'R' && (($request->service_type == 'H' && !empty($request->codeAltanSuplementary) && !empty($request->broadband))))
      ) {
        $service = Service::getConnect('W')->find($id);

        AltanCode::where(['services_id' => $id])->update(['status' => 'T']);

        if ($request->service_type != 'F' && ($request->type == 'A' || ($request->type == 'P' && ($request->service_type == 'H' || $request->service_type == 'T')))) {
          $altan              = AltanCode::getConnect('W');
          $altan->services_id = $service->id;

          if ($request->service_type != 'F') {
            $altan->codeAltan = $request->codeAltan;
          } else {
            $altan->codeAltan = null;
          }

          $altan->supplementary = 'N';
          $altan->status        = 'A'; //$request->status;
          $altan->save();
        }

        if ($request->type != 'A' && $request->service_type != 'F') {
          $altanS                = AltanCode::getConnect('W');
          $altanS->services_id   = $service->id;
          $altanS->codeAltan     = $request->codeAltanSuplementary;
          $altanS->supplementary = 'Y';
          $altanS->status        = 'A'; //$request->status;
          $altanS->save();
        }

        if ($request->type == 'P') {
          ServiceChanel::getConnect('W')->where([
            ['id_service', $id],
            ['status', 'A'],
          ])->update(['status' => 'I']);

          if ($request->plan_type == 'G') {
            if (!empty($request->chanels) && count($request->chanels)) {
              foreach ($request->chanels as $chanel) {
                $ser_ch             = ServiceChanel::getConnect('W');
                $ser_ch->id_channel = $chanel;
                $ser_ch->id_service = $id;
                $ser_ch->status     = 'A';
                $ser_ch->date_reg   = date('Y-m-d H:i:s');
                $ser_ch->save();
              }

            }

            if (!empty($request->conc) && count($request->conc)) {
              foreach ($request->conc as $conc) {
                $ser_ch                  = ServiceChanel::getConnect('W');
                $ser_ch->id_concentrator = $conc;
                $ser_ch->id_service      = $id;
                $ser_ch->status          = 'A';
                $ser_ch->date_reg        = date('Y-m-d H:i:s');
                $ser_ch->save();
              }

            }
          } else {
            if (!empty($request->lists) && count($request->lists)) {
              foreach ($request->lists as $list) {
                $ser_ch              = ServiceChanel::getConnect('W');
                $ser_ch->id_list_dns = $list;
                $ser_ch->id_service  = $service->id;
                $ser_ch->status      = 'A';
                $ser_ch->date_reg    = date('Y-m-d H:i:s');
                $ser_ch->save();
              }
            }
          }
        }
        if ($request->type == 'A') {
          ServiceChanel::getConnect('W')->where([
            ['id_service', $id],
            ['status', 'A'],
          ])->update(['status' => 'I']);

          if (!empty($request->listsA)) {
            $ser_ch              = ServiceChanel::getConnect('W');
            $ser_ch->id_list_dns = $request->listsA;
            $ser_ch->id_service  = $service->id;
            $ser_ch->status      = 'A';
            $ser_ch->date_reg    = date('Y-m-d H:i:s');
            $ser_ch->save();
          }
        }

        $service->title          = $request->title;
        $service->description    = $request->description;
        $service->periodicity_id = $request->periodicity_id;
        $service->type           = $request->type;
        $service->service_type   = $request->service_type;
        $service->blim_service   = $request->blim_service;

        if (!empty($request->price_pay)) {
          $service->price_pay = $request->price_pay;
        } else {
          $service->price_pay = 0;
        }

        if (!empty($request->gb)) {
          $service->gb = $request->gb;
        } else {
          $service->gb = 0;
        }

        if (!empty($request->sms)) {
          $service->sms = $request->sms;
        } else {
          $service->sms = 0;
        }

        if (!empty($request->min)) {
          $service->min = $request->min;
        } else {
          $service->min = 0;
        }

        if (!empty($request->is_band_twenty_eight)) {
          $service->is_band_twenty_eight = $request->is_band_twenty_eight;
        } else {
          $service->is_band_twenty_eight = null;
        }

        if ($request->service_type == 'H') {
          $service->broadband = $request->broadband;
        }

        $service->supplementary = 'N';
        /*
         * Solo planes de telefonia y retencion de internet hogar poseen codigo altan suplementarios
         */
        if (($request->type == 'P' && ($request->service_type == 'M' || $request->service_type == 'MH')) ||
          ($request->type == 'R' && $request->service_type == 'H')) {
          $service->codeAltan     = $request->codeAltanSuplementary;
          $service->supplementary = 'Y';
        } else {
          $service->codeAltan = $request->codeAltan;
        }
        /**/

        if (!empty($request->plan_type)) {
          $service->plan_type = $request->plan_type;
        }

        // if (!empty($request->servEightFifteen) && $request->service_type == 'F') {
        //   $service->codeAltan = $request->servEightFifteen;
        // }

        $service->status   = $request->status;
        $service->date_reg = date('Y-m-d H:i:s');
        $service->save();

        if ($request->service_type == 'F') {

          $zonas       = array();
          $arrServFibs = explode(',', $request->serv_fiber_zone);
          foreach ($arrServFibs as $key => $servFib) {
            $servf = explode('-', base64_decode($servFib));
            if (count($servf) == 2) {
              array_push($zonas, $servf[0]);
            }
          }

          $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';
          $zonasAmb = FiberZone::getConnect('R')
            ->where('ambiente', $ambiente)
            ->pluck('id');

          FiberServiceZone::getConnect('W')
            ->where('service_id', $service->id)
            ->whereNotIn('fiber_zone_id', $zonas)
            ->whereIn('fiber_zone_id', $zonasAmb)
            ->update([
              'date_modified' => date('Y-m-d H:i:s'),
              'status'        => 'T',
            ]);

          $arrServFibs = explode(',', $request->serv_fiber_zone);
          foreach ($arrServFibs as $key => $servFib) {
            $servf = explode('-', base64_decode($servFib));
            if (count($servf) == 2) {

              $service_fz = FiberServiceZone::getConnect('W')
                ->updateOrCreate(
                  [
                    'fiber_zone_id' => $servf[0],
                    'service_id'    => $service->id,
                  ],
                  [
                    'service_pk'    => $servf[1],
                    'status'        => $request->status,
                    'date_modified' => date('Y-m-d H:i:s'),
                  ]
                );
            }
          }
        } else {
          FiberServiceZone::getConnect('W')
            ->where('service_id', $service->id)
            ->update([
              'status'        => 'T',
              'date_modified' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($request->status == 'I') {
          $idsP = PackPrices::getPacksIdByService($id);
          Pack::getConnect('W')->where('status', 'A')
            ->whereIn('id', $idsP->pluck('pack_id'))
            ->update(['status' => 'I']);

          $idsSP = ServicesProm::getConnect('R')->select('id')
            ->where([
              ['service_id', $service->id],
              ['status', 'A'],
            ])
            ->get();

          if (count($idsSP) > 0) {
            ServicesProm::getConnect('W')->whereIn('id', [$idsSP->pluck('id')])
              ->update(['status' => 'I']);

            Pack::getConnect('W')->where('status', 'A')
              ->whereIn('service_prom_id', $idsSP->pluck('id'))
              ->update(['service_prom_id' => null]);
          }

        }

        return response()->json(array('success' => true, 'msg' => 'El servicio ' . $service->title . ' ha sido actualizado con exito', 'numError' => 0));
      }

      return response()->json(array('success' => false, 'msg' => 'Hubo un error actualizando el servicio.', 'numError' => 0));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error actualizando el servicio', 'errorMsg' => $e, 'numError' => 1));
    }
  }

  public function destroy(Request $request, $id)
  {
    try {
      ServiceChanel::getConnect('W')->where([
        ['id_service', $id],
        ['status', 'A']]
      )->update(['status' => 'I']);

      $service         = Service::getConnect('W')->find($id);
      $service->status = 'T';
      $service->save();

      FiberServiceZone::getConnect('W')
        ->where('service_id', $service->id)
        ->update([
          'date_modified'  => date('Y-m-d H:i:s'),
          'status'         => 'T']);

      $idsP = PackPrices::getPacksIdByService($id);
      Pack::getConnect('W')->where('status', 'A')
        ->whereIn('id', $idsP->pluck('pack_id'))
        ->update(['status' => 'I']);

      PackPrices::getConnect('W')->whereIn('status', ['I', 'A'])
        ->where('service_id', $id)
        ->update(['status' => 'T']);

      $idsSP = ServicesProm::getConnect('R')->select('id')
        ->where([
          ['service_id', $service->id],
          ['status', 'A'],
        ])
        ->get();

      if (count($idsSP) > 0) {
        ServicesProm::getConnect('W')->whereIn('id', [$idsSP->pluck('id')])
          ->update(['status' => 'T']);

        Pack::getConnect('W')->where('status', 'A')
          ->whereIn('service_prom_id', $idsSP->pluck('id'))
          ->update(['service_prom_id' => null]);
      }

      return response()->json(array('success' => true, 'msg' => 'El servicio ' . $service->title . ' ha sido eliminado con exito', 'numError' => 0));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error eliminando el servicio', 'errorMsg' => $e, 'numError' => 1));
    }
  }

  //retorna serivios de retencion con periodos <= a numero de dias dado
  public function getServRetByPeriod(Request $request)
  {
    try {
      if (!empty($request->days)) {
        $dias     = $request->days;
        $serv_ret = Service::getConnect('R')->select('islim_services.id', 'islim_services.title', 'islim_periodicities.days')
          ->join('islim_periodicities', 'islim_services.periodicity_id', 'islim_periodicities.id')
          ->where('islim_services.type', 'R')
          ->where('islim_services.status', 'A')
          ->where('islim_periodicities.status', 'A')
          ->where('islim_periodicities.days', '<=', $dias)
          ->get();

        if (!empty($serv_ret)) {
          return response()->json(array('success' => true, 'ret_services' => $serv_ret));
        }

        return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando servicios', 'numError' => 1));
      }
      return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando servicios', 'numError' => 2));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando servicios', 'errorMsg' => $e, 'numError' => 3));
    }
  }

  public function getFiberServicesList(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $fiber_zone = FiberZone::getConnect('R')->find($request->fiber_zone_id);
      if (!empty($fiber_zone)) {

        $datarr               = array();
        $datarr['fiber_zone'] = $fiber_zone->id;

        $ListServicio815 = API815::doRequest("get-plans", 'POST', $datarr);

        $detailServicios = array();
        if ($ListServicio815['success']) {

          $ListServicio815 = $ListServicio815['data']['eightFifteen']['object'];

          foreach ($ListServicio815 as $Servicio815) {
            foreach ($Servicio815['field'] as $key => $field) {
              if ($field['attributes']['name'] == 'nombre') {
                array_push($detailServicios, ['id' => $Servicio815['attributes']['pk'], 'title' => $Servicio815['attributes']['model'] . '-' . $field['value'], 'model' => $Servicio815['attributes']['model'], 'value' => $field['value']]);
              }
            }
          }
        }
        return response()->json(array('success' => true, 'msg' => 'ok', 'data' => $detailServicios, 'numError' => 0));
      }
    }
    return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando servicios de fibra en zona', 'errorMsg' => 'Hubo un error consultando servicios de fibra en zona', 'numError' => 1));
  }

  public function getServiceFiberService(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {

      $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';

      $servsFZ = FiberServiceZone::getConnect('R')
        ->select(
          'islim_fiber_service_zone.fiber_zone_id',
          'islim_fiber_zone.name as fiber_zone_name',
          'islim_fiber_service_zone.service_pk as service_fz_pk'
        )
        ->join('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_fiber_service_zone.fiber_zone_id')
        ->where([
          ['islim_fiber_service_zone.service_id', $request->service_id],
          ['islim_fiber_zone.status', 'A'],
          ['islim_fiber_zone.ambiente', $ambiente],
        ])
        ->whereIn('islim_fiber_service_zone.status', ['A', 'I'])
        ->get();

      foreach ($servsFZ as $servFZ) {

        $servFZ->service_fz_name = '';

        $datarr               = array();
        $datarr['fiber_zone'] = $servFZ->fiber_zone_id;
        $datarr['pk']         = $servFZ->service_fz_pk;

        $Servicio815 = API815::doRequest("get-plans", 'POST', $datarr);

        if ($Servicio815['success']) {
          $Servicio815 = $Servicio815['data']['eightFifteen']['object'][0];
          foreach ($Servicio815['field'] as $key => $field) {
            if ($field['attributes']['name'] == 'nombre') {
              $servFZ->service_fz_name = $Servicio815['attributes']['model'] . '-' . $field['value'];
              $servFZ->model           = $Servicio815['attributes']['model'];
              $servFZ->value           = $field['value'];
            }
          }
        }
      }
      return response()->json(array('success' => true, 'msg' => 'ok', 'data' => $servsFZ, 'numError' => 0));
    }

    return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando detalle de servicio de fibra en zona', 'errorMsg' => 'Hubo un error consultando detalle de servicio de fibra en zona', 'numError' => 1));
  }
}
