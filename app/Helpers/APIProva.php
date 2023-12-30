<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

/*
* Clase que contiene métodos para conexión con API de prova 
* DOC: https://docs.google.com/document/d/1EokFava65qeyJBnvm25rcQgGMXuijSJk/edit?usp=sharing&ouid=117831663306606983648&rtpof=true&sd=true
*/
class APIProva
{
  private static function executeRequest($type, $path, $data = [])
  {
    $curl = curl_init();

    $curlopt = [
      CURLOPT_URL => env('URL_API_PROVA') . $path,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_TIMEOUT => 30000,
      CURLOPT_CUSTOMREQUEST => $type,
      CURLOPT_HTTPHEADER => array(
        "accept: */*",
        "accept-language: en-US,en;q=0.8",
        "content-type: application/json",
        "Authorization: Bearer " . env('TOKEN_API_PROVA')
      )
    ];

    if (count($data)) {
      $curlopt[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($curl, $curlopt);

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      $res = [
        'success' => false,
        'data'    => $err,
        'code'    => !empty($httpcode) ? $httpcode : 0
      ];
    } else {
      $dataJson = json_decode($response);

      if (!empty($dataJson)) {
        $res = [
          'success'  => true,
          'data'     => $dataJson,
          'original' => $response,
          'code'     => !empty($httpcode) ? $httpcode : 0
        ];
      }else{
        $res = [
          'success'  => false,
          'data'     => 'No se pudo obtener json.',
          'original' => $response,
          'code'     => !empty($httpcode) ? $httpcode : 0
        ];
      }
    }

    /*if (!$res['success']) {
      Log::error('Ocurrio un error al ejecutar el request ' . $path . ' de prova data enviada: ' . (count($data) ? json_encode($data) : 'N/A'), $res);
    }*/
    
    return $res;
  }

  public static function createSKU($data = []){
    return ['success' => true];
    $res = self::executeRequest('POST', 'crearSKU', $data);
    
    if($res['success']){
      if(!empty($res['data']->Exito)){
        return ['success' => true];
      }

      return ['success' => false, 'message' => strtolower($res['data']->descripcion)];
    }

    Log::error('Ocurrio un error al ejecutar el request createSKU de prova data enviada: ' . (count($data) ? json_encode($data) : 'N/A'), $res);

    return ['success' => false];
  }

  public static function updateSKU($data = [], $sku){
    return ['success' => true];
    $res = self::executeRequest('PUT', 'actualizaSKU/'.$sku, $data);

    if($res['success']){
      if(!empty($res['data']->Exito)){
        return ['success' => true];
      }

      return ['success' => false, 'message' => strtolower($res['data']->descripcion)];
    }

    Log::error('Ocurrio un error al ejecutar el request updateSKU de prova data enviada: ' . (count($data) ? json_encode($data) : 'N/A'), $res);

    return ['success' => false];
  }

  public static function deleteSKU($sku){
    return ['success' => true];
    $res = self::executeRequest('DELETE', 'eliminarSKU/'.$sku);

    if($res['success']){
      if(!empty($res['data']->Exito)){
        return ['success' => true];
      }

      return ['success' => false, 'message' => strtolower($res['data']->descripcion)];
    }

    Log::error('Ocurrio un error al ejecutar el request deleteSKU de prova data enviada: ' . $sku, $res);

    return ['success' => false];
  }

  public static function createUser($data = []){
    return ['success' => true];
    $res = self::executeRequest('POST', 'crearUsuario', $data);

    if($res['success']){
      if(!empty($res['data']->Exito)){
        return ['success' => true];
      }

      return ['success' => false, 'message' => strtolower($res['data']->descripcion)];
    }

    Log::error('Ocurrio un error al ejecutar el request createUser de prova data enviada: ' . (count($data) ? json_encode($data) : 'N/A'), $res);

    return ['success' => false];
  }

  public static function updateUser($data = [], $email){
    return ['success' => true];
    $res = self::executeRequest('PUT', 'actualizaUsuario/'.$email, $data);

    if($res['success']){
      if(!empty($res['data']->Exito)){
        return ['success' => true];
      }

      return ['success' => false, 'message' => strtolower($res['data']->descripcion)];
    }

    Log::error('Ocurrio un error al ejecutar el request updateUser de prova data enviada: ' . (count($data) ? json_encode($data) : 'N/A'), $res);

    return ['success' => false];
  }

  public static function deleteUser($email){
    return ['success' => true];
    $res = self::executeRequest('DELETE', 'eliminarUsuario/'.$email);

    if($res['success']){
      if(!empty($res['data']->Exito)){
        return ['success' => true];
      }

      return ['success' => false, 'message' => strtolower($res['data']->descripcion)];
    }

    Log::error('Ocurrio un error al ejecutar el request deleteUser de prova data enviada: ' . $email, $res);

    return ['success' => false];
  }
}
