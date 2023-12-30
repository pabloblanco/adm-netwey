<?php

namespace App;

use App\Organization;
use App\Profile;
use App\ProfileDetail;
use App\HistoryInventoryStatus;
use App\SellerInventoryTemp;
use App\User;
use App\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Log;

class SellerInventory extends Model
{
  protected $table = 'islim_inv_assignments';

  protected $fillable = [
    'users_email',
    'inv_arti_details_id',
    'obs',
    'status',
    'date_reg',
    'first_assignment',
    'date_orange',
    'date_red',
    'last_assigned_by',
    'last_assignment',
    'user_red',
    'red_notification_view'
  ];

  protected $primaryKey = 'inv_arti_details_id';

  public $incrementing = false;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\SellerInventory
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SellerInventory;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getTotalInventory($user)
  {
    //cantidad total de inventario asignado a un usuario
    //return DB::table('islim_inv_assignments AS ia')->distinct()
    $inv = self::getConnect('R')->distinct()
      ->join('islim_inv_arti_details AS i', 'i.id', '=', 'islim_inv_assignments.inv_arti_details_id')
      ->join('islim_inv_articles AS a', 'a.id', '=', 'i.inv_article_id')
      ->join('islim_users AS u', 'u.email', '=', 'islim_inv_assignments.users_email')
      ->where([
        'islim_inv_assignments.users_email' => $user,
        'islim_inv_assignments.status'      => 'A',
        'i.status'                          => 'A'])
      ->where('a.artic_type', '<>', 'F')
      ->count();

    //cantidad total de inventario preasignado a un usuario
    $preinv = SellerInventoryTemp::getConnect('R')->distinct()
      ->join('islim_inv_arti_details AS i', 'i.id', '=', 'islim_inv_assignments_temp.inv_arti_details_id')
      ->join('islim_inv_articles AS a', 'a.id', '=', 'i.inv_article_id')
      ->join('islim_users AS u', 'u.email', '=', 'islim_inv_assignments_temp.user_email')
      ->where([
        'islim_inv_assignments_temp.user_email' => $user,
        'islim_inv_assignments_temp.status'     => 'P',
        'i.status'                              => 'A'])
      ->where('a.artic_type', '<>', 'F')
      ->count();

    return $inv + $preinv;
  }

  public static function getTotalPermision($user, $type = false)
  {

    //consulta para saber si se pueden asignar equipos del tipo $type al usuario $user
    $haspermi = UserRole::getConnect('R')
      ->distinct()
      ->select('islim_user_roles.value')
      ->join('islim_users AS u', 'u.email', '=', 'islim_user_roles.user_email')
      ->join('islim_policies AS p', 'p.id', '=', 'islim_user_roles.policies_id')
      ->where([
        'p.code'                      => $type ? $type : 'LIV-DSE',
        'islim_user_roles.user_email' => $user,
        'islim_user_roles.status'     => 'A',
      ])->count();

    if ($haspermi > 0) {
      // el usuario $user tiene permiso para que le asignen equipos del tipo $type

      //consulta para obtener la cantidad maxima de equipos que se le pueden asignar al usuario segun el tipo y la politica que tenga
      $cant = UserRole::getConnect('R')
        ->distinct()
        ->select('islim_user_roles.value')
        ->join('islim_users AS u', 'u.email', '=', 'islim_user_roles.user_email')
        ->join('islim_policies AS p', 'p.id', '=', 'islim_user_roles.policies_id')
        ->where([
          'p.code'                      => $type ? $type : 'LIV-DSE',
          'islim_user_roles.user_email' => $user,
          'islim_user_roles.status'     => 'A']
        )
        ->first()
        ->value;
      return $cant;
    } else {
      return 0;
    }
  }

  public static function canRecieveMoreInventory($user)
  {
    return (SellerInventory::getTotalInventory($user) < (SellerInventory::getTotalPermision($user) * 1));
  }

