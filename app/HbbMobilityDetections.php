<?php

namespace App;

use App\Sale;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class HbbMobilityDetections extends Model
{
  protected $table = 'islim_hbb_mobility_detections';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\HbbMobilityDetections
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new HbbMobilityDetections;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getGracePeriodDataReport($filters = [])
  {

    $data = self::getConnect('R')
                ->select(
                  'islim_hbb_mobility_detections.msisdn',
                  'islim_hbb_mobility_detections.detc_timestamp as date_pg',
                  DB::raw('CONCAT(ROUND(islim_hbb_mobility_detections.home_lat,4),", ",ROUND(islim_hbb_mobility_detections.home_lon,4)) as point_act'),
                  DB::raw('CONCAT(ROUND(islim_hbb_mobility_detections.detc_lat,4),", ",ROUND(islim_hbb_mobility_detections.detc_lon,4)) as point_pg'),
                  DB::raw('ROUND(islim_hbb_mobility_detections.dits_home_tf,2) as distance'),
                  'islim_clients.user_mail as vendor',
                  'islim_hbb_mobility_detections.status'
                )
                ->join('islim_client_netweys','islim_client_netweys.msisdn','=','islim_hbb_mobility_detections.msisdn')
                ->join('islim_clients','islim_clients.dni','=','islim_client_netweys.clients_dni');
                //->whereIn('islim_hbb_mobility_detections.status', ['A']);

    if (is_array($filters)) {
      if(!empty($filters['msisdn_select'])){
        $data->whereIn('islim_hbb_mobility_detections.msisdn',$filters['msisdn_select']);
      }
      if(!empty($filters['dateb']) && !empty($filters['datee'])){
        $data->whereBetween('islim_hbb_mobility_detections.detc_timestamp', [$filters['dateb'], $filters['datee']]);
      }elseif(!empty($filters['dateb'])){
        $data->where('islim_hbb_mobility_detections.detc_timestamp', '>=', $filters['dateb']);
      }elseif(!empty($filters['datee'])){
        $data->where('islim_hbb_mobility_detections.detc_timestamp', '<=', $filters['datee']);
      }
    }

    $data = $data->orderBy('islim_hbb_mobility_detections.detc_timestamp', 'DESC')->get();
    return $data;

  }
}
