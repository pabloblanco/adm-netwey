<?php

namespace App;

use App\Policy;
use App\Profile;
use App\ProfileDetail;
use App\SellerWare;
use App\UserRole;
use App\UserWarehouse;
use App\UserDeliveryAddress;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use Log;

class User extends Authenticatable
{
  use Notifiable;
  protected $table = 'islim_users';

  protected $fillable = [
    'name',
    'last_name',
    'id_org',
    'email',
    'parent_email',
    'password',
    'dni',
    'platform',
    'phone',
    'phone_job',
    'profession',
    'position',
    'address',
    'second_password',
    'status',
    'charger_com',
    'charger_balance',
    'residue_amount',
    'password_date',
    'url_latter_contract',
    'is_locked',
    'esquema_comercial_id',
    'reset_session',
    'telmovpay_id',
    'code_curp'    
  ];

  protected $hidden = [
    'password', 'date_reg', 'date_mod',
  ];

  protected $primaryKey = 'email';

  protected $keyType = 'string';

  public $incrementing = false;

  const CREATED_AT = 'date_reg';

  const UPDATED_AT = 'date_mod';

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\User
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new User;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getUserByEmail($email)
  {
    return self::getConnect('R')
      ->select('name', 'last_name', 'email', 'second_password', 'code_curp', 'status')
      ->where('email', $email)
      ->first();
  }

  public static function setStatusLockUser($email, $status)
  {
    return self::getConnect('W')
      ->where('email', $email)
      ->update(['is_locked' => $status]);
  }

  public static function getUserWH($email = false)
  {
    if ($email) {
      return self::getConnect('R')
        ->select(
          'islim_wh_users.warehouses_id',
          'islim_users.name',
          'islim_users.last_name',
          'islim_users.email'
        )
        ->join(
          'islim_wh_users',
          'islim_wh_users.users_email',
          'islim_users.email'
        )
        ->where('islim_wh_users.status', 'A')
        ->where(function ($query) use ($email) {
          $query->where('islim_users.parent_email', $email)
            ->orWhere('islim_users.email', $email);
        })
        ->get();
    }

    return [];
  }

  public static function getName_lastName($email = false)
  {
    if ($email) {
      return self::getConnect('R')
        ->select(
          'islim_users.name',
          'islim_users.last_name'
        )
        ->where('islim_users.email', $email)
        ->first();
    }

    return [];
  }

  public static function doLogin($email, $password)
  {
    $user = User::select('email', 'password', 'password_date')->where(['email' => $email, 'status' => 'A'])->get();
    foreach ($user as $u) {
      if (empty($u->password_date)) {
        $u->password_date = date('Y-m-d H:i:s');
        $u->save();
      }

      if (strtotime('+ ' . env('PASS_EXPIRE', 60) . ' days', strtotime($u->password_date)) <= time()) {
        return -1;
      }

      return Hash::check($password, $u->password);
    }
    return false;
  }

  public static function adminPermission($email, $password)
  {
    $user = User::select('password', 'platform')->where(['email' => $email, 'status' => 'A'])->get();
    if ($user->platform == 'admin') {
      return Hash::check($password, $user->password);
    }
    return false;
  }

  public static function getUsers()
  {
    $users = User::getConnect('R')->whereIn('status', ['A', 'I'])->get();
    foreach ($users as $user) {
      $userPolicies = UserRole::getConnect('R')->select('policies_id', 'roles_id', 'value')->where('user_email', $user->email)->where('status', 'A')->get();
      foreach ($userPolicies as $userPolicy) {
        $policies = Policy::getConnect('R')->select('code', 'type')->where('status', 'A')->where('id', $userPolicy->policies_id)->get();
        foreach ($policies as $policy) {
          $userPolicy->code = $policy->code;
          $userPolicy->type = $policy->type;
        }
      }
      $user->policies = $userPolicies;
      $warehouses     = UserWarehouse::getConnect('R')->where(['users_email' => $user->email, 'status' => 'A'])->count();
      $user->isSeller = $warehouses > 0 ? true : false;
      $user->wh       = $warehouses;
    }
    return $users;
  }

