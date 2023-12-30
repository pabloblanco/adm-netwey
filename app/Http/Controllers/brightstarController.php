<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Orders;
use App\OrderDetail;
use App\Client;
use App\Inventory;
use App\AltanCode;
use App\Sale;
use App\WebPay;
use App\Service;
use App\ClientNetwey;
use App\Tripleta;
use App\Product;

class brightstarController extends Controller
{
    public function register(){
    	$html = view('pages.ajax.brightstar.registerDn')->render();

        return response()->json(array('success' => true, 'msg'=> $html, 'numError' => 0));
    }

    /*Devuelve los datos de una orden dado su id y que su estatus sea Activo y el pago sea exitoso*/
    public function getOrders(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		$res = ["error" => true, "message" => "Ocurrio un error."];

    		$orderId = $request->order;

    		if(!empty($orderId)){
    			//buscando la orden a la que se le va a asociar el dn
    			$order = Orders::select('id', 'amount', 'monto_envio', 'sub_monto', 'client_id', 'date')
    							 ->where([['id', $orderId], ['status', 'A']])
    						     ->first();

    			if(!empty($order)){
    				//Buscando la venta para verificar que el pago haya sido exitoso
    				$purchase = WebPay::select('id_webpay', 'date')
			    							->where([['order_id', $order->id], ['status', 'A']])
			    							->first();

			    	if(!empty($purchase)){
			    		$reshtml["purchase"] = $purchase;				
	    				$reshtml["order"] = $order;

	    				//Buscando cliente asociado a la compra
	    				$client = Client::select('dni', 'name', 'last_name', 'email', 'phone_home')
				    					  ->where('dni', $order->client_id)
				    					  ->first();

				    	if(!empty($client)){
				    		$reshtml["client"] = $client;

				    		//Buscando el detalle de la compra
				    		$details = OrderDetail::select(
			    								   			'islim_orders_details.id',
			    								   			'islim_orders_details.sku',
			    								   			'price',
			    								   			'lat',
			    								   			'lng',
			    								   			'serviciability',
			    								   			'title',
			    								   			'description'
			    								   		)
				    								->join(
			    								   			'islim_inv_articles',
			    								   			'islim_inv_articles.id',
			    								   			'=',
			    								   			'islim_orders_details.id_articles'
			    								   		)
			    								    ->where('id_ordens', $orderId)
			    								    ->get();

			    			if(count($details)){
			    				$reshtml["details"] = $details;
			    			}
				    	}
				    	$res["error"] = false;
				    	$res["html"] = view('pages.ajax.brightstar.orders', compact('reshtml'))->render();
				    }else{
				    	$res = ["error" => true, "message" => "La orden no se ha pagado o ya fue procesada."];
				    }
    			}else{
    				$res = ["error" => true, "message" => "No se encontro la orden o ya fue procesada."];
    			}
    		}else{
    			$res = ["error" => true, "message" => "No se puede procesar la solicitud."];
    		}

    		return response()->json($res);
    	}
    	return redirect()->route('root');
    }

    /*Hace el alta a nivel de base de datos de un DN comprado por brightstar*/
    public function processOrders(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		$res = ["error" => true, "message" => "Ocurrio un error."];

    		$orderId = $request->order;

    		if(!empty($orderId)){
    			//buscando la orden a la que se le va a asignar el dn
    			$order = Orders::select('id', 'amount', 'monto_envio', 'sub_monto', 'client_id', 'date')
    							 ->where([['id', $orderId], ['status', 'A']])
    						     ->first();

    			if(!empty($order)){
    				//buscando cliente asociado a la compra
    				$client = Client::select('dni', 'name', 'last_name', 'email', 'phone_home')
			    					  ->where('dni', $order->client_id)
			    					  ->first();

			    	if(!empty($client)){
			    		//Buscano compra para validar que el pago fue exitoso
			    		$purchase = WebPay::select('id','id_webpay')
			    							->where([['order_id', $order->id], ['status', 'A']])
			    							->first();

			    		if(!empty($purchase)){
			    			//Buscando detalle de la compra
				    		$details = OrderDetail::select(
			    								   			'islim_orders_details.id',
			    								   			'id_articles',
			    								   			'id_details',
			    								   			'address',
			    								   			'islim_orders_details.sku',
			    								   			'price',
			    								   			'lat',
			    								   			'lng',
			    								   			'serviciability',
			    								   			'title',
			    								   			'description'
			    								   		)
				    								->join(
			    								   			'islim_inv_articles',
			    								   			'islim_inv_articles.id',
			    								   			'=',
			    								   			'islim_orders_details.id_articles'
			    								   		)
			    								    ->where('id_ordens', $orderId)
			    								    ->get();

			    			if(count($details)){
			    				$arrArt = [];

			    				$now = date('Y-m-d H:i:s');

								foreach ($details as $detail) {
									if(!empty($request->{"dn-".$detail->id}) && !empty($request->{"iccid-".$detail->id}) && !empty($request->{"imei-".$detail->id})){

										//Verificando si dn ya esta asignado a un usuario
			    						$clientExist = ClientNetwey::select('msisdn')
			    												     ->where('msisdn', $request->{"dn-".$detail->id})
			    												     ->first();
										if(empty($clientExist)){
											//Agregnado data a array para poder verificar que vengan todos los dn requeridos para la compra
											$arrArt []= [
			    								"article" => [
			    									"inv_article_id" => $detail->id_articles,
													"warehouses_id" => env('WHEREHOUSE'),
													"msisdn" => $request->{"dn-".$detail->id},
													"iccid" => $request->{"iccid-".$detail->id},
													"imei" => $request->{"imei-".$detail->id},
													"date_reception" => $now,
													"date_sending" => $now,
													"status" => "V"
												],
												"serviceId" => $detail->id_details,
												"address" => $detail->address,
												"serviciability" => $detail->serviciability,
												"lat" => $detail->lat,
												"lng" => $detail->lng,
												"price" => $detail->price,
												"idDetail" => $detail->id,
		    								];
										}else{
											return response()->json(["error" => false, "message" => "El dn ".$request->{"dn-".$detail->id}." ya esta asociado a un cliente."]);
										}
	    							}
								}
								if(count($arrArt) == count($details)){
									//Aqui se procesa la compra
									foreach ($arrArt as $article) {
										//Creando el nuevo articulo
										$newart = Inventory::create($article["article"]);
										$newart->save();
										//$newArtId ;

										//Buscando servicio que compro el cliente
										$service = Service::select('id', 'title')
															->where('id', $article["serviceId"])
															->first();

										//Codigo de transaccion unica
										$codTra = uniqid().time();

										$point = DB::raw("(GeomFromText('POINT(".$article['lat']." ".$article['lng'].")'))");

										//Buscando el codigo de altam
										$codeAltan = AltanCode::select('codeAltan')
																->where([
																	['services_id', $service->id],
																	['supplementary', 'N'],
																	['status', 'A']
																])
																->first();

										//Creando el nuevo cliente
										$clientData = [
													"msisdn" => $article["article"]["msisdn"],
													"clients_dni" => $client->dni,
													"service_id" => $service->id,
													"address" => $article["address"],
													"type_buy" => "CO",
													"periodicity" => "MENSUAL",
													"unique_transaction" => $codTra,
													"serviceability" => $article["serviciability"],
													"lat" => $article["lat"],
													"lng" => $article["lng"],
													"point" => $point,
													"date_buy" => $order->date,
													"price_remaining" => 0,
													"date_reg" => $now,
													"status" => "I"
												];

										$newClient = ClientNetwey::create($clientData);
										$newClient->save();

										//Data de la venta (Plan que compro el cliente)
										$sale = [
											"services_id" => $service->id,
											"concentrators_id" =>  env('CONCENTRATOR_ID'),
											"api_key" => env('API_KEY_ALTAM_BR'),
											"unique_transaction" => $codTra,
											"type" => "P",
											"id_point" => "dmd@dmdgroup.net",
											"description" => $service->title,
											"amount" => $article["price"],
											"amount_net" => $article["price"] - ($article["price"] * env('TAX')),
											"com_amount" => 0,
											"msisdn" => $article["article"]["msisdn"],
											"conciliation" => "Y",
											"users_email" => "dmd@dmdgroup.net",
											"date_reg" => $now,
											"status" => 'I',
											"codeAltan" => $codeAltan->codeAltan,
											"inv_arti_details_id" => $newart->id
										];

										$sale = Sale::create($sale);
										$sale->save();

										//Actualizando dn en la tabla de detalle de compra
										OrderDetail::where('id', $article["idDetail"])
													 ->update([
													 	'msisdn' => $article["article"]["msisdn"],
													 	'status' => 'OK'
													 ]);

										//Actualizando estatus del dn en la tabla tripleta
										Tripleta::where('msisdn', $article["article"]["msisdn"])  
												  ->update(['status' => 'P']);

										//Marcando orden como procesada
										$order->status = 'P';
										$order->save();

										//Actualizando estatus del pago
										$purchase->status = 'E';
										$purchase->save();

										$res = ["error" => false, "message" => "Información actualizada correctamente."];
									}
								}else{
									$res = ["error" => true, "message" => "Debe asociar todos los dn de la compra."];
								}
			    			}else{
			    				$res = ["error" => true, "message" => "La compra no tiene detalle asociado."];
			    			}
			    		}else{
			    			$res = ["error" => true, "message" => "La compra no se ha pagado o ya fue procesada."];
			    		}
			    	}else{
			    		$res = ["error" => true, "message" => "No se encontro el cliente asociado a la compra."];
			    	}
    			}else{
    				$res = ["error" => true, "message" => "No se encontro la orden o ya fue procesada."];
    			}
    		}else{
    			$res = ["error" => true, "message" => "No se puede procesar la solicitud."];
    		}

    		return response()->json($res);
    	}
    	return redirect()->route('root');
    }

    /*Devuelve inventario de los productos que se venden desde brighstar(Los que tengan SKU), el inventario es consultado en la API de brightstar*/
    public function getInventary(Request $request){
    	$articulos = Product::select('sku', 'title', 'description')
    						  ->where('status', 'A')
    						  ->whereNotNull('sku')
    						  ->get();
    	
    	$error = true;
    	$devices = [];

    	if($articulos->count() > 0){
    		$data = ['data' => []];
    		$titles = [];
    		foreach ($articulos as $articulo) {
    			$data['data'] []= ['sku' => $articulo->sku];
    			$titles[$articulo->sku] = [
    										'title' => $articulo->title,
    										'desc' => $articulo->description
    									  ];
    		}

    		$data = json_encode($data);

    		$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => env('API_BRIGHTSTAR')."get-inventary-brightstar",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => "",
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 30,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => $data,
				CURLOPT_HTTPHEADER => array(
					"Content-Type: application/json",
					"cache-control: no-cache"
				)
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if (!$err) {
				$response = json_decode($response);
				if(!$response->error){
					$error = false;

					foreach ($response->data as $articulo) {
						$articulo->title = $titles[$articulo->sku]['title'];
						$articulo->desc = $titles[$articulo->sku]['desc'];
						$devices []= $articulo;
		    		}
				}
			}
    	}

    	$html = view('pages.ajax.brightstar.inventary', compact('error', 'devices'))->render();
    	return response()->json(array('html'=> $html));
    }
}
