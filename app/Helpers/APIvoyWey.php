<?php
namespace App\Helpers;

use App\Helpers\CommonHelpers;
use App\OrderDetail;
use App\Orders;
use App\User;

class APIvoyWey
{
  public static function nomina($request, $current_page = false)
  {
    $response = CommonHelpers::executeCurl(
      env('URL_API_VOYWEY') . "nomina?page=" . $current_page,
      'POST',
      [
        'Content-Type: application/json',
        'Authorization: Bearer ' . env('TOKEN_VOYWEY'),
      ],
      $request
    );
    //Log::info($request);
    if ($response['success']) {
      $response = self::get_infoUser($response); // agrego la informacion del vendedor
      $response = self::get_addressActivate($response); // agrego la direccion de activacion
      $response = self::verify_dn($response); // Verifico que venga el DN en el request
      return $response;
    } else {
      return [
        'success' => false,
        'data'    => [
          'msg'     => 'Ocurrio un error consultando nomina voywey.',
          'cod_err' => $response['data'],
        ],
      ];
    }
  }

  public static function get_repartidor($request)
  {
    $response = CommonHelpers::executeCurl(
      env('URL_API_VOYWEY') . "delivery-data",
      'POST',
      [
        'Content-Type: application/json',
        'Authorization: Bearer ' . env('TOKEN_VOYWEY'),
      ],
      $request
    );

    if ($response['success']) {

      return $response['data'];
    } else {
      return [
        'success' => false,
        'data'    => [
          'msg'     => 'Ocurrio un error consultando nomina voywey.',
          'cod_err' => $response['data'],
        ],
      ];
    }
  }
  private static function get_infoUser($datainput = false)
  {
    if ($datainput) {

      foreach ($datainput['data']->data->data as $info) {

        if (isset($info->seller)) {
          if (!empty($info->name_seller) && !empty($info->lastname_seller)) {
            $info->nameUser     = $info->name_seller;
            $info->lastNameUser = $info->lastname_seller;
          } else {
            $dataMail = Orders::getMail_vendedor($info->folio);
            if (!empty($dataMail)) {
              $dataUser = User::getName_lastName($dataMail->seller_email);
              if (!empty($dataUser)) {
                $info->nameUser     = $dataUser->name;
                $info->lastNameUser = $dataUser->last_name;
              } else {
                $info->nameUser     = 'S/N';
                $info->lastNameUser = 'S/N';
              }
            } else {
              $info->nameUser     = 'S/N';
              $info->lastNameUser = 'S/N';
            }
          }
        } else {
          $info->seller       = 'S/N';
          $info->nameUser     = 'S/N';
          $info->lastNameUser = 'S/N';
        }
      }
      return $datainput;
    }
  }
/**
 * [verify_dn Verifico si viene el Dn en el request]
 * @param  boolean $datainput [datos de la consulta]
 * @return [type]             [datos procesados]
 */
  private static function verify_dn($datainput = false)
  {
    if ($datainput) {
      foreach ($datainput['data']->data->data as $info) {
        if (!isset($info->DN)) {
          $info->DN = 'Error API';
        }
      }
      return $datainput;
    }
  }

  private static function get_addressActivate($datainput = false)
  {
    if ($datainput) {
      foreach ($datainput['data']->data->data as $info) {
        $addressActive = OrderDetail::getAddress_active($info->folio);

        if (!empty($addressActive)) {
          $info->address_active = $addressActive->address;
        } else {
          $info->address_active = 'S/N';
        }
      }
      return $datainput;
    }
  }
  public static function conciliacion($request, $current_page = false)
  {
    $response = CommonHelpers::executeCurl(
      env('URL_API_VOYWEY') . "conciliation?page=" . $current_page,
      'POST',
      [
        'Content-Type: application/json',
        'Authorization: Bearer ' . env('TOKEN_VOYWEY'),
      ],
      $request
    );

    if ($response['success']) {

      if ($response['data']->data->total > 0) {

        $response = self::get_infoUser($response); // agrego la informacion del vendedor

        $response = self::get_addressActivate($response); // agrego la direccion de activacion
      }
      return $response;
    } else {
      return [
        'success' => false,
        'data'    => [
          'msg'     => 'Ocurrio un error consultando conciliacion voywey.',
          'cod_err' => $response['data'],
        ],
      ];
    }
  }

  private static function get_invenTotal($dataInfo)
  {

    $inv_total = 0; //Sumo lo que esta disponible mas lo asignado a los repartidores
    foreach ($dataInfo->data->data as $inventory) {

      $inv_total            = $inventory->disp_bodega + $inventory->asignados;
      $inventory->inv_total = $inv_total;
    }
    return $dataInfo;
  }

