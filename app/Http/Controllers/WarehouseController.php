<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Warehouse;
use App\UserWarehouse;
use App\User;
use App\OrgWarehouse;
use App\Organization;

class WarehouseController extends Controller {
	public function index (){
		$warehouses = Warehouse::getConnect('R')->all();
		foreach ($warehouses as $warehouse) {
			$warehouse->position = 0;
		}
        return response()->json($warehouse);
	}

	public function show ($id){
		$warehouse = Warehouse::getConnect('R')->find($id);
        return response()->json($warehouse);
	}

	public function store (Request $request){

        if(User::hasPermission (session('user.email'), 'A1W-CWH')){

            /*$warehouse = Warehouse::getConnect('W')->create($request->except('position'));
            $myPoint = $request->input('position');
            $warehouse->position = \DB::raw($myPoint);
            $warehouse->date_reg = date ('Y-m-d H:i:s', time());
            $warehouse->save();*/

            $warehouse = Warehouse::getConnect('W');
            $warehouse->name = $request->input('name');
            $warehouse->address = $request->input('address');
            $warehouse->phone = $request->input('phone');
            $warehouse->lat = $request->input('lat');
            $warehouse->lng = $request->input('lng');
            $warehouse->position = \DB::raw($request->input('position'));

            if(!empty($request->input('group_log'))){
                $warehouse->group = $request->input('group_log');
                $warehouse->route = $request->input('route');
                $warehouse->street_number = $request->input('street_n');
                $warehouse->neighborhood = $request->input('neighb');
                $warehouse->locality = $request->input('locality');
                $warehouse->subLocality = $request->input('sublocality');
                $warehouse->state = $request->input('state');
                $warehouse->cp = $request->input('pc');
            }

            $warehouse->date_reg = date ('Y-m-d H:i:s');
            $warehouse->status = 'A';
            $warehouse->save();

            if (isset($request->org)) {
                $orgwh = OrgWarehouse::getConnect('W');
                $orgwh->id_wh = $warehouse->id;
                $orgwh->id_org = $request->org;
                $orgwh->save();
            }

            $emailList = $request->input('users_email_list');

            if (strlen($emailList) > 0) {
                $list = explode(';', $emailList);
                foreach ($list as $item) {
                    $userWarehouse = UserWarehouse::getConnect('W');
                    $userWarehouse->users_email = $item;
                    $userWarehouse->warehouses_id = $warehouse->id;
                    $userWarehouse->date_reg = date ('Y-m-d H:i:s', time());
                    $userWarehouse->status = 'A';
                    $userWarehouse->save();
                }
            }

            return 'La bodega se agrego exitosamente';

        } else {
            return 'Usted no posee permisos para realizar esta operación';

        }

	}

