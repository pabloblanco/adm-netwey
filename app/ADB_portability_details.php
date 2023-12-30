<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ADB_portability_details extends Model
{
  protected $table = 'islim_soap_portability_details';

  protected $fillable = [
    'id',
    'portID',
    'type',
    'MessageID',
    'date_reg',
    'DIDA',
    'DCR',
    'RIDA',
    'RCR',
    'PortingType',
    'SubscriberType',
    'RecoveryFlagType',
    'msisdn_from',
    'msisdn_to',
    'cant_DN',
    'type',
    'date_reg'];
  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\ADB_portability_details
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ADB_portability_details;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getInfoDetail Devuelve la informacion detallada de los mensajes enviados y recibidos de ADB]
 * @param  boolean $port_ID [description]
 * @return [type]           [description]
 */
  public static function getInfoDetail($port_ID = false)
  {

    if ($port_ID) {
      return self::getConnect('R')
        ->select(
          DB::raw('CONCAT(islim_soap_portability_details.MessageID, " - ", islim_soap_portability_errors.description) AS messageID'),
          'islim_soap_portability_details.type AS messageID_type',
          'islim_soap_portability_details.date_reg AS message_fecha'
        )
        ->join('islim_soap_portability_errors',
          'islim_soap_portability_errors.code_error',
          'islim_soap_portability_details.MessageID')
        ->where('portID', $port_ID)
        ->get();
    }

    return [];
  }

/**
 * [getInfoForCancel Obtiene la informacion necesaria para crear una solicitud de cancelacion de portabilidad]
 * @param  boolean $port_ID [description]
 * @return [type]           [description]
 */
  public static function getInfoForCancel($port_ID = false, $msisdn = false)
  {
    if ($port_ID && $msisdn) {
      $isData = self::getConnect('R')
        ->where([['portID', $port_ID],
          ['type', 'R']])
        ->whereIn('MessageID', ['1002', '1005'])
        ->orderBy('date_reg', 'desc')
        ->first();

      if (!empty($isData)) {

        DB::table('islim_portability')->where('portID', $port_ID)->update([
          'boton_disable' => 'Y',
          'date_process'  => date('Y-m-d H:i:s')]);

        self::getConnect('W')
          ->insert([
            'MessageID'        => '3001',
            'type'             => 'S',
            'date_reg'         => date('Y-m-d H:i:s'),
            'PortingType'      => $isData->PortingType,
            'SubscriberType'   => $isData->SubscriberType,
            'RecoveryFlagType' => $isData->RecoveryFlagType,
            'portID'           => $isData->portID,
            'DIDA'             => $isData->DIDA,
            'DCR'              => $isData->DCR,
            'RIDA'             => $isData->RIDA,
            'RCR'              => $isData->RCR,
            'cant_DN'          => '1',
            'msisdn_from'      => $msisdn,
            'msisdn_to'        => $msisdn,
          ]);
        return true;
      }
      return null;
    }
  }
}