  public static function getsellerUsers()
  {
    $users = User::where('status', 'A');
    foreach ($users as $user) {
      $warehouses     = UserWarehouse::where('users_email', $user->email)->count();
      $user->isSeller = $warehouses > 0 ? true : false;
    }
    return $users;
  }

  public static function getUser($email, $activos = 'Y')
  {
    $users = User::getConnect('R')->where(['email' => $email])->get();
    foreach ($users as $user) {
      //Consultando códigos de depósito
      $user->depositCodes = UserDeposit::getUserCodes($email);

      $userPolicies = UserRole::getConnect('R')->select('policies_id', 'roles_id', 'value')->where('user_email', $user->email);
      if ($activos == 'Y') {
        $userPolicies = $userPolicies->where('status', 'A');
      }

      $userPolicies = $userPolicies->get();

      foreach ($userPolicies as $userPolicy) {
        $policies = Policy::getConnect('R')->select('code', 'type')->where('status', 'A')->where('id', $userPolicy->policies_id)->get();
        foreach ($policies as $policy) {
          $userPolicy->code = $policy->code;
          $userPolicy->type = $policy->type;
        }
      }

      $user->policies = $userPolicies;
      $warehouses     = UserWarehouse::getConnect('R')->where(['users_email' => $user->email, 'status' => 'A'])->count();
      $user->isSeller = $warehouses > 0 ? true : false;
      $user->wh       = $warehouses;
      $profile        = ProfileDetail::getConnect('R')->select('id_profile')->where('user_email', $email)->first();

      if ($profile) {
        $user->profile = Profile::getConnect('R')->where('id', $profile->id_profile)->first();
      }

      if (!empty($user->id_org)) {
        $user->whRetail = SellerWare::getConnect('R')->select('islim_warehouses.name', 'islim_warehouses.id')
          ->join(
            'islim_warehouses',
            'islim_warehouses.id',
            '=',
            'islim_seller_ware.id_ware'
          )
          ->where([
            ['islim_seller_ware.status', 'A'],
            ['islim_warehouses.status', 'A'],
            ['islim_seller_ware.email', $user->email],
          ])
          ->get();

        $user->whRetailOrg = OrgWarehouse::getConnect('R')->select('islim_warehouses.name', 'islim_warehouses.id')
          ->join('islim_warehouses', function ($join) {
            $join->on('islim_warehouses.id', '=', 'islim_wh_org.id_wh')
              ->where('status', 'A');
          })
          ->where([
            ['islim_wh_org.id_org', $user->id_org],
          ])
          ->whereNotIn('islim_warehouses.id', $user->whRetail->pluck('id'))
          ->get();
      }
      
      if (!empty($user->esquema_comercial_id)) {
        $typeID = DB::table('islim_esquema_comercial')
          ->select('type', 'name')
          ->where('id', $user->esquema_comercial_id)
          ->first();

        $user->type_esquema_comercial = $typeID->type;
        $user->name_esquema_comercial = $typeID->name;
      }

      //Consultando dirección para envío de inventario prova
      if($user->platform == 'coordinador'){
        $user->delivery = UserDeliveryAddress::getActiveAddress($user->email);
      }

      $user->distributor = DB::connection('netwey-r')
      ->table('islim_distributor_user')
      ->select(
        'islim_distributors.id as distributor_id',
        'islim_distributors.description as name'
      )
      ->join('islim_distributors', function ($join) {

          $join->on('islim_distributors.id', '=', 'islim_distributor_user.distributor_id')
            ->where('islim_distributors.status', 'A');

      })
      ->where('islim_distributor_user.user_email', $user->email)
      ->where('islim_distributor_user.status', 'A')->first();

      return $user;
    }
  }

  public static function getWarehouses($email)
  {
    $userWarehouses = UserWarehouse::where('users_email', $email)->get();
    return $userWarehouses;
  }

