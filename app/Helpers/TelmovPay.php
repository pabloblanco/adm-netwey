<?php
namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TelmovPay
{
	/**
    * Busca si el vendedor esta dado de alta en TelmovPay.
    *
    * @param  	string  $email
    * @return 	string  TelmovPay_id
    * 			null si no se puede procesar
    */
	public static function getSeller($email = null)
	{
	    
		if (isset($email) && !empty($email)){

			$url = env('URL_TELMOVPAY') . 'api/seller-get';

			// Se envia la siguiente estructura:
			//
			// {
			//     "email" : "vendedor3@correo.com"
			// }

		    $dataBody = [
		        'email'	=> $email
		    ];

			$curl = curl_init();

		    if (env('APP_ENV', 'local') == 'local') {
		      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		    }

		    curl_setopt_array($curl, array(
		    	CURLOPT_URL            => $url,
		    	CURLOPT_RETURNTRANSFER => true,
		    	CURLOPT_ENCODING       => "",
		    	CURLOPT_TIMEOUT        => 60000,
		    	CURLOPT_CUSTOMREQUEST  => "POST",
		    	CURLOPT_POSTFIELDS     => json_encode($dataBody),
		    	CURLOPT_HTTPHEADER     => array(
	        		// Set here requred headers
		    		"accept: */*",
		    		"accept-language: en-US,en;q=0.8",
		    		"content-type: application/json",
			    	"Authorization: Bearer " . env('API_KEY_TELMOVPAY')	    		
		    	),
		    ));

		    $response = curl_exec($curl);
		    $err = curl_error($curl);

	    	curl_close($curl);

			// Devuelve la siguiente estructura:
			//
			// {
			//     "success": true,
			//     "data": [
			//         {
			//             "_id": "dd301970-c7cc-4000-8000-c066b6b866e6",
			//             "curp": "HEMG800722MJCRRD05",
			//             "emailAddress": "vendedor3@correo.com",
			//             "names": {
			//                 "firstName": "VENDEDOR",
			//                 "paternalLastName": "tres",
			//                 "maternalLastName": "N/A"
			//             },
			//             "partnerId": "83754047-8e57-4000-8000-458b054089e6",
			//             "creationDate": "2022-10-26T15:30:54.676Z",
			//             "status": "DEACTIVATED_BY_PARTNER",
			//             "agreementId": null,
			//             "address": {
			//                 "city": null,
			//                 "neighborhood": null,
			//                 "postalCode": null,
			//                 "state": null,
			//                 "streetNameAndNumber": null
			//             },
			//             "birthDate": null,
			//             "sex": null
			//         }
			//     ],
			//     "message": "Ejecución exitosa"
			// }

		    if (!$err){

		    	if (isset($response['success']) && !empty($response['success'])) {

			    	if ($response['success']){

				    	if (isset($response['data']['_id']) && !empty($response['data']['_id'])) {

				    		$telmovPayId = $response['data']['_id'];
				    		if (env('APP_TELMOV_DEBUG')){
						    	Log::info("email: ".$email);
							    Log::info("url: ".$url);	    	
					   			Log::info("request -->  ".json_encode($dataBody));
					   			Log::info("response --> ".$response);
					   			Log::info("telmovPayId --> ".$telmovPayId);	
					   			// $request = false, $data_send = false, $data_return = false, $time = false, $type = false, $error_details = false, $systemRequest = null
					   			// TelmovPayLogs::saveLogBD($reqObject, $params, $dataJson, $endTime, $type, $error, $systemRequest);
				   			} 
				   			return $telmovPayId;			    		

				    	}else{

				    		if (env('APP_TELMOV_DEBUG')){
						    	Log::info("email: ".$email);
							    Log::info("url: ".$url);	    	
					   			Log::info("request -->  ".json_encode($dataBody));
					   			Log::info("response --> ".$response);
					   			Log::info("message -->  No se pudo procesar porque el campo data->_id venia vacio");	
				   			} 
				   			return null;
				    	}
				    }else{

				    	if (env('APP_TELMOV_DEBUG')){
						    Log::info("email: ".$email);
							Log::info("url: ".$url);	    	
					   		Log::info("request -->  ".json_encode($dataBody));
					   		Log::info("response --> ".$response);
					   		Log::info("message -->  No se pudo procesar porque TelmovPay devolvio success:false");	
				   		} 
				   		return null;
				    }
			    }else{
			    	
			    	if (env('APP_TELMOV_DEBUG')){
					    Log::info("email: ".$email);
						Log::info("url: ".$url);	    	
				   		Log::info("request -->  ".json_encode($dataBody));
				   		Log::info("response --> ".$response);
				   		Log::info("message -->  No se pudo procesar porque TelmovPay devolvio success vacio");	
			   		}
			   		return null; 
				}
		    }else{
		    	
		    	if (env('APP_TELMOV_DEBUG')){
			    	Log::info("email: ".$email);
				    Log::info("url: ".$url);	    	
		   			Log::info("request -->  ".json_encode($dataBody));
		   			Log::info("response --> ".$response);
		   			Log::info("error procesando la consulta a la API Intermedia --> ".$err);
		   		}
		    }
		    return null;
		}

		if (env('APP_TELMOV_DEBUG')){
			Log::info("email: ".$email);
			Log::info("message -->  No se pudo procesar porque el campo email venia vacio");
		}
		return null;
	}

	/**
    * Busca si el vendedor esta activo o inactivo en TelmovPay.
    *
    * @param  	string  $email
    * @return 	boolean true si esta activo
    * 			boolean false si se encuentra inactivo
    * 			null si no se puede procesar
    */
	public static function isSellerActive($email = null)
	{

		if (isset($email) && !empty($email)){
			$url = env('URL_TELMOVPAY') . 'api/seller-get';

			// Se envia la siguiente estructura:
			//
			// {
			//     "email" : "ejemplo.martinez@netwey.com.mx"
			// }

		    $dataBody = [
		        'email'	=> $email
		    ];
	    
			$curl = curl_init();

		    if (env('APP_ENV', 'local') == 'local') {
		      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		    }

		    curl_setopt_array($curl, array(
		    	CURLOPT_URL            => $url,
		    	CURLOPT_RETURNTRANSFER => true,
		    	CURLOPT_ENCODING       => "",
		    	CURLOPT_TIMEOUT        => 60000,
		    	CURLOPT_CUSTOMREQUEST  => "POST",
		    	CURLOPT_POSTFIELDS     => json_encode($dataBody),
		    	CURLOPT_HTTPHEADER     => array(
	        		// Set here requred headers
		    		"accept: */*",
		    		"accept-language: en-US,en;q=0.8",
		    		"content-type: application/json",
			    	"Authorization: Bearer " . env('API_KEY_TELMOVPAY')	    		
		    	),
		    ));

		    $response = curl_exec($curl);
		    $err = curl_error($curl);

	    	curl_close($curl);

			// Devuelve la siguiente estructura:
			//
			// {
			//     "success": true,
			//     "data": [
			//         {
			//             "_id": "0ffebd21-9ddf-4000-8000-8643226003a5",
			//             "curp": "ZAGA960402HGRRRL09",
			//             "emailAddress": "alberto.zaragoza@netwey.com.mx",
			//             "names": {
			//                 "firstName": "ALBERTO",
			//                 "maternalLastName": "GARCIA",
			//                 "paternalLastName": "ZARAGOZA"
			//             },
			//             "partnerId": "83754047-8e57-4000-8000-458b054089e6",
			//             "creationDate": "2022-08-23T14:13:39.317Z",
			//             "status": "ACTIVE",
			//             "agreementId": null,
			//             "address": {
			//                 "city": null,
			//                 "neighborhood": null,
			//                 "postalCode": null,
			//                 "state": null,
			//                 "streetNameAndNumber": null
			//             },
			//             "birthDate": null,
			//             "sex": null
			//         }
			//     ],
			//     "message": "Ejecución exitosa"
			// }

		    if (!$err) {

		    	if(isset($response['success']) && !empty($response['success'])) {

			    	if ($response['success']){

			    		if (isset($response['data']['status']) && !empty($response['data']['status'])) {

				    		if ($response['data']['status'] == 'ACTIVE') {
					    		
					    		if (env('APP_TELMOV_DEBUG')){
							    	Log::info("email: ".$email);
							    	Log::info("url: ".$url);
						   			Log::info("request -->  ".json_encode($dataBody));
						   			Log::info("response --> ".$response);
						   			Log::info("isSellerActivated --> true");
					   			}
					   			return true;
					   		}else{

					   			if (env('APP_TELMOV_DEBUG')){
							    	Log::info("email: ".$email);
							    	Log::info("url: ".$url);
						   			Log::info("request -->  ".json_encode($dataBody));
						   			Log::info("response --> ".$response);
						   			Log::info("isSellerActivated --> false");
					   			}
					   			return false;
					   		}
			    		}

			    		if (env('APP_TELMOV_DEBUG')){
					    	Log::info("email: ".$email);
					    	Log::info("url: ".$url);
				   			Log::info("request -->  ".json_encode($dataBody));
				   			Log::info("response --> ".$response);
				   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio data status vacio");
			   			}
			   			return null;
			    	}else{

			    		if (isset($response['message']) && !empty($response['message'])) {
			    			$message = "message -->  No se pudo procesar la peticion. TelmovPay respondio: "$response['message'];
			    		}else{
			    			$message = "message -->  No se pudo procesar la peticion. TelmovPay respondio success:false sin mensaje"
			    		}

						if (env('APP_TELMOV_DEBUG')){
							Log::info("email: ".$email);
							Log::info($message);
						}
						return null;
			    	}
			    }else{

		    		if (env('APP_TELMOV_DEBUG')){
				    	Log::info("email: ".$email);
				    	Log::info("url: ".$url);
			   			Log::info("request -->  ".json_encode($dataBody));
			   			Log::info("response --> ".$response);
			   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio success vacio");
		   			}
		   			return null;	
		    }else{

		    	if (env('APP_TELMOV_DEBUG')){
			    	Log::info("email: ".$email);
				    Log::info("url: ".$url);	    	
		   			Log::info("request -->  ".json_encode($dataBody));
		   			Log::info("response --> ".$response);
		   			Log::info("error procesando la consulta a la API Intermedia --> ".$err);
		   		}
		   		return null;
		    }
		}

		if (env('APP_TELMOV_DEBUG')){
			Log::info("email: ".$email);
			Log::info("message -->  No se pudo procesar la peticion porque email es null o viene vacio");
		}
		return null;
	}

	/**
    * Registra el vendedor en TelmovPay.
    *
    * @param  	string  $email
    * @param  	string  $curp
    * @param  	string  $name 
    * @param  	string  $lastName
    * @return 	string  TelmovPay_id
    * 			null si no se puede procesar
    */
	public static function createSeller($email = '', $curp = '', $name = '', $lastName = '')
	{
		$telmovPayId = self::getSeller($email);

		if(isset($email) && isset($curp) && isset($name) && isset($lastName) && !empty($email) && !empty($curp) && !empty($name) && !empty($lastName)){

			// Si no esta registrado el vendedor con ese correo en TelmovPay, entonces se crea
			if (is_null($telmovPayId)){

				$TelmovPayId = '';
				$url = env('URL_TELMOVPAY') . 'api/seller-create';

				// Se envia la siguiente estructura:
				//
				// {
				//     "sellers":[
				//         {
				//             "curp":"HEMG800721MJCRRD05",
				//             "emailAddress": "mf5_blanco@hotmail.com",
				//             "firstName": "Gabriela",
				//             "paternalLastName":"Hernandez",
				//             "maternalLastName":"N/A"
				//         }
				//     ]
				// }

			    $dataBody = [
			        'emailAddress'		=> $email,
			        'curp'				=> $curp,
			        'firstName'			=> $name,
			        'paternalLastName'	=> $lastName,
			        'maternalLastName'	=> ''
			    ];
		    
				$curl = curl_init();

			    if (env('APP_ENV', 'local') == 'local') {
			      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			    }

			    curl_setopt_array($curl, array(
			    	CURLOPT_URL            => $url,
			    	CURLOPT_RETURNTRANSFER => true,
			    	CURLOPT_ENCODING       => "",
			    	CURLOPT_TIMEOUT        => 60000,
			    	CURLOPT_CUSTOMREQUEST  => "POST",
			    	CURLOPT_POSTFIELDS     => json_encode($dataBody),
			    	CURLOPT_HTTPHEADER     => array(
		        		// Set here requred headers
			    		"accept: */*",
			    		"accept-language: en-US,en;q=0.8",
			    		"content-type: application/json",
			    		"Authorization: Bearer " . env('API_KEY_TELMOVPAY')
			    	),
			    ));

			    $response = curl_exec($curl);
			    $err = curl_error($curl);

		    	curl_close($curl);

				// Devuelve la siguiente estructura:
				//
				// {
				// 	"success":true,
				// 	"data":
				// 	{
				// 		"valid":
				// 		[
				// 			{
				// 				"curp":"HEMG800721MJCRRD05",
				// 				"emailAddress":"mf5_blanco@hotmail.com",
				// 				"names":
				// 				{
				// 					"firstName":"Guadalupe",
				// 					"paternalLastName":"Hernandez",
				// 					"maternalLastName":"N\/A"
				// 				},
				// 				"telmovpay_id":"785213f2-3b47-4000-8000-741f68bfa5c3"
				// 			}
				// 		],
				// 		"invalid":[]
				// 	},
				// 	"message":"Ejecucion exitosa"
				// }

			    if (!$err){

			    	if(isset($response['success']) && !empty($response['success'])){

				    	if ($response['success']){

							if (isset($response['data']['valid']['telmovpay_id']) && !empty($response['data']['valid']['telmovpay_id'])) {

			    				$telmovPayId = self::getSeller($email);

			    				if (!is_null($telmovPayId)

							    	if (env('APP_TELMOV_DEBUG')){
								    	Log::info("email: ".$email);
									    Log::info("url: ".$url);	    	
							   			Log::info("request -->  ".json_encode($dataBody));
							   			Log::info("response --> ".$response);
							   			Log::info("telmovPayId --> El vendedor se creo con exito en TelmovPay y tiene el id: ".$telmovPayId);	   
						   			} 
						   			return $telmovPayId;
						   		}else{

						   			if (env('APP_TELMOV_DEBUG')){
								    	Log::info("email: ".$email);
									    Log::info("url: ".$url);	    	
							   			Log::info("request -->  ".json_encode($dataBody));
							   			Log::info("response --> ".$response);
							   			Log::info("message -->  Fallo la confirmacion de la creacion del seller en TelmovPay");	   
						   			} 
						   			return null;
						   		}
							}else{

					    		if (env('APP_TELMOV_DEBUG')){
							    	Log::info("email: ".$email);
							    	Log::info("url: ".$url);
						   			Log::info("request -->  ".json_encode($dataBody));
						   			Log::info("response --> ".$response);
						   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio data->valid->telmovpay_id vacio");
					   			}
					   			return null;
							}	
				    	}else{

				    		if (env('APP_TELMOV_DEBUG')){
						    	Log::info("email: ".$email);
						    	Log::info("url: ".$url);
					   			Log::info("request -->  ".json_encode($dataBody));
					   			Log::info("response --> ".$response);
					   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio success false");
				   			}
				   			return null;
			    		}
			    	}else{

			    		if (env('APP_TELMOV_DEBUG')){
					    	Log::info("email: ".$email);
					    	Log::info("url: ".$url);
				   			Log::info("request -->  ".json_encode($dataBody));
				   			Log::info("response --> ".$response);
				   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio success vacio");
			   			}
			   			return null;
			    	} 
		
			    }else{

			    	if (env('APP_TELMOV_DEBUG')){
				    	Log::info("email: ".$email);
					    Log::info("url: ".$url);	    	
			   			Log::info("request -->  ".json_encode($dataBody));
			   			Log::info("response --> ".$response);
			   			Log::info("error procesando la consulta a la API Intermedia --> ".$err);
			   		}
			   		return null;
				}
			}else{

				if (env('APP_TELMOV_DEBUG')){
					Log::info("email: ".$email." curp: ".$curp." name: ".$name." lastName: ".$lastName." telmovPayId: ".$telmovPayId);
					Log::info("message -->  El vendedor con telmovpay_id = ".$telmovPayId." ya estaba creado en TelmovPay");
				}
				return $telmovPayId;
			}	
		}

		if (env('APP_TELMOV_DEBUG')){
			Log::info("email: ".$email);
			Log::info("message -->  No se pudo procesar la peticion porque email, curp, name o lastname son null o viene alguno vacio");
		}
		return null;
	}

	/**
    * Busca si el vendedor esta dado de alta en TelmovPay.
    *
    * @param  	string  $email
    * @return 	TelmovPay_id
    * 			null si no se puede procesar
    */
	// Cambia el status del vendedor en TelmovPay
	// Devuelve el estado actual del vendedor en Telmov, True o False, o null si hubo algun error en la consulta
	public static function setSellerStatus($email = '', $activate = null)
	{
		if (isset($email) && !empty($email) && isset($activate) && !empty($activate)){

			if (self::isSellerActive($email) && !$activate) || (!self::isSellerActive($email) && $activate){
						
				$url = env('URL_TELMOVPAY') . 'api/seller-activation';

				// Se envia la siguiente estructura:
				//
				// {
				//     "seller_email" : "vendedor3@correo.com"
				// }

			    $dataBody = [
			        'seller_email'	=> $email
			    ];
		    
				$curl = curl_init();

			    if (env('APP_ENV', 'local') == 'local') {
			      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
			      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
			    }

			    curl_setopt_array($curl, array(
			    	CURLOPT_URL            => $url,
			    	CURLOPT_RETURNTRANSFER => true,
			    	CURLOPT_ENCODING       => "",
			    	CURLOPT_TIMEOUT        => 60000,
			    	CURLOPT_CUSTOMREQUEST  => "POST",
			    	CURLOPT_POSTFIELDS     => json_encode($dataBody),
			    	CURLOPT_HTTPHEADER     => array(
		        		// Set here requred headers
			    		"accept: */*",
			    		"accept-language: en-US,en;q=0.8",
			    		"content-type: application/json",
			    		"Authorization: Bearer " . env('API_KEY_TELMOVPAY')
			    	),
			    ));

			    $response = curl_exec($curl);
			    $err = curl_error($curl);

		    	curl_close($curl);

		    	//	Respuesta de la API intermedia de TelmovPay
		    	// {
    			//		"success": true,
    			//		"data": {
        		//			"status": true o false dependiendo de si estaba o inactivo previamente
    			//		},
    			//		"message": "Ejecución exitosa"
				//	}
 
			    if (!$err) { 

			    	if (isset($response['success']) && !empty($response['success']) {

			    		if ($response['success']) {

			    			if (isset($response['data']['status']) && !empty($response['data']['status'])) {

						    	if (env('APP_TELMOV_DEBUG')) {
							    	Log::info("email: ".$email);
								    Log::info("url: ".$url);	    	
						   			Log::info("request -->  ".json_encode($dataBody));
						   			Log::info("response --> ".$response);
						   			Log::info("status --> ".$response['data']['status']);	 
						   		}
						    	return $response['data']['status'];
						    }else{

					    		if (env('APP_TELMOV_DEBUG')){
							    	Log::info("email: ".$email);
							    	Log::info("url: ".$url);
						   			Log::info("request -->  ".json_encode($dataBody));
						   			Log::info("response --> ".$response);
						   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio data->status vacio");
					   			}
					   			return null;
						    }
						}else{

				    		if (env('APP_TELMOV_DEBUG')){
						    	Log::info("email: ".$email);
						    	Log::info("url: ".$url);
					   			Log::info("request -->  ".json_encode($dataBody));
					   			Log::info("response --> ".$response);
					   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio success false");
				   			}
				   			return null;
						}
					}else{

			    		if (env('APP_TELMOV_DEBUG')){
					    	Log::info("email: ".$email);
					    	Log::info("url: ".$url);
				   			Log::info("request -->  ".json_encode($dataBody));
				   			Log::info("response --> ".$response);
				   			Log::info("message -->  No se pudo procesar la peticion porque TelmovPay devolvio success vacio");
			   			}
			   			return null;
					}
			    }else{

			    	if (env('APP_TELMOV_DEBUG')){
				    	Log::info("email: ".$email);
					    Log::info("url: ".$url);	    	
			   			Log::info("request -->  ".json_encode($dataBody));
			   			Log::info("response --> ".$response);
			   			Log::info("error procesando la consulta a la API Intermedia --> ".$err);
			   		}
				    return null;
			    }	    	
			}else{

				if (env('APP_TELMOV_DEBUG')){
					Log::info("email: ".$email);
					Log::info("activate: ".$activate);	    	
					Log::info("message -->  No se proceso la peticion porque el vendedor ya estaba en ese estado Activo = ".$activate);
				}
				return null;
			}
		}else{

			if (env('APP_TELMOV_DEBUG')){
				Log::info("email: ".$email);
				Log::info("activate: ".$activate);	    	
				Log::info("message -->  No se pudo procesar la peticion porque email o activate son null o viene alguno vacio");
			}
			return null;
		}
	}
}
