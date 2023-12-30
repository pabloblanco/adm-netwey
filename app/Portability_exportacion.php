<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Portability_exportacion extends Model
{
  protected $table    = 'islim_soap_portability_result';
  protected $fillable = [
    'id',
    'msisdn',
    'status',
    'portID',
    'result',
    'type',
    'date_update',
    'dni_client',
    'reverse',
    'date_reg',
    'date_ported',
    'detail_error',
    'msisdn_sufijo'];
  protected $hidden     = [];
  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Portability_exportacion
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Portability_exportacion;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getDTPotabilityPeriod Lista las portabilidades de salida durante un periodo seleccionado]
 * @param  array  $filters [description]
 * @return [type]          [description]
 */
  public static function getDTPotabilityPeriod($filters = [])
  {

    $data = self::getConnect('R')
      ->select(
        'islim_soap_portability_result.id',
        'islim_soap_portability_result.msisdn',
        'islim_sales.id AS sales_id',
        'islim_sales.date_reg AS sales_date',
        'islim_soap_portability_result.date_reg AS port_date',
        'islim_soap_portability_result.portID',
        'islim_soap_portability_result.dni_client',
        DB::raw('CONCAT(islim_clients.name, " ", islim_clients.last_name) AS NameClient'),
        'islim_soap_portability_result.status',
        'islim_soap_portability_result.result'
      )
      ->join('islim_sales',
        'islim_sales.msisdn',
        'islim_soap_portability_result.msisdn_sufijo')
      ->join('islim_clients',
        'islim_clients.dni',
        'islim_soap_portability_result.dni_client')
      ->where([
        ['islim_soap_portability_result.type', 'S'],
        ['islim_soap_portability_result.status', '!=', 'T'],
        ['islim_sales.type', 'P']]);
    //->whereNotNull('islim_soap_portability_result.dni_client')
    //->whereNotNull('islim_soap_portability_result.msisdn_sufijo');

    if (is_array($filters)) {
      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data = $data->whereBetween('islim_soap_portability_result.date_reg',
          [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data = $data->where('islim_soap_portability_result.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data = $data->where('islim_soap_portability_result.date_reg', '<=', $filters['dateEnd']);
      }
    }

    if (!empty($filters['typePort'])) {
      $data = $data->where('result', $filters['typePort']);
    }

    $data = $data->orderBy('islim_soap_portability_result.date_reg', 'DESC')->get();
    return $data;

  }

}
