<?php
/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Enero 2022
 */
namespace App;

use App\ClientNetwey;
use App\Helpers\APIAltan;
use App\StockProvaDetail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Inv_reciclers extends Model
{
  protected $table    = 'islim_inv_reciclers';
  protected $fillable = [
    'id',
    'inv_article_id',
    'warehouses_id',
    'serial',
    'iccid',
    'imei',
    'imsi',
    'date_reception',
    'date_sending',
    'price_pay',
    'date_reg',
    'status',
    'obs',
    'msisdn_sufijo',
    'checkAltan',
    'checkOffert',
    'msisdn',
    'user_netwey',
    'origin_netwey',
    'user_mail',
    'ReciclerType',
    'date_update',
    'detail_error',
    'codeOffert',
    'user_update',
    'loadInventary',
    'date_loading'];

  protected $primaryKey = 'id';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Inv_reciclers
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new Inv_reciclers;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [is_Inprocess revisa si el DN esta solo creado o fue reciclado]
 * @param  [type]  $msisdn [description]
 * @return boolean         [description]
 */
  public static function is_Inprocess($msisdn)
  {
    $inprocess = self::getConnect('R')
      ->select('status')
      ->where('msisdn', $msisdn)
      ->whereIn('status', ['C', 'F'])
      ->first();

    if (!empty($inprocess)) {
      return true;
    }
    return false;
  }

/**
 * [get_reciclerStop Revisa si el DN esta en espera de cron, para evitar que se ingrese mas de una vez el dn]
 * @param  [type] $msisdn [description]
 * @return [type]         [description]
 */
  public static function get_reciclerStop($msisdn, $error = true)
  {
    $existe = self::getConnect('W')
      ->where('msisdn', $msisdn);

    if ($error) {
      $existe = $existe->whereIn('status', ['C', 'E']);
    } else {
      $existe = $existe->whereIn('status', ['C']);
    }
    return $existe->get();
  }

/**
 * [chekkingOffert Revisa en altan si el msisdn posee una oferta por defecto]
 * @param  [type] $msisdn [DN a verificar en altan]
 * @param  [type] $type [tipo de msisdn: HBB, MIFI, Telefonia]
 * @return [type]         [description]
 */
  public static function chekkingOffert($msisdn, $type)
  {
    $profile = APIAltan::doRequest('profile', $msisdn);
    $profile = json_decode($profile);

    if (is_object($profile) && $profile->status == 'success') {

      $StatusProfile = strtolower($profile->msisdn->status);
      //$oferta        = "";
      $codeOffert = $profile->msisdn->offer;

      if ($StatusProfile == 'active') {
        //Se revisa si la oferta es la primaria, si es asi se recicla
        //Ofertas por defecto
        $default = false;
        if ($type == 'H') {
          $CodesHBB = explode(',', env('OFERT_HBB'));
          if (in_array($codeOffert, $CodesHBB)) {
            $default = true;
          }
          //$oferta = env('OFERT_HBB', '1000000040');
        } elseif ($type == 'M') {
          $CodesMIFI = explode(',', env('OFERT_MIFI'));
          if (in_array($codeOffert, $CodesMIFI)) {
            $default = true;
          }
          // $oferta = env('OFERT_MIFI', '1000001002');
        } elseif ($type == 'T') {
          $CodesTELF = explode(',', env('OFERT_TELF'));
          if (in_array($codeOffert, $CodesTELF)) {
            $default = true;
          }
          // $oferta = env('OFERT_TELF', '1000001002');
        }
        if ($default) {
          //Significa que si se puede reciclar
          $RespRecicler = array('success' => true,
            'code'                          => 'RECICLER',
            'msg'                           => ' No se puede registrar el msisdn ' . $msisdn . ' en este momento, se procesara como >RECICLAJE<. Para manana estara disponible el msisdn ',
            'offert'                        => !empty($codeOffert) ? $codeOffert : 'N/O');
        } else {
          //El Dn posiblemente esta en uso
          $iscandidate = false;
          //CONSULTAR SI ESTE TIPO DE CONDICION SE DEBA ACTIVAR
          /*
          $DN = ClientNetwey::existDN($msisdn);
          if (!empty($DN)) {
          if ($DN->status == 'I') {
          $timeLast = Sale::getTimeRecharge($msisdn);
          if (!empty($timeLast)) {
          if ($timeLast->dias_recharge >= 120) {
          $iscandidate  = true;
          $RespRecicler = array('success' => true,
          'code'                          => 'RECICLER',
          'msg'                           => ' El DN se encuentra Inactivo en netwey y posee una oferta distinta a la defecto, tiene ' . $timeLast->dias_recharge . ' dias sin realizar recargas, es un candidato de Reciclaje',
          'offert'                        => !empty($codeOffert) ? $codeOffert : 'N/O');
          }
          }
          }
          }*/
          if (!$iscandidate) {
            $RespRecicler = array('success' => false,
              'code'                          => 'DIFF_OFFER',
              'msg'                           => ' No se puede registrar el msisdn ' . $msisdn . ' en este momento, posee una oferta distinta a la de defecto',
              'offert'                        => !empty($codeOffert) ? $codeOffert : 'N/O');
          }
        }
      } elseif ($StatusProfile == 'idle' || $StatusProfile == 'preactive') {
        //Segun Altan esta disponible para ser activado
        $RespRecicler = array('success' => true,
          'code'                          => 'RECICLER',
          'msg'                           => ' No se puede registrar el msisdn ' . $msisdn . ' en este momento, se procesara como >RECICLAJE<. Para manana estara disponible el msisdn',
          'offert'                        => !empty($codeOffert) ? $codeOffert : 'N/O');
      } else {
        $RespRecicler = array('success' => false,
          'code'                          => 'OTHER_STATUS',
          'msg'                           => 'El msisdn ' . $msisdn . ' se encuentra en estatus (' . $StatusProfile . ') en profile. El status no es acto para reciclaje',
          'offert'                        => !empty($codeOffert) ? $codeOffert : 'N/O');
      }
    } else {
      //Dio un error el profile, se debera notificar

      $errorAltan1 = false;
      $errorAltan2 = false;

      if (isset($profile->description_altan)) {
        $search1       = "the subscriber does not exist";
        $search2       = "limit counting";
        $cadena        = strtolower($profile->description_altan);
        $coincidencia1 = strpos($cadena, $search1);
        $coincidencia2 = strpos($cadena, $search2);

        if ($coincidencia1 !== false) {
          $errorAltan1  = true;
          $RespRecicler = array('success' => false,
            'code'                          => 'DN_NOTFOUND_ALTAN',
            'msg'                           => ' El msisdn ' . $msisdn . ' no existe en Altan y no deberia reciclarse ni cargarse en inventario');
        } elseif ($coincidencia2 !== false) {
          $errorAltan2 = true;
          //limite de conexiones al limite
        }
      }
      if (!$errorAltan1 || $errorAltan2) {
        $RespRecicler = array('success' => false,
          'code'                          => 'FAIL_ALTAN',
          'msg'                           => ' El msisdn ' . $msisdn . ' no se puede registrar en este momento, existen problemas para consultar el profile Altan, espere unos minutos para volver a intentar');
      }
    }
    return $RespRecicler;
  }

/**
 * [chekkingClient Verifica el cliente y revisa su oferta en altan para reciclarlo si aun esta en default]
 * @param  [type] $msisdn [description]
 * @return [type]         [description]
 */
  public static function chekkingClient($msisdn)
  {
    $DNtype = Inventory::getType($msisdn);
    if (!empty($DNtype)) {
      if ($DNtype->artic_type == 'F') {
        //Reciclaje de Fibra//
        //Se dejara a futuro implementar la logica para determinar un DN de fibra para reciclaje//
        $complex = "";
        if ($DNtype->status == 'A') {
          $complex = " se encuentra disponible para la venta";
        } elseif ($DNtype->status == 'V') {
          $complex = " se encuentra vendido y no se puede reciclar";
        }
        return array('success' => false,
          'code'                 => 'DN_FIBRA',
          'msg'                  => ' El msisdn ' . $msisdn . ' es de tipo Fibra,' . $complex);
      }
    }
    $DN = ClientNetwey::existDN($msisdn);
    if (!empty($DN)) {
      //Si es cliente de netwey miramos la oferta que tiene el profile
      $type = $DN->dn_type;

      if ($type != 'F') {
        $RespRecicler = self::chekkingOffert($msisdn, $type);
        return $RespRecicler;
      }
      //Respuesta por defecto $RespRecicler
    } else {
      return array('success' => false,
        'code'                 => 'DN_NOT_CLIENT',
        'msg'                  => ' El msisdn ' . $msisdn . ' esta libre de ser usado, no esta asignado a un cliente');
    }
  }

/**
 * [Verify_msisdn Revisa si el DN que se sospecha se debe reciclar]
 * @param [type] $request [request es la lista de datos asociados al msisdn a revisar]
 * @param [type] $origin_netwey [es la procedencia de los datos: one:one, file, sftp, call_center-seller]

 */
  public static function Verify_msisdn_recicler($request, $origin_netwey = false)
  {
    //INIT//
    //El origin_netwey seller no usa este metodo
    $permitI = array("call_center", "file", "sftp");

    if (in_array($origin_netwey, $permitI)) {
      $infoReciclers = $request;
      $msisdn        = $request['msisdn'];
    } else {
      //origin_netwey == 'one'
      $infoReciclers = $request->input();
      $msisdn        = $request->msisdn;
    }
    //elimino los espacios en blanco si lo hubiese y trabajo como cadena
    $msisdn = trim((String) $msisdn);

    if (empty($msisdn)) {
      return array('success' => false,
        'code'                 => 'DN_EMPTY',
        'msg'                  => 'El msisdn a revisar para reciclaje no puede ser vacio.');
    }
    $isProva = false;
    if ($origin_netwey == 'file' || $origin_netwey == 'one') {
      //Verifico si el Dn que se esta en prova
      $DNprova = StockProvaDetail::getDetailRecicler($msisdn);
      if (!empty($DNprova)) {
        if (self::is_Inprocess($msisdn)) {
          $isProva = true;
        }
      }
    }

    if (!$isProva) {
      $RespRecicler = array('success' => false,
        'code'                          => 'DN_EXISTS_INV',
        'msg'                           => 'No se puede crear el articulo, porque el msisdn ' . $msisdn . ' se encuentra asignado a otro artículo.');
    } else {
      $RespRecicler = array('success' => false,
        'code'                          => 'DN_EXISTS_INV',
        'msg'                           => 'El msisdn ' . $msisdn . ' fue notificado previamente via sftp Prova. Actualmente cuenta con un proceso activo de reciclaje, por favor espera que concluya este proceso');
    }

    $statusDN = Inventory::existDN($msisdn);
    if (!empty($statusDN) && !$isProva) {
      if ($statusDN->status != 'A') {

        $loadingReject = false;
        $RespClient    = self::chekkingClient($msisdn);
        if ($RespClient['code'] == 'FAIL_ALTAN') {
          $infoReciclers['checkAltan']   = 'Y';
          $infoReciclers['checkOffert']  = 'N';
          $infoReciclers['detail_error'] = $RespClient['msg'];
          $RespRecicler                  = $RespClient;

        } elseif ($RespClient['code'] == 'DIFF_OFFER') {
          $infoReciclers['checkOffert']  = 'Y';
          $infoReciclers['codeOffert']   = $RespClient['offert'];
          $infoReciclers['checkAltan']   = 'N';
          $infoReciclers['detail_error'] = $RespClient['msg'];
          $RespRecicler                  = $RespClient;

        } elseif ($RespClient['code'] == 'RECICLER') {
          $infoReciclers['checkOffert'] = 'N';
          $infoReciclers['checkAltan']  = 'N';
          $infoReciclers['codeOffert']  = $RespClient['offert'];
          $RespRecicler                 = $RespClient;

        } elseif ($RespClient['code'] == 'DN_FIBRA') {
          $loadingReject                 = true;
          $infoReciclers['status']       = 'E';
          $infoReciclers['detail_error'] = $RespClient['msg'];
          $RespRecicler                  = $RespClient;

        } elseif ($RespClient['code'] == 'OTHER_STATUS') {
          $loadingReject                 = true;
          $infoReciclers['status']       = 'E';
          $infoReciclers['detail_error'] = $RespClient['msg'];
          $infoReciclers['codeOffert']   = $RespClient['offert'];
          $RespRecicler                  = $RespClient;

        } elseif ($RespClient['code'] == 'DN_NOT_CLIENT') {
          $RespRecicler['msg'] .= $RespClient['msg'];
          $RespRecicler = $RespClient;

        } elseif ($RespClient['code'] == 'DN_NOTFOUND_ALTAN') {
          $permitF = array("call_center", "seller");

          if (!in_array($origin_netwey, $permitF)) {

            //peticion: one, file y sftp
            $loadingReject = true;
            //No se necesita en: seller o call_center ya que el DN quiere entrar a netwey por tanto es probable que no exista en Altan
            $infoReciclers['status']       = 'E';
            $infoReciclers['detail_error'] = $RespClient['msg'];
          }
          $RespRecicler = $RespClient;
        }

        $DN           = ClientNetwey::existDN($msisdn);
        $statusClient = "";
        if (!empty($DN)) {
          $date1        = new \DateTime($DN->date_reg);
          $statusClient = $DN->status;
          $origen       = "Client";
        } else {
          $date1  = new \DateTime($statusDN->date_reg);
          $origen = "Inventory";
        }
        $date2 = new \DateTime("now");

        //$intervalo = $date1->diff($date2)->format('%Y años %m meses %d days %H horas %i minutos %s segundos');
        $week = $date1->diff($date2)->format('%a');

        $waitLoadind = 40; //Dias de espera
        if (($week <= $waitLoadind && $statusClient != 'I') ||
          ($week <= 5 && $statusClient == 'I')) {
          //Tiene menos de 40 dias de estar en inventario y no debe ser un caso de reciclaje, si entra aca es debido a que se cargo de nuevo el DN de forma seguida

          if ($origen == "Client" && $statusClient == 'A') {
            $loadingReject = true;
            $statusClient  = "Cliente activo";
          }
          $msjRecurense = 'El Dn se trato de cargar de forma reiterativa en un lapso corto de ' . $week . ' dias. (' . $origen . '-' . $statusClient . ')';
        }
        $EnReciclaje = self::get_reciclerStop($msisdn);
        //Si ya esta en la lista pendiente por reciclar se elimina y se crea nuevo registro pendiente por procesar

        if (!empty($EnReciclaje) && $EnReciclaje->count() > 0) {
          //Elimino logicamente los anteriores registros del DN
          foreach ($EnReciclaje as $key) {
            $key->status      = 'T';
            $key->date_update = date('Y-m-d H:i:s', time());
            $key->save();
          }
        }
        if (isset(session('user')->email)) {
          $infoReciclers['user_netwey'] = session('user')->email;
        }
        if ($origin_netwey) {
          $infoReciclers['origin_netwey'] = $origin_netwey;
        }
        $infoReciclers['date_reg'] = date('Y-m-d H:i:s', time());

        if ($origin_netwey == 'one' ||
          $origin_netwey == 'file' || $origin_netwey == 'sftp') {
          $infoReciclers['ReciclerType'] = 'C'; //Reciclaje completo (Prefijo+inventario)
        } else {
          //Seller //call_center(Solicitud de portacion) (solo prefijo)
          $infoReciclers['ReciclerType'] = 'P';
        }
        //Nuevo registro en reciclaje
        try {
          if ($loadingReject) {
            if (empty($msjRecurense)) {
              $infoReciclers['status'] = 'E';
            } else {
              //Los de carga recurrente solo los guardo pero no los muestro
              $infoReciclers['status'] = 'T';
            }
          }
          if (empty($infoReciclers['detail_error']) && !empty($msjRecurense)) {
            $infoReciclers['detail_error'] = $msjRecurense;
          }
          //Log::info((String) json_encode($infoReciclers));

          $infoReciclers = self::getConnect('W')->create($infoReciclers);
        } catch (Exception $e) {
          Log::info('error creacion ' . $e->message());
        }
        //End Nuevo registro en reciclaje
      } else {
        $RespRecicler['msg'] .= " Nota: El DN se encuentra registrado en inventario como disponible para la venta";
      }
    }
    return $RespRecicler;
  }

/**
 * [searchReportRecicler Consulta del reporte de bajas y finiquitos]
 * @param  [type] $filter [description]
 * @return [type]         [description]
 */
  public static function searchReportRecicler($filter)
  {
    //Solo muestra status y dias de deuda los DN que estan en solicitud o dan error.
    $invRecicle = self::getConnect('R')
      ->select(
        'islim_inv_reciclers.id',
        'islim_inv_reciclers.msisdn',
        'islim_inv_reciclers.origin_netwey',
        'islim_inv_reciclers.user_netwey',
        'islim_inv_reciclers.date_reg',
        'islim_inv_reciclers.codeOffert',
        'islim_inv_reciclers.checkOffert',
        'islim_inv_reciclers.checkAltan',
        'islim_inv_reciclers.status',
        'islim_inv_reciclers.obs',
        'islim_inv_reciclers.detail_error',
        'islim_inv_reciclers.ReciclerType',
        'islim_inv_reciclers.loadInventary',
        //'islim_client_netweys.status AS statusClient',
        DB::raw('(SELECT islim_client_netweys.status FROM islim_client_netweys WHERE islim_client_netweys.msisdn = islim_inv_reciclers.msisdn AND (islim_inv_reciclers.status = "C" OR islim_inv_reciclers.status = "E" OR islim_inv_reciclers.status = "M")) AS statusClient'),
        DB::raw('DATEDIFF(NOW(),(SELECT islim_sales.date_reg FROM islim_sales WHERE islim_sales.type = "R" AND islim_sales.msisdn = islim_inv_reciclers.msisdn  AND (islim_inv_reciclers.status="C" OR islim_inv_reciclers.status="E" OR islim_inv_reciclers.status = "M") ORDER BY islim_sales.msisdn DESC,  islim_sales.date_reg DESC LIMIT 1)) AS dias_recharge'))
      ->leftJoin('islim_client_netweys',
        'islim_client_netweys.msisdn',
        'islim_inv_reciclers.msisdn');

    if (!empty($filter['status'])) {
      if ($filter['status'] == 'C') {
        //Solicitados
        $invRecicle = $invRecicle->whereIN('islim_inv_reciclers.status', ['C', 'M']);
      } elseif ($filter['status'] == 'P') {
        //procesados
        $invRecicle = $invRecicle->whereIN('islim_inv_reciclers.status', ['F', 'P']);
      } elseif ($filter['status'] == 'E') {
        //error
        $invRecicle = $invRecicle->where('islim_inv_reciclers.status', 'E')
          ->orWhere('islim_inv_reciclers.checkAltan', 'Y');

      } elseif ($filter['status'] == 'R') {
        //rechazados
        $invRecicle = $invRecicle->where('islim_inv_reciclers.status', 'R');
      }
    }
    $invRecicle = $invRecicle->where([
      ['islim_inv_reciclers.status', '!=', 'T'],
      ['islim_inv_reciclers.date_reg', '>=', $filter['dateStar']],
      ['islim_inv_reciclers.date_reg', '<=', $filter['dateEnd']]]);

    $invRecicle = $invRecicle->get();
    return $invRecicle;
  }

/**
 * [setReciclerItem Actualiza los status del item a reciclar]
 * @param [type] $filter [description]
 */
  public static function setReciclerItem($filter)
  {
    $reg = self::getConnect('W')
      ->where([
        ['id', $filter['id']],
        ['msisdn', $filter['msisdn']]]);

    if ($filter['status'] == 'C') {
      $reg = $reg->update([
        'checkOffert' => 'N',
        'checkAltan'  => 'N',
        'status'      => $filter['status'],
        'date_update' => date('Y-m-d H:i:s', time()),
        'user_update' => session('user')->email,
      ]);
    } else {
      $reg = $reg->update([
        'status'      => $filter['status'],
        'date_update' => date('Y-m-d H:i:s', time()),
        'user_update' => session('user')->email,
        'obs'         => !empty($filter['obs']) ? $filter['obs'] : null]);

      //Debo revisar el la cancelacion pertenece a un DN de sftp de prova
      $regDNcancel = self::getConnect('R')
        ->where([
          ['id', $filter['id']],
          ['msisdn', $filter['msisdn']]])
        ->first();

      if (!empty($regDNcancel)) {
        if ($regDNcancel->origin_netwey == 'sftp') {

          $updateProva = StockProvaDetail::getDetailByDN($filter['msisdn'], 'PR');

          if (!empty($updateProva)) {
            $updateProva->status  = 'E';
            $updateProva->comment = 'No se reciclo el DN, cancelado por ' . session('user')->email;
            $updateProva->save();
          }
        }
      }
    }
    return $reg;
  }
}
