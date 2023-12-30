<?php

namespace App\Http\Controllers;

use App\ArticlePack;
use App\FiberArticleZone;
use App\FiberZone;
use App\Helpers\API815;
use App\Helpers\APIProva;
use App\Pack;
use App\Product;
use App\ProductsCategory;
use App\ProductsProvider;
use App\User;
use Illuminate\Http\Request;
//use Log;

class ProductController extends Controller
{
  private static $typeProva = [
    '1' => 'MODEM',
    '2' => 'SIM',
    '3' => 'CELULAR',
  ];

  public function index()
  {
    $products = Product::getConnect('R')->all();
    foreach ($products as $product) {
      $pcats = ProductsCategory::getConnect('R')->where('id', $product->category_id)->get();
      foreach ($pcats as $cat) {
        $products->category_name = $cat->title;
      }
      $pprov = ProductsProvider::getConnect('R')->where('dni', $product->provider_dni)->get();
      foreach ($pprov as $prov) {
        $products->provider_name = $prov->name;
      }
    }
    return response()->json($products);
  }

  public function store(Request $request)
  {
    if (User::hasPermission(session('user.email'), 'A2P-CPR')) {
      $product           = Product::getConnect('W')->create($request->input());
      $product->date_reg = date('Y-m-d H:i:s');
      $product->save();

      if ($request->artic_type == 'F') {
        $arrArtFibs = explode(',', $request->prod_fiber_zone);
        foreach ($arrArtFibs as $key => $artFib) {
          $artf = explode('-', base64_decode($artFib));
          if (count($artf) == 2) {
            $article_fz                = FiberArticleZone::getConnect('W');
            $article_fz->fiber_zone_id = $artf[0];
            $article_fz->article_id    = $product->id;
            $article_fz->product_pk    = $artf[1];
            $article_fz->status        = $request->status;
            $article_fz->date_modified = date('Y-m-d H:i:s');
            $article_fz->save();
          }
        }
      }

      $msg = 'El producto se ha creado con exito';

      if (!empty($request->sku)) {
        if (!empty(self::$typeProva[$request->category_id])) {
          $res = APIProva::createSKU([
            'sku'         => $request->sku,
            'descripcion' => $request->title,
            'tipo'        => self::$typeProva[$request->category_id],
          ]);

          if (!$res['success']) {
            $msg .= ', pero fallo sincronización con prova';
          }
        }
      }

      return $msg;
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }

  public function update(Request $request, $id)
  {
    if (User::hasPermission(session('user.email'), 'A2P-UPR')) {
      $msg     = 'El producto se ha actualizado con exito';
      $product = Product::getConnect('W')->find($id);

      $delete = false;
      if ($product->sku != $request->sku) {
        $delete = $product->sku;
      }

      $product->category_id  = $request->category_id;
      $product->provider_dni = $request->provider_dni;
      $product->title        = $request->title;
      $product->description  = $request->description;
      $product->brand        = $request->brand;
      $product->model        = $request->model;
      //$product->type_barcode = $request->type_barcode;
      $product->sku        = $request->sku;
      $product->artic_type = $request->artic_type;
      $product->price_ref  = $request->price_ref;
      $product->status     = $request->status;
      $product->save();

      if ($request->artic_type == 'F') {

        $zonas      = array();
        $arrArtFibs = explode(',', $request->prod_fiber_zone);
        foreach ($arrArtFibs as $key => $artFib) {
          $artf = explode('-', base64_decode($artFib));
          if (count($artf) == 2) {
            array_push($zonas, $artf[0]);
          }
        }

        $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';
        $zonasAmb = FiberZone::getConnect('R')
          ->where('ambiente', $ambiente)
          ->pluck('id');

        FiberArticleZone::getConnect('W')
          ->where('article_id', $product->id)
          ->whereNotIn('fiber_zone_id', $zonas)
          ->whereIn('fiber_zone_id', $zonasAmb)
          ->update([
            'status'        => 'T',
            'date_modified' => date('Y-m-d H:i:s'),
          ]);

        $arrArtFibs = explode(',', $request->prod_fiber_zone);
        foreach ($arrArtFibs as $key => $artFib) {
          $artf = explode('-', base64_decode($artFib));
          if (count($artf) == 2) {

            $article_fz = FiberArticleZone::getConnect('W')
              ->updateOrCreate(
                [
                  'fiber_zone_id' => $artf[0],
                  'article_id'    => $product->id,
                ],
                [
                  'product_pk'    => $artf[1],
                  'status'        => $request->status,
                  'date_modified' => date('Y-m-d H:i:s'),
                ]
              );
          }
        }
      } else {
        FiberArticleZone::getConnect('W')
          ->where('article_id', $product->id)
          ->update([
            'date_modified' => date('Y-m-d H:i:s'),
            'status'        => 'T',
          ]);
      }

      if ($request->status == 'I') {
        $idsP = ArticlePack::getConnect('W')->getPacksIdByartic($id);
        Pack::where('status', 'A')
          ->whereIn('id', $idsP->pluck('pack_id'))
          ->update(['status' => 'I']);
      }

      if (!empty($request->sku)) {
        if ($delete) {
          APIProva::deleteSKU($delete);
        }

        if (!empty(self::$typeProva[$request->category_id])) {
          if (!$delete) {
            $res = APIProva::updateSKU([
              'sku'         => $request->sku,
              'descripcion' => $request->title,
              'tipo'        => self::$typeProva[$request->category_id],
            ], $request->sku);
          }

          if ($delete || !$res['success']) {
            //Si no se consiguio el sku se crea el producto
            if ($delete || (!empty($res['message']) && $res['message'] == 'no existe el sku')) {
              $res2 = APIProva::createSKU([
                'sku'         => $request->sku,
                'descripcion' => $request->title,
                'tipo'        => self::$typeProva[$request->category_id],
              ]);

              if (!$res2['success']) {
                $msg .= ', pero fallo sincronización con prova';
              }
            } else {
              $msg .= ', pero fallo sincronización con prova';
            }
          }
        }
      }

      return $msg;
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }

  public function destroy($id)
  {
    if (User::hasPermission(session('user.email'), 'A2P-DPR')) {
      $product         = Product::getConnect('W')->find($id);
      $product->status = 'T';
      $product->save();

      FiberArticleZone::getConnect('W')
        ->where('article_id', $product->id)
        ->update([
          'status' => 'T',
          'date_modified' => date('Y-m-d H:i:s')
        ]);

      $idsP = ArticlePack::getPacksIdByartic($id);
      Pack::getConnect('W')->where('status', 'A')
        ->whereIn('id', $idsP->pluck('pack_id'))
        ->update(['status' => 'I']);

      ArticlePack::getConnect('W')->whereIn('status', ['I', 'A'])
        ->where('inv_article_id', $id)
        ->update(['status' => 'T']);

      APIProva::deleteSKU($product->sku);

      return response()->json($product);
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }

  public function view()
  {
    if (session('user.platform') == 'admin') {
      $products = Product::getProductData(['A', 'I']);
    } else {
      $products = Product::getProductData(['A']);
    }

    $categories = ProductsCategory::getConnect('R')->where('status', 'A')->get();
    $providers  = ProductsProvider::getConnect('R')->where('status', 'A')->get();

    $listFZ = FiberZone::getfiberZone();

    $object = array(
      'products'   => !empty($products) ? $products : null,
      'categories' => !empty($categories) ? $categories : null,
      'providers'  => !empty($providers) ? $providers : null,
      'fiberzones' => !empty($listFZ) ? $listFZ : null,
    );

    $html = view('pages.ajax.product', compact('object'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function getFiberProductsList(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $fiber_zone = FiberZone::getConnect('R')->find($request->fiber_zone_id);
      if (!empty($fiber_zone)) {

        $datarr               = array();
        $datarr['fiber_zone'] = $fiber_zone->id;

        $ListEquipo815 = API815::doRequest("get-equipos", 'POST', $datarr);

        $detailEquipos = array();
        if ($ListEquipo815['success']) {
          $ListEquipo815 = $ListEquipo815['data']['eightFifteen']['object'];

          foreach ($ListEquipo815 as $key => $Equipo815) {

            $faz = FiberArticleZone::getConnect('R')
              ->where([
                ['fiber_zone_id', $fiber_zone->id],
                ['product_pk', $Equipo815['attributes']['pk']],
              ])
              ->whereIn('status', ['A', 'I'])
              ->first();

            if (!empty($faz)) {
              $art_asociate = $faz->article_id;
            } else {
              $art_asociate = null;
            }

            array_push($detailEquipos, ['id' => $Equipo815['attributes']['pk'], 'title' => $Equipo815['field']['value'], 'art_asociate' => $art_asociate]);
          }
        }

        return response()->json(array('success' => true, 'msg' => 'ok', 'data' => $detailEquipos, 'numError' => 0));
      }
    }
    return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando articulos de fibra en zona', 'errorMsg' => 'Hubo un error consultando articulos de fibra en zona', 'numError' => 1));
  }

  public function getArticleFiberProduct(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {

      $ambiente = env('APP_ENV') == 'production' ? 'P' : 'QA';

      $artsFZ = FiberArticleZone::getConnect('R')
        ->select(
          'islim_fiber_article_zone.fiber_zone_id',
          'islim_fiber_zone.name as fiber_zone_name',
          'islim_fiber_article_zone.product_pk as product_fz_pk'
        )
        ->join('islim_fiber_zone', 'islim_fiber_zone.id', 'islim_fiber_article_zone.fiber_zone_id')
        ->where([
          ['islim_fiber_article_zone.article_id', $request->product_id],
          ['islim_fiber_zone.status', 'A'],
          ['islim_fiber_zone.ambiente', $ambiente],
        ])
        ->whereIn('islim_fiber_article_zone.status', ['A', 'I'])
        ->get();

      foreach ($artsFZ as $artFZ) {

        $artFZ->product_fz_name = '';
        $datarr                 = array();
        $datarr['fiber_zone']   = $artFZ->fiber_zone_id;
        $datarr['pk']           = $artFZ->product_fz_pk;

        $Equipo815 = API815::doRequest("get-equipos", 'POST', $datarr);

        if ($Equipo815['success']) {
          //Log::info((String) json_encode($Equipo815['data']));
          $artFZ->product_fz_name = $Equipo815['data']['eightFifteen']['object'][0]['field']['value'];
        }
      }

      return response()->json(array('success' => true, 'msg' => 'ok', 'data' => $artsFZ, 'numError' => 0));

    }

    return response()->json(array('success' => false, 'msg' => 'Hubo un error consultando detalle de articulo de fibra en zona', 'errorMsg' => 'Hubo un error consultando detalle de articulo de fibra en zona', 'numError' => 1));
  }

  public function isUniqueSku(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->sku)) {
        $cant = Product::getConnect('R')
          ->where('sku', $request->sku)
          ->where('status', '<>', 'T');
        if (!empty($request->id)) {
          if ($request->id != '0') {
            $cant = $cant->where('id', '<>', $request->id);
          }
        }

        $cant = $cant->count();

        if ($cant == 0) {
          return response()->json(true);
        }
      }
      return response()->json(false);
    }
    return response()->json(false);
  }
}
