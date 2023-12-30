<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Organization;
use DataTables;

class ProfileController extends Controller {

	public function store(Request $request){
		if (Organization::where('rfc',$request->rfc)->count() > 0){
			return response()->json(array('status' => 'error', 'message'=>'El RFC ya se encuentra registrado, verifique e intente de nuevo', 'numError'=>001));
		}else{
			$organization = new Organization;
			$organization->rfc =$request->rfc;
			$organization->business_name =$request->business_name;
			$organization->address =$request->address;
			$organization->status =$request->status;
			$organization->save();
			return response()->json(array('status' => 'success', 'message'=>'La organizacion fue creada exitosamente', 'numError'=>000));
		}
	}
	public function update(Request $request, $rfc){
		$organization = Organization::first($rfc);
		$organization->rfc = $request->rfc;
		$organization->business_name = $request->business_name;
		$organization->address = $request->address;
		$organization->status = $request->status;
		$organization->save();
		return response()->json(array('status' => 'success', 'message'=>'La organizacion fue actualizada exitosamente', 'numError'=>000));
	}
	public function delete($rfc){
		Organization::first($rfc)->update(['status'=>'T']);
		return response()->json(array('status' => 'success', 'message'=>'La organizacion fue eliminada exitosamente', 'numError'=>000));
	}
	public function view(){
		$html = view('pages.ajax.organization.organization')->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}
	public function getorganizationdt(){
        $organization = Organization::where('status','A');
        return DataTables::eloquent($organization)->make();
    }
    public function assignResponsible($rfc,$email){
    	$organization = Organization::where('rfc',$rfc)->first();
    	$user = User::where('email',$email)->first();
    	$resposible = new Resposible;
    	$resposible->id_org = $organization->id;
    	$resposible->name = $user->name;
    	$resposible->phone = $user->phone;
    	$resposible->email = $email;
    	$resposible->position = 'master';
    	$resposible->status = 'A';
    	$resposible->save();
    	return response()->json(array('status' => 'success', 'message'=>'Se ha asociado exitosamente un Responsable', 'numError'=>000));
    }
}