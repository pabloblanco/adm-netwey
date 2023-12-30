<?php

namespace App;

use App\Sale;
use App\SellerInventory;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Log;

class LowRequest extends Model
{
  protected $table = 'islim_request_dismissal';

  protected $fillable = [
    'id',
    'user_req',
    'user_dismissal',
    'id_reason',
    'reason_deny',
    'user_process',
    'status',
    'date_reg',
    'date_step1',
    'date_step2',
    'discounted_amount',
    'user_finish',
    'cash_request',
    'days_cash_request',
    'cash_abonos',
    'cant_abonos',
    'article_request',
    'cash_hbb',
    'cash_telf',
    'cash_mifi',
    'cash_fibra',
    'cash_total',
    'date_liquidacion',
    'mount_liquidacion'];

  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\LowRequest
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new LowRequest;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function GetRequest($filter = [])
  {
    return self::getConnect('R')
      ->where([
        ['islim_request_dismissal.id', $filter['id']],
        ['islim_request_dismissal.status', $filter['status']]])
      ->first();
  }

  public static function GetListRequest($filter = [], $cheking = true)
  {
    $data = self::getConnect('R')
      ->select(
        'islim_request_dismissal.id',
        'islim_request_dismissal.user_req',
        DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) AS userDetail_req'),
        'islim_request_dismissal.user_dismissal',
        DB::raw('CONCAT(UserLow.name," ",UserLow.last_name) AS userDetail_low'),
        'islim_request_dismissal.status',
        'islim_reason_dismissal.reason',
        'islim_request_dismissal.date_reg',
        'islim_request_dismissal.cash_request',
        'islim_request_dismissal.days_cash_request',
        'islim_request_dismissal.article_request',
        'islim_request_dismissal.cash_abonos',
        'islim_request_dismissal.cant_abonos',
        'islim_request_dismissal.cash_total',
        'islim_request_dismissal.cash_hbb',
        'islim_request_dismissal.cash_telf',
        'islim_request_dismissal.cash_mifi',
        'islim_request_dismissal.cash_fibra',
        'islim_request_dismissal.status',
        DB::raw("(SELECT COUNT(EVI.id_req_dismissal) FROM islim_documentation_dismissal AS EVI WHERE EVI.id_req_dismissal = islim_request_dismissal.id AND EVI.status = 'A') AS cant_evidenci")
      )
      ->join('islim_reason_dismissal',
        'islim_reason_dismissal.id',
        'islim_request_dismissal.id_reason')
      ->leftJoin('islim_users',
        'islim_users.email',
        'islim_request_dismissal.user_req')
      ->leftJoin('islim_users AS UserLow',
        'UserLow.email',
        'islim_request_dismissal.user_dismissal')
      ->where([
        ['islim_request_dismissal.status', 'R'],
        ['islim_request_dismissal.date_reg', '>=', $filter['dateStar']],
        ['islim_request_dismissal.date_reg', '<=', $filter['dateEnd']]]);

    if (!empty($filter['emailLow'])) {
      $data = $data->where('islim_request_dismissal.user_dismissal', $filter['emailLow']);
    }
    $data = $data->orderBy('islim_request_dismissal.date_reg', 'DESC')->get();

    if ($cheking) {
      //Se recalculara la deuda de los usuarios que esta en proceso de solicitud de baja y se actualizan
      $data = json_decode(json_encode($data));

      if (!empty($data) && count($data) > 0) {
        self::chekingDeudaUser($data);
        $data = self::GetListRequest($filter, false);
      }
      //END recalculara la deuda de los usuarios que esta en proceso de solicitud de baja y se actualizan
    }
    return $data;
  }

