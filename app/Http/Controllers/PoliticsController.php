<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\UserController;

use App\Organization;
use App\Policy;
use App\PolicyProfile;
use App\Profile;
use Illuminate\Support\Facades\DB;
use App\User;
use App\UserRole;

class PoliticsController extends Controller
{
    
    //Vista de politicas predeterminadas
    public function view(){
        $userController = new UserController;
        $roles = $userController->policies();

        $org = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $profiles = Profile::getProfileByOrgs($org->pluck('id'));

        $html = view("pages.ajax.politics.default_politics", array(
                "roles"=>$roles,
                "profiles"=>$profiles, "default"=>true        
        ))->render();
        return response()->json(array("success"=>true, "msg"=>$html, "numError"=>0 ) );
    }

    //Actualiza politicas predeterminadas a un perfil
    public function update(Request $request){
        if (!$request->isMethod('post') && !$request->ajax())
            return response()->json(array('success'=>false, 'msg' => "'El tipo de petición recibida no está permitida.'"));

        if (!User::hasPermission(session('user.email'), 'USR-UPP'))
            return response()->json(array('success'=>false, 'msg' => "Usted no posee permisos para realizar esta operación."), 200);

        if($request->profile != null && Profile::where("id", $request->profile)->first() )
        {   
            $idProfile = $request->profile;
            $policies = Policy::getConnect('R')->where('status', 'A')->get();
            DB::table("islim_policy_profile")->where("profile_id", $idProfile)->update(["value"=>"0"]);

            //Si no enviaron politicas de tipo check saltar la iteracion del array
            $skip = false;
            if( empty($request->iptCheck)  ){
                $skip = true;
            }
            
            foreach ($policies as $policy) {
                $valueID  = 'value_' . $policy->roles_id . $policy->id;
                $exist = PolicyProfile::where("policy_id", $policy->id)->where("profile_id", $idProfile)->first();

                if( !$skip ){
                    //Inputs de tipo CHECHBOX
                    foreach( $request->iptCheck as $item){
                        if($valueID == $item["name"] ){
                            if($exist != null){
                                //Si existe solo actualiza el value
                                DB::table("islim_policy_profile")->where("policy_id", $policy->id)->where("profile_id", $idProfile)
                                    ->update([
                                        "value"=> 1,
                                    ]);
                            }else{
                                $newPolicy = new PolicyProfile();
                                $newPolicy->policy_id = $policy->id;
                                $newPolicy->profile_id = $idProfile;
                                $newPolicy->value = 1;
                                $newPolicy->save();
                            }
                            
                        }
                    }
                }
                
                //Inputs de tipo TEXT
                foreach($request->iptText as $item){
                    if($valueID == $item["name"] ){

                        if( $exist != null ){
                            DB::table("islim_policy_profile")->where("policy_id", $policy->id)->where("profile_id", $idProfile)
                                ->update([
                                    "value"=> $item["value"],
                                ]);
                        }else{
                            $newPolicy = new PolicyProfile();
                            $newPolicy->policy_id = $policy->id;
                            $newPolicy->profile_id = $idProfile;
                            $newPolicy->value = $item["value"] ;
                            $newPolicy->save();
                        }
                    }
                }
            }

            return response()->json(array('success' => true, 'msg'=> 'Datos guardados correctamente'));
            
        }else{
            return response()->json(array('success'=>false, 'msg' => "Debe seleccionar un perfil"));
        }
        
    }



    //Vista de politicas masivas
    public function viewMassivePolitics(){
        $userController = new UserController;
        $roles = $userController->policies();

        $org = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $profiles = Profile::getProfileByOrgs($org->pluck('id'));

        $html = view("pages.ajax.politics.massive.list_massive_politics", array(
                "roles"=>$roles,
                "profiles"=>$profiles, "default"=>true        
        ))->render();
        return response()->json(array("success"=>true, "msg"=>$html, "numError"=>0 ) );
    }


    //Obtiene usuarios por perfil "ACTIVOS"
    public function getUsersByProfile(Request $request){

        $name = trim($request->name);
        $idProfile = $request->profile;

        $users = UserRole::select(
            "islim_users.email",
            DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username'),
            "islim_profiles.name as profile"
        )
        ->join("islim_users", "islim_users.email", "islim_user_roles.user_email")
        ->join("islim_profile_details", "islim_profile_details.user_email", "islim_users.email")
        ->join("islim_profiles", "islim_profiles.id", "islim_profile_details.id_profile")
        ->where("islim_users.status", "A")
        ->whereNotIn("islim_users.email", ["admin@admin.com"])
        ->where("islim_profile_details.id_profile", $idProfile)
        ->where(function($query) use ($name) {
            $query->where([
                [DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name)'), 'like', '%' . str_replace(' ', '%', $name) . '%']
            ])
            ->orWhere('islim_users.email', 'like', '%' . str_replace(' ', '%', $name) . '%');
        });

        $users = $users->groupBy("islim_users.email")->get();

        return response()->json(array(
            "success"=>true,
            "users"=> $users
        ), 200);

    }