  public static function getUserPolicies($user)
  {
    $userPolicies = UserRole::select('policies_id', 'roles_id', 'value')->where('user_email', $user->email)->where('status', 'A')->get();
    foreach ($userPolicies as $userPolicy) {
      $policies = Policy::getConnect('R')->select('code')->where('status', 'A')->where('id', $userPolicy->policies_id)->get();
      foreach ($policies as $policy) {
        $userPolicy->code = $policy->code;
      }
    }
    return $user;
  }

  public static function getUserSellerStatus($user)
  {
    $warehouses     = UserWarehouse::select('users_email')->where('users_email', $user->email)->count();
    $user->isSeller = $warehouses >= 0 ? true : false;
    return $user;
  }

  /**
  GESTIÓN DE PERMISOS
   */

  public static function hasPermission($email, $code)
  {
    $response = false;
    $values   = UserRole::getConnect('R')->select('policies_id', 'roles_id')->where('user_email', $email)->where('status', 'A')->where('value', '>', 0)->get();
    foreach ($values as $value) {
      $policies = Policy::getConnect('R')->select('code')->where('status', 'A')->where('id', $value->policies_id)->where('roles_id', $value->roles_id)->get();
      foreach ($policies as $policy) {
        if ($policy->code == $code) {
          $response = true;
        }
      }
    }
    return $response;
  }

  /**
  paquete de permisos (codigos de politicas que comienzan con el mismo formato ejem (REP*))
   */
  public static function hasPermissionPack($email, $codesArray)
  {
    $response = false;
    $values   = UserRole::getConnect('R')->select('policies_id', 'roles_id')->where('user_email', $email)->where('status', 'A')->where('value', '>', 0)->get();
    foreach ($values as $value) {
      $policies = Policy::getConnect('R')
        ->select('code')
        ->where('status', 'A')
        ->where('id', $value->policies_id)
        ->where('roles_id', $value->roles_id)
        ->where(function ($query) use ($codesArray) {
          foreach ($codesArray as $key => $code) {
            if ($key == 0) {
              $query = $query->where('code', 'like', $code . '%');
            } else {
              $query = $query->orWhere('code', 'like', $code . '%');
            }
          }
        })
        ->count();

      if ($policies > 0) {
        $response = true;
      }
    }
    return $response;
  }

  /*
  Devuelve los codigosde politicas para visualizacion de items que tiene asignado el usuario logueado
  NOTA: Se debe tener en cuenta que las politicas a las politicas debe comenzar por $ini
   */
  public static function hasAnyReport($email = false, $ini = false)
  {
    if ($email && $ini) {
      $hasReport = Policy::getConnect('R')
        ->select('islim_policies.code')
        ->join('islim_user_roles', function ($join) use ($email) {
          $join->on('islim_user_roles.policies_id', '=', 'islim_policies.id')
            ->where([
              ['islim_user_roles.status', 'A'],
              ['islim_user_roles.user_email', $email],
              ['islim_user_roles.value', '>', '0'],
            ]);
        })
        ->where('islim_policies.code', 'like', $ini . '%')
        ->where('islim_policies.status', 'A')
        ->get();

      return $hasReport;
    }
    return 0;
  }

