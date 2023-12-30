<?php

/*
Septiembre 2022
 */

namespace App\Helpers;

use Illuminate\Http\Request;

use App\TelmovPayLogs;
use App\SystemTokenLife;
use App\TelmovPayRequest;

class Curl {

  public function __construct() {

    date_default_timezone_set('America/Mexico_City');

  }

  /**
   * 
   * Función que ejecuta las llamadas de forma estandarizada a otros sistemas externos
   * 
   * @param request: Nombre del request a llamar. Debe ser una llave del arreglo público REQUESTS.
   * @param URLParams: Representa el elemento a enviar como parámetro en la URL. Si el request usa identificadores en la estructura del URL, esto contiene dicho identificador, en caso contrario contiene o una representación de objeto en JSON o un valor nulo.
   * @param params: Representa el elemento a enviar como parámetro en el cuerpo de la llamada. Contiene o una representación de objeto en JSON o un valor nulo.
   * @param token: Representa el token de seguridad a enviar en la cabecera de la llamada. Contiene o una cadena o un valor nulo.
   * 
   * @return 'success': resultado del procesamiento de la llamada a Telmovpay (true o false).
      'data': objeto(s) retornados por Telmovpay (En caso de error en ejecución puede contener algo de información del error).
      'message': descripción del resultado de la ejecución (Exitosa o descripción del error).
      'debug': solo disponible en ambiente de desarrollo. Contiene información técnica relacionada con el error para ayudar a precisar mejor la falla.
   * 
   */
  public static function execute ($service = null, $URLParams = null, $params = null, Request $systemRequest = null) {

    $startTime   = microtime(true);

    if (!isset($service))
      return ['success' => false, 'data' => NULL, 'message' => 'Servicio no existe'];

    $REQUESTS = Google::$REQUESTS;

    foreach (Slack::$REQUESTS as $i => $v)
      $REQUESTS[$i] = $v;

    foreach (Telmovpay::$REQUESTS as $i => $v)
      $REQUESTS[$i] = $v;

    if (!array_key_exists($service, $REQUESTS))
      return ['success' => false, 'data' => NULL, 'message' => 'Proceso no existe'];

    $request = $REQUESTS[$service];

    $url = ($service === 'token' || $service === 'slack-notification') ?
      $request['url'] :
      (env('URL_TELMOVPAY') . '/' . $request['url']);

    $curl = curl_init();

    /**
     * Si el entorno es local, no se validará el certificado de SSL ni en el HOST ni en nuestro servidor
     */
    if (env('APP_ENV', 'local') == 'local') {

      curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

    }

    /**
     * Si envían Parámetros para el URL, validamos si el request lo usa como Identificador o si es un objeto lo que se espera
     */
    if (isset($URLParams))
      $url = $url . ($request['usesParamId'] ? '/' : '?') . $URLParams;

    $header = ['content-type: application/json'];

    $token = null;

    if (!($service === 'token' || $service === 'slack-notification'))
      $token = SystemTokenLife::getToken($systemRequest);

    /**
     * Si envían el Token de autenticación, lo configuramos en la cabecera de la llamada.
     */
    if (isset($token))
      $header[] = 'Authorization: ' . $token['tokenType'] . ' ' . $token['token'];

    $options = [
      CURLOPT_URL => $url,
      CURLOPT_HTTPHEADER => $header,
      CURLOPT_CUSTOMREQUEST => $request['method'],
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 60,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1
    ];

    if ($request['method'] !== 'GET')
      $options[CURLOPT_CUSTOMREQUEST] = $request['method'];

    if ($request['method'] == 'POST')
      $options[CURLOPT_POST] = 1;

    if (is_array($params) && count($params) && $request['method'] !== 'GET')
      $options[CURLOPT_POSTFIELDS] = json_encode($params);

    curl_setopt_array($curl, $options);

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);

    curl_close($curl);

    $endTime = round((microtime(true) - $startTime), 2);

    $type = 'OK';
    $dataJson = null;

    if (!($httpcode == 200 || $httpcode == '200')) {

      $type = 'ERROR';
      $dataJson = isset($response) ? $response : null;

    } else {

      $dataJson = isset($response) ? json_decode($response) : null;
      $error = null;

    }

    $reqObject = new TelmovPayRequest;
    $reqObject->setIp($systemRequest->ip());
    $reqObject->setBearerToken($systemRequest->bearerToken());
    $reqObject->setMethod($request['method']);
    $reqObject->setUrl($url);
    $reqObject->setMethodIntermedia($systemRequest->method());
    $reqObject->setUrlIntermedia($systemRequest->url());

    if (strcmp($service, 'token') !== 0) {

      $reqObject->setHeader($header);
      $reqObject->setPath(env('APP_URL'));

    } else {

      $reqObject->setHeader($systemRequest->header());
      $reqObject->setPath(env('URL_TELMOVPAY'));

    }

    TelmovPayLogs::saveLogBD($reqObject, $params, $dataJson, $endTime, $type, $error, $systemRequest);

    $data = NULL;

    if (env('APP_DEBUG'))
      $data = [ 'url' => $url, 'options' => ['url' => $url, 'httpheader' => $header, 'method' => $request['method']], 'request' => $request ];

    /**
     * Si el status retornado por la llamada es 200, todo marchó como se esperaba y el proceso se ejecutó correctamente (Si estamos en desarrollo, retornamos además la información con la que se configuró la llamada como parte del error para posible depuración).
     */
    if ($httpcode == 200 || $httpcode == '200')
      #return ['success' => true, 'data' => $dataJson, 'message' => 'Ejecución exitosa', 'debug' => $data];
      return ['success' => true, 'data' => $dataJson, 'message' => 'Ejecución exitosa'];

    /**
     * Si hubo un error, lo retornamos con una estructura legible (Si estamos en desarrollo, retornamos además la información con la que se configuró la llamada como parte del error para posible depuración)
     */
    return ['success' => false, 'data' => [
      'httpcode' => $httpcode,
      'error' => isset($error) ? $error : NULL,
      'response' => $dataJson
    ], 'message' => 'Error en ejecución'];
    /*
    return ['success' => false, 'data' => [
      'httpcode' => $httpcode,
      'error' => isset($error) ? $error : NULL,
      'response' => $dataJson
    ], 'message' => 'Error en ejecución', 'debug' => $data];
    */

  }

}