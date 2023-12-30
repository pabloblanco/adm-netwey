<?php
/*
Creado por Luis
Diciembre 2020
 */
namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Promontion extends Model
{
  protected $table = 'islim_gift_services';

  protected $fillable = [
    'id', 'msisdn', 'service_id', 'id_sale', 'activation_date', 'expired_date', 'activated_date', 'date_reg', 'comment', 'status'];

  public $timestamps = false;

  protected $primaryKey = 'id';

  protected $hidden = [
    'status', 'expired_date', 'comment'];
  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Promontion;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getPromontionReport($msisdn, $filtre_date)
  {
    $filtre_date['date_ini'] = Carbon::now()->format('Y-m-d H:i:s');
    $filtre_date['date_end'] = Carbon::now()->format('Y-m-d H:i:s');

    $data = self::getConnect('R')
      ->select(
        'islim_gift_services.id',
        'islim_gift_services.msisdn',
        'objs.title as service',
        'islim_gift_services.id_sale',
        'islim_gift_services.activation_date',
        'islim_gift_services.activated_date',
        'islim_gift_services.date_reg'
      )
      ->join('islim_services as objs', 'objs.id', '=', 'islim_gift_services.service_id')
      ->where('islim_gift_services.msisdn', $msisdn)
      ->whereIn('islim_gift_services.status', ['A', 'P']);

    if (is_array($filtre_date)) {
      if (!empty($filters['date_ini']) && !empty($filters['date_end'])) {
        $data->whereBetween('islim_gift_services.date_reg', [$filters['date_ini'], $filters['date_end']]);
      } elseif (!empty($filters['date_ini'])) {
        $data->where('islim_gift_services.date_reg', '>=', $filters['date_ini']);
      } elseif (!empty($filters['date_end'])) {
        $data->where('islim_gift_services.date_reg', '<=', $filters['date_end']);
      }
    }
    $data = $data->orderBy('islim_gift_services.date_reg', 'DESC')->get();
    //dd($data);
    return $data;
  }
}
