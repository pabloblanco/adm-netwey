<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BlimService;
use App\Service;

class BlimServiceController extends Controller {

    public function view () {
        $blimservices = BlimService::where('status','<>','T')->get();
      
        $html = view(
                        'pages.ajax.blim_services',
                        compact(
                            'blimservices'                            
                        )
                    )->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }


    public function store(Request $request){
        
        try {
            if(!empty($request->name) && 
               !empty($request->description) && 
               !empty($request->sku) &&
               !empty($request->status)
              ){

                $blimservice = BlimService::getConnect('W')
                                            ->where('sku',$request->sku)
                                            ->orWhere('name',$request->name)
                                            ->first();

                if($blimservice){
                    if($blimservice->status != 'T'){                     
                        if($blimservice->sku == $request->sku)
                            return response()->json(array('success' => false, 'msg'=>'No se puede crear el servicio blim con el SKU: "'.$request->sku.'", este SKU ya está en uso', 'numError'=>0));
                       
                        if($blimservice->name == $request->name)
                            return response()->json(array('success' => false, 'msg'=>'No se puede crear un nuevo servicio blim con el nombre : "'.$request->name.'", este nombre ya está en uso', 'numError'=>0));                        
                    }
                }
                else{
                    $blimservice = new BlimService;
                    $blimservice->getConnect('W');
                }

                
                $blimservice->name = $request->name;
                $blimservice->description = $request->description;
                $blimservice->sku = $request->sku;
              
                $blimservice->status = $request->status;

                if(!empty($request->price))
                    $blimservice->price = $request->price;

                $blimservice->save();

                return response()->json(array('success' => true, 'msg'=>'El servicio blim: "'. $blimservice->name .'" se ha creado con exito', 'numError'=>0));
            }

            return response()->json(array('success' => false, 'msg'=>'Hubo un error creando el servicio', 'numError'=>0));
            
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error creando el servicio', 'errorMsg' => $e, 'numError'=>1));
        }
    }

    public function update(Request $request, $id){
        try {
            if(!empty($request->name) && 
               !empty($request->description) && 
               !empty($request->sku) &&
               !empty($request->status)
            ){
                
                $blimservice = BlimService::getConnect('W')->find($id);
                if($blimservice){

                    $blimservice_val = BlimService::getConnect('W')
                                                ->where('id','<>',$id)
                                                ->where(function ($query) use ($request) {
                                                    $query->where('sku',$request->sku)
                                                    ->orWhere('name',$request->name);
                                                })
                                                ->first();

                    if($blimservice_val){
                        if($blimservice_val->status != 'T'){                     
                            if($blimservice_val->sku == $request->sku)
                                return response()->json(array('success' => false, 'msg'=>'No se puede actualizar el servicio blim con el SKU: "'.$request->sku.'", este SKU ya está en uso', 'numError'=>0));
                           
                            if($blimservice_val->name == $request->name)
                                return response()->json(array('success' => false, 'msg'=>'No se puede actualizar el servicio blim con el nombre : "'.$request->name.'", este nombre ya está en uso', 'numError'=>0));                            
                        }
                        else{
                            if($blimservice_val->sku == $request->sku)
                                $blimservice_val->sku = substr(time().'-'.$blimservice_val->sku, 0, 45);
                            if($blimservice_val->name == $request->name)
                                $blimservice_val->name = substr(time().'-'.$blimservice_val->name, 0, 45);
                            $blimservice_val->save();
                        }
                    }


                    $blimservice->name = $request->name;
                    $blimservice->description = $request->description;
                    $blimservice->sku = $request->sku;
                    $blimservice->status = $request->status; 
                    if(!empty($request->price))
                        $blimservice->price = $request->price;              
                    else
                        $blimservice->price = null; 
                    $blimservice->save();  

                    if($request->status == 'I'){
                        Service::where('status', 'A')
                            ->where('blim_service',$id)
                            ->update(['status' => 'I']);
                    }              

                    return response()->json(array('success' => true, 'msg'=>'El servicio blim: "'. $blimservice->name .'" ha sido actualizado con exito', 'numError'=>0));
                }
            }


            return response()->json(array('success' => false, 'msg'=>'Hubo un error actualizando el servicio blim', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error actualizando el servicio blim', 'errorMsg' => $e, 'numError'=>1));
        }
    }

    public function destroy(Request $request, $id) {
        try {
            
            $blimservice = BlimService::getConnect('W')->find($id);
            $blimservice->status = 'T';
            $blimservice->save();
           
            Service::where('status', 'A')
                ->where('blim_service',$id)
                ->update(['status' => 'I']);
              

            return response()->json(array('success' => true, 'msg'=>'El servicio Blim '. $blimservice->name .' ha sido eliminado con exito', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error eliminando el servicio Blim', 'errorMsg' => $e, 'numError'=>1));
        }
    }

}