/**
 * [SetRejectRequest Se actualiza el registro de la solicitud de baja]
 * @param [type] $Id     [id del registro de solicitud]
 * @param [type] $type     [tipo de actualizacion]
 * @param [type] $motivo     [Si se rechaza este campo viene con el mensaje del porque se rechazo]
 * @param [type] $discounted_amount [Si se finaliza la baja este valor viene con el descuento realizado al usuario dado de baja]
 */
  public static function SetUpdateRequest($Id, $type, $motivo = false, $discounted_amount = false)
  {

    $data = self::getConnect('W')
      ->where('islim_request_dismissal.id', $Id);

    if ($type == "reject") {
      $data->update([
        'date_step1'   => date('Y-m-d H:i:s'),
        'reason_deny'  => $motivo,
        'status'       => 'D',
        'user_process' => session('user')->email]);
    } elseif ($type == "acept") {
      $data->update([
        'date_step1'   => date('Y-m-d H:i:s'),
        'status'       => 'P',
        'user_process' => session('user')->email]);
    } elseif ($type == "finish") {
      $data->update([
        'date_step2'        => date('Y-m-d H:i:s'),
        'status'            => 'F',
        'discounted_amount' => $discounted_amount,
        'user_finish'       => session('user')->email]);
    }
    return true;
  }

  /**
   * Filtro del reporte que retorna el nombre del usuario que esta pidiendo
   */
  public static function GetUsersSearchList($querySearch)
  {
    return self::getConnect('R')
      ->select('islim_request_dismissal.user_dismissal',
        'islim_users.name',
        'islim_users.last_name',
        DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username'))
      ->join('islim_users',
        'islim_users.email',
        'islim_request_dismissal.user_dismissal')
      ->where(function ($query) use ($querySearch) {
        $query->where('islim_users.name', 'like', '%' . $querySearch . '%')
          ->orWhere('islim_users.last_name', 'like', '%' . $querySearch . '%')
          ->orWhere('islim_users.email', 'like', $querySearch . '%');
      })
      ->where('islim_request_dismissal.status', 'R')
      ->limit(10)
      ->get();
  }

  //Bajas en finiquito y Reporte de bajas
  public static function GetListProcess($filter, $cheking = true)
  {
    $data = self::getConnect('R')
      ->select(
        'islim_request_dismissal.id',
        'islim_request_dismissal.user_req',
        'islim_request_dismissal.user_dismissal',
        'islim_reason_dismissal.reason',
        DB::raw('CONCAT(UserLow.name," ",UserLow.last_name) AS userDetail_low'),
        'islim_request_dismissal.date_reg',
        'islim_request_dismissal.cash_total',
        'islim_request_dismissal.date_step1',
        'islim_request_dismissal.date_step2',
        'islim_request_dismissal.cash_request',
        'islim_request_dismissal.article_request',
        'islim_request_dismissal.cash_abonos',
        'islim_request_dismissal.status',
        'islim_request_dismissal.reason_deny',
        'islim_request_dismissal.discounted_amount',
        'islim_request_dismissal.mount_liquidacion',
        'islim_request_dismissal.date_liquidacion',
        'islim_request_dismissal.cash_hbb',
        'islim_request_dismissal.cash_telf',
        'islim_request_dismissal.cash_mifi',
        'islim_request_dismissal.cash_fibra',
        'UserLow.residue_amount',
        'islim_distributors.description as distributor',
        DB::raw('( islim_request_dismissal.cash_total -  UserLow.residue_amount ) AS cash_discount_total'),
        DB::raw("(SELECT COUNT(EVI.id_req_dismissal) FROM islim_documentation_dismissal AS EVI WHERE EVI.id_req_dismissal = islim_request_dismissal.id AND EVI.status = 'A') AS cant_evidenci")
      )
      ->join('islim_reason_dismissal',
        'islim_reason_dismissal.id',
        'islim_request_dismissal.id_reason')
      ->leftJoin('islim_users AS UserLow',
        'UserLow.email',
        'islim_request_dismissal.user_dismissal')
      ->leftJoin('islim_distributor_user', function ($join) {
      $join->on('islim_distributor_user.user_email', '=', 'UserLow.email')
        ->where('islim_distributor_user.status', 'A');
    })
    ->leftJoin('islim_distributors', function ($join) {
      $join->on('islim_distributors.id', '=', 'islim_distributor_user.distributor_id')
        ->where('islim_distributors.status', 'A');
    })
      ->where([
        ['islim_request_dismissal.date_reg', '>=', $filter['dateStar']],
        ['islim_request_dismissal.date_reg', '<=', $filter['dateEnd']]]);

    if (!empty($filter['statusCash'])) {
      if ($filter['statusCash'] == "Ycash") {
        $data = $data->where('islim_request_dismissal.cash_total', '>', 0);
      } else {
        $data = $data->where('islim_request_dismissal.cash_total', '=', 0);
      }
    }

    if (!empty($filter['statusLow'])) {
      $data = $data->where('islim_request_dismissal.status', $filter['statusLow']);
    } else {
      $data = $data->where('islim_request_dismissal.status', '!=', 'T');
    }

    if (!empty($filter['user_dismissal'])) {
      $search = explode(',', $filter['user_dismissal']);
      $data = $data->whereIn('islim_request_dismissal.user_dismissal', $search);
    }
    $data = $data->orderBy('islim_request_dismissal.date_reg', 'DESC');


    // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
    //             return is_numeric($binding) ? $binding : "'{$binding}'";
    //         })->toArray());

    //     Log::alert($query);




    $data = $data->get();

    if ($cheking) {
      //Se recalculara la deuda de los usuarios que esta en proceso de solicitud de baja y se actualizan
      $data = json_decode(json_encode($data));

      if (!empty($data) && count($data) > 0) {
        self::chekingDeudaUser($data);
        $data = self::GetListProcess($filter, false);
      }
      //END recalculara la deuda de los usuarios que esta en proceso de solicitud de baja y se actualizan
    }
    return $data;
  }

  public static function getInProcessRequestByUser($email)
  {
    return self::getConnect('W')
      ->select(
        'id',
        'article_request',
        'cash_hbb',
        'cash_telf',
        'cash_mifi',
        'cash_fibra',
        'cash_request',
        'cash_abonos',
        'cash_total'
      )
      ->where([
        ['status', 'P'],
        ['user_dismissal', $email]])
      ->first();
  }

/**
 * [setUpdateDeuda Cuando se muestra el reporte de solicitud de bajas se recalcula las deudas]
 * @param [type] $objdata           [id del registro de solicitud a actualizar]
 * @param [type] $cash_request      [dinero en efectivo]
 * @param [type] $cash_abonos       [dinero en abono]
 * @param [type] $cash_hbb          [dinero de equipos hbb]
 * @param [type] $cash_telf         [dinero de equipos telf]
 * @param [type] $cash_mifi         [dinero de equipos mifi]
 * @param [type] $cash_fibra        [dinero de equipos fibra]
 * @param [type] $cant_abonos       [dinero de abonos]
 * @param [type] $days_cash_request [dias dinero en efectivo con deuda]
 */
  public static function setUpdateDeuda($objdata, $cash_request, $cash_abonos, $cash_hbb, $cash_telf, $cash_mifi, $cash_fibra, $cant_abonos, $days_cash_request)
  {

    $article_request = (int) $cash_hbb + (int) $cash_telf + (int) $cash_mifi + (int) $cash_fibra;

    $cash_total = (int) $cash_request + $article_request;

    $objdata = self::getConnect('W')
      ->where('id', $objdata)
      ->update([
        'cash_total'        => $cash_total,
        'cash_request'      => $cash_request,
        'cash_abonos'       => $cash_abonos,
        'article_request'   => $article_request,
        'cash_hbb'          => $cash_hbb,
        'cash_telf'         => $cash_telf,
        'cash_mifi'         => $cash_mifi,
        'cash_fibra'        => $cash_fibra,
        'cant_abonos'       => $cant_abonos,
        'days_cash_request' => $days_cash_request]);
    return $objdata;
  }

/**
 * [chekingDeudaUser Revisa los usuarios que se estan consultando en el reporte: solicitud de bajas, reporte de bajas y finiquito de bajas]
 * @param  [type] $objdata [objeto con la informacion a ser revisada y actualizada]
 * @return [type]          [description]
 */
  public static function chekingDeudaUser($objdata)
  {
    if (!empty($objdata)) {

      foreach ($objdata as $dataUpdate) {

        if ($dataUpdate->status == 'R' || $dataUpdate->status == 'P') {
          $sales = Sale::getSalesByuser($dataUpdate->user_dismissal, 'E')
            ->orderBy('date_reg', 'ASC')
            ->get();

          $efectivo = $sales->sum('amount');

          $cash_request = User::getTotalDebt([
            'user_email' => $dataUpdate->user_dismissal,
          ]);

          if ($cash_request->count()) {
            $efectivo += $cash_request = $cash_request[0]->debt;
          } else {
            $efectivo += $cash_request = 0;
          }

          $days_cash_request = null;
          if ($sales->count() > 0) {
            $days_cash_request = Carbon::now()->diffInDays(Carbon::createFromFormat('Y-m-d H:i:s', $sales[0]->date_reg));
          }

          //Deuda en inventario HBB
          $inv_deuda_hbb = SellerInventory::getDeudaUser($dataUpdate->user_dismissal, 'H');

          //Deuda en inventario telefonia
          $inv_deuda_telf = SellerInventory::getDeudaUser($dataUpdate->user_dismissal, 'T');

          //Deuda en inventario mifi
          $inv_deuda_mifi = SellerInventory::getDeudaUser($dataUpdate->user_dismissal, 'M');

          //Deuda en inventario fibra
          $inv_deuda_fibra = SellerInventory::getDeudaUser($dataUpdate->user_dismissal, 'F');

          //Deuda Ventas en abono
          $ventasAbono = Sale::getTotalSalesByType([
            'user' => $dataUpdate->user_dismissal,
            'whtI' => false]);

          $cash_abonos = null;
          $cant_abonos = null;

          if (!empty($ventasAbono)) {
            $cash_abonos = $ventasAbono->total_mount;
            $cant_abonos = $ventasAbono->total_sales;
          }

          if ($efectivo != $dataUpdate->cash_request
            || $inv_deuda_hbb != $dataUpdate->cash_hbb
            || $inv_deuda_telf != $dataUpdate->cash_telf
            || $inv_deuda_mifi != $dataUpdate->cash_mifi
            || $inv_deuda_fibra != $dataUpdate->cash_fibra
            || $cash_abonos != $dataUpdate->cash_abonos) {

            self::setUpdateDeuda($dataUpdate->id, $efectivo, $cash_abonos, $inv_deuda_hbb, $inv_deuda_telf, $inv_deuda_mifi, $inv_deuda_fibra, $cant_abonos, $days_cash_request);
          }
        }
      }
      return true;
    }
    return false;
  }

}