  public static function inventory($request, $current_page = false)
  {
    $response = CommonHelpers::executeCurl(
      env('URL_API_VOYWEY') . "inventory?page=" . $current_page,
      'POST',
      [
        'Content-Type: application/json',
        'Authorization: Bearer ' . env('TOKEN_VOYWEY'),
      ],
      $request
    );

    if ($response['success']) {
      $response = self::get_invenTotal($response['data']); // agrego la informacion del inventario total

      return [
        'success' => true,
        'data'    => $response->data];

    } else {
      return [
        'success' => false,
        'data'    => [
          'msg'     => 'Ocurrio un error consultando inventario voywey.',
          'cod_err' => $response['data'],
        ],
      ];
    }
  }
  public static function inventoryDetail($request, $current_page = false)
  {
    $response = CommonHelpers::executeCurl(
      env('URL_API_VOYWEY') . "detail-inventory?page=" . $current_page,
      'POST',
      [
        'Content-Type: application/json',
        'Authorization: Bearer ' . env('TOKEN_VOYWEY'),
      ],
      $request
    );
    if ($response['success']) {

      return [
        'success' => true,
        'data'    => $response['data']];

    } else {
      return [
        'success' => false,
        'data'    => [
          'msg'     => 'Ocurrio un error consultando detalle de inventario voywey.',
          'cod_err' => $response['data'],
        ],
      ];
    }
  }

  public static function makeReportInventory($request)
  {

    $current_page = 1;
    $step1        = self::inventory($request, $current_page);
    if ($step1['success']) {
      $data  = array();
      $pos   = 0;
      $limit = 0;

      //Recorro la lista de las N bodegas */
      $arrayStep1 = array();
      $arrayStep1 = $step1['data']->data;

      while ($step1['data']->next_page_url != null) {
        $current_page++;

        $dataInfo = self::inventory($request, $current_page);

        for ($i = 0; $i < count($dataInfo['data']->data); $i++) {
          array_push($arrayStep1, $dataInfo['data']->data[$i]);
        }
      }

      foreach ($arrayStep1 as $key) {

        //informacion general de la bodega*/
        $data[$pos]['id_bodega']   = $key->id;
        $data[$pos]['name']        = $key->name;
        $data[$pos]['disp_bodega'] = $key->disp_bodega;
        $data[$pos]['asignados']   = $key->asignados;
        $data[$pos]['en_camino']   = $key->en_camino;
        $limit                     = $pos + $limit + $key->asignados;
        $limitBodeg                = $key->disp_bodega;

        foreach ($key->detail as $keydetail) {
          //detalle generales de equipos en posecion de los repartidores/
          $data[$pos]['deliveryName']     = $keydetail->deliveryName;
          $data[$pos]['deliveryLastName'] = $keydetail->deliveryLastName;
          $data[$pos]['deliveryEmail']    = $keydetail->deliveryEmail;
          $data[$pos]['sku']              = $keydetail->sku;
          $data[$pos]['nameProduct']      = $keydetail->nameProduct;

          $dataDetail = array('warehouse' => $data[$pos]['id_bodega'], 'sku' => $data[$pos]['sku'], 'email' => $data[$pos]['deliveryEmail']);

          $current2_page = 1;
          $step2         = self::inventoryDetail($dataDetail, $current2_page);

          if ($step2['success']) {
            //Listo los DN en detalles de los equipos en posecion de los empleados
            $arrayStep2 = array();
            $arrayStep2 = $step2['data']->data->data;

            while ($step2['data']->data->next_page_url != null) {
              $current2_page++;

              $dataInfo = self::inventoryDetail($request, $current2_page);

              for ($i = 0; $i < count($dataInfo['data']->data->data); $i++) {
                array_push($arrayStep2, $dataInfo['data']->data->data[$i]);
              }
            }

            foreach ($arrayStep2 as $keydetailEquipo) {
              $data[$pos]['dn']      = $keydetailEquipo->dn;
              $data[$pos]['estatus'] = $keydetailEquipo->estatus;
              $pos++;

              if ($pos <= $limit) {
                $data[$pos] = $data[$pos - 1];
              } else {
                break;
              }

            }
          }
        }

        //Detalles de equipo en deposito de bodega
        $posBod = 0;
        foreach ($key->detail_warehouse as $detailBodega) {
          //detalle de equipos en la bodega*/
          $data[$pos]['deliveryName']     = 'S/N';
          $data[$pos]['deliveryLastName'] = 'SN';
          $data[$pos]['deliveryEmail']    = 'SN';
          $data[$pos]['sku']              = $detailBodega->sku;
          $data[$pos]['nameProduct']      = $detailBodega->nameProduct;
          $data[$pos]['dn']               = $detailBodega->dn;
          $data[$pos]['estatus']          = "Disponible";
          $pos++;
          $posBod++;
          if ($posBod < $limitBodeg) {
            $data[$pos] = $data[$pos - 1];
          } else {
            break;
          }
        }
      }
      return $data;
    }
  }
}