  public static function getReport($emails, $names, $status, $types, $org = null)
  {
    $queryParent = '(CASE WHEN (islim_users.parent_email IS NOT NULL) THEN (SELECT CONCAT( (CASE b.name WHEN NULL THEN "" ELSE b.name END) , " ", (CASE b.last_name WHEN NULL THEN "" ELSE b.last_name END)) FROM islim_users AS b WHERE b.email = islim_users.parent_email) ELSE "N/A" END) AS parent';

    $queryType = '(CASE islim_users.platform ' .
      'WHEN "admin" THEN "Administrador" ' .
      'WHEN "coordinador" THEN "Coordinador" ' .
      'WHEN "vendor" THEN "Vendedor" ' .
      'WHEN "promotor" THEN "Promotor" ' .
      'ELSE "N/A" END) AS type';

    $users = User::getConnect('R')
      ->distinct()
      ->select(
        'islim_users.email',
        'islim_users.name',
        'islim_users.parent_email',
        'islim_users.last_name',
        'islim_users.platform',
        'islim_users.phone',
        'islim_users.phone_job',
        'islim_users.address',
        'islim_users.status',
        DB::raw($queryType),
        DB::raw($queryParent)
      );

    if (session('user')->profile->type != "master") {
      $users = $users->where('id_org', session('user')->id_org);
    }

    if (session('user')->profile->type == "master" && !empty($org)) {
      $users = $users->where('id_org', $org);
    }

    if (isset($types) && !empty($types) && (count($types) > 0)) {
      $users = $users->whereIn('platform', $types);
    }

    if (isset($status) && !empty($status) && (count($status) > 0)) {
      $users = $users->whereIn('status', $status);
    }

    if (isset($emails) && !empty($emails) && (count($emails) > 0)) {
      $users = $users->whereIn('email', $emails);
    }

    if (isset($names) && !empty($names) && (count($names) > 0)) {
      $users = $users->orWhere(function ($query) use ($names) {
        $query->whereIn('name', $names)->orWhere(function ($q) use ($names) {
          $q->whereIn('last_name', $names);
        });
      });
    }

    //echo $users->toSql(); exit();

    return $users->get();
  }

  public static function getReportStructOrg($org)
  {
    $data = new Collection;

    $users = User::getConnect('R')->select(
      DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as nombre'),
      'islim_users.email',
      'islim_users.id_org',
      'islim_users.parent_email',
      'islim_users.phone',
      'islim_users.position',
      'islim_profiles.name',
      'islim_profiles.hierarchy',
      'islim_dts_organizations.business_name'
    )
      ->join('islim_profile_details', function ($join) {
        $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
          ->where('islim_profile_details.status', 'A');
      })
      ->join('islim_profiles', function ($join) {
        $join->on('islim_profiles.id', '=', 'islim_profile_details.id_profile')
          ->where('islim_profiles.type', 'commercial');
      })
      ->join('islim_dts_organizations', 'islim_dts_organizations.id', '=', 'islim_users.id_org')
      ->whereIn('islim_users.id_org', $org)
      ->orderBy('islim_users.id_org', 'ASC')
      ->orderBy('islim_profiles.hierarchy', 'ASC')
      ->get();

    $count = 0;
    //Bandera usada para reiniciar contador del usuario gerente o de mas rango en la estructura jerarquica
    $orgb = null;
    foreach ($users as $user) {
      if (count($data->where('email', $user->email)) == 0) {
        if (empty($orgb)) {
          $orgb = $user->id_org;
        }

        if ($user->id_org == $orgb) {
          $count++;
        } else {
          $count = 1;
          $orgb  = $user->id_org;
        }
        $data->push(self::getDataForOrg($user, $count, 1));
        $data = self::getChildOrg($data, $user->email, $users, 1);
      }
    }
    return $data;
  }

  //Funcion recursiva para obtener usuario en formato de estructura jerarquica
  private static function getChildOrg($collection = null, $email = null, $data = null, $level = 1)
  {
    if (!empty($collection) && !empty($email) && !empty($data)) {
      //Filtra los usuario relacionados con el email dado
      $users = $data->where('parent_email', $email);
      //Se aumenta el nivel de la jerarquia por cada llamado a la funcion esto se usa para poder armar string
      //de posicion jerarquica
      $level = $level + 1;
      foreach ($users as $user) {
        //Condicion para romper recursividad
        if ($user->email == $email) {
          break;
        }

        $collection->push(self::getDataForOrg($user, self::getPos($collection, $level, $user->business_name), $level));
        $collection = self::getChildOrg($collection, $user->email, $data, $level);
      }
    }
    return $collection;
  }

