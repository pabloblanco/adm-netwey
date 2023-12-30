<?php

namespace App\Http\Controllers;

use App\ArticlePack;
use App\EsquemaComercial;
use App\Financing;
use App\Pack;
use App\PackEsquema;
use App\PackPrices;
use App\Product;
use App\Service;
use App\ServicesProm;
use App\FiberServiceZone;
use App\FiberArticleZone;
use Illuminate\Http\Request;

use Log;

class PackController extends Controller
{
  public function index()
  {
    $packs = Pack::getPacks('A');
    return response()->json($packs);
  }

  public function show($id)
  {
    $pack = Pack::getPacks($id, 'A');
    return response()->json($pack);
  }

  public function store(Request $request)
  {
    $inputs = $request->input();
    if (!empty($inputs['title']) && !empty($inputs['description'])) {
      $inputs['date_reg'] = date('Y-m-d H:i:s');
      $pack               = Pack::create($inputs);

      if (!empty($request->id_esquema) && count($request->id_esquema)) {
        foreach ($request->id_esquema as $esq) {
          PackEsquema::create([
            'id_pack'    => $pack->id,
            'id_esquema' => $esq,
            'date_reg'   => $inputs['date_reg'],
            'status'     => 'A',
          ]);
        }
      }
      return ['success' => true, 'msg' => 'El paquete se ha creado con exito'];
    }
    return ['success' => false, 'msg' => 'No se pudo crear el paquete'];
  }

