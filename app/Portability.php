<?php
/*
Autor: Ing. LuisJ
Febrero 2021, actualizado en Agosto 2021
 */
namespace App;

use App\Sale;
use Illuminate\Database\Eloquent\Model;

class Portability extends Model
{
  protected $table    = 'islim_portability';
  protected $fillable = [
    'id',
    'sale_id',
    'dn_portability',
    'dn_netwey',
    'company_id',
    'nip',
    'photo_front',
    'photo_back',
    'date_reg',
    'date_process',
    'status',
    'Observation',
    'details_error',
    'portID',
    'latest_soap',
    'boton_disable'];
  protected $hidden = [
    'company_id', 'photo_front', 'photo_back'];
  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Portability;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getDTPotabilityPeriod($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_portability.id',
        'islim_portability.sale_id',
        'islim_portability.dn_portability as msisdn_user',
        'islim_portability.dn_netwey as msisdn_netwey',
        'islim_portability.nip',
        'islim_portability.date_reg',
        'islim_portability.date_process',
        'islim_portability.status',
        'islim_portability.Observation',
        'islim_portability.details_error',
        'islim_portability.portID',
        'islim_portability.latest_soap',
        'islim_portability.boton_disable'
      )
      ->where('status', '!=', 'T');

    if (is_array($filters)) {
      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data->whereBetween('islim_portability.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data->where('islim_portability.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data->where('islim_portability.date_reg', '<=', $filters['dateEnd']);
      }
    }

    if (!empty($filters['status'])) {
      $data->where('status', $filters['status']);
    }

    $data = $data->orderBy('islim_portability.status', 'ASC')
      ->orderBy('islim_portability.date_reg', 'ASC')->get();
    //  print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
    // exit;
    return $data;
  }

/**
 * [newPortability Registro una nueva portabilidad]
 * @param  [type] $DN_Trans [description]
 * @param  [type] $DN_Port  [description]
 * @param  [type] $operador [description]
 * @param  [type] $nip      [description]
 * @return [type]           [description]
 */
  public static function newPortability($DN_Trans, $DN_Port, $operador, $nip)
  {
    $idSales = Sale::existDN($DN_Trans);
    if (!empty($idSales)) {
      $NewData                 = self::getConnect('W');
      $NewData->sale_id        = $idSales->id;
      $NewData->dn_portability = $DN_Port;
      $NewData->dn_netwey      = $DN_Trans;
      $NewData->company_id     = $operador;
      $NewData->nip            = $nip;
      $NewData->date_reg       = date('Y-m-d H:i:s');
      $NewData->Observation    = "Peticion desde Call Center";
      $NewData->photo_front    = '0';
      $NewData->photo_back     = '0';
      $NewData->save();
      return ['success' => true];
    } else {
      return ['success' => false, 'msg' => 'No se encuentra el registro necesario de la venta'];
    }
  }

/**
 * [inProcess revuelve la fecha en que esta en proceso de portacion activo un DN]
 * @param  [type]  $DN_Trans [description]
 * @param  boolean $DN_Port  [description]
 * @return [type]            [description]
 */
  public static function inProcess($DN_Trans, $DN_Port = false)
  {
    $InProcess = self::getConnect('R')
      ->select('id', 'date_reg')
      ->whereIn('status', ['A', 'S', 'SS', 'SA']);

    if ($DN_Port) {
      $InProcess = $InProcess->where(function ($q) {
        $q->where('dn_portability', $DN_Port)
          ->orWhere('dn_netwey', $DN_Trans);
      });
    } else {
      $InProcess = $InProcess->where('dn_netwey', $DN_Trans);
    }

    return $InProcess->first();
  }
}
