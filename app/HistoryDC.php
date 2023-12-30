<?php

namespace App;

use App\Sale;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class HistoryDC extends Model
{
  protected $table = 'islim_history_dc';

  protected $fillable = [
    'id',
    'msisdn',
    'type',
    'date_event',
    'date_reg',
    'status'];

  public $timestamps = false;

  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new HistoryDC;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function createRecord($dn = false, $type = false, $dateEv = false)
  {
    if ($dn && $type && $dateEv) {
      $reg = self::select('type')
        ->where([
          ['msisdn', $dn],
          ['status', 'A'],
        ])
        ->orderBy('date_reg', 'DESC')
        ->first();

      if (empty($reg) || $reg->type != $type) {
        $new             = new HistoryDC;
        $new->msisdn     = $dn;
        $new->type       = $type;
        $new->date_event = $dateEv;
        $new->date_reg   = date('Y-m-d H:i:s');
        $new->status     = 'A';
        $new->save();
      }
    }
  }

  public static function processDns($dns = [], $type = false, $dateEv = false)
  {
    if (!empty($dns) && count($dns) && $type && $dateEv) {
      foreach ($dns as $dn) {
        self::createRecord($dn, $type, $dateEv);
      }
    }
  }

  public static function processChurn($dns = [])
  {
    if (!empty($dns) && count($dns)) {
      foreach ($dns as $dn) {
        $lastRe = Sale::select(
          'islim_sales.date_reg',
          'islim_periodicities.days'
        )
          ->join(
            'islim_services',
            'islim_services.id',
            'islim_sales.services_id'
          )
          ->join(
            'islim_periodicities',
            'islim_periodicities.id',
            'islim_services.periodicity_id'
          )
          ->where([
            ['islim_sales.type', 'R'],
            ['islim_sales.msisdn', $dn],
          ])
          ->whereIn('islim_sales.status', ['A', 'E'])
          ->orderBy('islim_sales.date_reg', 'DESC')
          ->first();

        if (!empty($lastRe)) {
          $date = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $lastRe->date_reg
          )
            ->addMonths(3)
            ->addDays($lastRe->days);

          self::createRecord(
            $dn,
            'C90',
            $date->toDateTimeString()
          );
        }
      }
    }
  }

  public static function processChurn30($dns = [])
  {
    if (!empty($dns) && count($dns)) {
      foreach ($dns as $dn) {
        $lastRe = Sale::select(
          'islim_sales.date_reg',
          'islim_periodicities.days'
        )
          ->join(
            'islim_services',
            'islim_services.id',
            'islim_sales.services_id'
          )
          ->join(
            'islim_periodicities',
            'islim_periodicities.id',
            'islim_services.periodicity_id'
          )
          ->where([
            ['islim_sales.type', 'R'],
            ['islim_sales.msisdn', $dn],
          ])
          ->whereIn('islim_sales.status', ['A', 'E'])
          ->orderBy('islim_sales.date_reg', 'DESC')
          ->first();

        if (!empty($lastRe)) {
          $date = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $lastRe->date_reg
          )
            ->addMonths(1)
            ->addDays($lastRe->days);

          self::createRecord(
            $dn,
            'C30',
            $date->toDateTimeString()
          );
        }
      }
    }
  }
}