    //Editar asignacion de politicas masivas a usuarios
    public function massiveEditPolicies(Request $request)
    {   
        if (!User::hasPermission(session('user.email'), 'USR-EAP'))
            return response()->json(array('success'=>false, 'msg' => "Usted no posee permisos para realizar esta operación."), 200);

        if($request->idProfile == null){
            return response()->json(array("success"=>false,"msg"=>"Debe seleccionar un perfil"), 200);
        }
        

        $iptChecks = $request->iptCheck;
        $iptText = $request->iptText;
        $users = $request->users;
        $idProfile = $request->idProfile;

        /* Si no llegan usuarios en el request, busca a todos los usuarios activos con el perfil seleccionado
        para aplicar las politicas a todos los usuarios del perfil seleccionado */
        if($users == null && $idProfile != null ){  
            $users = User::select("islim_users.email")
            ->join("islim_profile_details", "islim_profile_details.user_email", "islim_users.email")
            ->where("islim_users.status", "A")
            ->whereNotIn("islim_users.email", ["admin@admin.com"])
            ->where("islim_profile_details.id_profile", $idProfile)
            ->groupBy("islim_users.email")->get();
            
            $users = $users->pluck("email");

            if( sizeof($users) == 0 ){
                $profile = Profile::where("id", $idProfile)->first();
                return response()->json(array('success'=>false,'msg' => "No se encontraron usuarios activos con el perfil: ".$profile->name. " para asignar políticas."), 200);
            }
        }

        $policies = Policy::getConnect('R')->where('status', 'A')->get();
        foreach ($policies as $policy)
        {
            $valueID  = 'value_' . $policy->roles_id . $policy->id;
            foreach($users as $user)
            {
                if($iptChecks != null)
                {
                    //Insertar o Actualizar inputs de tipo CHECKOBX
                    foreach($iptChecks as $check)
                    {
                        if( $valueID == $check["name"] )
                        {
                            $action = $check["value"]; // Valor marcado en el check A = Add -- D = Delete

                            $exist = UserRole::where("policies_id", $policy->id)
                                ->where("roles_id", $policy->roles_id)
                                ->where("user_email", $user)->first();

                            if( isset($exist->user_email ) != null ){
                                $usrRole = DB::table("islim_user_roles")
                                    ->where("policies_id", $policy->id)
                                    ->where("roles_id", $policy->roles_id)
                                    ->where("user_email", $user);

                                if($action == "A" ){ // A = Add
                                    $usrRole->update([
                                        "value" => "1",
                                        "status" => "A",
                                    ]);
                                }
                                if($action == "D"){ //D = Delete
                                    $usrRole->update([
                                        "value" => "0",
                                        "status" => "I",
                                    ]);
                                }
                            }else{
                                if($action == "A" ){ // A = Add
                                    DB::table('islim_user_roles')->insert([
                                        'user_email' => $user,
                                        'policies_id' => $policy->id,
                                        'roles_id' => $policy->roles_id,
                                        'value' => 1,
                                        'date_reg' => date("Y-m-d H:m:s"),
                                        'status' => "A"
                                    ]);
                                }
                            }
                        }
                    }                    
                }


                //Insertar/actualizar inputs de tipo TEXT
                foreach( $iptText as $item )
                {
                    if($valueID == $item["name"])
                    {
                        if( $item["value"] != null )
                        {
                            $exist = UserRole::where("policies_id", $policy->id)
                                ->where("roles_id", $policy->roles_id)
                                ->where("user_email", $user)->first();

                            if( isset($exist->user_email) != null ){
                                $usrRole = DB::table("islim_user_roles") 
                                    ->where("policies_id", $policy->id)
                                    ->where("roles_id", $policy->roles_id)
                                    ->where("user_email", $user);

                                $usrRole->update([
                                    "value" => $item["value"]
                                ]);
                            }else{
                                DB::table('islim_user_roles')->insert([
                                    'user_email' => $user,
                                    'policies_id' => $policy->id,
                                    'roles_id' => $policy->roles_id,
                                    'value' => $item["value"],
                                    'date_reg' => date("Y-m-d H:m:s"),
                                    'status' => "A"
                                ]);
                            }
                        }
                    }
                }
                
            }
        }

        return response()->json(array('success'=>true, 'msg' => "Datos guardados correctamente."), 200);
    }


}
