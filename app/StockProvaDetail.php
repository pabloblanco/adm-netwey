<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockProvaDetail extends Model
{
  protected $table = 'islim_stock_prova_detail';

  protected $fillable = [
    'id_stock_prova',
    'box',
    'sku',
    'msisdn',
    'iccid',
    'imei',
    'branch',
    'name',
    'users',
    'folio',
    'status',
    'last_user_action',
    'reg_date_action',
    'coo_date_action',
    'statusRecycling',
    'user_assignment',
    'comment',
    'date_reg'
  ];

  protected $primaryKey = 'id';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\StockProvaDetail
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

  public static function getOrderRequest($filters = [])
  {

    $data = self::getConnect('R')
      ->select(
        'islim_stock_prova.file_name as file',
        'islim_stock_prova_detail.box',
        'islim_stock_prova_detail.sku',
        'islim_stock_prova_detail.msisdn',
        'islim_stock_prova_detail.iccid',
        'islim_stock_prova_detail.imei',
        'islim_stock_prova_detail.branch',
        DB::raw('CONCAT(lower(islim_stock_prova_detail.name)," - ",lower(islim_stock_prova_detail.users)) as user'),
        'islim_stock_prova_detail.folio',
        'islim_stock_prova_detail.status',
        'islim_inv_reciclers.status as recicler_status',
        DB::raw('CONCAT(lower(islim_users.name)," - ",lower(islim_stock_prova_detail.last_user_action)) as last_user_action'),
        'islim_stock_prova_detail.reg_date_action',
        'islim_stock_prova_detail.coo_date_action',
        'islim_stock_prova_detail.comment',
        'islim_stock_prova_detail.date_reg'
      )
      ->join('islim_stock_prova', 'islim_stock_prova.id', 'islim_stock_prova_detail.id_stock_prova')

      ->leftJoin('islim_users','islim_users.email','islim_stock_prova_detail.last_user_action')
      ->leftJoin('islim_inv_reciclers',
        function($join){
              $join->on('islim_inv_reciclers.msisdn', '=', 'islim_stock_prova_detail.msisdn')
                   ->where('islim_inv_reciclers.status', '<>', 'T')
                   ->whereRaw("islim_inv_reciclers.id = (select MAX(ir.id) from islim_inv_reciclers as ir where ir.msisdn = islim_stock_prova_detail.msisdn and ir.status <> 'T')");
      })
      ->where('islim_stock_prova.status', '<>', 'T');

      //->where('islim_stock_prova_detail.status', '<>', 'T');


    if (is_array($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $data->whereBetween('islim_stock_prova_detail.date_reg', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $data->where('islim_stock_prova_detail.date_reg', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $data->where('islim_stock_prova_detail.date_reg', '<=', $filters['datee']);
      }

      if (!empty($filters['status'])) {
        $data->where('islim_stock_prova_detail.status', $filters['status']);
      }

    }
    // if(!empty($guiarr)){
    //   $data->whereIn('islim_stock_prova_detail.folio',$guiarr_e);
    // }

    // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
    //             return is_numeric($binding) ? $binding : "'{$binding}'";
    //         })->toArray());

    //   Log::info($query);




    $data = $data->get();
    return $data;
  }

  public static function getPenddingOrderDetails($folio, $user)
  {
    $coord = User::getConnect('R')
      ->select('email')
      ->where('parent_email', $user)
      ->where('status', 'A')
      ->get();

    $data = self::getConnect('R')
      ->select(
        'islim_stock_prova_detail.id',
        'islim_stock_prova_detail.msisdn',
        'islim_stock_prova_detail.sku',
        'islim_stock_prova_detail.iccid',
        'islim_stock_prova_detail.imei',
        'islim_stock_prova_detail.name',
        'islim_stock_prova_detail.users',
        'islim_stock_prova_detail.comment',
        'islim_inv_articles.id as article_id',
        'islim_inv_articles.title as article_name',
        'islim_inv_articles.price_ref as price'
      )
      ->join('islim_inv_articles', 'islim_stock_prova_detail.sku', 'islim_inv_articles.sku')
      ->where('islim_stock_prova_detail.status', 'A')
      ->where('islim_stock_prova_detail.folio', $folio)
      ->whereIn('islim_stock_prova_detail.users', $coord->pluck('email'))
      ->get();
    return $data;
  }

  public static function activeReport($msisdn)
  {
    return self::getConnect('R')
      ->select('id')
      ->where([
        ['msisdn', $msisdn],
        ['status', 'A'],
      ])
      ->count();
  }

  public static function getReportByDn($msisdn){
    return self::getConnect('R')
                ->select('id', 'status', 'folio')
                ->where([
                  ['msisdn', $msisdn],
                  ['status', '!=', 'T']
                ])
                ->first();
  }

  public static function getDetailByDN($msisdn, $status = false)
  {
    $data = self::getConnect('W')
      ->select('id', 'msisdn', 'status')
      ->where('msisdn', $msisdn);
    if ($status) {
      $data = $data->where('status', $status);
    }
    return $data->first();
  }

/**
 * [getDetailRecicler Revisa si el DN en cuestion se debe cargar en inventario y asignar al vendedor]
 * @param  [type] $msisdn [description]
 * @return [type]         [description]
 */
  public static function getDetailRecicler($msisdn)
  {
    return self::getConnect('W')
      ->select('id', 'statusRecycling', 'user_assignment')
      ->where([
        ['msisdn', $msisdn],
        ['status', 'PR']])
      ->first();
  }
}
