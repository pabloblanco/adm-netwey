<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProductsProvider;
use App\User;

class ProductsProviderController extends Controller {
	public function index (){
		$providers = ProductsProvider::getConnect('R')->all();
        return response()->json($providers);
	}

	public function show ($dni){
		$provider = ProductsProvider::getConnect('R')->find($dni);
        return response()->json($provider);
	}

	public function store (Request $request){
        if(User::hasPermission (session('user.email'), 'A1P-CPV')){
			$test = ProductsProvider::getConnect('R')->find($request->dni);
			if(empty($test)){
				$provider = ProductsProvider::getConnect('W')->create($request->input());
		        $provider->date_reg = date ('Y-m-d H:i:s', time());
		        $provider->dni = $request->rfc;
		        $provider->save();
		        return 'El proveedor se ha creado con exito';
			}elseif($test->status=='T'){
				$this->update($request,$test->dni);
		        return 'El proveedor se ha creado con exito';
			}else{
				return 'El proveedor ya existe';
			}
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
	}

	public function update (Request $request, $dni){

        if(User::hasPermission (session('user.email'), 'A1P-UPV')){
			$provider = ProductsProvider::getConnect('W')->find($dni);
	        $provider->rfc = $request->rfc;
	        $provider->name = $request->name;
	        $provider->business_name = $request->business_name;
	        $provider->address = $request->address;
	        $provider->email = $request->email;
	        $provider->phone = $request->phone;
	        $provider->responsable = $request->responsable;
	        $provider->status = $request->status;
	        $provider->save();
	        return 'El proveedor se ha actualizado con exito';
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
	}

	public function destroy ($dni) {
        if(User::hasPermission (session('user.email'), 'A1P-DPV')){
			$provider = ProductsProvider::getConnect('W')->where('dni', $dni)->update(['status'=>'T']);
			return response()->json($provider);
        } else {
            return 'Usted no posee permisos para realizar esta operación';
        }
	}

	public function view (){
		if (session('admin')){
			$providers = ProductsProvider::getConnect('R')->whereIn('status',['A','I'])->get();
		}else{
			$providers = ProductsProvider::getConnect('R')->where('status','A')->get();
		}
		$html = view('pages.ajax.provider', compact('providers'))->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}
}