	public function update (Request $request, $id){
        if(User::hasPermission (session('user.email'), 'A1W-UWH')){
            $warehouse = Warehouse::getConnect('W')->find($id);
            $warehouse->name = $request->name;
            $warehouse->address = $request->address;
            $warehouse->phone = $request->phone;
            $warehouse->lat = $request->lat;
            $warehouse->lng = $request->lng;
            $myPoint = $request->position;
            $warehouse->position = \DB::raw($myPoint);
            $warehouse->status = $request->status;

            if(!empty($request->input('group_log'))){
                $warehouse->group = $request->input('group_log');
                $warehouse->route = $request->input('route');
                $warehouse->street_number = $request->input('street_n');
                $warehouse->neighborhood = $request->input('neighb');
                $warehouse->locality = $request->input('locality');
                $warehouse->subLocality = $request->input('sublocality');
                $warehouse->state = $request->input('state');
                $warehouse->cp = $request->input('pc');
            }else{
                $warehouse->group = null;
            }

            $warehouse->save();

            if (OrgWarehouse::getConnect('R')->where('id_wh', $id)->count() > 0){
                if(!empty($request->org))
                    OrgWarehouse::getConnect('W')->where('id_wh', $id)->update(['id_org'=>$request->org]);
                else
                    OrgWarehouse::getConnect('W')->where('id_wh', $id)->update(['id_org' => null]);
            }else{
                if(!empty($request->org)){
                    $orgwh = OrgWarehouse::getConnect('W');
                    $orgwh->id_wh = $id;
                    $orgwh->id_org = $request->org;
                    $orgwh->save();
                }
            }

            UserWarehouse::getConnect('W')->where('warehouses_id', $id)->update(['status' => 'T']);

            $emailList = $request->users_email_list;
            if (strlen($emailList) > 0) {

                $list = explode(';', $emailList);
                foreach ($list as $item) {
                    $test = UserWarehouse::getConnect('R')->where(['users_email' => $item, 'warehouses_id' => $id])->count();
                    if($test == 0){
                        $new = UserWarehouse::getConnect('W');
                        $new->users_email = $item;
                        $new->warehouses_id = $id;
                        $new->date_reg = date ('Y-m-d H:i:s', time());
                        $new->status = 'A';
                        $new->save();
                        return 'creo';
                    }else{
                        UserWarehouse::getConnect('W')->where(['users_email' => $item, 'warehouses_id' => $warehouse->id])->update(['status' => 'A']);
                    }
                }
            }
            return 'La bodega se actualizo exitosamente';
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
	}

	public function destroy ($id){
        if(User::hasPermission (session('user.email'), 'A1W-DWH')){
            $userWarehouses = UserWarehouse::getConnect('W')->where('warehouses_id', $id)->update(['status' => 'T']);
            $warehouse = Warehouse::getConnect('W')->find($id);
            $warehouse->status = 'T';
            $warehouse->save();
            if(OrgWarehouse::getConnect('R')->where('id_wh', $id)->count() > 0){
                //OrgWarehouse::getConnect('W')->where('id_wh', $id)->update(['id_org'=>$request->org,'status'=>'T']);
                OrgWarehouse::getConnect('W')->where('id_wh', $id)->update(['id_org' => null,'status' => 'T']);
            }
            return response()->json($warehouse);
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
	}
    // bodegas que posee un usuario o sus asociados
    public function uwhs(){
        $usr_arr_email = array();
        $users = User::getConnect('R')->where('parent_email',session('user.email'))->get();
        if(!empty($users)){
            foreach ($users as $user) {
                $usr_arr_email[] = $user->email;
            }
        }
        $usr_arr_email[]= session('user.email');
        $userwhs = UserWarehouse::getConnect('R')->whereIn('users_email',$usr_arr_email)->where('status','A')->get();
        $uwh_arr_id = array();
        foreach ($userwhs as $uwh) {
            $uwh_arr_id[] = $uwh->warehouses_id;
        }
        return $uwh_arr_id;
    }

    // usuarios que pueden crear bodegas
    public function uccwh(){
        $users = User::getUsers()->where('parent_email',session('user.email'))->where('platform','vendor');
        $userx = User::getUser(session('user.email'))->where('platform','vendor');
        if(!empty($userx)){
            $users[]=$userx;
        }
        $usr_arr_email = array();
        foreach ($users as $user) {
            if(!empty($user->policies)){
                foreach ($user->policies as $policy) {
                    if ($policy->code == 'CB0-M1N') {
                        if ($policy->value > $user->wh) {
                            $usr_arr_email[]=$user->email;
                        }
                    }
                }
            }
        }
        return $usr_arr_email;
    }

	public function view (){
        $org_id = session('user.id_org');
        if (session('user.platform')=='admin' && (session('user.profile.id')=='1' || session('user.profile.id')=='3')){
            $warehouses = Warehouse::getConnect('R')->whereIn('status',['A','I'])->get();
            $users = User::getConnect('R')->select('islim_users.email', 'islim_users.name', 'islim_users.last_name')
                            ->join('islim_profile_details',
                              function($join){
                                  $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
                                   ->whereIn('islim_profile_details.id_profile',['3','7']);
                              })
                            //->where('islim_users.platform','vendor')
                            ->get();
        }else{
            $uwh_arr_id = $this->uwhs(); // bodegas que posee un usuario o sus asociados
            $users_arr =$this->uccwh(); // usuarios que pueden crear bodegas
            $warehouses = Warehouse::getConnect('R')->whereIn('id',$uwh_arr_id)->get();
            $users = User::getConnect('R')->select('email', 'name', 'last_name')->where('status','A')->whereIn('email',$users_arr)->get();
        }
        foreach ($warehouses as $warehouse) {
            $organization = OrgWarehouse::getConnect('R')->where('id_wh', $warehouse->id)->first();
            $warehouse->position = 0;
            $warehouse->users = $warehouse->getAssignedUser($warehouse->id);
            $warehouse->org = isset($organization) ? $organization->id_org : null;
        }

        $orgs = Organization::getConnect('R')->when($org_id,function($query,$org_id){
            return $query->where('id', $org_id);
        },function($query){
             return $query;
        })->get();

		$html = view('pages.ajax.warehouses', compact('warehouses', 'users','orgs'))->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}
}
