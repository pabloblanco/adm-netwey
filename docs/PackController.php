<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Pack;
use App\Service;
use App\Product;
use App\ArticlePack;
use App\PackPrices;

class PackController extends Controller
{
    public function index() {
        $packs = Pack::getPacks('A');
        return response()->json($packs);
    }

    public function show ($id) {
		$pack = Pack::getPacks($id, 'A');
        return response()->json($pack);
	}

	public function store (Request $request) {
		$pack = Pack::create($request->input());
        $pack->date_reg = date ('Y-m-d H:i:s', time());
        $pack->save();
        return 'El paquete se ha creado con exito';
	}

    public function update (Request $request, $id) {
        $pack = Pack::find($id);
        $pack->title = $request->title;
        $pack->description = $request->description;
        $pack->status = $request->status;
        $pack->date_ini = $request->date_ini;
        $pack->date_end = $request->date_end;
        $pack->save();
        if ($request->status == 'A' || $request->status == 'I' || $request->status == 'T') {
            ArticlePack::where(['pack_id' => $id])->update(['status' => $request->status]);
            PackPrices::where(['pack_id' => $id])->update(['status' => $request->status]);
        }
        return 'El paquete se ha actualizado con exito';
    }

	public function destroy ($id) {
		$pack = Pack::find($id);
        $pack->status = 'T';
        $pack->save();
		return response()->json($pack);
	}

    public function view () {
        $packs = Pack::getPacks(session('admin') ? ['A', 'I'] : ['A'], 'A', 'A');
        $html = view('pages.ajax.pack', compact('packs'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }
    
    public function detailView (Request $request, $id) {
        $pack = Pack::getPack($id, 'A', 'A');
        $products = Product::getProducts(session('admin') ? ['A', 'I'] : ['A']);
        $services = Service::getServices('A', 'A');
        $hasproducts = (ArticlePack::where(['status' => 'A', 'pack_id' => $id])->count() > 0) ? true : false;
        $html = view('pages.ajax.pack.detail', compact('pack', 'products', 'services', 'hasproducts'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function updateService (Request $request, $id) {
        $pack = Pack::find($id);
        try {
            $relSP = PackPrices::where(['pack_id' => $id, 'service_id' => $request->service_id, 'type' => $request->method_pay])->first();
            $relSP = !isset($relSP) ? new PackPrices() : $relSP;
            $relSP->pack_id = $id;
            $relSP->service_id = $request->service_id;
            $relSP->type = $request->method_pay;
            $relSP->price_pack = $request->price_pack;
            $relSP->price_serv = $request->price_serv;
            $relSP->total_price = $request->total_price;
            $relSP->status = $request->status;
            $relSP->save();
            return response()->json(array('success' => true, 'msg'=>'El servicio se ha asociado con éxito', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error asociando el servicio', 'numError'=>1));
        }
    }

    public function updateProduct (Request $request, $id) {
        $pack = Pack::find($id);
        try {
            $relAP = ArticlePack::where(['pack_id' => $id, 'inv_article_id' => $request->inv_article_id])->first();
            if (!isset($relAP)) {
                $relAP = new ArticlePack();
                $relAP->pack_id = $id;
                $relAP->inv_article_id = $request->inv_article_id;
                $relAP->status = $request->status;
                $relAP->save();
            } else {
                ArticlePack::where(['pack_id' => $id, 'inv_article_id' => $request->inv_article_id])->update(['status' => 'A']);
            }
            return response()->json(array('success' => true, 'msg'=>'El producto se ha asociado con éxito', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error asociando el producto', 'numError'=>1));
        }
    }

    public function destroyRelation ($id, $product, $service) {
    }

    public function destroyProduct (Request $request, $id, $product) {
        try {
            $rel = ArticlePack::where(['pack_id' => $id, 'inv_article_id' => $product])->update(['status' => 'I']);
            return response()->json(array('success' => true, 'msg'=>'Se desasoció el producto de forma correcta', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un problema desasociando el producto', 'errorMsg'=>'Hubo un problema desasociando el producto', 'numError'=>1));
        }
    }

    public function destroyService (Request $request, $id, $service) {
        try {
            $rel = PackPrices::where(['pack_id' => $id, 'service_id' => $service])->update(['status' => 'T']);
            return response()->json(array('success' => true, 'msg'=>'Se desasoció el servicio de forma correcta', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un problema desasociando el servicio', 'errorMsg'=>'Hubo un problema desasociando el servicio', 'numError'=>1));
        }
    }

    public function associatedView ($id) {
        $pack = Pack::getPack($id, 'A', 'A');
        $products = Product::getProducts(session('user.platform')=='admin' ? ['A', 'I'] : ['A']);
        $services = Service::getServices('A', 'A');
        $html = view('pages.ajax.pack.detail', compact('pack', 'products', 'services'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }
}
