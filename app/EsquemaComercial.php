<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EsquemaComercial extends Model
{
  protected $table = 'islim_esquema_comercial';

  protected $fillable = [
    'name',
    'division',
    'region',
    'type',
    'status',
    'date_edit',
    'user_netwey'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\EsquemaComercial
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

  public static function getEsquemaByType($type, $name = false)
  {
    $data = self::getConnect('R')
      ->select('id', 'name')
      ->where('type', $type)->where('status', 'A');

    if ($name) {
      $data = $data->where([
        ['name', 'like', $name . '%']]);
    }
    $data = $data->orderBy('name', 'ASC');

    return $data;
  }

/**
 * [getListEsquema Lista de esquema comercial]
 * @param  array  $filter [description]
 * @return [type]         [description]
 */
  public static function getListEsquema($filter = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_esquema_comercial.id',
        'islim_esquema_comercial.name AS nameScheme',
        DB::raw('CONCAT(
        islim_users.name," ",
        islim_users.last_name," ( ",
        islim_users.email," )" ) AS responsable'),
        DB::raw('IF (islim_esquema_comercial.type = "D", "Division",
            IF (islim_esquema_comercial.type = "R", "Region", "Coordinacion"))  AS type'))
      ->leftJoin('islim_users',
        'islim_users.esquema_comercial_id',
        'islim_esquema_comercial.id');

    if (!empty($filter['type'])) {
      $data = $data->where('islim_esquema_comercial.type', $filter['type']);
    }

    if (!empty($filter['nameScheme'])) {
      $data = $data->where('islim_esquema_comercial.id', $filter['nameScheme']);
    }
    $data = $data->where('islim_esquema_comercial.status', 'A');
    return $data->get();
  }

/**
 * [GetSchemeSearchList Busqueda de un item por su nombre]
 * @param [type] $querySearch [description]
 */
  public static function GetSchemeSearchList($querySearch)
  {
    return self::getConnect('R')
      ->select('islim_esquema_comercial.id',
        'islim_esquema_comercial.name AS nameScheme',
        DB::raw('CONCAT(islim_esquema_comercial.name," ( ",
          IF (islim_esquema_comercial.type = "D", "Division",
            IF (islim_esquema_comercial.type = "R", "Region", "Coordinacion"))," )" ) AS NameLabelScheme'),
        DB::raw('IF (islim_esquema_comercial.type = "D", "Division",
            IF (islim_esquema_comercial.type = "R", "Region", "Coordinacion")) AS type'))
      ->where([
        ['islim_esquema_comercial.name', 'like', '%' . $querySearch . '%'],
        ['islim_esquema_comercial.status', 'A']])
      ->limit(10)
      ->get();
  }
  /**
   * [UpdateScheme Actualiza el item del esquema comercial o lo elimina logicamente]
   * @param [type]  $id      [description]
   * @param boolean $newName [description]
   */
  public static function UpdateScheme($id, $newName = false)
  {
    if ($newName && !empty($newName)) {
      //es un update
      return self::getConnect('W')
        ->where([['id', $id], ['status', '!=', 'T']])
        ->update([
          'name'        => $newName,
          'user_netwey' => session('user')->email,
          'date_edit'   => date('Y-m-d H:i:s')]);
    } else {
      //es una eliminacion
      return self::getConnect('W')
        ->where([['id', $id], ['status', '!=', 'T']])
        ->update([
          'status'      => 'T',
          'user_netwey' => session('user')->email,
          'date_edit'   => date('Y-m-d H:i:s')]);
    }
  }
/**
 * [isPermiteDelete Se verifica que no existan responsables en los id del esquema]
 * @param  [type]  $id [description]
 * @return boolean     [description]
 */
  public static function isSchemeDelete($id)
  {
    if (!empty($id)) {

      //Revisamos que tipo de esquema es
      $esquema = self::getConnect('R')
        ->select('islim_esquema_comercial.type')
        ->where('islim_esquema_comercial.id', $id)
        ->first();

      if (!empty($esquema)) {

        if ($esquema->type == 'D') {
          //Buscamos si tiene regiones
          $esquemaDivision = self::getConnect('R')
            ->select('islim_esquema_comercial.type')
            ->where('islim_esquema_comercial.division', $id)
            ->first();
          if (!empty($esquemaDivision)) {
            return false;
          }
        } elseif ($esquema->type == 'R') {
          //Buscamos si hay coordinaciones
          $esquemaRegion = self::getConnect('R')
            ->select('islim_esquema_comercial.type')
            ->where('islim_esquema_comercial.region', $id)
            ->first();
          if (!empty($esquemaRegion)) {
            return false;
          }
        } else {
//es una coordinacion y verificamos que usuarios estan en esa coordinacion
          $datos = User::getConnect('R')
            ->select('islim_users.email')
            ->where('islim_users.esquema_comercial_id', $id)
            ->first();
          if (!empty($datos)) {
            return false;
          }
        }
      }
      return true;
    }
    return false;
  }

/**
 * [InsertScheme Insercion de un nuevo item en el esquema comercial]
 * @param [type]  $type    [tipo: Division, Region o Coordinacion]
 * @param [type]  $newName [Nombre del nuevo item]
 * @param boolean $id      [Id del padre del item]
 */
  public static function InsertScheme($type, $newName, $id = false)
  {
    $item       = self::getConnect('W');
    $item->name = $newName;
    $item->type = $type;
    if ($type == 'R' && $id) {

      $item->division = $id;
    } elseif ($type == 'C' && $id) {

      $item->region = $id;
    }
    $item->date_edit   = date('Y-m-d H:i:s');
    $item->user_netwey = session('user')->email;
    $item->save();
    return $item->id;
  }

}
