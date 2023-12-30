<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

//use App\Sale;

class HistoryDC2 extends Model
{
  protected $table = 'islim_history_dc_2';
  //protected $table = 'islim_history_dc_copy';

  //cuando se pruebe con islim_history_dc_copy  cambiar en todos los sitios de este archivo islim_history_dc_2 por islim_history_dc_copy

  protected $fillable = [
    'id',
    'msisdn',
    'type',
    'date_event',
    'date_reg',
    'status'];

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
      $obj = new HistoryDC2;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function createRecord($msisdn = false, $type = false, $date_ev = false, $date_reg = false, $isRec = false)
  {
    if ($msisdn && $type && $date_ev && $date_reg) {
      if ($type == 'C90' || $type == 'D90') {
        self::resetLastActive($msisdn, ['C90', 'D90', 'A90', 'REC']);
      } elseif ($type == 'A90') {
        self::resetLastActive($msisdn, ['C90', 'D90']);

        $lastActive = self::getConnect('R')
          ->where([
            ['status', 'LA'],
            ['msisdn', $msisdn],
          ])
          ->whereIn('type', ['A90', 'REC'])
          ->get();

        if (!$lastActive->count()) {
          if ($isRec) {
            return self::getConnect('W')
              ->insert([
                'msisdn'     => $msisdn,
                'type'       => 'REC',
                'date_event' => $date_ev,
                'date_reg'   => $date_reg,
                'status'     => 'LA',
              ]);
          }
        } else {
          //comparando si es del mismo mes
          $dr = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $lastActive[0]->date_event
          );

          $de = Carbon::createFromFormat('Y-m-d H:i:s', $date_ev);

          if ($dr->format('Y-m') == $de->format('Y-m')) {
            self::resetLastActive($msisdn, ['A90']);
          } else {
            self::resetLastActive($msisdn, ['A90', 'REC']);
          }
        }
      }

      return self::getConnect('W')
        ->insert([
          'msisdn'     => $msisdn,
          'type'       => $type,
          'date_event' => $date_ev,
          'date_reg'   => $date_reg,
          'status'     => 'LA',
        ]);
    }

    return false;
  }

  public static function resetLastActive($msisdn = false, $types = [])
  {
    if ($msisdn && count($types)) {
      self::getConnect('W')
        ->where([
          ['status', 'LA'],
          ['msisdn', $msisdn],
        ])
        ->whereIn('type', $types)
        ->update(['status' => 'A']);

      return true;
    }

    return false;
  }

  public static function getLastTag($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
        ->select(
          'msisdn',
          'type',
          'date_event'
        )
        ->where([
          ['msisdn', $msisdn],
          ['status', 'LA'],
        ])
        ->get();
    }
  }

  public static function isReactivation($msisdn = false)
  {
    if ($msisdn) {
      $data = self::getConnect('R')
        ->select('msisdn')
        ->where([
          ['msisdn', $msisdn],
          ['status', 'LA'],
        ])
        ->whereIn('type', ['D90', 'C90'])
        ->count();

      return $data >= 1;
    }

    return false;
  }

  public static function getClientsByTag($type = [], $type_reg = 'H', $date = null)
  {
    if (empty($type))
      return [];
    
    $query = self::getConnect('R')
      ->select(
        'islim_history_dc_2.msisdn',
        'islim_clients.name',
        'islim_clients.last_name',
        'islim_clients.phone_home',
        'islim_clients.phone',
        'islim_clients.email',
        'islim_clients.dni',
        'islim_history_dc_2.date_event',
        'islim_client_buy_back.answer',
        'islim_client_buy_back.acept',
        'islim_client_buy_back.comment',
        'islim_client_buy_back.date_reg as date_call'
      )
      ->join(
        'islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_history_dc_2.msisdn'
      )
      ->join(
        'islim_clients',
        'islim_clients.dni',
        'islim_client_netweys.clients_dni'
      )
      ->leftJoin(
        'islim_client_buy_back',
        function ($join) {
          $join->on('islim_client_buy_back.msisdn', 'islim_history_dc_2.msisdn')
            ->where('islim_client_buy_back.is_last', 'Y');
        }
      );

    $historyDCStatus = ['LA'];
    if ($date) {
      $query->whereRaw('DATE_FORMAT(islim_history_dc_2.date_event, "%m/%Y") = ?', $date);
      array_push($historyDCStatus, 'A');
    }
    
    $query->where('islim_client_netweys.dn_type', $type_reg)
      ->whereIn('islim_client_netweys.status', ['A', 'S'])
      ->whereIn('islim_history_dc_2.type', $type)
      ->whereIn('islim_history_dc_2.status', $historyDCStatus)
      ->groupBy('islim_history_dc_2.msisdn');

    return $query;
  }
}
