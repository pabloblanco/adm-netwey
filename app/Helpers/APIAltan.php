<?php
namespace App\Helpers;

class APIAltan
{

  public static function doRequest($request, $msisdn, $lat = false, $lng = false, $isMobile = false)
  {
    $url  = '';
    $data = ['apiKey' => env('API_KEY_ALTAM')];

    if ($request == 'profile') {
      $url = env('URL_ALTAM') . 'profile/';
    }
    if ($request == 'suspend') {
      $url = env('URL_ALTAM') . 'suspend/';
    }
    if ($request == 'activate') {
      $url = env('URL_ALTAM') . 'resume/';
    }
    if ($request == 'preDesactivate') {
      $url = env('URL_ALTAM') . 'predeactivate/';
    }
    if ($request == 'reactivate') {
      $url = env('URL_ALTAM') . 'reactivate/';
    }
    if ($request == 'barring') {
      $url = env('URL_ALTAM') . 'barring/';
    }
    if ($request == 'unbarring') {
      $url = env('URL_ALTAM') . 'unbarring/';
    }
    if ($request == 'deactivate') {
      $url = env('URL_ALTAM') . 'deactivate/';
    }
    if ($request == 'serviciability') {
      $url         = env('URL_ALTAM') . 'serviceability/';
      $data['lat'] = $lat;
      $data['lng'] = $lng;

      if ($isMobile) {
        $data['mobility'] = 'Y';
      }
    }

    if ($request != 'serviciability') {
      $url = $url . $msisdn;
    }

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
      CURLOPT_POSTFIELDS     => json_encode($data),
      CURLOPT_HTTPHEADER     => array(
        // Set here requred headers
        "accept: */*",
        "accept-language: en-US,en;q=0.8",
        "content-type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return "cURL Error #:" . $err;
    } else {
      return ($response);
    }
  }

  public static function simSwap($msisdn, $iccid)
  {
    $curl = curl_init();

    $data = [
      'apiKey' => env('API_KEY_ALTAM'),
      'iccid'  => $iccid,
    ];

    curl_setopt_array($curl, array(
      CURLOPT_URL            => env('URL_ALTAM') . "simSwap/" . $msisdn,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_TIMEOUT        => 60000,
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POSTFIELDS     => json_encode($data),
      CURLOPT_HTTPHEADER     => array(
        "accept: */*",
        "accept-language: en-US,en;q=0.8",
        "content-type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return ["error" => true, "msg" => $err];
    } else {
      return ["error" => false, "data" => $response];
    }
  }

  public static function removeSuplementary($msisdn = false, $offer = false)
  {
    if ($msisdn && $offer) {
      $curl = curl_init();

      $data = [
        'apiKey' => env('API_KEY_ALTAM'),
        'offer'  => $offer,
      ];

      curl_setopt_array($curl, array(
        CURLOPT_URL            => env('URL_ALTAM') . "remove/" . $msisdn,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_TIMEOUT        => 60000,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => array(
          "accept: */*",
          "accept-language: en-US,en;q=0.8",
          "content-type: application/json",
        ),
      ));

      $response = curl_exec($curl);
      $err      = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return ["error" => true, "msg" => $err];
      } else {
        $response = json_decode($response);

        if ($response->status == 'success') {
          return ["error" => false, "data" => $response->transactionId];
        }

        return ["error" => true, "msg" => $response->message];
      }
    }
  }
  /* Se dejo de usar y se paso a usar healthNetworkv3*/
/*
public static function healthNetwork($lat, $lng) {
$curl = curl_init();

//
$data = [
'apiKey' => env('API_KEY_ALTAM'),
'lat'    => $lat,
'lng'    => $lng,
];

curl_setopt_array($curl, array(
CURLOPT_URL            => env('URL_ALTAM') . "networkHealth/",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING       => "",
CURLOPT_MAXREDIRS      => 10,
CURLOPT_TIMEOUT        => 30000,
CURLOPT_FOLLOWLOCATION => true,
CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST  => "POST",
CURLOPT_POSTFIELDS     => json_encode($data),
CURLOPT_HTTPHEADER     => array(
"content-type: application/json",
),
));

$response = curl_exec($curl);
$err      = curl_error($curl);

curl_close($curl);

if ($err) {
return ["error" => true, "msg" => $err];
} else {
return ["error" => false, "data" => $response];
}
}*/

  public static function healthNetworkv3($msisdn)
  {
    $curl = curl_init();

    $data = [
      'apiKey' => env('API_KEY_ALTAM')];

    curl_setopt_array($curl, array(
      CURLOPT_URL            => env('URL_ALTAM') . "networkHealthByMsisdn/" . $msisdn,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 60000,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POSTFIELDS     => json_encode($data),
      CURLOPT_HTTPHEADER     => array(
        "content-type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    //dd($response);
    $err = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return ["error" => true, "msg" => $err];
    } else {
      return ["success" => true, "data" => $response];
    }
  }

  public static function compensationBonus($msisdn)
  {
    $curl = curl_init();

    //
    $data = [
      //'apiKey' => 'ESHDDFH$%WWEYRHSDHdfghdsf34632',
      'apiKey' => env('API_KEY_ALTAM'),
    ];

    curl_setopt_array($curl, array(
      CURLOPT_URL            => env('URL_ALTAM') . "profile/" . $msisdn,
      //CURLOPT_URL => 'https://dev.netwey.com.mx/apiAltan/profile/9516670007',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 60000,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POSTFIELDS     => json_encode($data),
      CURLOPT_HTTPHEADER     => array(
        "content-type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return ["error" => true, "msg" => $err];
    } else {
      return ["error" => false, "data" => $response];
    }
  }

  public static function retentionActive($msisdn, $offer)
  {
    $curl = curl_init();

    //
    $data = [
      'apiKey' => env('API_KEY_ALTAM'),
      'offer'  => $offer,
    ];

    curl_setopt_array($curl, array(
      CURLOPT_URL            => env('URL_ALTAM') . "supplementary/" . $msisdn,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 60000,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POSTFIELDS     => json_encode($data),
      CURLOPT_HTTPHEADER     => array(
        "content-type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return ["error" => true, "msg" => $err];
    } else {
      return ["error" => false, "data" => $response];
    }
  }

  public static function validImei($imei)
  {
    $curl = curl_init();

    $data = [
      'apiKey' => env('API_KEY_ALTAM'),
    ];

    curl_setopt_array($curl, array(
      CURLOPT_URL            => env('URL_ALTAM') . "imei-status/" . $imei,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING       => "",
      CURLOPT_MAXREDIRS      => 10,
      CURLOPT_TIMEOUT        => 60000,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST  => "POST",
      CURLOPT_POSTFIELDS     => json_encode($data),
      CURLOPT_HTTPHEADER     => array(
        "content-type: application/json",
      ),
    ));

    $response = curl_exec($curl);
    $err      = curl_error($curl);

    curl_close($curl);

    if ($err) {
      return ["error" => true, "msg" => $err];
    } else {
      $response = json_decode($response);

      if ($response->status == 'success') {
        return ["error" => false, "data" => $response];
      }

      return ["error" => true, "msg" => 'ocurrio un error cosultando a altan.', "original" => $response];
    }
  }
}
