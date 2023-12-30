<?php
namespace App\Helpers;

class APIClient {
	public static function getClient($msisdn){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		    CURLOPT_URL => env('URL_CLIENT').'get-info-client',
		    CURLOPT_RETURNTRANSFER => true,
		    CURLOPT_ENCODING => "",
		    CURLOPT_TIMEOUT => 30000,
		    CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => json_encode(['msisdn' => $msisdn]),
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_SSL_VERIFYHOST => false,
		    CURLOPT_HTTPHEADER => array(
		    	// Set here requred headers
		        "accept: */*",
		        "accept-language: en-US,en;q=0.8",
		        "content-type: application/json",
		        "Authorization: Bearer ".env('TOKE_CLIENT')
		    ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		    return json_encode([
		    			'success' => false, 
		    			'data' => [
		    				'msg' => 'Ocurrio un error consultando profile.', 
		    				'cod_err' => $err
		    			]
		    		]);
		}else{
			return json_decode($response);
		}
	}
}