  //Retorna las coordinaciones
  public function get_coordinations(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $name = $request->input('name');

      if (!empty($name)) {
        $data = EsquemaComercial::getEsquemaByType('C', $name)
          ->limit(10)->get();

        return response()->json(array('success' => true, 'coordinations' => $data));
      }

      return response()->json(array('success' => false));
    }
  }

  public function update(Request $request, $id)
  {
    $pack                      = Pack::find($id);
    $pack->title               = $request->title;
    $pack->description         = $request->description;
    $pack->date_ini            = $request->date_ini;
    $pack->date_end            = $request->date_end;
    $pack->view_web            = $request->view_web;
    $pack->desc_web            = $request->desc_web;
    $pack->sale_type           = $request->sale_type;
    $pack->pack_type           = $request->pack_type;
    $pack->acept_coupon        = $request->acept_coupon;
    $pack->is_portability      = $request->is_portability;
    $pack->service_prom_id     = $request->service_prom_id;
    $pack->is_visible_payjoy   = $request->is_visible_payjoy;
    $pack->is_visible_coppel   = $request->is_visible_coppel;
    $pack->is_visible_paguitos = $request->is_visible_paguitos;
    $pack->valid_identity      = $request->valid_identity;

    if ($pack->pack_type == 'MH' || $pack->pack_type == 'F') {
      $pack->is_migration = $request->is_migration;
    } else {
      $pack->is_migration = 'N';
    }

    $pack->status = $request->status;

    $date = date('Y-m-d H:i:s');

    PackEsquema::where(['id_pack' => $id])->update(['status' => 'T']);
    if (!empty($request->id_esquema) && count($request->id_esquema)) {
      foreach ($request->id_esquema as $esq) {
        PackEsquema::create([
          'id_pack'    => $id,
          'id_esquema' => $esq,
          'date_reg'   => $date,
          'status'     => 'A',
        ]);
      }
    }

    if (!empty($request->is_band_twenty_eight)) {
      $pack->is_band_twenty_eight = $request->is_band_twenty_eight;
    } else {
      $pack->is_band_twenty_eight = null;
    }

    $pack->save();

    if ($request->status == 'A') {
      $ids = PackPrices::select('service_id')
        ->where([
          ['pack_id', $id],
          ['status', 'A'],
        ])
        ->first();

      if (!empty($ids)) {
        Service::where('id', $ids->service_id)->update(['status' => 'A']);
      }

      $ida = ArticlePack::select('inv_article_id')
        ->where([
          ['pack_id', $id],
          ['status', 'A'],
        ])
        ->first();
      if (!empty($ida)) {
        Product::where('id', $ida->inv_article_id)->update(['status' => 'A']);
      }
    }

    return ['success' => true, 'msg' => 'El paquete se ha actualizado con exito'];
  }

  public function destroy($id)
  {
    $pack         = Pack::find($id);
    $pack->status = 'T';
    $pack->save();

    PackPrices::where(['pack_id' => $id])->update(['status' => 'T']);
    ArticlePack::where(['pack_id' => $id])->update(['status' => 'T']);
    PackEsquema::where(['id_pack' => $id])->update(['status' => 'T']);

    return response()->json($pack);
  }

  public function view()
  {
    $packs = Pack::getDataPaks();
    foreach ($packs as $pack) {
      $pack->esquemas = PackEsquema::getEsquemasByPack($pack->id);
    }

    $services_proms = ServicesProm::getServicesProm();
    $html           = view('pages.ajax.pack', compact('packs', 'services_proms'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function detailView(Request $request, $id)
  {
    $pack           = Pack::getPackById($id);
    $pack_type_prod = $pack->pack_type;
    if ($pack_type_prod == 'MH') {
      $pack_type_prod = 'M';
    }
    $financing = Financing::where('status', 'A')->get();
    if($pack_type_prod != 'F'){
      $products = Product::getProductData(['A'], $pack_type_prod);
      $services  = Service::getActiveServiceByType($pack->pack_type, 'A', $pack->is_band_twenty_eight);
      $html      = view('pages.ajax.pack.detail', compact('pack', 'products', 'services', 'financing'))->render();
    }
    else{
      $html      = view('pages.ajax.pack.detail', compact('pack', 'financing'))->render();
    }


    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function updateService(Request $request, $id)
  {
    $pack = Pack::find($id);
    try {
      PackPrices::where([
        'pack_id'    => $id,
        'service_id' => $request->service_id,
      ])
        ->whereIn('status', ['I', 'A'])
        ->update(['status' => 'T']);

      $relSP             = new PackPrices();
      $relSP->pack_id    = $id;
      $relSP->service_id = $request->service_id;

      if ($request->method_pay == 'CO') {
        $relSP->type         = $request->method_pay;
        $relSP->id_financing = null;
      } else {
        $relSP->type         = 'CR';
        $relSP->id_financing = $request->method_pay;
      }

      $relSP->price_pack  = $request->price_pack;
      $relSP->price_serv  = $request->price_serv;
      $relSP->total_price = $request->price_pack + $request->price_serv;
      $relSP->status      = 'A';
      $relSP->save();

      return response()->json(array('success' => true, 'msg' => 'El servicio se ha asociado con éxito', 'numError' => 0));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error asociando el servicio', 'numError' => 1));
    }
  }

  public function updateProduct(Request $request, $id)
  {
    $pack = Pack::find($id);
    try {
      if ($request->retail == 'Y') {
        if (ArticlePack::areMoreProductsRetail($request->inv_article_id)) {
          return response()->json([
            'success'  => false,
            'msg'      => 'El producto Ya tiene un pack tipo retail asociado.',
            'numError' => 0,
          ]);
        }
      }

      //ArticlePack::where('pack_id', $id)->update(['status'=> 'I']);

      ArticlePack::where([
        ['pack_id', $id],
        ['inv_article_id', $request->inv_article_id],
      ])
        ->whereIn('status', ['I', 'A'])
        ->update(['status' => 'T']);

      // $relAP                 = new ArticlePack();
      // $relAP->pack_id        = $id;
      // $relAP->inv_article_id = $request->inv_article_id;
      // $relAP->retail         = $request->retail;
      // $relAP->status         = 'A';
      // $relAP->save();

      $relAP = ArticlePack::getConnect('W')
        ->updateOrCreate(
            [
              'pack_id' => $id,
              'inv_article_id' => $request->inv_article_id
            ],
            [
              'retail' => $request->retail,
              'status' => 'A'
            ]
        );


      return response()->json(array('success' => true, 'msg' => 'El producto se ha asociado con éxito', 'numError' => 0));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error asociando el producto', 'numError' => 1));
    }
  }

  public function destroyRelation($id, $product, $service)
  {
  }

  public function destroyProduct(Request $request, $id, $product)
  {
    try {
      $rel = ArticlePack::where([
        'pack_id'        => $id,
        'inv_article_id' => $product,
      ])
        ->update(['status' => 'T']);

      return response()->json(array('success' => true, 'msg' => 'Se desasoció el producto de forma correcta', 'numError' => 0));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un problema desasociando el producto', 'errorMsg' => 'Hubo un problema desasociando el producto', 'numError' => 1));
    }
  }

  public function destroyService(Request $request, $id, $service)
  {
    try {
      $rel = PackPrices::where([
        'pack_id'    => $id,
        'service_id' => $service,
      ])
        ->update(['status' => 'T']);

      return response()->json(array('success' => true, 'msg' => 'Se desasoció el servicio de forma correcta', 'numError' => 0));
    } catch (Exception $e) {
      return response()->json(array('success' => false, 'msg' => 'Hubo un problema desasociando el servicio', 'errorMsg' => 'Hubo un problema desasociando el servicio', 'numError' => 1));
    }
  }

  public function associatedView($id)
  {
    $pack      = Pack::getPack($id, 'A', 'A');
    $products  = Product::getProducts(session('user.platform') == 'admin' ? ['A', 'I'] : ['A']);
    $services  = Service::getServices('A', 'A');
    $financing = Financing::where('status', 'A')->get();
    $html      = view('pages.ajax.pack.detail', compact('pack', 'products', 'services', 'financing'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  //Retorna los servicios que se pueden asociar a un pack de fibra, si el pack tiene un producto asociado toma en cuenta las zonas de fibra de ese producto
  public function getServicesFiberZonePack(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $pack_id = $request->input('pack_id');
      $product_id = $request->input('product_id');
      $zones = [];
      if($product_id !== ''){
        $ambiente = env('APP_ENV')=='production'?'P':'QA';
        $fiberzones = FiberArticleZone::getConnect('R')
                  ->select('islim_fiber_zone.id')
                  ->where('islim_fiber_article_zone.article_id',$product_id)
                  ->where('islim_fiber_article_zone.status','A')
                  ->join('islim_fiber_zone', function ($join) use ($ambiente) {
                    $join->on('islim_fiber_zone.id', '=', 'islim_fiber_article_zone.fiber_zone_id')
                      ->where('islim_fiber_zone.status', 'A')
                      ->where('islim_fiber_zone.ambiente', $ambiente);
                  })->get();
        if(count($fiberzones)){
          $zones = $fiberzones->pluck('id');
        }
      }

      $pack = Pack::getPackById($pack_id);
      $services  = Service::getActiveServiceByType($pack->pack_type, 'A', $pack->is_band_twenty_eight,$zones);

      if($services)
        return response()->json(array('success' => true, 'services' => $services));
      return response()->json(array('success' => false));
    }
  }

  //Retorna los articulos que se pueden asociar a un pack de fibra, si el pack tiene un servicio asociado toma en cuenta las zonas de fibra de ese servicio
  public function getProductsFiberZonePack(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $pack_id = $request->input('pack_id');
      $service_id = $request->input('service_id');
      $zones = [];
      if($service_id !== ''){
        $ambiente = env('APP_ENV')=='production'?'P':'QA';
        $fiberzones = FiberServiceZone::getConnect('R')
                  ->select('islim_fiber_zone.id')
                  ->where('islim_fiber_service_zone.service_id',$service_id)
                  ->where('islim_fiber_service_zone.status','A')
                  ->join('islim_fiber_zone', function ($join) use ($ambiente) {
                    $join->on('islim_fiber_zone.id', '=', 'islim_fiber_service_zone.fiber_zone_id')
                      ->where('islim_fiber_zone.status', 'A')
                      ->where('islim_fiber_zone.ambiente', $ambiente);
                  })->get();
        if(count($fiberzones)){
          $zones = $fiberzones->pluck('id');
        }
      }

      $pack = Pack::getPackById($pack_id);
      $products = Product::getProductData(['A'], $pack->pack_type, $zones);

      if($products)
        return response()->json(array('success' => true, 'products' => $products));
      return response()->json(array('success' => false));
    }
  }

}
