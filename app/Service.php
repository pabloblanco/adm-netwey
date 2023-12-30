<?php

namespace App;

use App\Broadband;
use App\Periodicity;
use App\ServiceChanel;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
  protected $table = 'islim_services';

  protected $fillable = [
    'id',
    'periodicity_id',
    'codeAltan',
    'title',
    'description',
    'price_pay',
    'price_remaining',
    'broadband',
    'supplementary',
    'date_reg',
    'status',
    'type',
    'method_pay',
    'gb',
    'plan_type',
    'service_type',
    'primary_service',
    'type_hbb',
    'min',
    'sms',
    'is_band_twenty_eight'];

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
      $obj = new Service;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getActiveServiceByType($type = false, $type_service = false, $band_te = false, $fiber_zones = false)
  {
    $data = self::getConnect('R')
      ->select(
        'islim_services.id',
        'islim_services.title',
        'islim_services.description',
        'islim_services.price_pay'
      );

    if ($type) {
      if ($type == 'F') {
        $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';
        $data     = $data->join('islim_fiber_service_zone', function ($join) use ($ambiente) {
          $join->on('islim_services.id', '=', 'islim_fiber_service_zone.service_id')
            ->where('islim_fiber_service_zone.status', 'A');
        })->join('islim_fiber_zone', function ($join) use ($ambiente, $fiber_zones) {
          $join->on('islim_fiber_zone.id', '=', 'islim_fiber_service_zone.fiber_zone_id')
            ->where('islim_fiber_zone.status', 'A')
            ->where('islim_fiber_zone.ambiente', $ambiente);
          if ($fiber_zones) {
            $join = $join->whereIn('islim_fiber_zone.id', $fiber_zones);
          }
        });
      }
    }

    $data = $data->where('islim_services.status', 'A');

    if ($type) {
      $data = $data->where('islim_services.service_type', $type);
    }

    if ($type_service) {
      $data = $data->where('islim_services.type', $type_service);
    }

    if ($band_te) {
      $data = $data->where('islim_services.is_band_twenty_eight', $band_te);
    }

    return $data->get();
  }

  public static function getServicesFullData($status = [], $type = false)
  {
    if (count($status)) {
      $data = self::getConnect('R')
        ->select(
          'islim_periodicities.periodicity',
          'Islim_broadbands.num_broad',
          'islim_altan_codes.codeAltan as sup',
          'islim_services.*',
          'islim_blim_services.name as blim_service_name'
        )
        ->join(
          'islim_periodicities',
          'islim_periodicities.id',
          'islim_services.periodicity_id'
        )
        ->leftJoin('Islim_broadbands', function ($join) {
          $join->on(
            'Islim_broadbands.broadband',
            'islim_services.broadband'
          )
            ->where('Islim_broadbands.status', 'A');
        })
        ->leftJoin('islim_altan_codes', function ($join) {
          $join->on(
            'islim_altan_codes.services_id',
            'islim_services.id'
          )
            ->where([
              ['islim_altan_codes.status', 'A'],
              ['islim_altan_codes.supplementary', 'Y'],
            ]);
        })
        ->leftJoin(
          'islim_blim_services',
          'islim_blim_services.id',
          'islim_services.blim_service'
        )
        ->where([
          ['islim_periodicities.status', 'A'],
        ])
        ->whereIn('islim_services.status', $status);

      if ($type) {
        $data->where('islim_services.type', $type);
      }

      //  $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
      //     return is_numeric($binding) ? $binding : "'{$binding}'";
      // })->toArray());

      // Log::info($query);

      $data = $data->orderBy('islim_services.id', 'DESC')->get();

      foreach ($data as $service) {
        $service->concentrators = ServiceChanel::getConcService($service->id);
        $service->channels      = ServiceChanel::getChService($service->id);
        $service->lists         = ServiceChanel::getListService($service->id);
      }

      return $data;
    }

    return [];
  }

  public static function getServices($status, $type)
  {
    $services;
    if (!isset($type)) {
      $services = Service::where('status', $status)->get();
    } else {
      $services = Service::where(['status' => $status, 'type' => $type])->get();
    }
    foreach ($services as $service) {
      $service->periodicity           = Periodicity::where(['status' => 'A', 'id' => $service->periodicity_id])->first();
      $service->broadband             = Broadband::where(['status' => 'A', 'broadband' => $service->broadband])->first();
      $codeAltanSuplementary          = AltanCode::select('codeAltan')->where(['services_id' => $service->id, 'status' => 'A', 'supplementary' => 'Y'])->first();
      $service->codeAltanSuplementary = isset($codeAltanSuplementary) ? $codeAltanSuplementary->codeAltan : null;

      $service->concentrators = ServiceChanel::select('islim_concentrators.id', 'islim_concentrators.name')
        ->join(
          'islim_concentrators',
          'islim_concentrators.id',
          '=',
          'islim_service_channel.id_concentrator'
        )
        ->where([
          ['islim_service_channel.status', 'A'],
          ['islim_service_channel.id_service', $service->id],
          ['islim_concentrators.status', 'A'],
        ])
        ->get();

      $service->channels = ServiceChanel::select('islim_channels.id', 'islim_channels.name')
        ->join(
          'islim_channels',
          'islim_channels.id',
          '=',
          'islim_service_channel.id_channel'
        )
        ->where([
          ['islim_service_channel.status', 'A'],
          ['islim_service_channel.id_service', $service->id],
          ['islim_channels.status', 'A'],
        ])
        ->get();

      $service->lists = ServiceChanel::select('islim_list_dns.id', 'islim_list_dns.name')
        ->join(
          'islim_list_dns',
          'islim_list_dns.id',
          '=',
          'islim_service_channel.id_list_dns'
        )
        ->where([
          ['islim_service_channel.status', 'A'],
          ['islim_service_channel.id_service', $service->id],
          ['islim_list_dns.status', 'A'],
        ])
        ->get();
    }
    return $services;
  }

  public static function getService($id, $status)
  {
    $service              = Service::where(['id' => $id, 'status' => $status])->first();
    $service->periodicity = Periodicity::where(['status' => 'A'])->first();
    $service->broadband   = Broadband::where(['status' => 'A', 'broadband' => $service->broadband])->first();
    return $service;
  }

  public static function getPeriodicity($id_service = false)
  {
    if ($id_service) {
      return self::getConnect('R')
        ->select(
          'islim_services.codeAltan',
          'islim_services.periodicity_id',
          'islim_periodicities.periodicity',
          'islim_periodicities.days')
        ->join(
          'islim_periodicities',
          'islim_periodicities.id',
          'islim_services.periodicity_id'
        )
        ->where('islim_services.id', $id_service)
        ->first();
    }

    return null;
  }

  public static function getPeriodicityFibra($id_service = false, $fiberZone = false)
  {
    if ($id_service && $fiberZone) {
      return self::getConnect('R')
        ->select(
          'islim_fiber_service_zone.service_pk AS codeAltan',
          'islim_services.periodicity_id',
          'islim_periodicities.periodicity',
          'islim_periodicities.days')
        ->join(
          'islim_periodicities',
          'islim_periodicities.id',
          'islim_services.periodicity_id'
        )
        ->join('islim_fiber_service_zone',
          'islim_fiber_service_zone.service_id',
          'islim_services.id')
        ->where([
          ['islim_services.id', $id_service],
          ['islim_fiber_service_zone.status', 'A'],
          ['islim_fiber_service_zone.fiber_zone_id', $fiberZone]])
        ->first();
    }
    return null;
  }

  public static function getDetailService($id_service)
  {
    return self::getConnect('R')
      ->select('title', 'description')
      ->where('id', $id_service)
      ->first();
  }
}
