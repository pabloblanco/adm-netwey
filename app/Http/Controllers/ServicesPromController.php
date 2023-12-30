<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ServicesProm;
use App\Service;
use App\Pack;

class ServicesPromController extends Controller {

    public function view () {
        $services_prom = ServicesProm::select('islim_services_prom.*')
                        ->selectRaw("CONCAT(islim_services.title,' (',islim_services.description,')') as service_aso")
                        ->where('islim_services_prom.status','<>','T')
                        ->join('islim_services', function($join){
                                $join->on('islim_services.id', '=', 'islim_services_prom.service_id')
                                     ->where('islim_services.status', 'A');
                            })
                        ->get();

        $services = Service::getActiveServiceByType();
      
        $html = view(
                        'pages.ajax.services_prom',
                        compact(
                            'services_prom','services'                            
                        )
                    )->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }


    public function store(Request $request){
        
        try {
            if(!empty($request->name) && 
               !empty($request->service_id) && 
               !empty($request->qty) &&
               !empty($request->status)
              ){

                $service_prom = ServicesProm::getConnect('W')
                                            ->where('name',$request->name)
                                            ->first();

                if($service_prom){
                    if($service_prom->status != 'T'){ 
                        if($service_prom->name == $request->name)
                            return response()->json(array('success' => false, 'msg'=>'No se puede crear un nuevo servicio promocional con el nombre : "'.$request->name.'", este nombre ya está en uso', 'numError'=>0));                        
                    }
                }
                else{
                    $service_prom = new ServicesProm;
                    $service_prom->getConnect('W');
                }

                
                $service_prom->name = $request->name;
                $service_prom->service_id = $request->service_id;
                $service_prom->qty = $request->qty;              
                $service_prom->status = $request->status;

                if(!empty($request->period_days))
                    $service_prom->period_days = $request->period_days;

                $service_prom->save();

                return response()->json(array('success' => true, 'msg'=>'El servicio promocional: "'. $service_prom->name .'" se ha creado con exito', 'numError'=>0));
            }

            return response()->json(array('success' => false, 'msg'=>'Hubo un error creando el servicio', 'numError'=>0));
            
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error creando el servicio', 'errorMsg' => $e, 'numError'=>1));
        }
    }

    public function update(Request $request, $id){
        try {
            if(!empty($request->name) && 
               !empty($request->service_id) && 
               !empty($request->qty) &&
               !empty($request->status)
            ){
                
                $service_prom = ServicesProm::getConnect('W')->find($id);
                if($service_prom){

                    $service_prom_val = ServicesProm::getConnect('W')
                                                ->where('id','<>',$id)
                                                ->where('name',$request->name)                      
                                                ->first();

                    if($service_prom_val){
                        if($service_prom_val->status != 'T'){ 
                            if($service_prom_val->name == $request->name)
                                return response()->json(array('success' => false, 'msg'=>'No se puede actualizar el servicio promocional con el nombre : "'.$request->name.'", este nombre ya está en uso', 'numError'=>0));                            
                        }
                        else{                           
                            if($service_prom_val->name == $request->name){
                                $service_prom_val->name = substr(time().'-'.$service_prom_val->name, 0, 45);
                                $service_prom_val->save();
                            }
                        }
                    }


                    $service_prom->name = $request->name;
                    $service_prom->service_id = $request->service_id;
                    $service_prom->qty = $request->qty;              
                    $service_prom->status = $request->status;

                    if(!empty($request->period_days))
                    $service_prom->period_days = $request->period_days;

                    $service_prom->save();  



                    if($request->status == 'I'){
                        Pack::where('service_prom_id', $service_prom->id)
                            ->update(['service_prom_id' => null]);
                    }              

                    return response()->json(array('success' => true, 'msg'=>'El servicio promocional: "'. $service_prom->name .'" ha sido actualizado con exito', 'numError'=>0));
                }
            }


            return response()->json(array('success' => false, 'msg'=>'Hubo un error actualizando el servicio blim', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error actualizando el servicio blim', 'errorMsg' => $e, 'numError'=>1));
        }
    }

    public function destroy(Request $request, $id) {
        try {            
            $service_prom = ServicesProm::getConnect('W')->find($id);
            $service_prom->status = 'T';
            $service_prom->save();
           
            Pack::where('service_prom_id', $service_prom->id)
                            ->update(['service_prom_id' => null]);

            return response()->json(array('success' => true, 'msg'=>'El servicio promocional: '. $service_prom->name .' ha sido eliminado con exito', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error eliminando el servicio promocional', 'errorMsg' => $e, 'numError'=>1));
        }
    }

}