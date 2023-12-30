<?php

namespace App;

use App\ProductsCategory;
use Illuminate\Database\Eloquent\Model;
use Log;

class Product extends Model
{
  protected $table = 'islim_inv_articles';

  protected $fillable = [
    'provider_dni',
    'category_id',
    'title',
    'description',
    'brand',
    'model',
    'type_barcode',
    'date_reg',
    'status',
    'sku',
    'artic_type',
    'price_ref'
  ];

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
      $obj = new Product;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getActiveProduct()
  {
    return self::getConnect('R')
      ->select('id', 'title')
      ->where('status', 'A')
      ->get();
  }

  public static function getProductData($status = [], $type = false, $fiber_zones = false)
  {
    if (count($status)) {
      $data = self::getConnect('R')
        ->select(
          'islim_inv_articles.*',
          'islim_inv_categories.title as category_name',
          'islim_providers.name as provider_name'
        )
        ->join(
          'islim_inv_categories',
          'islim_inv_categories.id',
          'islim_inv_articles.category_id'
        )
        ->join(
          'islim_providers',
          'islim_providers.dni',
          'islim_inv_articles.provider_dni'
        );

      if ($type) {
        if ($type == 'F') { //solo si es fibra
          $ambiente = env('APP_ENV')=='production'?'P':'QA';

          $data = $data->join('islim_fiber_article_zone', function ($join) {
            $join->on('islim_fiber_article_zone.article_id', '=', 'islim_inv_articles.id')
              ->where('islim_fiber_article_zone.status', 'A');
          })
          ->join('islim_fiber_zone', function ($join) use ($ambiente, $fiber_zones) {
            $join->on('islim_fiber_zone.id', '=', 'islim_fiber_article_zone.fiber_zone_id')
              ->where('islim_fiber_zone.status', 'A')
              ->where('islim_fiber_zone.ambiente', $ambiente);
              if($fiber_zones){
                $join=$join->whereIn('islim_fiber_zone.id',$fiber_zones);
              }
          });
        }
      }

      $data = $data->whereIn('islim_inv_articles.status', $status);

      if ($type) {
        $data = $data->where('islim_inv_articles.artic_type', $type);
      }

      $data = $data->groupBy('islim_inv_articles.id')
              ->orderBy('islim_inv_articles.id', 'DESC');

      // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
      //     return is_numeric($binding) ? $binding : "'{$binding}'";
      // })->toArray());

      // Log::info($query);

      return $data->get();
    }

    return [];
  }

  public static function getProducts($status)
  {
    $products = Product::whereIn('status', $status)->get();
    foreach ($products as $product) {
      $product->category = ProductsCategory::where(['id' => $product->category_id])->first();
    }
    return $products;
  }

  public static function getProduct($id)
  {
    $product           = Product::where(['id' => $id])->first();
    $product->category = ProductsCategory::where(['status' => 'A', 'id' => $product->category_id])->first();
    return $product;
  }

  public static function getProductsWT($wt = [])
  {
    return self::getConnect('R')
      ->select('id', 'title', 'sku', 'artic_type')
      ->where('status', 'A')
      ->whereNotIn('id', $wt)
      ->get();
  }

  public static function getProducts_fromSKU($sku = false)
  {
    if ($sku) {
      return self::getConnect('R')
        ->where('sku', $sku)
        ->first();
    }
    return null;
  }
}