  //Devuelve la posicion del usuario en la jerarquia concatenado
  private static function getPos($collection, $level, $orgn)
  {
    $str = '';
    for ($i = 1; $i <= $level; $i++) {
      //para contar los usuario que pertenencen al nivel $i
      $tempc = $collection->where('level', $i);
      //para que solo cuente los usuarios de la misma org
      $tempc = $tempc->where('org', $orgn);
      $l     = $tempc->count();
      if ($level == $i) {
        $l++;
        $str .= $l == 0 ? '1' : $l;
      } else {
        $str .= $l . '.';
      }
    }
    return $str;
  }

  //Devuelve array con la data necesaria para reporte de estructura organizativa
  private static function getDataForOrg($user = null, $pos = 0, $level = 0)
  {
    return [
      'pos'       => $pos,
      'org'       => $user->business_name,
      'name'      => $user->nombre,
      'phone'     => $user->phone,
      'position'  => $user->position,
      'profile'   => $user->name,
      'hierarchy' => $user->hierarchy,
      'email'     => $user->email,
      'parent'    => $user->parent_email,
      'level'     => $level,
    ];
  }

  //Devuelve listado de usuarios con los filtros dados
  public static function getUserFilter($filters = [])
  {
    $users = User::select(
      'islim_users.name',
      'islim_users.last_name',
      'islim_users.email',
      'islim_users.platform',
      'islim_users.dni',
      'islim_users.phone',
      'islim_users.phone_job',
      'islim_users.profession',
      'islim_users.position',
      'islim_users.address',
      'islim_profiles.type',
      'islim_profiles.name as profile_name',
      'islim_dts_organizations.business_name',
      'islim_distributors.description as distributor'
    )
    ->whereNotIn('islim_users.status', ['I', 'T'])
    ->leftJoin('islim_dts_organizations', 'islim_dts_organizations.id', 'islim_users.id_org')
    ->leftJoin('islim_distributor_user', function ($join) {
      $join->on('islim_distributor_user.user_email', '=', 'islim_users.email')
        ->where('islim_distributor_user.status', 'A');
    })
    ->leftJoin('islim_distributors', function ($join) {
      $join->on('islim_distributors.id', '=', 'islim_distributor_user.distributor_id')
        ->where('islim_distributors.status', 'A');
    })
    ->leftJoin('islim_profile_details', function ($join) {
      $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
        ->where('islim_profile_details.status', 'A');
    })
    ->leftJoin('islim_profiles', function ($join) {
      $join->on('islim_profiles.id', '=', 'islim_profile_details.id_profile')
        ->where('islim_profiles.status', 'A');
    });

    if (is_array($filters) && count($filters) > 0) {
      if (!empty($filters['org'])) {
        $users->where('islim_users.id_org', $filters['org']);
      }
      if (!empty($filters['coord'])) {
        $users->where('islim_users.parent_email', $filters['coord']);
      }
      if (!empty($filters['status'])) {
        $users->where('islim_users.status', $filters['status']);
      }
      if (!empty($filters['profile'])) {
        $users->where('islim_profiles.id', $filters['profile']);
      }
      if (!empty($filters['userType'])) {
        $users->where('islim_users.platform', $filters['userType']);
      }
      if (!empty($filters['distributor'])) {
        $users->where('islim_distributor_user.distributor_id', $filters['distributor']);
      }

      if (!empty($filters['search'])) {
        $words = explode("*", $filters['search']);
        $words = implode("%", $words);
        $users->where(function ($query) use ($words) {
          $query->whereRaw("CONCAT(islim_users.name,' ',islim_users.last_name) like ?", [$words . '%'])
            ->orWhere('islim_users.email', 'like', $words . '%');
        });
      }
    }
    return $users;
  }

