<?php

namespace App;

use App\Product;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Inventory extends Model
{
  protected $table = 'islim_inv_arti_details';

  protected $fillable = [
    'id',
    'parent_id',
    'inv_article_id',
    'warehouses_id',
    'serial',
    'msisdn',
    'iccid',
    'imei',
    'imsi',
    'date_reception',
    'date_sending',
    'price_pay',
    'obs',
    'date_reg',
    'status',
    'dn_autogen'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Inventory
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Inventory;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function existDN($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
      //->select('id', 'status')
        ->where([
          //['status', '!=', 'T'],
          ['msisdn', $msisdn]])
        ->first();

    }

    return null;
  }

  public static function getAvailableDnAutogen()
  {
    $dn=self::getConnect('R')
      ->where([
        ['msisdn',">=",env('MIN_FIBER_DN','1000000001')],
        ['msisdn',"<=",env('MAX_FIBER_DN','1999999999')],
        ['dn_autogen','Y']
      ])
      ->whereRaw('msisdn REGEXP "^[0-9]+$" = 1')
      ->whereRaw('LENGTH(msisdn) = 10')
      ->max('msisdn');

    if(!empty($dn)){
      $dn = (String)($dn+1);
    }
    else{
      $dn = env('MIN_FIBER_DN','1000000001');
    }
    while(
      self::existDN($dn) != null
      && $dn >= env('MIN_FIBER_DN','1000000001')
      && $dn <= env('MAX_FIBER_DN','1999999999')
    ){
      $dn = (String)($dn+1);
    }
    if($dn >= env('MIN_FIBER_DN','1000000001')
      && $dn <= env('MAX_FIBER_DN','1999999999')){
      return $dn;
    }
    else{
      return null;
    }
  }

  public static function existMACIMEI($macimei = false)
  {
    if ($macimei) {
      return self::getConnect('R')
        ->select('id')
        ->where([
          ['status', '!=', 'T'],
          ['imei', $macimei]])
        ->first();
    }

    return null;
  }

  public static function getModelo($msisdn = false)
  {
    if ($msisdn) {
      $data = self::getConnect('R')
        ->select('islim_inv_articles.title',
          'islim_inv_articles.brand',
          'islim_inv_articles.model',
          'islim_inv_arti_details.imei'
        )
        ->join('islim_inv_articles', 'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id')
        ->where('islim_inv_arti_details.msisdn', $msisdn)
        ->first();
      return $data;
    }
  }

  public static function getInventaryDetail($status = [], $wh = [])
  {
    if (count($status)) {
      $data = self::getConnect('R')
        ->select(
          'islim_inv_arti_details.*',
          'islim_inv_articles.title',
          'islim_inv_articles.artic_type'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
      /*->join(
      'islim_warehouses',
      'islim_warehouses.id',
      'islim_inv_arti_details.warehouses_id'
      )*/
        ->whereIn('islim_inv_arti_details.status', $status);

      if (count($wh)) {
        $data->whereIn('warehouses_id', $wh);
      }

      return $data->orderBy('islim_inv_arti_details.id', 'DESC'); //->get();
    }

    return [];
  }

  public static function getArticle($id, $statusProduct, $statusParent = 'A')
  {
    $article         = Inventory::where(['id' => $id, 'status' => $statusProduct])->first();
    $article->parent = Product::where(['id' => $article->inv_article_id, 'status' => $statusParent])->first();
    return $article;
  }

  public static function getReportMerma($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title',
        'islim_warehouses.name',
        DB::raw('(select islim_inv_assignments_tracks.id from islim_inv_assignments_tracks where islim_inv_assignments_tracks.inv_arti_details_id = islim_inv_arti_details.id and islim_inv_assignments_tracks.destination_wh is not null order by islim_inv_assignments_tracks.id DESC limit 1) as id_track')
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_warehouses',
        'islim_warehouses.id',
        'islim_inv_arti_details.warehouses_id'
      )
      ->where('islim_inv_arti_details.status', 'A');

    if (!empty($filters['warehouse'])) {
      if ($filters['warehouse'] == 'MT') {
        $data->where('islim_inv_arti_details.warehouses_id', env('WHEREHOUSE_THETF'));
      } else {
        $data->where('islim_inv_arti_details.warehouses_id', env('WH_MERMA_OLD'));
      }
    } else {
      $data->whereIn('islim_inv_arti_details.warehouses_id', [env('WH_MERMA_OLD'), env('WHEREHOUSE_THETF')]);
    }

    $data = $data->get();

    foreach ($data as $inv) {
      if (!empty($inv->id_track)) {
        $detail = SellerInventoryTrack::getDetailTrack($inv->id_track);

        if (!empty($detail)) {
          $inv->date_reg = $detail->date_reg;

          if (!empty($detail->assigned_name)) {
            $inv->assigned_name = $detail->assigned_name;
          }

          if (!empty($detail->assigned_last_name)) {
            $inv->assigned_name .= ' ' . $detail->assigned_last_name;
          }

          if (!empty($detail->origin_name)) {
            $inv->origin_name = $detail->origin_name;
          }

          if (!empty($detail->origin_last_name)) {
            $inv->origin_name .= ' ' . $detail->origin_last_name;
          }
        }
      }
    }

    return $data;
  }

  public static function getMermaWarehouseOldEquipment($filters = [])
  {
    $data = self::getConnect('R')->select(
      'msisdn', 'title',
      DB::raw("CONCAT(users_supervisor.name, ' ', users_supervisor.last_name) AS name_supervisor"),
      DB::raw("CONCAT(users_seller.name, ' ', users_seller.last_name) AS name_seller"),
      'first_assignment', 'date_red'
    )
      ->join('islim_inv_assignments', function ($join) {
        $join->on('islim_inv_assignments.inv_arti_details_id', '=', 'islim_inv_arti_details.id')
          ->whereNotNull('islim_inv_assignments.date_red')
          ->where('islim_inv_assignments.status', '=', 'T')
          ->orderBy('islim_inv_assignments.date_reg', 'desc');
      })
      ->join('islim_users as users_supervisor', 'islim_inv_assignments.users_email', '=', 'users_supervisor.email')
      ->join('islim_users as users_seller', 'islim_inv_assignments.user_red', '=', 'users_seller.email')
      ->join('islim_inv_articles', 'islim_inv_arti_details.inv_article_id', '=', 'islim_inv_articles.id')
      ->where('islim_inv_arti_details.status', '=', 'A')
      ->where('islim_inv_arti_details.warehouses_id', '=', env('WH_MERMA_OLD'))
      ->where('islim_inv_assignments.date_reg', '>=', $filters['dateb'])
      ->where('islim_inv_assignments.date_reg', '<=', $filters['datee']);

    if (!empty($filters['msisdns'])) {
      $msisdns = explode(',', $filters['msisdns']);
      $data->whereIn('msisdn', $msisdns)->get();
    } else {
      $data->get();
    }

    return $data;
  }

  public static function getDetailById($id)
  {
    return self::getConnect('R')
      ->select(
        'islim_inv_arti_details.id',
        'islim_inv_articles.artic_type',
        'islim_inv_arti_details.iccid',
        'islim_inv_arti_details.price_pay'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->where([
        ['islim_inv_arti_details.status', 'A'],
        ['islim_inv_arti_details.id', $id]])
      ->first();
  }

  /**
   * [getType Devuelve el tipo de Dn]
   * @param  [type] $msisdn [description]
   * @return [type]         [description]
   */
  public static function getType($msisdn)
  {
    return self::getConnect('R')
      ->select(
        'islim_inv_articles.artic_type',
        'islim_inv_arti_details.status'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->where('islim_inv_arti_details.msisdn', $msisdn)
      ->first();
  }
  /**
   * Cuando un usuario se da de baja y aun tenia equipos al momento de finalizar el proceso estos equipos se pasan a la bodega de merma por bajas
   */

/**
 * [setNewWarehouseLow Pasa el equipos a la bodega de merma bajas]
 * @param [type] $idInv    [id de detail_inv]
 * @param [type] $msjLow [mensaje descriptivo]
 */
  public static function setNewWarehouseLow($idInv, $msjLow)
  {
    self::getConnect('W')
      ->where('id', $idInv)
      ->update([
        'warehouses_id' => env('WH_MERMA_LOW'),
        'obs'           => $msjLow,
      ]);
  }

  public static function getInfoDN($msisdn = false)
  {
    if ($msisdn) {
      return self::getConnect('R')
        ->select('id', 'imei', 'status')
        ->where('msisdn', $msisdn)
        ->first();
    }
    return null;
  }

  public static function setInventaryStatus($msisdn, $status)
  {
    self::getConnect('W')
      ->where('msisdn', $msisdn)
      ->update([
        'status' => $status,
      ]);
  }
}