  public static function getSellerInventoryReport($user, $product, $date_ini, $date_end)
  {

    //$report = DB::table('islim_inv_assignments')->distinct()->select(

    $report = self::getConnect('R')->distinct()->select(
      'islim_inv_assignments.users_email',
      'islim_users.name as user_name',
      'islim_users.last_name as user_lname',
      'islim_users.parent_email',
      'islim_inv_assignments.date_reg',
      'islim_inv_assignments.inv_arti_details_id',
      'islim_inv_articles.title as article',
      'islim_inv_arti_details.price_pay',
      'islim_inv_arti_details.imei',
      'islim_inv_arti_details.msisdn',
      'islim_inv_arti_details.iccid',
      'islim_inv_arti_details.date_reg as birth_modem',
      'islim_inv_assignments.obs',
      'islim_inv_assignments.first_assignment'
    )
      ->join('islim_users', function ($join) {
        $join->on('islim_users.email', '=', 'islim_inv_assignments.users_email');
      })
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->where('islim_inv_assignments.status', 'A')
      ->where('islim_inv_arti_details.status', 'A')
      ->orderBy('islim_inv_assignments.date_reg');

    //$totalamount = DB::table('islim_inv_assignments')
    $totalamount = self::getConnect('R')
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->where('islim_inv_arti_details.status', 'A')
      ->where('islim_inv_assignments.status', 'A');

    if (isset($user) && !empty($user)) {
      $report      = $report->where('islim_inv_assignments.users_email', $user);
      $totalamount = $totalamount->where(['islim_inv_assignments.users_email' => $user]);
    }

    if (isset($product) && !empty($product)) {
      $report      = $report->where('islim_inv_articles.id', $product);
      $totalamount = $totalamount->where(['islim_inv_articles.id' => $product]);
    }
    if ((isset($date_ini) && !empty($date_ini)) && (isset($date_end) && !empty($date_end))) {
      $report      = $report->whereBetween('islim_inv_assignments.date_reg', [$date_ini, $date_end]);
      $totalamount = $totalamount->whereBetween('islim_inv_assignments.date_reg', [$date_ini, $date_end]);
    } else {
      if (isset($date_ini) && !empty($date_ini)) {
        $report      = $report->where('islim_inv_assignments.date_reg', '>=', $date_ini);
        $totalamount = $totalamount->where('islim_inv_assignments.date_reg', '>=', $date_ini);
      } else {
        if (isset($date_end) && !empty($date_end)) {
          $report      = $report->where('islim_inv_assignments.date_reg', '<=', $date_end);
          $totalamount = $totalamount->where('islim_inv_assignments.date_reg', '<=', $date_end);
        }
      }
    }
    return ['inventory' => $report->get(), 'amount' => $totalamount->sum('price_pay')];
  }

  public static function getUserInventory($user)
  {
    $assig = self::getConnect('R')
      ->select(
        'islim_inv_arti_details.id',
        'islim_inv_articles.title as title',
        'islim_inv_arti_details.msisdn',
        'islim_inv_arti_details.imei',
        'islim_inv_arti_details.price_pay as price',
        'islim_inv_assignments.users_email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.status as status_user',
        'islim_inv_assignments.status',
        DB::raw('CONCAT("A") as type'), //Asignado
        'islim_inv_assignments.date_red'
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_assignments.inv_arti_details_id',
        'islim_inv_arti_details.id'
      )
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_inv_assignments.users_email'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->where([
        ['islim_inv_arti_details.status', 'A'],
        ['islim_inv_assignments.users_email', $user],
        ['islim_inv_assignments.status', 'A']]);

    $preassig = SellerInventoryTemp::getConnect('R')
      ->select(
        'islim_inv_arti_details.id',
        'islim_inv_articles.title as title',
        'islim_inv_arti_details.msisdn',
        'islim_inv_arti_details.imei',
        'islim_inv_arti_details.price_pay as price',
        'islim_inv_assignments_temp.user_email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.status as status_user',
        'islim_inv_assignments_temp.status',
        DB::raw('CONCAT("P") as type'), //preasignado
        DB::raw('CONCAT("N") as type')
      )
      ->join(
        'islim_inv_arti_details',
        'islim_inv_assignments_temp.inv_arti_details_id',
        'islim_inv_arti_details.id'
      )
      ->join(
        'islim_inv_articles',
        'islim_inv_articles.id',
        'islim_inv_arti_details.inv_article_id'
      )
      ->join(
        'islim_users',
        'islim_users.email',
        'islim_inv_assignments_temp.user_email'
      )
      ->where([
        ['islim_inv_arti_details.status', 'A'],
        ['islim_inv_assignments_temp.user_email', $user],
        ['islim_inv_assignments_temp.status', 'P']]);

    return ($assig->union($preassig)->get());
  }

