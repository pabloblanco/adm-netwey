<?php

namespace App\Http\Controllers;

use App\Bank;
use App\EsquemaComercial;
use App\Helpers\CommonHelpers;
use App\Helpers\ValidateString;
use App\Helpers\APIProva;
use App\Helpers\TelmovPay;
use App\Organization;
use App\OrgWarehouse;
use App\ParentDeleteUser;
use App\Policy;
use App\PolicyProfile;
use App\Profile;
use App\ProfileDetail;
use App\Role;
use App\SellerInventory;
use App\SellerInventoryTrack;
use App\SellerWare;
use App\User;
use App\UserDeposit;
use App\UserRole;
use App\UserDeliveryAddress;
use App\UserWarehouse;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
  public function index()
  {
    $users = User::all();
    return response()->json($users);
  }

  public function downloadCSVUsers(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->input('filter');

      $fileName = 'usuarios_' . date('Ymd');
      $headers  = array(
        "Content-type"        => "text/csv",
        "Content-Disposition" => "attachment; filename=" . $fileName . ".csv",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0",
        "filename"            => $fileName . ".csv");

      $users = User::getUserFilter($filters)->get();

      $columns = array('Usuario', 'Tipo de usuario', 'I.N.E', 'Teléfono', 'Teléfono oficina', 'Organización', 'Profeción', 'Cargo', 'Dirección', 'Distribuidor');

      $callback = function () use ($users, $columns) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $columns);

        foreach ($users as $user) {
          if ($user->platform == 'admin') {
            $user->platform = "Administrador";
          }

          if ($user->platform == 'vendor') {
            $user->platform = "Vendedor";
          }

          if ($user->platform == 'coordinador') {
            $user->platform = "Coordinador";
          }

          if ($user->platform == 'call') {
            $user->platform = "Call center";
          }

          if ($user->platform == 'promotor') {
            $user->platform = "Promotor";
          }

          if (empty($user->phone)) {
            $user->phone = 'N/A';
          }

          if (empty($user->phone_job)) {
            $user->phone_job = 'N/A';
          }

          if (empty($user->business_name)) {
            $user->business_name = 'N/A';
          }

          if (empty($user->profession)) {
            $user->profession = 'N/A';
          }

          if (empty($user->position)) {
            $user->position = 'N/A';
          }

          if (empty($user->address)) {
            $user->address = 'N/A';
          }

          if (empty($user->distributor)) {
            $user->distributor = 'N/A';
          }

          fputcsv($file, array($user->name . ' ' . $user->last_name, $user->platform, $user->dni, $user->phone, $user->phone_job, $user->business_name, $user->profession, $user->position, $user->address, $user->distributor));
        }
        fclose($file);
      };
      return response()->stream($callback, 200, $headers);
    }
  }

  public function getFilterUsers(Request $request)
  {
    if ($request->isMethod('post')) {
      $org = $request->input('org');

      $supervisors = User::select('email', 'name', 'last_name')
        ->where('platform', 'coordinador')
        ->whereIn('status', ['A']);

      if (!empty($org)) {
        $supervisors->where('id_org', $org);
      }

      return response()->json(array('cs' => $supervisors->get()));
    }
  }

  public function getProfilesByPlatform(Request $request)
  {
    if ($request->isMethod('post')) {
      $platform = $request->input('platform');

      //return response()->json(array('profiles' => $platform));

      // if(!empty($platform)){ // todas
      $profiles = Profile::getProfilesByPlatform($platform);

      return response()->json(array('profiles' => $profiles));
      // }

    }
  }

  public function getFilterProfiles(Request $request)
  {
    if ($request->isMethod('post')) {
      $org = $request->input('org');
      if (empty($org)) {
        // todas
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $orgs = $orgs->pluck('id');
      } else {
        $orgs = array($org); //una sola organizacion
      }
      $profiles = Profile::getProfileByOrgs($orgs);

      return response()->json(array('cs' => $profiles));
    }
  }

  public function getFilterSupervisors(Request $request)
  {
    if ($request->isMethod('post')) {
      $org = $request->input('org');
      $pro = $request->input('pro');
      if (empty($org)) {
        // todas
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $orgs = $orgs->pluck('id');
      } else {
        $orgs = array($org); //una sola organizacion
      }

      if (empty($pro)) {
        // todas
        $profs = Profile::getProfileByOrgs($orgs);
        $profs = $profs->pluck('id');
      } else {
        $profs = array($pro); //una sola organizacion
      }

      $supervisors = User::getSupervisorsByOrgsAndProf($orgs, $profs);

      return response()->json(array('cs' => $supervisors));
    }
  }

  public function getSupervisors(Request $request)
  {
    if ($request->isMethod('post')) {
      $org = $request->input('org');
      $pro = $request->input('pro');

      $supervisors = User::getSupervisorsProfiles($org, $pro);

      return response()->json(array('cs' => $supervisors));
    }
  }

  public function getReplacements(Request $request)
  {
    if ($request->isMethod('post')) {
      $org = $request->input('org');
      $pro = $request->input('pro');

      if ($org == "") {
        $replacements = User::getReplacementsProfiles($pro);
      } else {
        $replacements = User::getReplacementsProfiles($pro, $org);
      }

      return response()->json(array('cs' => $replacements));
    }
  }

  public function getusersdt(Request $request)
  {
    $filters = $request->input('filter');

    if (!empty($request->search)) {
      $filters['search'] = $request->search['value'];
    }

    $users = User::getUserFilter($filters);

    return DataTables::eloquent($users)
      ->editColumn('name', '{{$name}} {{$last_name}}')
      ->editColumn('platform', function (User $user) {
        switch ($user->platform) {
          case 'admin':
            return "Administrador";
          case 'vendor':
            return "Vendedor";
          case 'coordinador':
            return "Coordinador";
          case 'call':
            return "Call center";
          case 'promotor':
            return "Promotor";
        }
      })
      ->editColumn('phone', function (User $user) {
        if (!empty($user->phone)) {
          return $user->phone;
        } else {
          return 'N/A';
        }
      })
      ->editColumn('phone_job', function (User $user) {
        if (!empty($user->phone_job)) {
          return $user->phone_job;
        } else {
          return 'N/A';
        }
      })
      ->editColumn('id_org', function ($user) {
        if (!empty($user->business_name)) {
          return $user->business_name;
        } elseif ($user->type == 'master') {
          return 'MASTER';
        }

        return 'N/A';
      })
      ->editColumn('profession', function (User $user) {
        if (!empty($user->profession)) {
          return $user->profession;
        } else {
          return 'N/A';
        }
      })
      ->editColumn('position', function (User $user) {
        if (!empty($user->position)) {
          return $user->position;
        } else {
          return 'N/A';
        }
      })
      ->editColumn('address', function (User $user) {
        if (!empty($user->address)) {
          return $user->address;
        } else {
          return 'N/A';
        }
      })
      ->addColumn('edit', function (User $user) {
        if (User::hasPermission(session('user.email'), 'AMU-UUS')) {
          if (session('user')->profile->name == "Super Usuario") {
            return true;
          } elseif ($user->profile_name != "Super Usuario") {
            return true;
          }
        }
        return false;
      })
      ->addColumn('chpass', function (User $user) {
        if (User::hasPermission(session('user.email'), 'AMU-CPU')) {
          if (session('user')->profile->name == "Super Usuario") {
            return true;
          } elseif ($user->profile_name != "Super Usuario") {
            return true;
          }
        }

        return false;
      })
      ->addColumn('delete', function (User $user) {
        if (User::hasPermission(session('user.email'), 'AMU-DUS') && $user->email != session('user.email')) {
          if (session('user')->profile->name == "Super Usuario") {
            return true;
          } elseif ($user->profile_name != "Super Usuario") {
            return true;
          }
        }

        return false;
      })->make(true);
  }

  private function setReplacement($replacement, $email)
  {
    $sub_query = ParentDeleteUser::getConnect('W')
      ->where([
        ['parent_email', $replacement],
        ['status', 'A'],
      ]);
    $subordinates = $sub_query->get();

    if ($subordinates) {
      User::getConnect('W')
        ->whereIn('email', $subordinates->pluck('email')->toArray())
        ->update(['parent_email' => $email, 'date_mod' => date('Y-m-d H:i:s')]);

      $sub_query->update(['status' => 'I', 'date_modified' => date('Y-m-d H:i:s')]);

      User::getConnect('W')
        ->where('email', $replacement)
        ->update(['replaced' => $email, 'date_mod' => date('Y-m-d H:i:s')]);
    }

    $parentdels = ParentDeleteUser::getConnect('W')
      ->where([
        ['email', $replacement],
        ['status', 'A'],
      ])->get();

    if ($parentdels) {
      foreach ($parentdels as $key => $parentdel) {
        $npd               = ParentDeleteUser::getConnect('W');
        $npd->email        = $email;
        $npd->parent_email = $parentdel->parent_email;
        $npd->status       = 'A';
        $npd->save();
      }
    }
  }

  public function store(Request $request)
  {
    if (User::hasPermission(session('user.email'), 'AMU-APA')) {
      $msg = 'El usuario se ha creado con exito';
      $test = User::getConnect('R')->find($request->email);
      $replacement = false;

      if (empty($test)) {
        $user        = User::getConnect('W');
        $user->email = $request->email;

        if (session('user.platform') != 'admin') {
          $user->parent_email = session('user.email');
        } else {
          $user->parent_email = $request->parent_email;
        }

        $user->dni           = $request->dni;
        $user->name          = strtoupper(ValidateString::normaliza($request->name));
        $user->last_name     = strtoupper(ValidateString::normaliza($request->last_name));
        $user->password      = Hash::make($request->password);
        $user->platform      = $request->platform;
        $user->phone         = str_replace(" ", "", $request->phone);
        $user->phone_job     = str_replace(" ", "", $request->phone_job);
        $user->charger_com   = $request->commission;
        $user->profession    = $request->profession;
        $user->position      = $request->position;
        $user->password_date = date('Y-m-d H:i:s');
        //$user->code_curp     = strtoupper($request->curp); Comentado debido a Telmovpay

        if (!empty($request->organization)) {
          if (Profile::getConnect('R')->where('id', $request->profile)->get()->first()->type != 'master') {
            $user->id_org = $request->organization;
          }
        }

        $user->address = $request->address;
        $user->status  = $request->status;

        //Si viene la segunda clave
        if ($request->secondPass) {
          $user->second_password = Hash::make($request->secondPass);
        }

        //start Id de islim_esquema_comercial
        if (!empty($request->coordinacion_select)) {
          //coordinacion
          $user->esquema_comercial_id = $request->coordinacion_select;
        } else {
          if (!empty($request->region_select)) {
            //region
            $user->esquema_comercial_id = $request->region_select;
          } else {
            if (!empty($request->division_select)) {
              $user->esquema_comercial_id = $request->division_select;
            }
          }
        }
        //end Id de islim_esquema_comercial

        $user->save();

        //Creando códigos de depósito
        //creando el del BBVA
        if ($request->has('codBV') && $request->has('bbvaid') && !empty($request->codBV)) {
          if (!UserDeposit::checkCodeAndBank(strtoupper($request->codBV), $request->bbvaid)) {
            UserDeposit::setInactiveCodes($user->email, [$request->bbvaid]);
            UserDeposit::createCode($user->email, strtoupper($request->codBV), $request->bbvaid);
          }
        }

        //creando el del banco Azteca
        if ($request->has('bankAccount') && $request->has('codBA') && !empty($request->codBA)) {
          if (!UserDeposit::checkCodeAndBank($request->codBA, $request->bankAccount)) {
            $group = Bank::getBankByGruop('AZ');
            UserDeposit::setInactiveCodes($user->email, $group->pluck('id')->toArray());
            UserDeposit::createCode($user->email, $request->codBA, $request->bankAccount);
          }
        }

        //Permisos a las bodegas que pueden acceder los vendedores retail
        if (!empty($request->organization)) {
          $org = Organization::getConnect('R')->select('type')->where('id', $request->organization)->first();

          if ($request->platform == 'vendor' && !empty($org) && $org->type == 'R' && !empty($request->ware_org) && count($request->ware_org)) {

            foreach ($request->ware_org as $value) {
              $rel           = SellerWare::getConnect('W');
              $rel->id_ware  = $value;
              $rel->email    = $user->email;
              $rel->status   = 'A';
              $rel->date_reg = date('Y-m-d H:i:s');
              $rel->save();
            }
          }
        }

        $profile             = ProfileDetail::getConnect('W');
        $profile->id_profile = $request->profile;
        $profile->user_email = $user->email;
        $profile->status     = $user->status;
        $profile->save();

        $policies = Policy::getConnect('R')->where('status', 'A')->get();

        foreach ($policies as $policy) {
          $policyID = 'policy_id_' . $policy->id;
          $roleID   = 'role_id_' . $policy->roles_id;
          $valueID  = 'value_' . $policy->roles_id . $policy->id;

          if (!empty($request->$policyID) && !empty($request->$roleID)) {
            $userRole              = UserRole::getConnect('W');
            $userRole->user_email  = $request->email;
            $userRole->date_reg    = date('Y-m-d H:i:s', time());
            $userRole->status      = 'A';
            $userRole->policies_id = $request->$policyID;
            $userRole->roles_id    = $request->$roleID;
            $userRole->value       = 0;
            if ($policy->id == $request->$policyID && $policy->roles_id == $request->$roleID) {
              if ($request->has($valueID) && ($request->$valueID != 0)) {
                $userRole->value = $request->$valueID;
              }
            }
            $userRole->save();
          }
        }

        if ($request->has('replacement')) {
          self::setReplacement($request->replacement, $user->email);
          $replacement = true;
        }

        //Guardando dirección para envío de inventario en caso de que sea un coordinador
        if($request->platform == 'coordinador'){
          $addressDel = UserDeliveryAddress::getConnect('W');
          $addressDel->email = $request->email;
          $addressDel->street = $request->street;
          $addressDel->colony = $request->colony;
          $addressDel->municipality = $request->municipality;
          $addressDel->state = $request->state;
          $addressDel->postal_code = $request->pc;
          $addressDel->ext_number = $request->ext_number;
          $addressDel->int_number = $request->int_number;
          $addressDel->reference = $request->reference;
          $addressDel->user_reg = session('user.email');
          $addressDel->date_reg = date('Y-m-d H:i:s');
          $addressDel->status = 'A';
          $addressDel->save();

          $res = APIProva::createUser([
            'nombre' => $request->name.' '.$request->last_name,
            'email' => $request->email,
            'referencia' => $request->reference,
            'direccion' => [
              'calle' => $request->street,
              'colonia' => $request->colony,
              'municipio' => $request->municipality,
              'estado' => $request->state,
              'code_postal' => $request->pc,
              'num_ext' => $request->ext_number,
              'num_int' => $request->int_number,
              'telefono' => str_replace(" ", "", $request->phone)
            ]
          ]);

          if(!$res['success']){
            $msg .= ', pero fallo sincronización con prova';
          }
        }

        //Asignación de Distribuidor

        $profile_inherit = Profile::getConnect('R')->find($request->profile);

        if($request->organization == 1 && $profile_inherit->hierarchy_branch == 'P'){

            if($profile_inherit->hierarchy > 4){

              $new_id = null;

              if (!empty($request->parent_email)) {
              
                $distributor_user_parent = DB::connection('netwey-r')->table('islim_distributor_user')->where('user_email', $request->parent_email)->where('status', 'A')->first();

                $new_id = !empty($distributor_user_parent) ? $distributor_user_parent->distributor_id : null;

                if($new_id != null){
                  
                  DB::connection('netwey-w')
                        ->table('islim_distributor_user')
                        ->insert([
                          'user_email' => $request->email,
                          'distributor_id' => $new_id,
                          'status' => 'A',
                          'date_reg' => date('Y-m-d H:i:s'),
                          'user_email_allocator' => session('user.email')]);
                }
              }

              if($replacement){

                $this->inherit_distriburor($request->email, $new_id);

              }

            }elseif ($profile_inherit->hierarchy == 4) {

              $new_id = !empty($request->distributor_select) ? $request->distributor_select : null;  

              if($new_id != null){

                  DB::connection('netwey-w')
                      ->table('islim_distributor_user')
                      ->insert([
                        'user_email' => $request->email,
                        'distributor_id' => $new_id,
                        'status' => 'A',
                        'date_reg' => date('Y-m-d H:i:s'),
                        'user_email_allocator' => session('user.email')]);
              }

              if($replacement){

                $this->inherit_distriburor($request->email, $new_id);

              }
            }  
        }

        return $msg;
      } elseif ($test->status == 'T') {
        $this->update($request, $test->email);

        //Guardando dirección para envío de inventario en caso de que sea un coordinador
        if($request->platform == 'coordinador'){
          UserDeliveryAddress::deleteReg($request->email);

          $addressDel = UserDeliveryAddress::getConnect('W');
          $addressDel->email = $request->email;
          $addressDel->street = $request->street;
          $addressDel->colony = $request->colony;
          $addressDel->municipality = $request->municipality;
          $addressDel->state = $request->state;
          $addressDel->postal_code = $request->pc;
          $addressDel->ext_number = $request->ext_number;
          $addressDel->int_number = $request->int_number;
          $addressDel->reference = $request->reference;
          $addressDel->user_reg = session('user.email');
          $addressDel->date_reg = date('Y-m-d H:i:s');
          $addressDel->status = 'A';
          $addressDel->save();

          $res = APIProva::updateUser([
            'nombre' => $request->name.' '.$request->last_name,
            'email' => $request->email,
            'referencia' => $request->reference,
            'direccion' => [
              'calle' => $request->street,
              'colonia' => $request->colony,
              'municipio' => $request->municipality,
              'estado' => $request->state,
              'code_postal' => $request->pc,
              'num_ext' => $request->ext_number,
              'num_int' => $request->int_number,
              'telefono' => str_replace(" ", "", $request->phone)
            ]
          ], $request->email);
          //TODO: Validar respues "no consiguió el usuario para llamar al request de creación"
        }



        /*************************************************************************
        *
        *   Da de alta al usuario en la plataforma de TelmovPay
        * 
        *************************************************************************
        //Comentado debido a Telmovpay
        $telmovPayId = '';
        $user = User::getUserByEmail($user->email);
        if ($user->status == 'A' && User::hasPermission($user->email, 'SEL-TLP')){
          $telmovPayId = TelmovPay::createSeller($user->email, $user->code_curp, $user->name, $user->last_name);
          if (!is_null($telmovPayId)){
            User::where([['email', $user_email], ['status', 'A']])->update(['telmovpay_id' => $telmovPayId]);
          }else{
            return 'El usuario se ha creado con exito pero no se pudo activar en Telmov';
          }
        }

        /*************************************************************************
        *
        *   Fin del bloque para dar de alta al usuario en la plataforma de TelmovPay
        * 
        *************************************************************************/


        return 'El usuario se ha creado con exito';
      } else {
        return 'El usuario ya existe';
      }
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }

  public function deleteCodDep(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->bank) && !empty($request->cod) && !empty($request->user)) {
        UserDeposit::deleteCode($request->user, $request->cod, $request->bank);

        return response()->json([
          'error' => false,
        ]);
      }

      return response()->json([
        'error' => true,
      ]);
    }
  }

  public function show($user_email)
  {
    $user = User::find($user_email);
    return response()->json($user);
  }

  public function chpass(Request $request, $user_email)
  {

    if ((User::hasPermission(session('user.email'), 'AMU-CPU')) && (!empty($request->ch_password))) {

      $user = User::find($user_email);
      if (!Hash::check($request->ch_password, $user->password)) {
        $user->password      = Hash::make($request->ch_password);
        $user->password_date = date('Y-m-d H:i:s');
        $user->save();

        return 'La contraseña del usuario ' . $user_email . ' se actualizo correctamente';
      } else {
        return 'La contraseña no puede ser igual a la actual.';
      }

    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }

  public function updateUserPass(Request $request)
  {
    if ($request->isMethod('post') /*&& $request->ajax()*/) {
      if (!empty($request->user) && !empty($request->ac_password) && !empty($request->n1_password)) {
        $user = User::find($request->user);

        if (Hash::check($request->ac_password, $user->password)) {
          if ($request->n1_password != $request->ac_password) {
            $user->password      = Hash::make($request->n1_password);
            $user->password_date = date('Y-m-d H:i:s');
            $user->save();

            return response()->json(['success' => true, 'msg' => 'Contraseña actualizada, por favor inicie sesión.']);
          } else {
            return response()->json(['success' => false, 'msg' => 'La contraseña no puede ser igual a la actual.']);
          }
        }

        return response()->json(['success' => false, 'msg' => 'No se pudo actualizar la contraseña.']);
      }

      return response()->json(['success' => false, 'msg' => 'Ocurrio un error']);
    }
  }

  public function update(Request $request, $user_email)
  {
    if (User::hasPermission(session('user.email'), 'AMU-UUS')) {

      $user = User::getConnect('W')->find($user_email);
      $msg = 'El usuario se ha actualizado con exito';
      $change = ($user->parent_email != $request->parent_email);
      $replacement = false;

      if (session('user.platform') == 'admin') {
        $user->parent_email = $request->parent_email;
      }

      $user->name        = strtoupper(ValidateString::normaliza($request->name));
      $user->last_name   = strtoupper(ValidateString::normaliza($request->last_name));
      $user->platform    = $request->platform;
      $user->dni         = $request->dni;
      $user->phone       = str_replace(" ", "", $request->phone);
      $user->phone_job   = str_replace(" ", "", $request->phone_job);
      $user->charger_com = $request->commission;
      $user->profession  = $request->profession;
      //$user->code_curp   = strtoupper($request->curp); Comentado debido a Telmovpay

      if (!empty($request->profile) && Profile::getConnect('R')->where('id', $request->profile)->get()->first()->type != 'master') {
        $user->id_org = $request->organization;
      }

      $user->position = $request->position;
      $user->address  = $request->address;
      $user->status   = $request->status;

      //Si viene la segunda clave
      if ($request->secondPass) {
        $user->second_password = Hash::make($request->secondPass);
      }

      //start Id de islim_esquema_comercial
      if (!empty($request->coordinacion_select)) {
        //coordinacion
        $user->esquema_comercial_id = $request->coordinacion_select;
      } else {
        if (!empty($request->region_select)) {
          //region
          $user->esquema_comercial_id = $request->region_select;
        } else {
          if (!empty($request->division_select)) {
            $user->esquema_comercial_id = $request->division_select;
          }
        }
      }
      //end Id de islim_esquema_comercial

      $user->save();

      //Creando códigos de depósito
      //creando el del BBVA
      if ($request->has('codBV') && $request->has('bbvaid') && !empty($request->codBV)) {
        if (!UserDeposit::checkCodeAndBank(strtoupper($request->codBV), $request->bbvaid)) {
          UserDeposit::setInactiveCodes($user->email, [$request->bbvaid]);
          UserDeposit::createCode($user->email, strtoupper($request->codBV), $request->bbvaid);
        }
      }

      //creando el del banco Azteca
      if ($request->has('bankAccount') && $request->has('codBA') && !empty($request->codBA)) {
        if (!UserDeposit::checkCodeAndBank($request->codBA, $request->bankAccount)) {
          $group = Bank::getBankByGruop('AZ');

          UserDeposit::setInactiveCodes($user->email, $group->pluck('id')->toArray());
          UserDeposit::createCode($user->email, $request->codBA, $request->bankAccount);
        }
      }

      //Permisos a las bodegas que pueden acceder los vendedores retail
      SellerWare::getConnect('W')->where('email', $user->email)->update(['status' => 'I']);
      if (!empty($request->organization)) {
        $org = Organization::select('type')->where('id', $request->organization)->first();

        if ($request->platform == 'vendor' && !empty($org) && $org->type == 'R' && !empty($request->ware_org) && count($request->ware_org)) {

          foreach ($request->ware_org as $value) {
            $rel           = new SellerWare;
            $rel->id_ware  = $value;
            $rel->email    = $user->email;
            $rel->status   = 'A';
            $rel->date_reg = date('Y-m-d H:i:s');
            $rel->save();
          }
        }
      }

      //Agregando vendedores a una organizacion en caso de que el usuario actualizado sea un coordinador
      if ($user->platform == "coordinador" && !empty($request->organization) && $request->organization != $user->id_org) {
        User::where([['parent_email', $user_email], ['status', 'A'], ['platform', 'vendor']])->update(['id_org' => $request->organization]);
      }

      $profile = ProfileDetail::where('user_email', $user_email)->first();
      if (!$profile) {
        $profile             = new ProfileDetail;
        $profile->id_profile = $request->profile;
        $profile->user_email = $user->email;
        $profile->status     = $user->status;
        $profile->save();
      } else {
        ProfileDetail::where('user_email', $user_email)->update(['id_profile' => $request->profile]);
      }

      //OJO aqui se traspasa la deuda al nuevo coordinador asignado.
      //!!!!!NO QUITAR ESTE CODIGO!!!!!!//
      /*if (AssignedSales::where(['users_email'=>$user_email, 'status'=>'P'])->count() > 0 && $user->parent_email != null ){
      if(AssignedSales::where(['users_email'=>$user_email, 'status'=>'P'])->first()->parent_email != $user->parent_email){
      $assignedSales=AssignedSales::where(['users_email'=>$user_email, 'status'=>'P'])->update(['parent_email'=>$user->parent_email]);
      }
      }*/

      $policies = Policy::getConnect('R')->where('status', 'A')->get();

      if ($request->status == 'A' || $request->status == 'I' || $request->status == 'T') {
        $rolesuser = UserRole::where('user_email', $user_email)->get();
        $whuser    = UserWarehouse::where('users_email', $user_email)->get();
        $invuser   = SellerInventory::where('users_email', $user_email)->count();

        if (isset($rolesuser) && (count($rolesuser) > 0)) {
          foreach ($rolesuser as $ru) {
            UserRole::where(['user_email' => $user_email, 'policies_id' => $ru->policies_id, 'roles_id' => $ru->roles_id])->update(['status' => $request->status]);
          }
        }

        if (isset($invuser) && ($invuser > 0)) {
          if ($request->status == 'T') {

            $invs = SellerInventory::getConnect('R')
              ->where('users_email', $user_email)
              ->where('status', '=', 'A')
              ->get();

            SellerInventory::where(['users_email' => $user_email, 'status' => 'A'])->update(['status' => 'T']);

            foreach ($invs as $key => $inv) {

              $inventory = Inventory::getConnect('R')
                ->find($inv->inv_arti_details_id);

              SellerInventoryTrack::setInventoryTrack(
                $inventory->id,
                $user_email,
                null,
                null,
                $inventory->warehouses_id,
                session('user')->email
              );
            }

          }
        }
      }

      foreach ($policies as $policy) {
        $policyID = 'policy_id_' . $policy->id;
        $roleID   = 'role_id_' . $policy->roles_id;
        $valueID  = 'value_' . $policy->roles_id . $policy->id;

        $userRole = UserRole::where(['user_email' => $user_email, 'policies_id' => $request->$policyID, 'roles_id' => $request->$roleID])->first();
        if (!empty($userRole)) {
          $value = 0;
          if (!empty($request->$valueID)) {
            $value = $request->$valueID;
          }
          UserRole::where(['user_email' => $user_email, 'policies_id' => $request->$policyID, 'roles_id' => $request->$roleID])->update(['status' => $request->status, 'value' => $value]);
        } else {
          if (!empty($request->$policyID) && !empty($request->$roleID)) {
            $userRole              = new UserRole();
            $userRole->user_email  = $request->email;
            $userRole->date_reg    = date('Y-m-d H:i:s', time());
            $userRole->status      = $request->status;
            $userRole->policies_id = $request->$policyID;
            $userRole->roles_id    = $request->$roleID;
            $userRole->value       = 0;
            if ($request->has($valueID) && $request->$valueID != 0) {
              $userRole->value = $request->$valueID;
            }
            $userRole->save();
          }
        }
      }

      if ($request->has('replacement')) {
        self::setReplacement($request->replacement, $user->email);  
        $replacement = true;      
      }

      if (session('user.email') == $user_email) {
        $user = User::getUser($user_email);
        session(['user' => $user]);
      }

      //Guardando dirección para envío de inventario en caso de que sea un coordinador
      if($request->platform == 'coordinador'){
        UserDeliveryAddress::deleteReg($request->email);

        $addressDel = UserDeliveryAddress::getConnect('W');
        $addressDel->email = $request->email;
        $addressDel->street = $request->street;
        $addressDel->colony = $request->colony;
        $addressDel->municipality = $request->municipality;
        $addressDel->state = $request->state;
        $addressDel->postal_code = $request->pc;
        $addressDel->ext_number = $request->ext_number;
        $addressDel->int_number = $request->int_number;
        $addressDel->reference = $request->reference;
        $addressDel->user_reg = session('user.email');
        $addressDel->date_reg = date('Y-m-d H:i:s');
        $addressDel->status = 'A';
        $addressDel->save();

        $res = APIProva::updateUser([
          'nombre' => $request->name.' '.$request->last_name,
          'referencia' => $request->reference,
          'direccion' => [
            'calle' => $request->street,
            'colonia' => $request->colony,
            'municipio' => $request->municipality,
            'estado' => $request->state,
            'code_postal' => $request->pc,
            'num_ext' => $request->ext_number,
            'num_int' => $request->int_number,
            'telefono' => str_replace(" ", "", $request->phone)
          ]
        ], $request->email);
        
        if(!$res['success']){
          //Si no se consiguio el sku se crea el producto
          if(!empty($res['message']) && $res['message'] == 'no existe el usuario'){
            $res2 = APIProva::createUser([
              'nombre' => $request->name.' '.$request->last_name,
              'email' => $request->email,
              'referencia' => $request->reference,
              'direccion' => [
                'calle' => $request->street,
                'colonia' => $request->colony,
                'municipio' => $request->municipality,
                'estado' => $request->state,
                'code_postal' => $request->pc,
                'num_ext' => $request->ext_number,
                'num_int' => $request->int_number,
                'telefono' => str_replace(" ", "", $request->phone)
              ]
            ]);

            if(!$res2['success']){
              $msg .= ', pero fallo sincronización con prova';
            }
          }else{
            $msg .= ', pero fallo sincronización con prova';
          }
        }
      }

      //Actualiza el distribuidor de todos los usuarios supervisados por este usuario.


      $profile_inherit = Profile::getConnect('R')->find($request->profile);

      if($request->organization == 1 && $profile_inherit->hierarchy_branch == 'P'){

          $distributor_user = DB::connection('netwey-r')->table('islim_distributor_user')->where('user_email', $user_email)->where('status', 'A')->first();

          $old_id = !empty($distributor_user) ? $distributor_user->distributor_id : null;
          $new_id = !empty($request->distributor_select) ? $request->distributor_select : null;  

          if($profile_inherit->hierarchy > 4 && ($change || $replacement)){

              if($change){
                  $distributor_user_parent = DB::connection('netwey-r')->table('islim_distributor_user')->where('user_email', $request->parent_email)->where('status', 'A')->first();

                  $this->inherit_distriburor($request->parent_email, !empty($distributor_user_parent) ? $distributor_user_parent->distributor_id : null);
              }else{

                $this->inherit_distriburor($user_email, $old_id);

              }

          }elseif ($profile_inherit->hierarchy == 4 &&  ($new_id != $old_id || $replacement)) {

            if($new_id != $old_id){

              DB::connection('netwey-w')
                  ->table('islim_distributor_user')
                  ->where('user_email', $user_email)
                  ->where('status', 'A')
                  ->update(['status' => 'T']);

              if($new_id != null){

                DB::connection('netwey-w')
                    ->table('islim_distributor_user')
                    ->insert([
                      'user_email' => $user_email,
                      'distributor_id' => $new_id,
                      'status' => 'A',
                      'date_reg' => date('Y-m-d H:i:s'),
                      'user_email_allocator' => session('user.email')]);
              }

              $this->inherit_distriburor($user_email, $new_id);

            }else{

              $this->inherit_distriburor($user_email, $old_id);

            }

          }  
      }

      /*************************************************************************
      *
      *   Actualiza el estado del usuario en la plataforma de TelmovPay
      * 
      *************************************************************************

      $telmovPaySellerStatus = '';
      $user = User::getUserByEmail($user->email);

      if ($user->status == 'A' && User::hasPermission($user->email, 'SEL-TLP')){

        $telmovPaySellerStatus = TelmovPay::setSellerStatus($user->email, true);

        if (!is_null($telmovPaySellerStatus)){

          if ($telmovPaySellerStatus){
            $msg .= ' y se activo en la plataforma de TelmovPay';
          }else{
            $msg .= 'y se desactivo en la plataforma de TelmovPay';
          }

        }else{
          $msg .= ', pero no se actualizo en la plataforma de TelmovPay';
        }
        
      }else{

        $telmovPaySellerStatus = TelmovPay::setSellerStatus($user->email, false);

        if (!is_null($telmovPaySellerStatus)){

          if ($telmovPaySellerStatus){
            $msg .= ' y se activo en la plataforma de TelmovPay';
          }else{
            $msg .= 'y se desactivo en la plataforma de TelmovPay';
          }

        }else{
          $msg .= ', pero no se actualizo en la plataforma de TelmovPay';
        }
        
      }

      /*************************************************************************
      *
      *   Fin del bloque para actualizar el estado en la plataforma de TelmovPay
      * 
      *************************************************************************/

      return $msg;
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }
  }

  public function destroy($user_email)
  {
    if (User::hasPermission(session('user.email'), 'AMU-DUS')) {
      $user      = User::getConnect('W')->find($user_email);
      $rolesuser = UserRole::getConnect('R')->where('user_email', $user_email)->get();
      $invuser   = SellerInventory::getConnect('R')
        ->where('users_email', $user_email)
        ->where('status', 'A')
        ->get();
      $whuser = UserWarehouse::getConnect('R')->where('users_email', $user_email)->get();
      #$apuser = AssignedPack::where('users_email',$user_email)->get();

      if (!empty($user->parent_email)) {
        // tiene supervisor
        $parent_user = User::getConnect('R')->find($user->parent_email);
        if ($parent_user) {
          // el supervisor existe
          if ($parent_user->status = 'A') {
            // y esta activo
            $parent_email = $parent_user->email;
          }
        }
      }

      $subordinados = User::getConnect('W')
        ->where('parent_email', $user_email)
        ->get();

      if (!empty($parent_email) && count($subordinados) > 0) {
        foreach ($subordinados as $key => $subordinado) {
          $subordinado->parent_email = $parent_email;
          $subordinado->save();

          $pdu               = ParentDeleteUser::getConnect('W');
          $pdu->email        = $subordinado->email;
          $pdu->parent_email = $user_email;
          $pdu->status       = 'A';
          $pdu->save();
        }
      }

      $user->status = 'T';
      $user->save();

      if (isset($rolesuser) && (count($rolesuser) > 0)) {
        foreach ($rolesuser as $ru) {
          UserRole::getConnect('W')->where(['user_email' => $user_email, 'policies_id' => $ru->policies_id, 'roles_id' => $ru->roles_id])->update(['status' => 'T']);
        }
      }

      if (isset($invuser) && (count($invuser) > 0)) {
        foreach ($invuser as $ui) {

          $invs = SellerInventory::where([
            'users_email'         => $user_email,
            'inv_arti_details_id' => $ui->inv_arti_details_id,
          ])
            ->where('status', '<>', 'T')
            ->get();

          SellerInventory::getConnect('W')->where(['users_email' => $user_email, 'inv_arti_details_id' => $ui->inv_arti_details_id])->update(['status' => 'T']);

          foreach ($invs as $key => $inv) {

            $inventory = Inventory::getConnect('R')
              ->find($inv->inv_arti_details_id);

            SellerInventoryTrack::setInventoryTrack(
              $inventory->id,
              $user_email,
              null,
              null,
              $inventory->warehouses_id,
              session('user')->email
            );
          }
        }
      }

      if (isset($whuser) && (count($whuser) > 0)) {
        foreach ($whuser as $wu) {
          $invs = SellerInventory::where([
            'users_email'   => $user_email,
            'warehouses_id' => $wu->warehouses_id]
          )
            ->where('status', '<>', 'T')
            ->get();

          SellerInventory::getConnect('W')->where(['users_email' => $user_email, 'warehouses_id' => $wu->warehouses_id])->update(['status' => 'T']);

          foreach ($invs as $key => $inv) {

            $inventory = Inventory::getConnect('R')
              ->find($inv->inv_arti_details_id);

            SellerInventoryTrack::setInventoryTrack(
              $inventory->id,
              $user_email,
              null,
              null,
              $inventory->warehouses_id,
              session('user')->email
            );
          }
        }
      }

      //Eliminando dirección en prova
      if($user->platform == 'coordinador'){
        UserDeliveryAddress::deleteReg($user->email);
        APIProva::deleteUser($user->email);
      }

      /*************************************************************************
      *
      *   Deshabilita el usuario de la plataforma de Telmov
      * 
      *************************************************************************/

      $userDeleted = User::getUserByEmail($user->email);
      if ($userDeleted->status == 'T') {
        $telmovPaySellerStatus = TelmovPay::setSellerStatus($userDeleted->email, false);
      }

      /*************************************************************************
      *
      *   Fin del bloque para deshabilitar el usuario de la plataforma de Telmov
      * 
      *************************************************************************/

      return response()->json($user);
    } else {
      return 'Usted no posee permisos para realizar esta operación';
    }

  }
  //retorna true si el usuario se puede eliminar y false si no se puede eliminar, false sera retornado cuando si un usuario no tiene supervisor y si tiene subordinados, ese usuario no se puede eliminar hasta que los subordinados sean asignados a otro usuario
  public function isRemovable(Request $request)
  {
    $is_removable = false;
    $parent_email = false;
    $subordinates = false;

    if (!empty($request->get('email'))) {
      $user_email = $request->get('email');
      $user       = User::getConnect('R')->find($user_email);

      if (!empty($user->parent_email)) {
        //tiene supervisor
        $parent_user = User::getConnect('R')->find($user->parent_email);
        if ($parent_user) {
          //supervisor existe
          if ($parent_user->status = 'A') {
            // y esta activo
            $is_removable = true;
            $parent_email = $user->parent_email;
          }
        }
      }

      $subordinados = User::getConnect('R')
        ->where('parent_email', $user_email)
        ->count();
      if ($subordinados > 0) {
        // tiene subordindaos
        $subordinates = true;
      } else {
        $is_removable = true;
      }

      if ($is_removable) {
        return response()->json([
          'is_removable' => true,
          'parent_email' => $parent_email,
          'subordinates' => $subordinates]);
      }

    }
    return response()->json(['is_removable' => false]);
  }

  public function warehouses(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->org)) {
        $data = OrgWarehouse::select('islim_warehouses.name', 'islim_warehouses.id')
          ->join('islim_warehouses', function ($join) {
            $join->on('islim_warehouses.id', '=', 'islim_wh_org.id_wh')
              ->where('status', 'A');
          })
          ->where([
            ['islim_wh_org.id_org', $request->org]])
          ->get();

        if ($data->count()) {
          return response()->json(['error' => false, 'data' => $data]);
        }

      }

      return response()->json(['error' => true, 'data' => 'No se encontraron bodegas para esta organización']);
    }
  }

  public function getUser($user_email, $activos = 'Y')
  {
    return User::getUser($user_email, $activos);
  }

  public function policies()
  {
    $policies   = Policy::getConnect('R')->where('status', 'A')->get();
    $rol_arr_id = array();
    foreach ($policies as $role_id) {
      $rol_arr_id[] = $role_id->roles_id;
    }
    $roles = Role::getConnect('R')->where('status', 'A')->whereIn('id', $rol_arr_id)->get();
    foreach ($roles as $role) {
      $policies_role  = Policy::getConnect('R')->where('roles_id', $role->id)->where('status', 'A')->get();
      $role->policies = $policies_role;
    }
    return $roles;
  }

  public function getCodeDeposit(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->bank)) {
        $codesU = UserDeposit::getCodeByBank($request->bank);

        $codesU = $codesU->pluck('id_deposit')->toArray();

        $codes = [];
        for ($i = 0; $i <= 99; $i++) {
          $pos = $i;

          if ($i < 10) {
            $pos = '0' . $pos;
          }

          if (empty($codesU) || !in_array($pos, $codesU)) {
            $codes[] = $pos;
          }
        }

        return response()->json(['error' => false, 'codes' => $codes]);
      }

      return response()->json(['error' => true]);
    }
  }

  public function checkCodeDeposit(Request $request)
  {
    if (!empty($request->codBV)) {
      $check = UserDeposit::checkCode(
        strtoupper($request->codBV),
        !empty($request->user) ? $request->user : false
      );

      if ($check) {
        return response()->json('Código no disponible');
      }

      return response()->json('true');
    }

    return response()->json('');
  }

  public function view()
  {
    $users     = null;
    $users_scc = null;

    $roles = $this->policies();

    $org = Organization::getOrgsPermitByOrgs(session('user.id_org'));

    $profiles = Profile::getProfileByOrgs($org->pluck('id'));

    $supervisors = User::getSupervisorsByOrgsAndProf($org->pluck('id'), $profiles->pluck('id'));

    //Consultando datos del BBVA
    $bbva = Bank::getBankByGruop('BV');

    //Consultando datos del BBVA
    $azteca = Bank::getBankByGruop('AZ');

    //Consultando códigos usados de la primera opción de bando Azteca
    if ($azteca->count()) {
      $codesU = UserDeposit::getCodeByBank($azteca[0]->id);
      $codesU = $codesU->pluck('id_deposit')->toArray();
    }

    $codes = [];
    for ($i = 0; $i <= 99; $i++) {
      $pos = $i;

      if ($i < 10) {
        $pos = '0' . $pos;
      }

      if (empty($codesU) || !in_array($pos, $codesU)) {
        $codes[] = $pos;
      }
    }

    $distributors = DB::connection('netwey-r')
    ->table('islim_distributors')
    ->select('id', 'description')
    ->where('status', 'A')
    ->get();

    $html = view(
      'pages.ajax.user',
      compact(
        'users',
        'roles',
        'profiles',
        'org',
        'supervisors',
        'users_scc',
        'bbva',
        'azteca',
        'codes',
        'distributors'
      )
    )
      ->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }
  public function profiles($type)
  {

    $profile  = array();
    $policies = PolicyProfile::getConnect('R')
      ->select("policy_id", "value")
      ->where('profile_id', $type)
      ->where("value", ">", 0)->get();
      //->pluck('policy_id')->toArray();

    foreach ($policies as $key) {
      $polici = Policy::getConnect('R')->select('id', 'roles_id', 'type')->where('status', 'A')->where('id', $key->policy_id)->get()->first();
      if (!empty($polici)) {
        $profile[] = array('policy' => 'value_' . $polici->roles_id . $polici->id, 'item' => 'item_' . $polici->id, 'type' => $polici->type, 'panel' => 'panel_' . $polici->roles_id, 'value'=>$key->value);
      }
    }
    return $profile;
  }
  public function assign()
  {
    $policies = Policy::getConnect('R')->select('id', 'roles_id')->where('status', 'A')->get();
    foreach ($policies as $key) {
      $pro_pol             = new PolicyProfile;
      $pro_pol->profile_id = 1;
      $pro_pol->policy_id  = $key->id;
      $pro_pol->save();
      if ((($key->id >= 59) && ($key->id <= 63)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 2;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if ((($key->id >= 76) && ($key->id <= 82)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 3;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if ((($key->id >= 76) && ($key->id <= 90)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 4;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if ((($key->id >= 65) && ($key->id <= 70)) || (($key->id >= 96) && ($key->id <= 102)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 5;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if ((($key->id >= 59) && ($key->id <= 62)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 6;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if ((($key->id == 58) || ($key->id == 80)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 7;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if (($key->id == 111) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 8;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if (($key->id == 59) || (($key->id >= 107) && ($key->id <= 109)) || ($key->id == 110)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 9;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if (($key->roles_id == 16) || ($key->roles_id == 17) || ($key->roles_id == 19) || ($key->id == 54) || ($key->id == 59)) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 10;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
      if (($key->id == 48) || ($key->id == 51) || ($key->id == 52) || ($key->id == 54) || ($key->id == 56) || ($key->id == 57) || (($key->roles_id == 19) && ($key->id != 113))) {
        $pro_pol             = new PolicyProfile;
        $pro_pol->profile_id = 11;
        $pro_pol->policy_id  = $key->id;
        $pro_pol->save();
      }
    }
    return 'fino';
  }
  public function checkPass(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {

      $data = $request->all();

      $chk = User::verify_pass($data['myEmail'], $data['pass']);
      //  Log::info("Vericado ".$chk);
      if ($chk) {
        return response()->json(array('success' => true));
      }
    }
    return response()->json(array('success' => false));
  }
  public function checkInv(Request $request)
  {
    //Log::info('HOLA tengo inventario activo');
    if ($request->isMethod('post') && $request->ajax()) {

      $data = $request->all();

      $chk = SellerInventory::getConnect('R')
        ->where([['users_email', $data['user_email']],
          ['status', 'A']])->first();

      if ($chk) {
        return response()->json(array('success' => true));
      }
    }
    return response()->json(array('success' => false));
  }

  public function getdivision(Request $request)
  {
    //Log::info('HOLA tengo divisiones');
    if ($request->isMethod('post') && $request->ajax()) {

      //$data = $request->all();

      $chk = DB::table('islim_esquema_comercial')
        ->select('id', 'name')
        ->where([
          ['type', 'D'],
          ['status', 'A']])
        ->get();

      $dst = DB::table('islim_distributors')
        ->select('id', 'description')
        ->where('status', 'A')
        ->get();

      if ($chk && $dst) {
        // Log::info($chk);
        return response()->json(array('success' => true, 'divisions' => $chk, 'distributors' => $dst));
      }
    }
    return response()->json(array('success' => false));
  }

  public function getregions(Request $request)
  {
    //Log::info('HOLA tengo regiones');
    if ($request->isMethod('post') && $request->ajax()) {

      // $data = $request->all();
      // $division = $data['division'];
      //Log::info('regiones '.$division);
      $chk = DB::table('islim_esquema_comercial')
        ->select('id', 'name')
        ->where([
          ['type', 'R'],
          ['status', 'A']])
        ->get();

      if ($chk) {
        //Log::info($chk);
        return response()->json(array('success' => true, 'data' => $chk));
      }
    }
    return response()->json(array('success' => false));
  }

  public function getcoordinacion(Request $request)
  {
    //Log::info('HOLA tengo coordinaciones');
    if ($request->isMethod('post') && $request->ajax()) {

      //  $data = $request->all();
      // $regions = $data['regions'];
      //Log::info('coordinador '.$regions);
      $chk = DB::table('islim_esquema_comercial')
        ->select('id', 'name')
        ->where([
          ['type', 'C'],
          ['status', 'A']])
        ->get();

      if ($chk) {
        // Log::info($chk);
        return response()->json(array('success' => true, 'data' => $chk));
      }
    }
    return response()->json(array('success' => false));
  }

  /**
   * [getListScheme Vista inicial de lista de esquemas comerciales
  ]
   * @return [type] [description]
   */
  public function getListScheme()
  {
    $addScheme = true;
    $query     = CommonHelpers::getOptionColumn('islim_esquema_comercial', 'type');
    $type      = array();

    foreach ($query as $keydate) {
      switch ($keydate) {
        case 'D':
          array_push($type, array('code' => 'D', 'description' => 'Division'));
          break;
        case 'R':
          array_push($type, array('code' => 'R', 'description' => 'Region'));
          break;
        case 'C':
          array_push($type, array('code' => 'C', 'description' => 'Coordinacion'));
          break;
        default:
          array_push($type, array('code' => '', 'description' => 'Tipo desconocido'));
          break;
      }
    }
    //-----------------------------------Data Selects-----------------------------//
    $Listdivision = array();
    $Infodivision = EsquemaComercial::getEsquemaByType('D')->get();
    foreach ($Infodivision as $key) {
      array_push($Listdivision, array('code' => $key->id, 'description' => $key->name));
    }
    $ListRegion = array();
    $InfoRegion = EsquemaComercial::getEsquemaByType('R')->get();
    foreach ($InfoRegion as $key) {
      array_push($ListRegion, array('code' => $key->id, 'description' => $key->name));
    }
    $ListCoord = array();
    $InfoCoord = EsquemaComercial::getEsquemaByType('C')->get();
    foreach ($InfoCoord as $key) {
      array_push($ListCoord, array('code' => $key->id, 'description' => $key->name));
    }
    $html = view('pages.ajax.business_scheme.list_scheme', compact('addScheme', 'type', 'Listdivision', 'ListRegion', 'ListCoord'))->render();
    return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
  }
/**
 * [getListSchemeDT Vista de consulta de esquemas comerciales
]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getListSchemeDT(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      $data = EsquemaComercial::getListEsquema($filters);
      return DataTables::of($data)
        ->editColumn('responsable', function ($c) {
          return !empty($c->responsable) ? $c->responsable : 'S/N';
        })
        ->make(true);

    }
  }
/**
 * [get_filter_scheme Lista nombre de lugares de esquema comercial]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function get_filter_scheme(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $name = $request->input('name');
      //Log::info($name);
      if (!empty($name)) {
        $schemes = EsquemaComercial::GetSchemeSearchList($name);
        return response()->json(array('success' => true, 'schemes' => $schemes));
      }
      return response()->json(array('success' => false));
    }
  }

  public function edit_scheme(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $idName = $request->input('id');
      $name   = $request->input('Newname');

      if (!empty($name) && !empty($idName)) {
        $schemes = EsquemaComercial::UpdateScheme($idName, $name);
        return response()->json(array('success' => true));
      }
      return response()->json(array('success' => false, 'msg' => 'No se envio de forma correcta el nuevo nombre, por tanto no se puede actualizar'));
    }
    return redirect()->route('root');
  }

  public function delete_scheme(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $idName = $request->input('id');
      $name   = $request->input('removeName');

      //se verifica que se pueda eliminar
      $banDelete = EsquemaComercial::isSchemeDelete($idName);

      if (!empty($idName) && $banDelete) {
        $schemes = EsquemaComercial::UpdateScheme($idName);
        return response()->json(array('success' => true));
      }
      return response()->json(array('success' => false, 'msg' => 'No se puede eliminar el registro ya que hay registros asociados a ' . $name));
    }
    return redirect()->route('root');
  }

  public function formCreate_scheme(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $typeView = $request->input('typeCreate');

      if ($typeView == 'D') {
        $html = view('pages.ajax.business_scheme.newDivision')->render();
      } elseif ($typeView == 'R') {
        $html = view('pages.ajax.business_scheme.newRegion')->render();
      } elseif ($typeView == 'C') {
        $html = view('pages.ajax.business_scheme.newCoordination')->render();
      }
      if (!empty($html)) {
        return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
      } else {
        return response()->json(['success' => false, 'msg' => 'No se pudo renderizar la pagina']);
      }
    }
    return redirect()->route('root');
  }
  public function create_scheme(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = $request->all();

      if ($data['typeFrom'] == 'D') {
        $Divi  = EsquemaComercial::InsertScheme('D', $data['inputDivision']);
        $Regi  = EsquemaComercial::InsertScheme('R', $data['inputRegion'], $Divi);
        $Coord = EsquemaComercial::InsertScheme('C', $data['inputCoordinacion'], $Regi);

      } elseif ($data['typeFrom'] == 'R') {
        $Regi  = EsquemaComercial::InsertScheme('R', $data['inputRegion'], $data['idDivision']);
        $Coord = EsquemaComercial::InsertScheme('C', $data['inputCoordinacion'], $Regi);

      } elseif ($data['typeFrom'] == 'C') {
        $Coord = EsquemaComercial::InsertScheme('C', $data['inputCoordinacion'], $data['idRegion']);
      }
      return response()->json(['success' => true, 'numError' => 0]);
    }
    return redirect()->route('root');
  }

  public function inherit_distriburor($parent_email, $distributor_id){

    $children = User::getConnect('R')->select('email')->where('parent_email', $parent_email)->where('status', 'A')->get()->pluck('email')->toArray();

    DB::connection('netwey-w')
    ->table('islim_distributor_user')
    ->whereIn('user_email', $children)
    ->where('status', 'A')
    ->update(['status' => 'T']);



    foreach ($children as $child) {

      if($distributor_id != null){

        DB::connection('netwey-w')
        ->table('islim_distributor_user')
        ->insert([
          'user_email' => $child,
          'distributor_id' => $distributor_id,
          'status' => 'A',
          'date_reg' => date('Y-m-d H:i:s'),
          'user_email_allocator' => session('user.email')
        ]);

      }

      $this->inherit_distriburor($child, $distributor_id);
    }

  }

  public function distributorsView() {
        $html = view('pages.ajax.user_distributor')->render();
        return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
    }

  public function getDistributorDT(Request $request){

    if (!User::hasPermission(session('user.email'), 'DDU-RDU'))
            return response()->json(['success' => false, 'message' => 'Usted no posee permisos para realizar esta operación.'], 403);

    if (!$request->isMethod('get') && !$request->ajax()) 
      return response()->json(['success' => false, 'message' => 'El tipo de petición recibida no está permitida.' ]);


    $distributors = DB::connection('netwey-r')->table('islim_distributors')->where('status', 'A')->get();

    return @DataTables::of($distributors)->toJson();
    
  }

  public function storeDistributor(Request $request){

    if (!User::hasPermission(session('user.email'), 'DDU-CDU'))
            return response()->json(['success' => false, 'message' => 'Usted no posee permisos para realizar esta operación.'], 403);

    if (!$request->isMethod('post') && !$request->ajax()) 
      return response()->json(['success' => false, 'message' => 'El tipo de petición recibida no está permitida.' ]);


    $distributor = DB::connection('netwey-w')
    ->table('islim_distributors')
    ->insert([
      'description' => $request->distributor_description,
      'status' => 'A',
      'date_reg' => date('Y-m-d H:i:s')
    ]);

    if ($distributor > 0) {
      return response()->json(['success' => true, 'msg' => 'Se ha Agregado Exitosamente.']);
    }else{
      return response()->json(['success' => false, 'msg' => 'No se pudo Agregar el Registro.']);
    }
  }

  public function updateDistributor(Request $request){

    if (!User::hasPermission(session('user.email'), 'DDU-UDU'))
            return response()->json(['success' => false, 'message' => 'Usted no posee permisos para realizar esta operación.'], 403);

    if (!$request->isMethod('post') && !$request->ajax()) 
      return response()->json(['success' => false, 'message' => 'El tipo de petición recibida no está permitida.' ]);


    $distributor = DB::connection('netwey-w')
    ->table('islim_distributors')
    ->where('id', $request->distributor_id)
    ->update([
      'description' => $request->distributor_description,
    ]);

    if ($distributor > 0) {
      return response()->json(['success' => true, 'msg' => 'Se ha Actualizado Exitosamente.']);
    }else{
      return response()->json(['success' => false, 'msg' => 'No se pudo Actualizar el Registro.']);
    }
  }

  public function destroyDistributor(Request $request, $id){

    if (!User::hasPermission(session('user.email'), 'DDU-DDU'))
            return response()->json(['success' => false, 'message' => 'Usted no posee permisos para realizar esta operación.'], 403);

    if (!$request->isMethod('delete') && !$request->ajax()) 
      return response()->json(['success' => false, 'message' => 'El tipo de petición recibida no está permitida.' ]);


    $distributor = DB::connection('netwey-w')
    ->table('islim_distributors')
    ->where('id', $id)
    ->update([
      'status' => 'T',
    ]);

    if ($distributor > 0) {
      return response()->json(['success' => true, 'msg' => 'Se ha Eliminado Exitosamente.']);
    }else{
      return response()->json(['success' => false, 'msg' => 'No se pudo Eliminar el Registro.']);
    }
  }
}
