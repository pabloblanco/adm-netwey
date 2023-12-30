<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FiberCityZone extends Model
{
  protected $table = 'islim_fiber_city_zone';

  protected $fillable = [
    'id',
    'fiber_zone_id',
    'fiber_city_id',
    'pk_city',
    'status',
    'poligono',
    'date_update',
    'user_mail'];

  public $timestamps = false;
  protected $casts   = [
    'poligono' => 'array'];

/**
 * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
 * @param String $typeCon
 *
 * @return App\FiberCityZone
 */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

/**
 * [getfiberCityZone Obtiene las ciudades y las olts para configurar o ver el mapa de cobertura]
 * @return [type] [description]
 */
  public static function getListCityZone($idFC = false)
  {
    $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';

    $data = self::getConnect('R')
      ->select('islim_fiber_city_zone.id',
        'Olts.name AS olt',
        'Citys.location AS city',
        'islim_fiber_city_zone.poligono',
        'islim_fiber_city_zone.status')
      ->join('islim_fiber_zone AS Olts',
        'Olts.id',
        'islim_fiber_city_zone.fiber_zone_id')
      ->join('islim_fiber_city AS CitysZone',
        'CitysZone.id',
        'islim_fiber_city_zone.fiber_city_id')
      ->join('islim_localy_mexico AS Citys',
        'Citys.id',
        'CitysZone.localy_id')
      ->where([
        ['Olts.ambiente', $ambiente],
        ['Olts.status', 'A'],
        ['CitysZone.status', 'A']])
      ->whereIn('islim_fiber_city_zone.status', ['A', 'I']);

    if ($idFC) {
      $data = $data->where('islim_fiber_city_zone.id', $idFC);
    }
    return $data->get();
  }

  public static function getCoordCenter($data = false)
  {

    if (!is_array($data)) {
      return null;
    }

    $num_coords = count($data);
    $X          = 0.0;
    $Y          = 0.0;
    $Z          = 0.0;

    foreach ($data as $coord) {
      $lat = $coord['lat'] * pi() / 180;
      $lon = $coord['lng'] * pi() / 180;
      $a   = cos($lat) * cos($lon);
      $b   = cos($lat) * sin($lon);
      $c   = sin($lat);
      $X += $a;
      $Y += $b;
      $Z += $c;
    }

    $X /= $num_coords;
    $Y /= $num_coords;
    $Z /= $num_coords;
    $lon = atan2($Y, $X);
    $hyp = sqrt($X * $X + $Y * $Y);
    $lat = atan2($Z, $hyp);

    return array('lat' => $lat * 180 / pi(), 'lng' => $lon * 180 / pi());
  }

  public static function updatePoligono($id = false, $data = false)
  {
    if ($id && $data) {
      return self::getConnect('W')
        ->where('id', $id)
        ->update([
          'poligono'    => (String) json_encode($data),
          'date_update' => date('Y-m-d H:i:s'),
          'user_mail'   => session('user')->email]);
    }
    return false;
  }

  public static function setStatusPoligono($id = false, $status = 'I')
  {
    if ($id) {
      return self::getConnect('W')
        ->where('id', $id)
        ->update([
          'status'      => $status,
          'date_update' => date('Y-m-d H:i:s'),
          'user_mail'   => session('user')->email]);
    }
    return false;
  }
}