/**
 * [getStatusInv_fromcsv obtiene el DN si se encuentra en la lista de inventario como activo]
 * @param  [type] $msisdn [description]
 * @return [type]         [description]
 */
  public static function getStatusInv_fromcsv($msisdn)
  {
    $qry = self::getConnect('R')
      ->select(
        'islim_inv_assignments.users_email AS assigned',
        'islim_inv_assignments.inv_arti_details_id',
        'islim_inv_arti_details.msisdn',
        'islim_inv_assignments.date_reg',
        DB::raw('IF(date_red is null,CONCAT("orange"),CONCAT("red")) AS color'),
        DB::raw('IF(date_red is null,islim_inv_assignments.date_orange,islim_inv_assignments.date_red) AS date_color')
      )
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
      ->whereIn('islim_inv_arti_details.status', ['A', 'S'])
      ->where([
        ['islim_inv_assignments.status', '=', 'A'],
        ['islim_inv_arti_details.msisdn', $msisdn]])
      ->where(function ($query) {
        $query->whereNotNull('islim_inv_assignments.date_red')
          ->orWhereNotNull('islim_inv_assignments.date_orange');
      })
      ->first();
    if (!empty($qry) && $qry->count() > 0) {
      return json_decode($qry);
    }
    return null;
  }

  public static function getStatusInv($filters = [])
  {
    if (session()->exists('user')) {
      $orgs         = Organization::getOrgsPermitByOrgs(session('user')->id_org);
      $id_profile   = session('user')->profile->id;
      $profile_type = session('user')->profile->type;
      $user_email   = session('user')->email;
    } else {
      $user = User::where('email', '=', $filters['user'])->first();
      $orgs = Organization::getOrgsPermitByOrgs($user->id_org);

      $profile_detail = ProfileDetail::where('user_email', '=', $filters['user'])
        ->where('status', '=', 'A')->first();
      $id_profile   = $profile_detail->id_profile;
      $profile_type = Profile::where('id', '=', $id_profile)->first()->type;
      $user_email   = $user->email;
    }

    $parents = array();

    $is_val = false;
    if (is_array($filters)) {
      if (!empty($filters['is_val'])) {
        if ($filters['is_val'] == 'true') {
          $is_val = true;
        }

        if (!$is_val || ($is_val && $id_profile != 15)) {
          if ($profile_type != "master") {
            $childs = User::select('email')
              ->where([
                'parent_email' => $user_email,
                'status'       => 'A'])
              ->orWhere('email', $user_email)
              ->get();
            $parents = $childs->pluck('email');
            //array_push($parents,$user_email);
          }
        }

        $qry = self::getConnect('R')
          ->select(
            'islim_inv_assignments.users_email AS assigned',
            DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) AS nameAssigned'),
            DB::raw('IF(islim_users.esquema_comercial_id IS NULL,
              (SELECT EQ.name FROM islim_esquema_comercial AS EQ WHERE EQ.id =  UserSupervisor.esquema_comercial_id),
              (SELECT EQ2.name FROM islim_esquema_comercial AS EQ2 WHERE EQ2.id =  islim_users.esquema_comercial_id )) AS esquema'),
            'islim_inv_assignments.inv_arti_details_id',
            'islim_inv_arti_details.msisdn',
            'islim_inv_articles.title',
            'islim_inv_articles.artic_type',
            'islim_history_status_inventory.url_evidencia as evidence',
            DB::raw('IF(islim_inv_assignments.date_red is null,CONCAT("orange"),CONCAT("red")) AS color'),
            DB::raw('IF(islim_inv_assignments.date_red is null,islim_inv_assignments.date_orange,islim_inv_assignments.date_red) AS date_color')
          )
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
          ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
          ->join('islim_users', 'islim_users.email', '=', 'islim_inv_assignments.users_email')
          ->join('islim_users AS UserSupervisor', 'islim_users.parent_email', '=', 'UserSupervisor.email')
          ->leftJoin('islim_history_status_inventory', function ($join) {
            $join->on('islim_history_status_inventory.users_email', '=', 'islim_inv_assignments.user_red')
            ->on('islim_history_status_inventory.inv_arti_details_id', '=', 'islim_inv_assignments.inv_arti_details_id')
            ->where('islim_history_status_inventory.status','!=','T')
            ->whereRaw("islim_history_status_inventory.id = (SELECT MAX(qry.id) FROM islim_history_status_inventory as qry WHERE qry.status = 'C' and qry.users_email = islim_history_status_inventory.users_email and qry.inv_arti_details_id = islim_history_status_inventory.inv_arti_details_id)");

          })
          ->whereIn('islim_inv_arti_details.status', ['A', 'S'])
          ->whereIn('islim_users.id_org', $orgs->pluck('id'))
          ->where('islim_inv_assignments.status', '=', 'A')
          ->where(function ($query) {
            $query->whereNotNull('islim_inv_assignments.date_red')
              ->orWhereNotNull('islim_inv_assignments.date_orange');
          })
          ->whereRaw('(
              IF(
                islim_inv_assignments.date_red is not null,
                islim_users.platform in ("coordinador","admin"),
                islim_users.platform in ("vendor","coordinador","admin")
              )
            )');

        if (count($parents) > 0) {
          $qry = $qry->whereIn('islim_users.parent_email', $parents);
        }

        if (is_array($filters)) {
          if (!empty($filters['dateb']) && !empty($filters['datee'])) {
            $qry = $qry->whereRaw('(IF(islim_inv_assignments.date_red is not null,islim_inv_assignments.date_red between ? and ?,islim_inv_assignments.date_orange between ? and ?))',[$filters['dateb'],$filters['datee'],$filters['dateb'],$filters['datee']]);
          } elseif (!empty($filters['dateb'])) {

            $qry = $qry->whereRaw('(IF(islim_inv_assignments.date_red is not null,islim_inv_assignments.date_red >= ? ,islim_inv_assignments.date_orange >= ? ))',[$filters['dateb'],$filters['dateb']]);

          } elseif (!empty($filters['datee'])) {
            $qry = $qry->whereRaw('(IF(islim_inv_assignments.date_red is not null,islim_inv_assignments.date_red <= ? ,islim_inv_assignments.date_orange <= ? ))',[$filters['datee'],$filters['datee']]);
          }

          if (!empty($filters['color'])) {
            if ($filters['color'] == 'red') {
              $qry = $qry->whereNotNull('islim_inv_assignments.date_red');
            }

            if ($filters['color'] == 'orange') {
              $qry = $qry->whereNotNull('islim_inv_assignments.date_orange')->whereNull('islim_inv_assignments.date_red');
            }

          }

          if (!empty($filters['msisdns'])) {
            $msisdns = explode(',', $filters['msisdns']);
            $qry     = $qry->whereIn('islim_inv_arti_details.msisdn', $msisdns);
          }
        }

        if($is_val){
          if (!empty($filters['evidence'] == "Y")) {
            $qry = $qry->whereNotNull('islim_history_status_inventory.url_evidencia')->whereNotNull('islim_inv_assignments.date_red');
          }else 
          if(!empty($filters['evidence'] == "N")){
            $qry = $qry->whereNull('islim_history_status_inventory.url_evidencia')->whereNotNull('islim_inv_assignments.date_red');
          }
        }

        $data = $qry;

        $data = $data->orderBy('date_color', 'ASC');
        //Log::info(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
        //exit;
        $data = $data->get();
        return $data;
      }
    }

    if (!$is_val || ($is_val && $id_profile != 15)) {
      if ($profile_type != "master") {
        $childs = User::select('email')
          ->where([
            'parent_email' => $user_email,
            'status'       => 'A'])
          ->orWhere('email', $user_email)
          ->get();
        $parents = $childs->pluck('email');
        //array_push($parents,session('user')->email);
      }
    }

    $qry = self::getConnect('R')
      ->select(
        'islim_inv_assignments.users_email as assigned',
        'islim_inv_assignments.inv_arti_details_id',
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title',
        'islim_inv_articles.artic_type',
        DB::raw('IF(islim_inv_assignments.date_red is null,CONCAT("orange"),CONCAT("red")) as color'),
        DB::raw('IF(islim_inv_assignments.date_red is null,islim_inv_assignments.date_orange,islim_inv_assignments.date_red) as date_color')
      )
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->join('islim_users', 'islim_users.email', '=', 'islim_inv_assignments.users_email')
      ->whereIn('islim_inv_arti_details.status', ['A', 'S'])
      ->whereIn('islim_users.id_org', $orgs->pluck('id'))
      ->where('islim_inv_assignments.status', '=', 'A')
      ->where(function ($query) {
        $query->whereNotNull('islim_inv_assignments.date_red')
          ->orWhereNotNull('islim_inv_assignments.date_orange');
      });

    if (count($parents) > 0) {
      $qry = $qry->whereIn('islim_users.parent_email', $parents);
    }

    // if (is_array($filters)) {
    //   if (!empty($filters['dateb']) && !empty($filters['datee'])) {
    //     $qry = $qry->whereBetween('islim_inv_assignments.date_reg', [$filters['dateb'], $filters['datee']]);
    //   } elseif (!empty($filters['dateb'])) {
    //     $qry = $qry->where('islim_inv_assignments.date_reg', '>=', $filters['dateb']);
    //   } elseif (!empty($filters['datee'])) {
    //     $qry = $qry->where('islim_inv_assignments.date_reg', '<=', $filters['datee']);
    //   }

    //   if (!empty($filters['color'])) {
    //     if ($filters['color'] == 'red') {
    //       $qry = $qry->whereNotNull('islim_inv_assignments.date_red');
    //     }

    //     if ($filters['color'] == 'orange') {
    //       $qry = $qry->whereNotNull('islim_inv_assignments.date_orange')->whereNull('islim_inv_assignments.date_red');
    //     }
    //   }

    //   if (!empty($filters['msisdns'])) {
    //     $msisdns = explode(',', $filters['msisdns']);
    //     $qry     = $qry->whereIn('islim_inv_arti_details.msisdn', $msisdns);
    //   }
    // }


    if (is_array($filters)) {
          if (!empty($filters['dateb']) && !empty($filters['datee'])) {

            if (!empty($filters['color'])) {
              if ($filters['color'] == 'red') {
                $qry = $qry->whereBetween('islim_inv_assignments.date_red', [$filters['dateb'], $filters['datee']]);
              }
              if ($filters['color'] == 'orange') {
                $qry = $qry->whereBetween('islim_inv_assignments.date_orange', [$filters['dateb'], $filters['datee']]);
              }
            }
            else{
              $qry = $qry->where(function ($query) use ($filters) {
                $query->whereBetween('islim_inv_assignments.date_orange', [$filters['dateb'], $filters['datee']])
                  ->orWhereBetween('islim_inv_assignments.date_red', [$filters['dateb'], $filters['datee']]);
              });
            }
          } elseif (!empty($filters['dateb'])) {

            if (!empty($filters['color'])) {
              if ($filters['color'] == 'red') {
                $qry = $qry->where('islim_inv_assignments.date_red', '>=', $filters['dateb']);
              }
              if ($filters['color'] == 'orange') {
                $qry = $qry->where('islim_inv_assignments.date_orange', '>=', $filters['dateb']);
              }
            }
            else{
              $qry = $qry->where(function ($query) use ($filters) {
                $query->where('islim_inv_assignments.date_orange', '>=', $filters['dateb'])->orWhere('islim_inv_assignments.date_red', '>=', $filters['dateb']);
              });
            }
          } elseif (!empty($filters['datee'])) {

            if (!empty($filters['color'])) {
              if ($filters['color'] == 'red') {
                $qry = $qry->where('islim_inv_assignments.date_red', '<=', $filters['datee']);
              }
              if ($filters['color'] == 'orange') {
                $qry = $qry->where('islim_inv_assignments.date_orange', '<=', $filters['datee']);
              }
            }
            else{
              $qry = $qry->where(function ($query) use ($filters) {
                $query->where('islim_inv_assignments.date_orange', '<=', $filters['datee'])->orWhere('islim_inv_assignments.date_red', '<=', $filters['datee']);
              });
            }
          }

          if (!empty($filters['color'])) {
            if ($filters['color'] == 'red') {
              $qry = $qry->whereNotNull('islim_inv_assignments.date_red');
            }

            if ($filters['color'] == 'orange') {
              $qry = $qry->whereNotNull('islim_inv_assignments.date_orange')->whereNull('islim_inv_assignments.date_red');
            }

          }

          if (!empty($filters['msisdns'])) {
            $msisdns = explode(',', $filters['msisdns']);
            $qry     = $qry->whereIn('islim_inv_arti_details.msisdn', $msisdns);
          }
        }


    $data = $qry;

    $data = $data->orderBy('date_color', 'DESC');
    // print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
    // exit;
    $data = $data->get();
    return $data;
  }


  public static function getHistoryStatusInv($filters = [])
  {

    $sub = HistoryInventoryStatus::getConnect('R')
          ->select('inv_arti_details_id')
          ->where('status','P')
          ->where('color_destino','N');

    if (is_array($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $sub = $sub->whereBetween('islim_history_status_inventory.date_reg', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $sub = $sub->where('islim_history_status_inventory.date_reg', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $sub = $sub->where('islim_history_status_inventory.date_reg', '<=', $filters['datee']);
      }
    }

    $sub = $sub->get();

    $inv_ids=[];
    $subqry = "";
    if($sub){
      $inv_ids = $sub->pluck('inv_arti_details_id')->toArray();
      if(count($inv_ids) > 0)
        $subqry = 'hsi.inv_arti_details_id IN ('.implode(',',$inv_ids).') AND ';
    }

    $data = self::getConnect('R')
      ->select(
        DB::raw('CONCAT(islim_users.name," ",islim_users.last_name," (",islim_inv_assignments.users_email,")") AS assigned'),
        DB::raw('(SELECT EQ.name FROM islim_esquema_comercial AS EQ WHERE EQ.id =  userCoordinator.esquema_comercial_id) as coordination'),
        DB::raw('CONCAT(userCoordinator.name," ",userCoordinator.last_name," (",userCoordinator.email,")") as nameCoordinator'),
        DB::raw('(SELECT EQ.name FROM islim_esquema_comercial AS EQ WHERE EQ.id =  userRegional.esquema_comercial_id) as region'),
        DB::raw('CONCAT(userRegional.name," ",userRegional.last_name," (",userRegional.email,")") as nameRegional'),
        'islim_inv_arti_details.msisdn',
        'islim_inv_articles.title',
        'islim_inv_articles.artic_type',
        DB::raw('IF(islim_inv_assignments.date_red is not null,CONCAT("red"), IF(islim_inv_assignments.date_orange is not null,CONCAT("orange"),CONCAT(""))) as color'),
        DB::raw('IF(islim_inv_assignments.date_red is not null,islim_inv_assignments.date_red, IF(islim_inv_assignments.date_orange is not null,islim_inv_assignments.date_orange,CONCAT(""))) as date_color'),
        'history_qry.last_date_orange',
        'history_qry.cant_orange'
      )
      ->join(DB::raw('(
        SELECT hsi.inv_arti_details_id, MAX(hsi.date_reg) as last_date_orange , COUNT(hsi.inv_arti_details_id) as cant_orange FROM netwey_test.islim_history_status_inventory AS hsi WHERE '.$subqry.' hsi.status = "P" and hsi.color_destino = "N" GROUP BY hsi.inv_arti_details_id) history_qry'),function($join){
           $join->on('islim_inv_assignments.inv_arti_details_id', '=', 'history_qry.inv_arti_details_id');
        }
      )
      ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->join('islim_users', 'islim_users.email', '=', 'islim_inv_assignments.users_email')
      ->join('islim_profile_details', function ($join) {
        $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
          ->where('islim_profile_details.status', 'A');
      })
      ->join(DB::raw('(SELECT u.email as email,IF(ipd.id_profile IN (11,19),(SELECT ic.email FROM islim_users ic WHERE u.parent_email = ic.email),IF(ipd.id_profile IN (10,18),(SELECT ic.email FROM islim_users ic WHERE u.email = ic.email),CONCAT(""))) as coordinator FROM islim_users u inner join islim_profile_details ipd on u.email = ipd.user_email AND  ipd.status = "A") usr_qry'),function($join){
           $join->on('islim_users.email', '=', 'usr_qry.email');
        }
      )
      ->join('islim_users AS userCoordinator', 'usr_qry.coordinator', '=', 'userCoordinator.email')
      ->join('islim_users AS userRegional', 'userCoordinator.parent_email', '=', 'userRegional.email')
      ->where('islim_inv_assignments.status', '=', 'A');

    if (is_array($filters)) {
      if (!empty($filters['color'])) {
        if ($filters['color'] == 'red') {
          $data = $data->whereNotNull('islim_inv_assignments.date_red');
        }

        if ($filters['color'] == 'orange') {
          $data = $data->whereNotNull('islim_inv_assignments.date_orange')->whereNull('islim_inv_assignments.date_red');
        }
      }
      if (!empty($filters['msisdns'])) {
        $data = $data->whereIn('islim_inv_arti_details.msisdn', $filters['msisdns']);
      }
    }

    // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
    //             return is_numeric($binding) ? $binding : "'{$binding}'";
    //         })->toArray());

    // Log::info($query);


    $data = $data->get();
    return $data;
  }

  public static function getInvAvailable($user, $sellers = [], $articles)
  {
    return self::getConnect('R')
      ->select('islim_inv_assignments.inv_arti_details_id')
      ->join(
        'islim_inv_arti_details',
        'islim_inv_arti_details.id',
        'islim_inv_assignments.inv_arti_details_id'
      )
      ->whereIn('islim_inv_arti_details.inv_article_id', $articles)
      ->where(function ($q) use ($sellers, $user) {
        $q->whereIn('islim_inv_assignments.users_email', $sellers)
          ->orWhere('islim_inv_assignments.users_email', $user);
      })
      ->where('islim_inv_assignments.status', 'A')
      ->count();
  }
/**
 * [getInventaryAssigne Obtiene el inventario asociado al vendedor y que se encuentra activo]
 * @param  [type] $sellers [description]
 * @return [type]          [description]
 */
  public static function getInventaryAssigne($sellers)
  {
    return self::getConnect('R')
      ->where([['users_email', $sellers],
        ['status', 'A']])
      ->get();
  }

/**
 * [setRemoveInventoryLow Remueve el inventario activo que este en la tabla del usuario que finalizo la baja]
 * @param [type] $sellers [description]
 */
  public static function setRemoveInventoryLow($sellers, $msjLow)
  {
    return self::getConnect('W')
      ->where([
        ['users_email', $sellers],
        ['status', 'A']])
      ->update([
        'status' => 'T',
        'obs'    => $msjLow,
      ]);
  }

  public static function getActiveAssignedByIdArtic($id)
  {
    return self::getConnect('W')
      ->select('users_email', 'inv_arti_details_id', 'date_red')
      ->where([
        ['inv_arti_details_id', $id],
        ['status', 'A'],
      ])
      ->first();
  }

  /**
   * Metodo para consultar info de los articulos por tipo asignados a un usuario
   * @param String $user
   * @param String $type
   *
   * @return App\Models\SellerInventory
   */
  public static function getDeudaUser($user = false, $type = 'H')
  {
    if ($user) {

      $inv_deuda = SellerInventory::getConnect('R')
        ->join(
          'islim_inv_arti_details',
          'islim_inv_arti_details.id',
          'islim_inv_assignments.inv_arti_details_id'
        )
        ->join(
          'islim_inv_articles',
          'islim_inv_articles.id',
          'islim_inv_arti_details.inv_article_id'
        )
        ->where([
          ['islim_inv_assignments.users_email', '=', $user],
          ['islim_inv_articles.artic_type', '=', $type],
          ['islim_inv_assignments.status', '=', 'A']
        ])->sum('islim_inv_arti_details.price_pay');

        return $inv_deuda;

    }
    return 0;
  }

/**
 * [setInventoryUser Nueva asignacion de inventario]
 * @param [type] $users_email         [description]
 * @param [type] $inv_arti_details_id [description]
 * @param string $obs                 [description]
 */
  public static function setInventoryUser($users_email, $inv_arti_details_id, $obs = "Asignado por cron de reciclaje")
  {
    $assigned                      = self::getConnect('W');
    $assigned->status              = 'A';
    $assigned->users_email         = $users_email;
    $assigned->inv_arti_details_id = $inv_arti_details_id;
    $assigned->date_reg            = date('Y-m-d H:i:s', time());
    $assigned->obs                 = $obs;
    $assigned->last_assigned_by    = $users_email;
    $assigned->last_assignment     = date('Y-m-d H:i:s', time());
    $assigned->save();
  }

  public static function getAssignByUserAndId($user, $id){
    return self::getConnect('R')
                ->select('date_reg', 'date_red', 'status')
                ->where([
                  ['users_email', $user],
                  ['inv_arti_details_id', $id]
                ])
                ->first();
  }
}
