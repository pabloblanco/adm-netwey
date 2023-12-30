<?php
namespace App\Helpers;

class API815
{
  public static function doRequest($request = false, $type = false, $data = false, $header = false)
  {
    if ($request && $type) {

      $url = '';
      if ($request == 'get-equipos' ||
        $request == 'get-plans' ||
        $request == 'conections-search' ||
        $request == 'search-client' ||
        $request == 'conections-update' ||
        $request == 'update-client' ||
        $request == 'autenticate' ||
        $request == 'refresh-citys-fiber') {
        $url = env('URL_API_815') . $request;
      } else {
        return ['success' => false, 'data' => 'Url no definida.'];
      }

      $curl = curl_init();
      if (env('APP_ENV', 'local') == 'local') {
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
      }

      if (!$header) {
        $header = array(
          "Content-Type: application/json",
          "Authorization: Bearer " . env('TOKEN_815'));
      }

      $options = array(
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 100,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => $type,
        CURLOPT_HTTPHEADER     => $header);

      if ($data) {
        if (is_array($data) && count($data) && $type == 'POST') {
          $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
      }
      curl_setopt_array($curl, $options);
      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $err      = curl_error($curl);
      curl_close($curl);

      if ($err) {
        // Log::info("err: " . (String) json_encode($err));
        return [
          'success' => false,
          'data'    => $err,
          'code'    => !empty($httpcode) ? $httpcode : 0,
        ];
      } else {
        $dataJson = json_decode($response, true);
        if (!empty($dataJson)) {

          if ($dataJson['success'] && isset($dataJson['data']['eightFifteen'])) {

            return [
              'success'  => true,
              'data'     => $dataJson['data'],
              'original' => $response,
              'code'     => !empty($httpcode) ? $httpcode : 0,
            ];
          } else {
            return [
              'success'  => false,
              'data'     => $dataJson['data'],
              'original' => $response,
              'code'     => !empty($httpcode) ? $httpcode : 0,
            ];
          }
        } else {
          // echo 'DATAJSON VACIO - ' . PHP_EOL;
          return [
            'success'  => false,
            'data'     => 'No se pudo obtener json.',
            'original' => $response,
            'code'     => !empty($httpcode) ? $httpcode : 0,
          ];
        }
      }
    }
    return ['success' => false, 'data' => 'Faltan datos.'];
  }

  public static function verifyEndPointFiberZone($url)
  {
    if (empty($url)) {
      return false;
    }
    stream_context_set_default([
      'ssl' => [
        'verify_peer'      => false,
        'verify_peer_name' => false,
      ],
    ]);
    $array             = @get_headers($url);
    $string            = $array[0];
    $accepted_response = array('200', '301', '302');
    foreach ($accepted_response as $key => $value) {
      if (strpos($string, $value)) {
        return true;
      }
    }
    return false;
  }

  public static function verifyEndPointCredencial($fiberZone_id)
  {
    $datain     = array('fiber_zone' => $fiberZone_id);
    $credencial = self::doRequest('autenticate', 'POST', $datain);

    if ($credencial['success']) {
      if (isset($credencial['data']['eightFifteen']['token'])) {
        return true;
      }
    }
    return false;
  }
}
