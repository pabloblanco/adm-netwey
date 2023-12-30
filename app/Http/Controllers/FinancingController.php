<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Financing;
use App\User;
use DataTables;

class FinancingController extends Controller
{
    public function view(){
        $html = view('pages.ajax.financing.list')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function listDT(Request $request){
		$fs = Financing::whereIn('status', ['A', 'I']);

        return DataTables::eloquent($fs)
        					->editColumn('amount_financing', function($f){
                                return '$'.$f->amount_financing;
                            })
        					->editColumn('total_amount', function($f){
                                return '$'.$f->total_amount;
                            })
                            ->editColumn('SEMANAL', function($f){
                                return '$'.$f->SEMANAL;
                            })
                            ->editColumn('MENSUAL', function($f){
                                return '$'.$f->MENSUAL;
                            })
                            ->editColumn('QUINCENAL', function($f){
                                return '$'.$f->QUINCENAL;
                            })
        					->editColumn('status', function($f){
        						if($f->status == 'A') return 'Activo';
                                if($f->status == 'I') return 'Inactivo';
                                return 'Eliminado';
                            })
                            ->editColumn('date_reg', function($f){
                                return date("d-m-Y", strtotime($f->date_reg));
                            })
                            ->make(true);
    }

    public function create(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		$msg = 'Financiamiento creado exitosamente.';
    		if(!empty($request->financing)){
    			$fin = Financing::where('id', $request->financing)->whereIn('status', ['A','I'])->first();
    			if(empty($fin)){
    				return response()->json(['error' => true, 'msg' => 'No se pudo actualizar el financiamiento.']);
    			}
    			$msg = 'Financiamiento actualizado correctamente.';
    		}
    		else{
    			$fin = new Financing;
    			$fin->date_reg = date('Y-m.d H:i:s');
    		}
    		$fin->name = $request->name;
    		$fin->amount_financing = $request->amountF;
    		$fin->total_amount = $request->amountT;
    		$fin->SEMANAL = $request->pays;
    		$fin->MENSUAL = $request->paym;
            $fin->QUINCENAL = $request->payq;
    		$fin->status = $request->status;
    		$fin->save();

    		return response()->json(['error' => false, 'msg' => $msg]);
    	}
    }

    public function edit(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		$data =  Financing::where('id', $request->financing)->whereIn('status', ['A','I'])->first();

    		if(!empty($data))
    			return response()->json(['error' => false, 'data' => $data]);
    	}
    	return response()->json(['error' => true, 'msg' => 'No se consiguio el financiamiento']);
    }

    public function delete(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		if(!empty($request->financing)){
    			$fin = Financing::select('id')->where('id', $request->financing)->first();
    			if(!empty($fin)){
    				$fin->status = 'T';
    				$fin->save();
    				return response()->json(['error' => false]);
    			}
    		}
    	}
    	return response()->json(['error' => true, 'msg' => 'No se consiguio el financiamiento']);
    }


    public function methodsView() {
        $html = view('pages.ajax.web_management.financing_methods')->render();
        return response()->json(['success' => true, 'msg' => $html, 'numError' => 0]);
    }

    public function getMethodsDiscounts (Request $request){

        if (!User::hasPermission(session('user.email'), 'DMF-RDM'))
            return response()->json(['message' => 'Usted no posee permisos para realizar esta operación.'], 403);

        if (!$request->isMethod('get') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.' ]);



        $discounts = DB::connection('netwey-r')->table('islim_financing_methods')->where('status', 'A');

        if (!empty($request->search))
            $discounts = $discounts->where('method', 'like', '%'.$request->search['value'].'%');

        return @DataTables::of($discounts->get())->toJson();
    }

    public function updateMethodsDiscounts(Request $request) {

        if (!$request->isMethod('post') && !$request->ajax())
            return response()->json([ 'message' => 'El tipo de petición recibida no está permitida.']);
        
        if (!User::hasPermission(session('user.email'), 'DMF-UDM'))
            return response()->json(['message' => 'Usted no posee permisos para realizar esta operación.'], 403);

        $date = date('Y-m-d H:i:s');
        $old = DB::connection('netwey-r')->table('islim_financing_methods')->find($request->method_id);

        $result = DB::connection('netwey-w')
        ->table('islim_financing_methods')
        ->where('id', $request->method_id)
        ->update([
            'discount' => $request->new_amount,
            'date_modif' => $date
        ]);

        if($result > 0){

            DB::connection('netwey-w')
            ->table('islim_history_financing_methods')
            ->insert([
                'financing_methods_id' => $request->method_id, 
                'discount_old' => $old->discount, 
                'discount_new' => $request->new_amount, 
                'date_reg' => $date, 
                'user_netwey' => session('user.email')
            ]);

            return response()->json([
                'success' => true
            ]);
        }
        return response()->json([
            'success' => false
        ]);
    }
}