  //retorna usuarios con supervisore para una o varias organizaciones y para uno o varios perfiles
  public static function getSupervisorsByOrgsAndProf($orgs, $profs)
  {
    //$orgs y $profs son un array
    if (!empty($orgs) || !empty($profs)) {
      $prof_id = session('user.profile.id');

      $users_parents = User::getConnect('R')
        ->select('islim_users.parent_email')
        ->join('islim_profile_details', function ($join) use ($profs) {
          $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
            ->where('islim_profile_details.status', 'A')
            ->whereIn('islim_profile_details.id_profile', $profs);
        })
        ->where(function ($query) use ($orgs, $prof_id) {
          $query->whereIn('id_org', $orgs);
          if (count($orgs) > 1 && ($prof_id == '1' || $prof_id == '2')) {
            $query = $query->orWhereNull('id_org');
          }
        })
        ->where('islim_users.status', 'A')
        ->whereNotNull('islim_users.parent_email')
        ->distinct();

      $users_parents = $users_parents->get();

      if ($users_parents) {
        $userssup = User::getConnect('R')
          ->where(function ($query) use ($orgs, $prof_id) {
            $query->whereIn('id_org', $orgs);
            if (count($orgs) > 1 && ($prof_id == '1' || $prof_id == '2')) {
              $query = $query->orWhereNull('id_org');
            }
          })
          ->where('islim_users.status', 'A')
          ->whereIn('islim_users.email', $users_parents->pluck('parent_email'))
          ->orderBy('name', 'ASC');
        $userssup = $userssup->get();

        return $userssup;
      }
    }
    return null;
  }

  //retorna usuarios eliminados que no han sido reemplazados para una o varias organizaciones y para uno o varios perfiles
  public static function getReplacementsByOrgsAndProf($orgs, $profs)
  {
    //$orgs y $profs son un array
    if (!empty($orgs) || !empty($profs)) {
      $prof_id = session('user.profile.id');

      $users_replacement = User::getConnect('R')
        ->select('islim_users.*')
        ->join('islim_profile_details', function ($join) use ($profs) {
          $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
            ->whereIn('islim_profile_details.id_profile', $profs);
        })
        ->where(function ($query) use ($orgs, $prof_id) {
          $query->whereIn('id_org', $orgs);
          if (count($orgs) > 1 && ($prof_id == '1' || $prof_id == '2')) {
            $query = $query->orWhereNull('id_org');
          }
        })
        ->where('islim_users.status', 'T')
        ->whereNull('islim_users.replaced');

      $users_replacement = $users_replacement->get();

      return $users_replacement;
    }
    return null;
  }

  //retorna usuarios que pueden ser supervisores de otros segun el profile y la organizacion a la que pertenece
  public static function getSupervisorsProfiles($org, $prof)
  {
    if (!empty($org) || !empty($prof)) {

      $profile        = Profile::getConnect('R')->find($prof);
      $profile_parent = Profile::getConnect('R')
        ->where([
          ['status', 'A'],
          ['hierarchy_branch', $profile->hierarchy_branch],
        ])
        ->where('hierarchy', '<', $profile->hierarchy);
      $profile_parent->get();

      if ($profile_parent) {
        $userssup = User::getConnect('R')
          ->select('islim_users.*', 'islim_distributors.description as distributor_name')
          ->leftJoin('islim_distributor_user', function ($join) {
          $join->on('islim_distributor_user.user_email', '=', 'islim_users.email')
              ->where('islim_distributor_user.status', 'A');
          })
          ->leftJoin('islim_distributors', function ($join) {
            $join->on('islim_distributors.id', '=', 'islim_distributor_user.distributor_id')
              ->where('islim_distributors.status', 'A');
          })
          ->join('islim_profile_details', function ($join) use ($profile_parent) {
            $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
              ->where('islim_profile_details.status', 'A')
              ->whereIn('islim_profile_details.id_profile', $profile_parent->pluck('id'));
          })
          ->where('id_org', $org)
          ->where('islim_users.status', 'A');

        $userssup = $userssup->get();
        return $userssup;
      }
    }
    return null;
  }

