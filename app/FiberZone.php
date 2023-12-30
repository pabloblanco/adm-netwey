<?php

namespace App;

use App\Helpers\ValidateString;
use Illuminate\Database\Eloquent\Model;

class FiberZone extends Model
{
  protected $table = 'islim_fiber_zone';

  protected $fillable = [
    'id',
    'name',
    'url_api',
    'param',
    'type_soft',
    'status',
    'ambiente',
    'configuration',
    'date_update',
    'email_user'];

  public $timestamps = false;
  protected $casts   = [
    'param'         => 'array',
    'configuration' => 'array'];

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\FiberZone
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

  public static function getfiberZone($filter = false)
  {
    $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';
    $data     = self::getConnect('R')
      ->select('id', 'name', 'url_api', 'param', 'type_soft', 'configuration')
      ->where([
        ['status', 'A'],
        ['ambiente', $ambiente]]);

    if ($filter) {
      if (!empty($filter['owner'])) {
        $data = $data->where('configuration->owner', $filter['owner']);
      }
      if (!empty($filter['id'])) {
        $data = $data->where('id', $filter['id']);
      }
    }
    $data = $data->get();
    return $data;
  }

  public static function registerZone($data)
  {
    $dataSave          = self::getConnect('W');
    $dataSave->name    = $data['nameZone'];
    $dataSave->url_api = $data['endpoint'];

    $infoParam              = new \stdClass;
    $infoParam->user        = $data['user'];
    $infoParam->password    = $data['password'];
    $infoParam->nodo_de_red = $data['nodo'];
    //$infoParam->mode_default = $data['modo'];
    //De momento se fija manual
    $infoParam->mode_default = 'dhcp';
    //$infoParam->dhcp_relay   = $data['relay'];
    //De momento se fija manual
    $infoParam->dhcp_relay = 'False';

    $dataSave->param = json_decode(json_encode($infoParam));

    $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';

    $dataSave->ambiente = $ambiente;
    //$dataSave->frecuencia_min = '120';

    $infoConfig = new \stdClass;
    if (!empty($data['msg'])) {
      $msj = ValidateString::normaliza($data['msg']);
    } else {
      $msj = "";
    }
    $infoConfig->sms       = $msj;
    $infoConfig->owner     = $data['owner_CU'];
    $infoConfig->collector = $data['collector'];

    $dataSave->configuration = json_decode(json_encode($infoConfig));
    $dataSave->date_update   = date('Y-m-d H:i:s');
    $dataSave->email_user    = session('user')->email;
    $dataSave->save();
    return $dataSave;
  }

  public static function RemoverZone($id)
  {
    return self::getConnect('W')
      ->where('id', $id)
      ->update(['status' => 'T',
        'date_update'      => date('Y-m-d H:i:s'),
        'email_user'       => session('user')->email]);
  }
  public static function UpdateZone($id, $data)
  {
    $infoParam = array(
      'user'         => $data['user'],
      'password'     => $data['password'],
      'nodo_de_red'  => $data['nodo'],
      'mode_default' => 'dhcp',
      'dhcp_relay'   => 'False');

    if (!empty($data['msg'])) {
      $msj = ValidateString::normaliza($data['msg']);
    } else {
      $msj = "";
    }
    $infoConfig = array(
      'sms'       => $msj,
      'owner'     => $data['owner_CU'],
      'collector' => $data['collector']);

    return self::getConnect('W')
      ->where('id', $id)
      ->update([
        'name'          => $data['nameZone'],
        'url_api'       => $data['endpoint'],
        'param'         => (String) json_encode($infoParam),
        'configuration' => (String) json_encode($infoConfig),
        'date_update'   => date('Y-m-d H:i:s'),
        'email_user'    => session('user')->email]);
  }
}
