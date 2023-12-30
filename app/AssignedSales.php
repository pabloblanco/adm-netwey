<?php

namespace App;

use App\AssignedSaleDetails;
use App\BankDeposits;
use App\Organization;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class AssignedSales extends Model
{
  protected $table = 'islim_asigned_sales';

  protected $fillable = [
    'id',
    'parent_email',
    'users_email',
    'user_process',
    'date_process',
    'n_tranfer',
    'bank_id',
    'amount',
    'amount_text',
    'date_accepted',
    'date_reject',
    'date_reg',
    'date_dep',
    'status',
    'alert_orange_send',
    'alert_red_send'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\AssignedSales
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new AssignedSales;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/**
 * [getListAssigneSaleLow Devuelve la lista de ventas pendientes por entregar efectivo de los subordinados de un usuario que esta en proceso de baja]
 * @param  [type] $UserLow      [Superior que se esta dando de baja]
 * @param  [type] $User         [Usuario inferior]
 * @param  string $status       [description]
 * @return [type]               [description]
 */
  public static function getListAssigneSaleLow($UserLow, $User, $status = 'V')
  {
    if (!empty($UserLow) && !empty($User)) {
      return self::getConnect('R')
        ->where([
          ['parent_email', $UserLow],
          ['users_email', $User],
          ['status', $status]])
        ->get();
    }
    return null;
  }

  public static function aceptReceptionVULow($id, $user, $data = [])
  {
    $data = self::getConnect('W')
      ->where([
        ['status', 'V'],
        ['id', $id],
        ['parent_email', $user],
      ])
      ->update($data);
  }

  public static function getAssignedSales($parent, $email, $status)
  {
    $sales;
    if (isset($email) && isset($parent)) {
      $sales = AssignedSales::where(['parent_email', $parent, 'users_email', $email, 'status', $status])->get();
    } elseif (isset($email)) {
      $sales = AssignedSales::where(['users_email', $email, 'status', $status])->get();
    } elseif (!empty($parent)) {
      $sales = AssignedSales::where(['parent_email', $parent, 'status', $status])->get();
    } else {
      $sales = AssignedSales::where(['status', $status])->get();
    }
    $ids = array();
    foreach ($sales as $sale) {
      $user         = User::select('name', 'last_name')->where(['email', $sale->users_email])->first();
      $parent       = User::select('name', 'last_name')->where(['email', $sale->parent_email])->first();
      $sale->user   = $user->name . ' ' . $user->last_name;
      $sale->parent = $parent->name . ' ' . $parent->last_name;
      $ids[]        = ['id' => $sale->id];
    }
    return array('sales' => $sales, 'ids' => $ids);
  }

  public static function getAssignedSalesDetails($id)
  {
    $details = AssignedSaleDetails::where(['asigned_sale_id', $id])->get();
    foreach ($details as $item) {
      $sale       = Sale::getSaleReport(null, null, null, null, null, ['A'], null, $item->unique_transaction)['sales'][0];
      $item->sale = $sale;
    }
    return $details;
  }

  public static function getDays_deb_old($days_deb)
  {
    // foreach ($deb_old as $days_deb){
    $days_old = self::getConnect('R')
      ->select(
        'islim_asigned_sales.date_reg as date_old_deb'
      )
      ->where([
        ['islim_asigned_sales.status', 'P'],
        ['islim_asigned_sales.parent_email', $days_deb->email],
      ])
      ->orderBy('islim_asigned_sales.date_reg', 'ASC')
      ->first();

    if (!empty($days_old)) {
      $date1 = date_create(date("Y-m-d", strtotime($days_old->date_old_deb)));
      $date2 = date_create(date("Y-m-d"));

      $resultado      = $date1->diff($date2);
      $resultado      = $resultado->format('%a');
      $alert_days_deb = false;
      if ($resultado > 4) {
        $alert_days_deb = true;
      }
      $days_deb->alert_days_deb = $alert_days_deb;
      $days_deb->days_old_deb   = $resultado . ' dia(s)';
      //Log::info("date_old: " . $days_old->date_old_deb);
      // Log::info("deb_old: " . $days_deb->days_old_deb);
      //Log::info("email: " . $days_deb->email);
    } else {
      $days_deb->alert_days_deb = false;
      $days_deb->days_old_deb   = 'N/A';
    }
    // }
    return $days_deb;
  }

  public static function getReportConciliations($filters = false)
  {
    if ($filters && is_array($filters)) {
      $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

      $data = BankDeposits::getConnect('R')->select(
        'islim_bank_deposits.amount',
        'islim_bank_deposits.id_deposit',
        'islim_bank_deposits.id',
        'islim_bank_deposits.cod_auth',
        'islim_bank_deposits.date_process',
        'islim_bank_deposits.reason_deposit',
        'islim_banks.name as bank',
        'islim_users.name',
        'islim_users.last_name',
        'ope.name as ope_name',
        'ope.last_name as ope_last_name',
        'sup.name as sup_name',
        'sup.last_name as sup_last_name'
      )
        ->leftJoin(
          'islim_banks',
          'islim_banks.id',
          'islim_bank_deposits.bank'
        )
        ->join(
          'islim_users',
          'islim_users.email',
          'islim_bank_deposits.email'
        )
      /*->join('islim_users',
      function($join) use ($orgs){
      $join->on('islim_users.email', '=', 'islim_bank_deposits.email')
      ->whereIn('islim_users.id_org', $orgs->pluck('id'));
      })*/
        ->leftJoin(
          'islim_users as sup',
          'sup.email',
          'islim_users.parent_email'
        )
        ->join(
          'islim_users as ope',
          'ope.email',
          'islim_bank_deposits.user_process'
        )
        ->where('islim_bank_deposits.status', 'A')
        ->whereIn('islim_users.id_org', $orgs->pluck('id'));

      if (!empty($filters['dateb'])) {
        $dateb = date('Y-m-d H:i:s', strtotime($filters['dateb']." 00:00:00"));
        $data  = $data->where('islim_bank_deposits.date_process', '>=', $dateb);
      }

      if (!empty($filters['datee'])) {
        $datee = date('Y-m-d H:i:s', strtotime($filters['datee']." 23:59:59"));
        $data  = $data->where('islim_bank_deposits.date_process', '<=', $datee);
      }

      if (!empty($filters['opefec'])) {
        $data = $data->where('islim_bank_deposits.user_process', $filters['opefec']);
      }

      if (!empty($filters['coord'])) {
        $data = $data->where('islim_users.email', $filters['coord']);
      }

      $data->orderBy('islim_bank_deposits.date_process', 'DESC');

      return $data->get();
    }

    return [];
  }

  public static function getReportRRE($filters = false)
  {
    $data = AssignedSales::getConnect('R')->select(
      'islim_asigned_sales.id',
      'islim_asigned_sales.amount',
      'islim_asigned_sales.date_reg',
      'islim_asigned_sales.date_accepted',
      'islim_asigned_sales.date_reject',
      'islim_asigned_sales.date_process',
      'islim_asigned_sales.status',
      'islim_users.name',
      'islim_users.last_name',
      'seller.name as seller_name',
      'seller.last_name as seller_last_name'
    )
      ->join('islim_users', 'islim_users.email', 'islim_asigned_sales.parent_email')
      ->join('islim_users as seller', 'seller.email', 'islim_asigned_sales.users_email')
      ->where('islim_asigned_sales.status', '!=', 'T');

    if ($filters && is_array($filters)) {
      if (!empty($filters['dateb'])) {
        $dateb = date('Y-m-d H:i:s', strtotime($filters['dateb']));
        $data  = $data->where('islim_asigned_sales.date_reg', '>=', $dateb);
      }

      if (!empty($filters['datee'])) {
        $datee = date('Y-m-d H:i:s', strtotime($filters['datee']) + (3600 * 23) + 3599);
        $data  = $data->where('islim_asigned_sales.date_reg', '<=', $datee);
      }

      if (!empty($filters['org'])) {
        $data = $data->where('islim_users.id_org', $filters['org']);
      } else {
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $data = $data->whereIn('islim_users.id_org', $orgs->pluck('id'));
      }

      if (!empty($filters['coord'])) {
        $data = $data->where('islim_asigned_sales.parent_email', $filters['coord']);
      }

      if (!empty($filters['seller'])) {
        $data = $data->where('islim_asigned_sales.users_email', $filters['seller']);
      }

      if (!empty($filters['status'])) {
        $data = $data->where('islim_asigned_sales.status', $filters['status']);
      }

      if (!empty($filters['alert'])) {
        if ($filters['alert'] == 'Gr') {
          $data = $data->where('islim_asigned_sales.status', 'P');
        }

        if ($filters['alert'] == 'G') {
          $data = $data->where('islim_asigned_sales.status', 'I');
        }

        if ($filters['alert'] == 'B' || $filters['alert'] == 'O' || $filters['alert'] == 'R') {
          $data = $data->where('islim_asigned_sales.status', 'V');

          $diff = DB::raw("TIME_FORMAT(TIME(TIMEDIFF(NOW(),islim_asigned_sales.date_reg)), '%H')");

          if ($filters['alert'] == 'B') {
            $data = $data->where($diff, '<', 6);
          }

          if ($filters['alert'] == 'O') {
            $data = $data->where([[$diff, '>=', 6], [$diff, '<', 12]]);
          }

          if ($filters['alert'] == 'R') {
            $data = $data->where($diff, '>=', 12);
          }

        }
      }
    }

    //$data = $data->get();

    return $data;
  }

  public static function getTotalDebtByUser($email)
  {
    return self::getConnect('R')
      ->select('amount')
      ->where([
        ['status', 'P'],
        ['parent_email', $email]])
      ->sum('amount');
  }

  public static function getLastConciliation($user)
  {
    return self::getConnect('R')
      ->select('date_process')
      ->where([
        ['status', 'A'],
        ['parent_email', $user]])
      ->whereNull('n_tranfer')
      ->whereNull('bank_id')
      ->orderBy('date_process', 'DESC')
      ->first();
  }


  /**
   * [getTotalCashMove Obtiene total de efectivo (recibido, entregado y conciliado) segun filtros]
   * @param [filters] $filters [filtros a aplicar]
   */

  public static function getTotalCashMove($filters = false, $print = false){
    if ($filters && is_array($filters)) {
      $data=self::getConnect('R')
            //->select(DB::raw('SUM(amount) as total_cash'))
            ->where('amount','>',0);

      if(!empty($filters['parent_email'])){
        $data=$data->where('parent_email',$filters['parent_email']);
      }
      if(!empty($filters['users_email'])){
        $data=$data->where('users_email',$filters['users_email']);
      }
      if(!empty($filters['status'])){
        $data=$data->whereIn('status',$filters['status']);
      }
      if(!empty($filters['date_accepted'])){
        $datetime = $filters['date_accepted'];
        $data=$data->where(function($query) use ($datetime) {
              $query->where('date_accepted','<=' ,$datetime)
              ->orWhere(function($query2) use ($datetime) {
                $query2->whereNull('date_accepted')
                ->where('date_reg','<=' ,$datetime);
              });
            });
      }

      if(!empty($filters['date_process'])){
        $datetime = $filters['date_process'];
        $data=$data->where(function($query) use ($datetime) {
              $query->where('date_process','<=' ,$datetime)
              ->orWhere(function($query2) use ($datetime) {
                $query2->whereNull('date_process')
                ->where('date_reg','<=' ,$datetime);
              });
            });
      }

      if(!empty($filters['date_reg'])){
        $datetime = $filters['date_reg'];
        $data=$data->where(function($query) use ($datetime) {
          $query->where('date_reg','<=' ,$datetime);
        });
      }



      if($print){
        $qry = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
                return is_numeric($binding) ? $binding : "'{$binding}'";
            })->toArray());

         return $qry;
      }
      else{
        //$data=$data->first();
        return $data;
      }
    }
    return null;
  }

}
