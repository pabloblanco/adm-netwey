<?php

namespace App;

use App\ArticlePack;
use App\PackPrices;
use App\Product;
use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
  protected $table = 'islim_packs';

  protected $fillable = [
    'title',
    'description',
    'price_arti',
    'date_ini',
    'date_end',
    'view_web',
    'desc_web',
    'sale_type',
    'pack_type',
    'is_band_twenty_eight',
    'acept_coupon',
    'is_portability',
    'date_reg',
    'service_prom_id',
    'is_visible_payjoy',
    'is_visible_coppel',
    'is_visible_paguitos',
    'is_migration',
    'valid_identity',
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
      $obj = new Pack;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getDataPaks()
  {
    return self::getConnect('R')
      ->select(
        'islim_packs.id',
        'islim_packs.title',
        'islim_packs.description',
        'islim_packs.desc_web',
        'islim_packs.sale_type',
        'islim_packs.view_web',
        'islim_packs.pack_type',
        'islim_packs.is_band_twenty_eight',
        'islim_packs.acept_coupon',
        'islim_packs.is_portability',
        'islim_packs.status',
        'islim_pack_prices.total_price',
        'islim_services.title as service',
        'islim_services.status as service_status',
        'islim_inv_articles.title as product',
        'islim_packs.service_prom_id',
        'islim_packs.is_visible_payjoy',
        'islim_packs.is_visible_coppel',
        'islim_packs.is_visible_paguitos',
        'islim_services_prom.name as service_prom_name',
        'islim_inv_articles.status as product_status',
        'islim_packs.is_migration',
        'islim_packs.valid_identity'
      )
      ->leftJoin(
        'islim_pack_prices',
        function ($join) {
          $join->on('islim_pack_prices.pack_id', 'islim_packs.id')
            ->where('islim_pack_prices.status', 'A');
        }
      )
      ->leftJoin(
        'islim_services',
        function ($join) {
          $join->on('islim_services.id', 'islim_pack_prices.service_id')
            ->whereIn('islim_services.status', ['A', 'I']);
        }
      )
      ->leftJoin(
        'islim_arti_packs',
        function ($join) {
          $join->on('islim_arti_packs.pack_id', 'islim_packs.id')
            ->where('islim_arti_packs.status', 'A');
        }
      )
      ->leftJoin(
        'islim_inv_articles',
        function ($join) {
          $join->on('islim_inv_articles.id', 'islim_arti_packs.inv_article_id')
            ->whereIn('islim_inv_articles.status', ['A', 'I']);
        }
      )
      ->leftJoin(
        'islim_services_prom',
        function ($join) {
          $join->on('islim_services_prom.id', 'islim_packs.service_prom_id')
            ->where('islim_services_prom.status', 'A');
        }
      )
      ->whereIn('islim_packs.status', ['A', 'I'])
      ->orderBy('islim_packs.status', 'ASC')
      ->orderBy('islim_packs.id', 'DESC')
      ->get();
  }

  public static function getPackById($id = false)
  {
    if ($id) {
      return self::getConnect('R')
        ->select(
          'islim_packs.id',
          'islim_packs.title',
          'islim_packs.description',
          'islim_packs.date_ini',
          'islim_packs.date_end',
          'islim_packs.pack_type',
          'islim_packs.is_band_twenty_eight',
          'islim_pack_prices.type as type_buy',
          'islim_pack_prices.price_pack',
          'islim_pack_prices.price_serv',
          'islim_pack_prices.total_price',
          'islim_services.id as service_id',
          'islim_services.title as service',
          'islim_services.status as service_status',
          'islim_inv_articles.title as product',
          'islim_inv_articles.id as product_id',
          'islim_inv_articles.status as product_status',
          'islim_financing.name as financing',
          'islim_arti_packs.retail'
        )
        ->leftJoin(
          'islim_pack_prices',
          function ($join) {
            $join->on('islim_pack_prices.pack_id', 'islim_packs.id')
              ->where('islim_pack_prices.status', 'A');
          }
        )
        ->leftJoin(
          'islim_services',
          function ($join) {
            $join->on('islim_services.id', 'islim_pack_prices.service_id')
              ->whereIn('islim_services.status', ['A', 'I']);
          }
        )
        ->leftJoin(
          'islim_financing',
          'islim_financing.id',
          'islim_pack_prices.id_financing'
        )
        ->leftJoin(
          'islim_arti_packs',
          function ($join) {
            $join->on('islim_arti_packs.pack_id', 'islim_packs.id')
              ->where('islim_arti_packs.status', 'A');
          }
        )
        ->leftJoin(
          'islim_inv_articles',
          function ($join) {
            $join->on(
              'islim_inv_articles.id',
              'islim_arti_packs.inv_article_id'
            )
              ->whereIn('islim_inv_articles.status', ['A', 'I']);
          }
        )
        ->where('islim_packs.id', $id)
        ->first();
    }

    return null;
  }

  //Deprecated
  public static function getPacks($status, $servicesStatus, $productsStatus)
  {
    $packs = Pack::whereIn('status', $status)->get();
    foreach ($packs as $pack) {
      $pack->products = ArticlePack::getProductsByPack($pack->id, $productsStatus);
      $pack->services = PackPrices::getServicesByPack($pack->id, $servicesStatus, $productsStatus);
      $pack->credit   = false;
      $pack->cash     = false;
      foreach ($pack->services as $s) {
        if ($s->method_pay == 'CO') {
          $pack->cash = true;
        } else {
          $pack->credit = true;
        }
      }
    }
    return $packs;
  }
  //Deprecated
  public static function getPack($id, $servicesStatus, $productsStatus)
  {
    $pack = Pack::where(['id' => $id])->first();
    if (isset($pack)) {
      $pack->products = ArticlePack::getProductsByPack($pack->id, $productsStatus);
      $pack->services = PackPrices::getServicesByPack($pack->id, $servicesStatus, $productsStatus);
      if (count($pack->services)) {
        $pack->services = $pack->services[0];
      } else {
        $pack->services = null;
      }

      $pack->credit = false;
      $pack->cash   = false;

      if (!empty($pack->services) && $pack->services->method_pay == 'CR') {
        $pack->credit = true;
      }

      if (!empty($pack->services) && $pack->services->method_pay == 'CO') {
        $pack->cash = true;
      }

      /*foreach ($pack->services as $s) {
    if ($s->method_pay == 'CR') {
    $pack->credit = true;
    }
    if ($s->method_pay == 'CO') {
    $pack->cash = true;
    }
    }*/
    }
    return $pack;
  }

}
