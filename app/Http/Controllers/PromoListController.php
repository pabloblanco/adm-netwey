<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ListDns;
use App\Helpers\ValidateString;

class PromoListController extends Controller {


    public function view(){
        $promos = ListDns::getConnect('R')->select('id', 'name', 'status', 'lifetime')->orderBy('date_reg', 'desc')->where('status', '!=', 'T')->where('lifetime', '>', 0)->get();

        $html = view('pages.ajax.promo_list', compact('promos'))->render();
        return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
    }

    public function store(Request $request){
        if($request->name != null && $request->status != ''){
            $name = strtoupper(ValidateString::normaliza($request->name));
            $all = ListDns::getConnect('R')->select('name')->where('status', '!=', 'T')->get();
            foreach ($all as $key => $value) {
                if($value->name == $name){
                    return response()->json(array('success' => false, 'msg' => 'No se ha creado la Lista, Nombre ya existe.', 'numError' => 0));
                }
            }
            $list = ListDns::getConnect('W');
            $list->name = $name;
            $list->lifetime = $request->lifetime;
            $list->status = $request->status;
            $list->date_reg = date("Y-m-d H:i:s");
            $list->save();
            return response()->json(array('success' => true, 'msg' => 'Se ha Creado la Lista Satisfactoriamente.', 'numError' => 0));
        }else{
            return response()->json(array('success' => false, 'msg' => 'No se ha creado la Lista, Faltan Datos.', 'numError' => 0));
        }  
    }

    public function update(Request $request){
        if($request->id != null && $request->name != null && $request->status != ''){
            $list = ListDns::getConnect('W')->find($request->id);
            if($list != null){
                $oldname = strtoupper(ValidateString::normaliza($list->name));
                $name = strtoupper(ValidateString::normaliza($request->name));
                if($oldname != $name){
                    $all = ListDns::getConnect('R')->select('name')->where('status', '!=', 'T')->get();
                    foreach ($all as $key => $value) {
                        if($value->name == $name){
                            return response()->json(array('success' => false, 'msg' => 'No se ha creado la Lista, Nombre ya existe.', 'numError' => 0));
                        }
                    }
                    $list->name = $name;
                }else{
                    $list->name = $oldname;
                }
                $list->lifetime = $request->lifetime;
                $list->status = $request->status;
                $list->save();
                return response()->json(array('success' => true, 'msg' => 'Se ha Actualizado la Lista Satisfactoriamente.', 'numError' => 0));
            }else{
                return response()->json(array('success' => false, 'msg' => 'No se ha Actualizado la Lista, Lista no Encontrada.', 'numError' => 0));
            }
        }else{
            return response()->json(array('success' => false, 'msg' => 'No se ha Actualizado la Lista, Faltan Datos.', 'numError' => 0));
        }  
    }
}
