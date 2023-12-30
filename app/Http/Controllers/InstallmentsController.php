<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TokensInstallments;
use App\ConfigIstallments;
use App\SaleInstallment;
use App\Sale;
use App\User;

class InstallmentsController extends Controller
{
	//Vista de configuracion para venta en abonos
    public function configView(){
    	//Cargando dias de la semana
    	$days = explode(',', env('INSTALLMENT_DAYS'));

    	//Cargando ultima configuracion activa
    	$config = ConfigIstallments::where('status', 'A')
    								 ->orderBy('date_reg', 'DESC')
    								 ->first();

    	$html = view('pages.ajax.installments.config', compact('config', 'days'))->render();

		return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
    }

    /*
    Guarda la configuración, por ahora solo edita el porcentaje para el calculo de los modems que se puede vender en abonos

    NOTA IMPORTANTE: Solo debe estar una configuracion activa a la vez, no se deben editar las configuraciones, se marca como eliminada y se crea una nueva, No se pueden Borrar las configuraciones.
    */
    public function configSave(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		if(!empty($request->porc)){
    			//Busca la ultima configuracion activa
	    		$configP = ConfigIstallments::where('status', 'A')
		    								 ->orderBy('date_reg', 'DESC')
		    								 ->first();

	    		//Marca como eliminadas todas las configuraciones guardadas
	    		ConfigIstallments::where('status', 'A')->update(['status' => 'T']);

	    		//Guardando la nueva configuracion
	    		ConfigIstallments::insert([
	    			'percentage' => !empty($request->porc) ? $request->porc : $configP->percentage,
	    			'end_day' => !empty($configP) ? $configP->end_day : 1,
	    			'week_sales' => !empty($configP) ? $configP->week_sales : 3,
	    			'days_quote' => !empty($configP) ? $configP->days_quote : 7,
					'quotes' => !empty($request->numberq) ? $request->numberq : $configP->quotes,
					'firts_pay' => !empty($request->porcq) ? $request->porcq : $configP->firts_pay,
					'm_permit_c' => !empty($request->qpc) ? $request->qpc : $configP->m_permit_c,
					'm_permit_s' => !empty($configP) ? $configP->m_permit_s : 1,
	    			'user_reg' => session('user')->email,
	    			'date_reg' => date('Y-m-d H:i:s'),
	    			'status' => 'A'
	    		]);

	    		return response()->json(['success' => true]);
	    	}

	    	return response()->json(['success' => false]);
    	}
    }

    //Vista de asignacion de modems para venta en abonos
    public function assignedView(){
    	$html = view('pages.ajax.installments.assign')->render();

		return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
    }

    //Busca coordinadores
    public function findUser(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		if(!empty($request->q)){
    			$fnd = $request->q;
    			$coords = User::select('name', 'last_name', 'email')
    							->where([
                                            ['status', 'A'],
                                            ['platform', 'coordinador']
                                        ])
    							->where(function($query) use($fnd){
    								$query->where('name', 'like', $fnd.'%')
    									  ->orWhere('last_name', 'like', $fnd.'%');
    							})
    							->limit(10)
            					->get();

            	return response()->json(array('success' => true, 'coords' => $coords));
            }
    	}
    }

    //Consulta el coordinador seleccionado para recrear el calculo de las ventas tomadas en cuenta para obtener el numero de modems que puede vender en abono 
    public function consultCoordinador(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		if(!empty($request->coord)){
    			$coordinador = $request->coord;
    			//Buscando si el coordinador tiene asignacion activa
    			$data = TokensInstallments::where([
    										['status', 'A'],
    										['assigned_user', $coordinador]
    									  ])
    									  ->first();

    			if(empty($data))
    				return response()->json([
    											'success' => false,
    											'msg' => 'No se consiguio información para el coordinador seleccionado.'
    										]);

    			//cargando configuracion que se uso para el calculo de modems
    			$config = ConfigIstallments::where('id', $data->config_id)->first();

    			//Ultimo dia tomado en cuenta para el numer de ventas
    			$dateSalesF = strtotime('-1 day', strtotime($data->date_reg));

    			$salesD = [];

    			//Ciclo para calcular las ventas el numero de semanas configurado
                for($i = 0; $i < $config->week_sales; $i++){
                	//Limite inferior de la fecha
                    $dateSalesI = strtotime('-6 day', $dateSalesF);

                    //consultando vendedores
                    $sellers = User::select('email')
                                       ->where([
                                            ['status', 'A'],
                                            ['platform', 'vendor'],
                                            ['parent_email', $coordinador]
                                        ])
                                       ->get();

                    $sellers = $sellers->pluck('email');

                    //Calculando ventas
                    $sales = Sale::select('id')
                                   ->where([
                                    ['type', 'P'],
                                    ['date_reg', '>=', date('Y-m-d', $dateSalesI).' 00:00:00'],
                                    ['date_reg', '<=', date('Y-m-d', $dateSalesF).' 23:59:59']
                                   ])
                                   ->whereIn('status', ['A', 'E']);

                    //Si el coordinador tiene vendedores se buscan ventas de el y sus vendedores
                    if(count($sellers))
                        $sales = $sales->where(function($query) use ($coordinador, $sellers){
                                    $query->where('users_email', $coordinador)
                                          ->orWhereIn('users_email', $sellers);
                                   });
                    else
                        $sales = $sales->where('users_email', $coordinador);

                    $salesD []= [
                    	'date_beg' => date('d-m-Y', $dateSalesI),
                    	'date_end' => date('d-m-Y', $dateSalesF),
                    	'count' => $sales->count()
                    ];

                    //Limite superior de la fecha
                    $dateSalesF = strtotime('-7 day', $dateSalesF);
                }

                $html = view('pages.ajax.installments.assign_detail', compact('salesD', 'data'))->render();

    			return response()->json(['success' => true, 'html' => $html]);
    		}

    		return response()->json(['success' => false, 'msg' => 'No se pudo procesar el request.']);
    	}
    }

    //Asigna modems al coordinador
    public function assignedCoord(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		if(!empty($request->coordinador) && (!empty($request->quantity) || $request->quantity === 0)){
    			//Buscando si el coordinador tiene asignacion activa
    			$data = TokensInstallments::where([
    										['status', 'A'],
    										['assigned_user', $request->coordinador]
    									  ])
    									  ->first();

    			if(!empty($data) && $request->quantity <= $data->tokens_cron){
    				//Calculo de ventas en abono abiertas
    				$sp = SaleInstallment::select('id')
    									 ->where([
    									 	['status', 'P'],
    									 	['coordinador', $request->coordinador]
    									 ])
    									 ->get()
    									 ->count();

    				if($request->quantity === 0)
						$data->tokens_available = 0;
					else
						$data->tokens_available = $request->quantity - $sp;

    				/*if(empty($data->process_user)){
    					$data->tokens_available = $request->quantity - $sp;
    				}
    				else{
    					if($request->quantity === 0)
    						$data->tokens_available = 0;
    					else{
    						$dif = $data->tokens_assigned - $request->quantity;
    						$data->tokens_available = $data->tokens_available - $dif;
    					}
    				}*/

    				$data->date_update = date('Y-m-d H:i:s');
    				$data->tokens_assigned = $request->quantity;
    				$data->process_user = session('user')->email;

    				$data->save();

    				return response()->json(['success' => true]);
    			}
    		}

    		return response()->json(['success' => false]);
    	}
    }
}
