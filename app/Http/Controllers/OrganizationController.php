<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Organization;
use DataTables;
use App\ProfileDetail;
use App\Responsible;

class OrganizationController extends Controller {

	public function store(Request $request){
		if (Organization::getConnect('R')->where('rfc',$request->rfc)->count() > 0){
			return response()->json(array('status' => 'error', 'message'=>'El RFC ya se encuentra registrado, verifique e intente de nuevo', 'numError'=>001));
		}else{
			$organization = Organization::getConnect('W');
			$organization->rfc = $request->rfc;
			$organization->business_name =$request->business_name;
			$organization->address = $request->address;
			$organization->status = $request->status;
			$organization->contact_name = $request->contact_name;
			$organization->contact_email = $request->contact_email;
			$organization->contact_address = $request->contact_address;
			$organization->contact_phone = $request->contact_phone;
			$organization->type = $request->type;
			$organization->save();
			return response()->json(array('status' => 'success', 'message'=>'La organizacion fue creada exitosamente', 'numError'=>000));
		}
	}

	public function update(Request $request, $rfc){
		$organization = Organization::getConnect('W')->where('rfc', $rfc)->first();
		$organization->rfc = $request->rfc;
		$organization->business_name = $request->business_name;
		$organization->address = $request->address;
		$organization->status = $request->status;
		$organization->contact_name = $request->contact_name;
		$organization->contact_email = $request->contact_email;
		$organization->contact_address = $request->contact_address;
		$organization->contact_phone = $request->contact_phone;
		$organization->type = $request->type;
		$organization->save();

		//Actualizando responsable de la organizacion
		if(!empty($request->responsible)){
			//$organization = Organization::where('rfc',$request->rfc)->first();
	    	$respon = Responsible::getConnect('W')->select('id')->where('id_org',$organization->id)->first();
	    	$user = User::getConnect('R')->where('email',$request->responsible)->first();
	    	if(!empty($respon)){
	    		$respon->name = $user->name.' '.$user->last_name;
		    	$respon->phone = $user->phone;
		    	$respon->email = $user->email;
		    	$respon->save();
	    	}else{
		    	$resposible = Responsible::getConnect('W');
		    	$resposible->id_org = $organization->id;
		    	$resposible->name = $user->name.' '.$user->last_name;
		    	$resposible->phone = $user->phone;
		    	$resposible->email = $user->email;
		    	$resposible->position = 'Gerente Comercial';
		    	$resposible->status = 'A';
		    	$resposible->save();
	    	}
		}
		return response()->json(array('status' => 'success', 'message'=>'La organizacion fue actualizada exitosamente', 'numError'=>000));
	}

	public function delete($rfc){
		Organization::getConnect('W')->where('rfc',$rfc)->update(['status'=>'T']);
		return response()->json(array('status' => 'success', 'message'=>'La organizacion fue eliminada exitosamente', 'numError'=>000));
	}

	public function view(){
		$prospect = array();
		$resposible = Responsible::getConnect('R')->all()->pluck('email')->toArray();
		$usersP = ProfileDetail::getConnect('R')->whereNotIn('user_email',$resposible)->where('id_profile','9')->get();
		foreach ($usersP as $key) {
			$prospect[] = $key->user_email;
		}
		$users = User::getConnect('R')->whereIn('email',$prospect)->where('id_org', null)->get();
		$html = view('pages.ajax.organization.organization', compact('users'))->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getorganizationdt(){
        $organization = Organization::getConnect('R')->where('status','A');
        return DataTables::eloquent($organization)
			->addColumn('action_edit', function( $organization ){
                return User::hasPermission(session('user')->email,'ORG-EDT');
            })
            ->addColumn('action_delete', function( $organization ){
                return User::hasPermission(session('user')->email,'ORG-DEL');
            })
			->make();
    }

    public function resposible($rfc){
    	$data = new \stdClass();
    	$data->error = true;
    	$org = Organization::getConnect('R')->select('id')->where('rfc',$rfc)->first();

    	if(!empty($org)){
	    	$data->resposible = Responsible::getConnect('R')->select('name','email')->where('id_org',$org->id)->first();
		    $data->users = User::getConnect('R')->select('name','last_name','email')->where('id_org',$org->id)->get();
		    $data->error = false;
	    }
	    return response()->json($data);
    }
}