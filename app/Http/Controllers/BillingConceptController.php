<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BillingConcept;
use App\User;
use App\Service;
use App\Pack;
use DataTables;
use Log;

class BillingConceptController extends Controller {

    public function view () {
        //$billingConcepts = BillingConcept::get();
        $services = Service::select('id')->orderBy('id')->get();
        $packs = Pack::select('id')->orderBy('id')->get();
      
        $html = view(
            'pages.ajax.billing_concepts',
            compact(
                'services',
                'packs'
            )
        )->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function listDT(Request $request){

        $lists = BillingConcept::whereNotNull('id');
        return DataTables::eloquent($lists)
                            ->addColumn('action', function( $list ){
                                return User::hasPermission(session('user')->email,'BIL-UBC');
                            })
                            ->editColumn('id', function($list){
                                return $list->id;
                            })
                            ->editColumn('shipping', function($list){
                                if($list->shipping == 'Y') return 'Si';
                                return 'No';
                            })
                            ->editColumn('is_financed', function($list){
                                if($list->is_financed == 'Y') return 'Si';
                                return 'No';
                            })
                            ->make(true);
    }


    public function store(Request $request){
        try {
            if(!empty($request->nro_identification) &&
               !empty($request->description) &&
               !empty($request->unit_key) &&
               !empty($request->service_id) &&
               !empty($request->product_key) &&
               !empty($request->shipping) &&
               !empty($request->is_financed)
              ){

                $billingconcept = BillingConcept::where([
                                                ['nro_identification',$request->nro_identification],
                                                ['service_id',$request->service_id],
                                                ['pack_id',$request->pack_id],
                                                ['shipping',$request->shipping],
                                                ['is_financed',$request->is_financed],
                                            ])
                                            ->first();

                if($billingconcept){
                    return response()->json(array('success' => false, 'msg'=>'No se puede crear el concepto de factura, ya existe otro con los mismo datos claves, por favor revise el concepto con el id # '.$billingconcept->id, 'numError'=>0));
                }

                $billingconcept = new BillingConcept;
                $billingconcept->getConnect('W');
                $billingconcept->nro_identification = $request->nro_identification;
                $billingconcept->description = $request->description;
                $billingconcept->unit_key = $request->unit_key;
                if(!empty($request->unit))
                    $billingconcept->unit = $request->unit;
                $billingconcept->service_id = $request->service_id;
                if(!empty($request->pack_id))
                    $billingconcept->pack_id = $request->pack_id;
                $billingconcept->product_key = $request->product_key;
                $billingconcept->shipping = $request->shipping;
                $billingconcept->is_financed = $request->is_financed;
                $billingconcept->save();

                return response()->json(array('success' => true, 'msg'=>'El Concepto de Facturación: "'. $billingconcept->description .'" se ha creado con exito', 'numError'=>0));
            }

            return response()->json(array('success' => false, 'msg'=>'Hubo un error creando el concepto de facturación', 'numError'=>0));
            
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error creando el concepto de facturación', 'errorMsg' => $e, 'numError'=>1));
        }
    }

    public function update(Request $request, $id){
        try {
            if(!empty($request->nro_identification) &&
               !empty($request->description) &&
               !empty($request->unit_key) &&
               !empty($request->service_id) &&
               !empty($request->product_key) &&
               !empty($request->shipping) &&
               !empty($request->is_financed)
              ){
                
                $billingconcept = BillingConcept::getConnect('W')->find($id);
                if($billingconcept){

                    $billingconcept_val = BillingConcept::getConnect('W')
                                                ->where('id','<>',$id)
                                                ->where([
                                                    ['nro_identification',$request->nro_identification],
                                                    ['service_id',$request->service_id],
                                                    ['pack_id',$request->pack_id],
                                                    ['shipping',$request->shipping],
                                                    ['is_financed',$request->is_financed],
                                                ])
                                                ->first();

                    if($billingconcept_val){
                        return response()->json(array('success' => false, 'msg'=>'No se puede crear el concepto de factura, ya existe otro con los mismo datos claves, por favor revise el concepto con el id # '.$billingconcept_val->id, 'numError'=>0));
                    }


                    $billingconcept->nro_identification = $request->nro_identification;
                    $billingconcept->description = $request->description;
                    $billingconcept->unit_key = $request->unit_key;
                    if(!empty($request->unit))
                        $billingconcept->unit = $request->unit;
                    else
                        $billingconcept->unit = null;
                    $billingconcept->service_id = $request->service_id;
                    if(!empty($request->pack_id))
                        $billingconcept->pack_id = $request->pack_id;
                    else
                        $billingconcept->pack_id = null;
                    $billingconcept->product_key = $request->product_key;
                    $billingconcept->shipping = $request->shipping;
                    $billingconcept->is_financed = $request->is_financed;
                    $billingconcept->save();

                    return response()->json(array('success' => true, 'msg'=>'El Concepto de Facturación: "'. $billingconcept->description .'" ha sido actualizado con exito', 'numError'=>0));
                }
            }


            return response()->json(array('success' => false, 'msg'=>'Hubo un error actualizando el concepto de facturación', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => false, 'msg'=>'Hubo un error actualizando el concepto de facturación', 'errorMsg' => $e, 'numError'=>1));
        }
    }

}