  //retorna usuarios eliminados de los que se puede tomar el puesto segun el profile y la organizacion a la que pertenecia
  public static function getReplacementsProfiles($prof, $org = null)
  {
    if (!empty($prof)) {
      $prof_id = session('user.profile.id');
      if ((($prof_id == '1' || $prof_id == '2') && empty($org)) || !empty($org)) {

        $replacements = User::getConnect('R')
          ->select('islim_users.*')
          ->join('islim_profile_details', function ($join) use ($prof) {
            $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
              ->where('islim_profile_details.id_profile', $prof);
          })
          ->where(function ($query) use ($org, $prof_id) {
            if (!empty($org)) {
              $query->where('id_org', $org);
            }
          })
          ->where('islim_users.status', 'T')
          ->whereNull('islim_users.replaced');

        //  $query = vsprintf(str_replace('?', '%s', $replacements->toSql()), collect($replacements->getBindings())->map(function ($binding) {
        //     return is_numeric($binding) ? $binding : "'{$binding}'";
        // })->toArray());

        // Log::info($query);

        return $replacements->get();
      }
    }
    return null;
  }

  /**
   * Metodo que retorna lista de los usuarios asociados a un usuario dado y a su vez los
   * usuarios asociados a estos (Realiza una busqueda recursiva)
   * @param String $email
   * @param Illuminate\Support\Collection $userList (Este parametro no se debe enviar en el llamado al método)
   *
   * @return App\Models\User
   */
  public static function getParents($email, &$userList = false)
  {
    if ($userList === false) {
      $userList = collect();
    }

    $users = self::getConnect('R')
      ->select(
        'islim_users.email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.platform',
        'islim_profiles.name as name_profile',
        'islim_profiles.hierarchy',
        'islim_profiles.type'
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->join(
        'islim_profiles',
        'islim_profiles.id',
        'islim_profile_details.id_profile'
      )
      ->where([
        ['islim_users.parent_email', $email],
        ['islim_users.status', 'A'],
        ['islim_profile_details.status', 'A'],
      ])
      ->get();

    if (!$users->count()) {
      return $users;
    }

    foreach ($users as $user) {
      $userList->push($user);
      self::getParents($user->email, $userList);
    }

    return $userList;
  }

  public static function verify_pass($email, $password)
  {

    $user = self::getConnect('R')
      ->select(
        'islim_users.email',
        'islim_users.password'
      )
      ->where([
        ['islim_users.email', $email],
        ['islim_users.status', 'A']
      ])
      ->first();

    //  Log::info($user);
    if (!empty($user)) {
      return Hash::check($password, $user->password);
    }
    return false;
  }

  public static function getAllDebt($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.email',
        'islim_user_deposit_id.id_deposit',
        'islim_users.residue_amount',
        'islim_user_deposit_id.id',
        'islim_asigned_sales.date_reg',
        'islim_users.is_locked',
        'islim_users.status'
      )
      ->join('islim_asigned_sales', 'islim_asigned_sales.parent_email', 'islim_users.email')
      ->join('islim_user_deposit_id', 'islim_user_deposit_id.email', 'islim_users.email')
      ->where([
        ['islim_asigned_sales.status', 'P'],
        ['islim_user_deposit_id.status', 'A'],
      ]);

    if (count($filters)) {
      if (!empty($filters['parent'])) {
        $data->where('islim_users.parent_email', $filters['parent']);
      }

      if (!empty($filters['user_email'])) {
        $data->where('islim_users.email', $filters['user_email']);
      }

      if (!empty($filters['status'])) {
        if (is_array($filters['status'])) {
          $data->whereIn('islim_users.status', $filters['status']);
        } else {
          $data->where('islim_users.status', $filters['status']);
        }
      }

      if (!empty($filters['orgs'])) {
        $data->whereIn('islim_users.id_org', $filters['orgs']);
      }
    }

    return $data->groupBy('islim_asigned_sales.parent_email')->get();
  }

  public static function getTotalDebt($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_users.email',
        DB::raw('SUM(islim_asigned_sales.amount) as debt')
      )
      ->join(
        'islim_asigned_sales',
        'islim_asigned_sales.parent_email',
        'islim_users.email'
      )
      ->where('islim_asigned_sales.status', 'P');

