<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KPISDismissal extends Model
{
  protected $table    = 'islim_kpis_dismissal';
  protected $fillable = [
    'regional_email',
    'coordinator_email',
    'year',
    'month',
    'old_articles',
    'decrease_articles',
    'assigned_articles',
    'kpi_result',
    'lost_articles_cost',
    'total_perc_discount',
    'regional_perc_discount',
    'coordinator_perc_discount',
    'regional_amount_discount',
    'coordinator_amount_discount',
    'date_reg'
  ];
  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\KPISDismissal
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new KPISDismissal;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }


  public static function getDTKPI($filters = []){

        /*Selecciono los grupos de instalacion que cumplen con los filtros*/

        $KPIS =  self::getConnect('R')
        ->select(
          'id',
          DB::raw("DATE_FORMAT(STR_TO_DATE(CONCAT('1,',month,',',year),'%d,%m,%Y'),'%m-%Y') as periodo"),
          'regional_email',
          'coordinator_email',
          'old_articles',
          'decrease_articles',
          'assigned_articles',
          'kpi_result',
          'lost_articles_cost',
          'total_perc_discount',
          'regional_perc_discount',
          'coordinator_perc_discount',
          'total_amount_discount',
          'regional_amount_discount',
          'coordinator_amount_discount'
        )
        ->where('status','<>','T');

        if (is_array($filters)) {
          if(!empty($filters['year'])){
              $KPIS = $KPIS->where('year',$filters['year']);
          }

          if (!empty($filters['month'])){
              $KPIS = $KPIS->where('month',$filters['month']);
          }

        }

        $KPIS = $KPIS->orderBy('regional_email')->get();

        return $KPIS;
    }
}