    if (count($filters)) {
      if (!empty($filters['parent'])) {
        $data = $data->where('islim_users.parent_email', $filters['parent']);
      }

      if (!empty($filters['user_email'])) {
        $data = $data->where('islim_users.email', $filters['user_email']);
      }

      if (!empty($filters['status'])) {
        if (is_array($filters['status'])) {
          $data = $data->whereIn('islim_users.status', $filters['status']);
        } else {
          $data = $data->where('islim_users.status', $filters['status']);
        }
      }

      if (!empty($filters['orgs'])) {
        $data = $data->whereIn('islim_users.id_org', $filters['orgs']);
      }

      if (!empty($filters['date_end'])) {
        $data = $data->where('islim_asigned_sales.date_reg', '<', $filters['date_end']);
      }

      if (!empty($filters['date_begin'])) {
        $data = $data->where('islim_asigned_sales.date_reg', '>=', $filters['date_begin']);
      }
    }

    $data = $data->groupBy('islim_asigned_sales.parent_email');

     // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
     //        return is_numeric($binding) ? $binding : "'{$binding}'";
     //    })->toArray());

     //    Log::info($query);

    return $data->get();
  }

  public static function getCoordUsers()
  {
    return self::getConnect('R')
      ->select(
        'islim_users.email',
        'islim_users.name',
        'islim_users.last_name',
        'islim_users.phone',
        'islim_esquema_comercial.name as esquema'
      )
      ->leftJoin(
        'islim_esquema_comercial',
        'islim_esquema_comercial.id',
        'islim_users.esquema_comercial_id'
      )
      ->join(
        'islim_profile_details',
        'islim_profile_details.user_email',
        'islim_users.email'
      )
      ->whereIn('islim_profile_details.id_profile', [10, 18])
      ->where([
        ['islim_users.status', 'A'],
        ['islim_users.platform', 'coordinador'],
        ['islim_profile_details.status', 'A'],
      ])
      ->get();
  }

  public static function getParentUsers($email,$status = ['A'])
  {
    return self::getConnect('R')
      ->select('email')
      ->where([
        ['parent_email', $email],
      ])
      ->whereIn('status',$status)
      ->get();
  }

  /**
   * [setStatusLowAcept Se actualiza el registro del usurios cuando se acepta la solicitud de baja y este quede en status de proceso en baja]
   * @param [type] $email [email del usuario a dar de baja]
   */
  public static function setStatusLowAcept($email, $status)
  {
    return self::getConnect('W')
      ->where('email', $email)
      ->update([
        'status'        => $status,
        'reset_session' => 'Y'
      ]);
  }

  /**
   * [setUpdateParentUsers Actualiza el usuario superior de aquel usuario que se esta dando de baja por el superior de este]
   * @param [type] $emailLow [description]
   * @param [type] $emailUp  [description]
   */
  public static function setUpdateParentUsers($emailLow, $emailUp)
  {
    return self::getConnect('W')
      ->where('parent_email', $emailLow)
      ->update(['parent_email' => $emailUp]);
  }

  /**
   * [getCoordinator retorna el coordinador de un usuario vendedor, el mismo usuario si es coordinador o null si el usuario no es ni vendedor ni coordinador]
   * @param [type] $email [description]
   */
  public static function getCoordinator($email)
  {
    $user = self::getConnect('R')
      ->select('islim_users.email', 'islim_users.parent_email', 'islim_profiles.platform')
      ->join('islim_profile_details', 'islim_profile_details.user_email', 'islim_users.email')
      ->join('islim_profiles', 'islim_profiles.id', 'islim_profile_details.id_profile')
      ->where([
        ['islim_profile_details.status', 'A'],
        ['islim_profiles.status', 'A'],
        ['islim_users.email', $email],
      ])
      ->first();

    if ($user) {
      if ($user->platform == 'vendor') {
        return $user->parent_email;
      }
      if ($user->platform == 'coordinador') {
        return $user->email;
      }
    }
    return null;
  }
}