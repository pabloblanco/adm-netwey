<?php

namespace App\Http\Controllers;

ini_set('max_execution_time', 180);

use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Helpers\CommonHelpers;
use Illuminate\Http\Request;
use App\ClientNetwey;
use App\MetricsBi2;
use App\HistoryDC2;
use App\HistoryDC;
use Carbon\Carbon;
use App\Reports;
use App\Service;
use App\Client;
use DataTables;
use App\Sale;
use Illuminate\Support\Facades\Log;

class ReportBIController extends Controller
{
	public function totalBase(){
		//Obteniendo ultima fecha de metricas kpi guarda en la bd para mostrarla en el front
		$date = MetricsBi2::getLastMetric('HBB','R');

		$html = view('pages.ajax.report_bi.total_base', compact('date'))->render();

		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getKPIs(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			if (empty($request->month) && empty($request->year) && empty($request->type))
				return response()->json(['bop' => 0, 'eop' => 0, 'aop' => 0]);

			$date = "$request->month/$request->year";
			$type = $request->type === 'H' ? 'HBB' : $request->type;
			$metrics = MetricsBi2::getMetricByDate($date, $type);

			if (empty($metrics))
				return response()->json(['bop' => 0, 'eop' => 0, 'aop' => 0, 'rec' => 0]);

			return response()->json([
									'bop' => number_format($metrics->BOP, 1),
									'eop' => number_format($metrics->EOP, 1),
									'aop' => number_format($metrics->AOP, 1),
									'rec' => number_format($metrics->REC, 1)
								]);
		}
	}

	public function rechargeBase(){
		$html = view('pages.ajax.report_bi.recharge_base')->render();
		return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
	}

	public function getClientsRechargeBase(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			$clients = [];
			if (!empty($request->month) && !empty($request->year) && !empty($request->type)) {
				$date = "$request->month/$request->year";
				$type = $request->type;
				$clients = Sale::reportRechargeBase($date, $type);
			}

			return DataTables::eloquent($clients)
								->editColumn('name', '{{$name}} {{$last_name}}')
								->editColumn('phone', function($client){
									if(!empty($client->phone)){
										return $client->phone;
									}else{
										return 'N/A';
									}
								})
								->editColumn('phone_home', function($client){
									if(!empty($client->phone_home)){
										return $client->phone_home;
									}else{
										return 'N/A';
									}
								})
								->editColumn('date_reg', function($client){
									return date("d-m-Y", strtotime($client->date_reg));
								})
								->make(true);
		}
	}

	public function downloadClientsRechargeBase(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			if(!empty($request->month) && !empty($request->year)){
				$filters = $request->all();

				$report = Reports::getConnect('W');

	            $report->name_report = 'recharge_base';

	            unset($filters['_token']);

	            $filters['typeC'] = $request->type;

	            $report->filters = json_encode($filters);
	            $report->user_profile = session('user')->profile->type;
	            $report->user = session('user')->email;
	            $report->status = 'C';
	            $report->date_reg = date('Y-m-d H:i:s');

	            $report->save();

	            return response()->json(array('error' => false));
				/*$clients = Sale::reportRechargeBase($request->month.'/'.$request->year, 'H')->get();

				$data []= ['MSISDN', 'Nombre', 'Teléfono', 'Teléfono Oficina', 'Email', 'I.N.E', 'Fecha Registro'];

				foreach ($clients as $client) {
					$data []= [
								$client->msisdn,
								$client->name.' '.$client->last_name,
								empty($client->phone_home) ? 'N/A' : $client->phone_home,
								empty($client->phone) ? 'N/A' : $client->phone,
								empty($client->email) ? 'N/A' : $client->email,
								$client->dni,
								date("d-m-Y", strtotime($client->date_reg))
							  ];
				}

				$url = $this->saveReport($data, 'base_recargadora_'.$request->month.'-'.$request->year);

				return response()->json(array('url' => $url));*/
			}
			return response()->json(array('error' => true));
		}
	}

	public function activeBase(){
		$date = MetricsBi2::getLastMetric('HBB','R');
		$html = view('pages.ajax.report_bi.active_base', compact('date'))->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getClientsActiveBase(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			$clients = [];
			if (!empty($request->month) && !empty($request->year) && !empty($request->type)) {
				$date = "$request->month/$request->year";
				$type = $request->type;
				$clients = HistoryDC2::getClientsByTag(['A90', 'REC'], $type, $date);
			}

			return DataTables::eloquent($clients)
								->editColumn('name', '{{$name}} {{$last_name}}')
								->editColumn('phone', function($client){
									if(!empty($client->phone)){
										return $client->phone;
									}else{
										return 'N/A';
									}
								})
								->editColumn('phone_home', function($client){
									if(!empty($client->phone_home)){
										return $client->phone_home;
									}else{
										return 'N/A';
									}
								})
								->editColumn('email', function($client){
									return !empty($client->email) ? $client->email : 'N/A';
								})
								->editColumn('date_event', function($client){
									return date("d-m-Y", strtotime($client->date_event));
								})
								->editColumn('answer', function($client){
									return (empty($client->answer) || $client->answer == 'N') ? 'No' : 'Si';
								})
								->editColumn('acept', function($client){
									return (empty($client->acept) || $client->acept == 'N') ? 'No' : 'Si';
								})
								->editColumn('comment', function($client){
									return empty($client->comment) ? 'N/A' : $client->comment;
								})
								->editColumn('date_call', function($client){
									return empty($client->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($client->date_call));
								})
								->make(true);
		}
	}

	public function getTotalActiveBase(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			if (empty($request->month) && empty($request->year) && empty($request->type))
				return [];

			$date = $request->month.'/'.$request->year;
			$type = $request->type === 'H' ? 'HBB' : $request->type;

			$data = MetricsBi2::getMetricByDate($date, $type);

			if (empty($data))
				return response()->json(array('total' => 0, 'date' => $date));

			return response()->json(array('total' => $data->A90, 'date' => $date));
		}
	}

	public function downloadClientsActiveBase(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$filters = $request->all();

			$report = Reports::getConnect('W');

            $report->name_report = 'active_base';

            unset($filters['_token']);

            $filters['typeC'] = $request->type;

            $report->filters = json_encode($filters);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));

			/*$date = date('m-Y', strtotime('-1 month', strtotime(date('Y-m-d'))));

			$clients = HistoryDC2::getClientsByTag(['A90', 'REC'], 'H')->get();

			$data []= [
						'MSISDN',
						'Nombre',
						'Teléfono',
						'Teléfono Oficina',
						'Email',
						'I.N.E',
						'Fecha del evento',
						'Contactado',
						'Acepto recompra',
						'Comentario',
						'Fecha llamada'
					];

			foreach ($clients as $client) {
				$phone = empty($client->phone_home) ? 'N/A' : $client->phone_home;
            	$phone2 = empty($client->phone) ? 'N/A' : $client->phone;
            	$email = empty($client->email) ? 'N/A' : $client->email;
            	$date_eve = !empty($client->date_event) ? date("d-m-Y", strtotime($client->date_event)) : 'N/A';
            	$answer = (empty($client->answer) || $client->answer == 'N') ? 'No' : 'Si';
            	$acept = (empty($client->acept) || $client->acept == 'N') ? 'No' : 'Si';
            	$commnet = empty($client->comment) ? 'N/A' : $client->comment;
            	$call_date = empty($client->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($client->date_call));

				$data []= [
							$client->msisdn,
							$client->name.' '.$client->last_name,
							$phone,
							$phone2,
							$email,
							$client->dni,
							$date_eve,
							$answer,
							$acept,
							$commnet,
							$call_date
						  ];
			}

			$url = $this->saveReport($data, 'Active_90_'.$date);

			return response()->json(array('url' => $url));*/
		}
	}

	public function churnTh(){
		$date = MetricsBi2::getLastMetric('HBB','R');

		$html = view('pages.ajax.report_bi.churn_th', compact('date'))->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getClientsChurnTh(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$clients = ClientNetwey::select(
										'islim_client_netweys.msisdn',
										'islim_clients.name',
										'islim_clients.last_name',
										'islim_clients.phone_home',
										'islim_clients.phone',
										'islim_clients.email',
										'islim_clients.dni',
										'islim_clients.date_reg'
									 )
									 ->join(
										'islim_clients',
										'islim_clients.dni',
										'=',
										'islim_client_netweys.clients_dni'
									 )
									 ->where('islim_client_netweys.tag', 'C30')
									 ->whereIn('islim_client_netweys.status', ['A', 'S']);

			return DataTables::eloquent($clients)
								->editColumn('name', '{{$name}} {{$last_name}}')
								->editColumn('phone', function($client){
									if(!empty($client->phone)){
										return $client->phone;
									}else{
										return 'N/A';
									}
								})
								->editColumn('phone_home', function($client){
									if(!empty($client->phone_home)){
										return $client->phone_home;
									}else{
										return 'N/A';
									}
								})
								->editColumn('date_reg', function($client){
									return date("d-m-Y", strtotime($client->date_reg));
								})
								->editColumn('date_churn', function($client){
									$res = HistoryDC::select('date_event')
													  ->where([
														['msisdn', $client->msisdn],
														['status', 'A'],
														['type', 'C30']
													  ])
													  ->orderBy('date_event', 'DESC')
													  ->first();

									if(!empty($res))
										return date('d-m-Y',strtotime($res->date_event));
									else
										return 'N/A';
								})
								->make(true);
		}
	}

	public function getMetricChurnTh(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			if(!empty($request->month) && !empty($request->year)){
				$date = $request->month.'/'.$request->year;

				//Consultando metricas del mes seleccionado
				$metricL = MetricsBi2::getMetricByDate($date);

				if(!empty($metricL) && !empty($metricL->C30)){
					$por = number_format((($metricL->C30 / $metricL->AOP) * 100), 2);

					return response()->json(array('total' => $metricL->C30, 'porcentaje' => $por, 'date' => $date));
				}

				return response()->json(array('total' => 0, 'porcentaje' => 0, 'date' => $date));
			}
		}
	}

	public function downloadChurnTh(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$date = date('m-Y', strtotime('-1 month', strtotime(date('Y-m-d'))));

			$clients = ClientNetwey::select(
										'islim_client_netweys.msisdn',
										'islim_clients.name',
										'islim_clients.last_name',
										'islim_clients.phone_home',
										'islim_clients.phone',
										'islim_clients.email',
										'islim_clients.dni',
										'islim_clients.date_reg'
									 )
									 ->join(
										'islim_clients',
										'islim_clients.dni',
										'=',
										'islim_client_netweys.clients_dni'
									 )
									 ->where('islim_client_netweys.tag', 'C30')
									 ->whereIn('islim_client_netweys.status', ['A', 'S'])
									 ->get();

			$data []= ['MSISDN', 'Nombre', 'Teléfono', 'Teléfono Oficina', 'Email', 'I.N.E', 'Fecha Registro', 'Fecha churn'];

			foreach ($clients as $client){
				$res = HistoryDC::select('date_event')
								  ->where([
									['msisdn', $client->msisdn],
									['status', 'A'],
									['type', 'C30']
								  ])
								  ->orderBy('date_event', 'DESC')
								  ->first();

				$data []= [
							$client->msisdn,
							$client->name.' '.$client->last_name,
							empty($client->phone_home) ? 'N/A' : $client->phone_home,
							empty($client->phone) ? 'N/A' : $client->phone,
							empty($client->email) ? 'N/A' : $client->email,
							$client->dni,
							date("d-m-Y", strtotime($client->date_reg)),
							!empty($res)? date("d-m-Y", strtotime($res->date_event)) : 'N/A'
						  ];
			}

			$url = $this->saveReport($data, 'Churn_30_'.$date);

			return response()->json(array('url' => $url));
		}
	}

	public function churn(){
		$date = MetricsBi2::getLastMetric('HBB','R');

		$html = view('pages.ajax.report_bi.churn', compact('date'))->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getClientsChurn(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			$clients = [];
			if (!empty($request->month) && !empty($request->year) && !empty($request->type)) {
				$date = "$request->month/$request->year";
				$type = $request->type;
				$clients = HistoryDC2::getClientsByTag(['C90'], $type, $date);
			}

			return DataTables::eloquent($clients)
								->editColumn('name', '{{$name}} {{$last_name}}')
								->editColumn('phone', function($client){
									if(!empty($client->phone)){
										return $client->phone;
									}else{
										return 'N/A';
									}
								})
								->editColumn('phone_home', function($client){
									if(!empty($client->phone_home)){
										return $client->phone_home;
									}else{
										return 'N/A';
									}
								})
								->editColumn('email', function($client){
									return !empty($client->email) ? $client->email : 'N/A';
								})
								->editColumn('date_event', function($client){
									return date("d-m-Y", strtotime($client->date_event));
								})
								->editColumn('answer', function($client){
									return (empty($client->answer) || $client->answer == 'N') ? 'No' : 'Si';
								})
								->editColumn('acept', function($client){
									return (empty($client->acept) || $client->acept == 'N') ? 'No' : 'Si';
								})
								->editColumn('comment', function($client){
									return empty($client->comment) ? 'N/A' : $client->comment;
								})
								->editColumn('date_call', function($client){
									return empty($client->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($client->date_call));
								})
								->make(true);
		}
	}

	public function getTotalChurn(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			if (empty($request->month) && empty($request->year) && empty($request->type))
				return [];

			$date = "$request->month/$request->year";
			$type = $request->type === 'H' ? 'HBB' : $request->type;

			//Nueva forma de calcular churn
			//Consultando metricas del mes seleccionado
			$metricL = MetricsBi2::getMetricByDate($date, $type);

			if(!empty($metricL) && !empty($metricL->CN90)){
				$por = number_format((($metricL->CN90 / $metricL->AOP) * 100), 2);

				return response()->json([
									'total' => $metricL->CN90,
									'totalCB' => $metricL->CB90,
									'totalCP' => $metricL->C90,
									'porcentaje' => $por,
									'date' => $date
								]);
			}

			return response()->json([
									'total' => 0,
									'totalCB' => 0,
									'totalCP' => 0,
									'porcentaje' => 0,
									'date' => $date
								]);
		}
	}

	public function downloadChurn(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$filters = $request->all();

			$report = Reports::getConnect('W');

            $report->name_report = 'churn_90';

            unset($filters['_token']);

            $filters['typeC'] = $request->type;

            $report->filters = json_encode($filters);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));

			/*$clients = HistoryDC2::getClientsByTag(['C90'], 'H')->get();

			$data []= [
						'MSISDN',
						'Nombre',
						'Teléfono',
						'Teléfono Oficina',
						'Email',
						'I.N.E',
						'Fecha del evento',
						'Contactado',
						'Acepto recompra',
						'Comentario',
						'Fecha llamada'
					];

			foreach ($clients as $client){
				$phone = empty($client->phone_home) ? 'N/A' : $client->phone_home;
            	$phone2 = empty($client->phone) ? 'N/A' : $client->phone;
            	$email = empty($client->email) ? 'N/A' : $client->email;
            	$date_eve = !empty($client->date_event) ? date("d-m-Y", strtotime($client->date_event)) : 'N/A';
            	$answer = (empty($client->answer) || $client->answer == 'N') ? 'No' : 'Si';
            	$acept = (empty($client->acept) || $client->acept == 'N') ? 'No' : 'Si';
            	$commnet = empty($client->comment) ? 'N/A' : $client->comment;
            	$call_date = empty($client->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($client->date_call));

				$data []= [
							$client->msisdn,
							$client->name.' '.$client->last_name,
							$phone,
							$phone2,
							$email,
							$client->dni,
							$date_eve,
							$answer,
							$acept,
							$commnet,
							$call_date
						  ];
			}

			$url = $this->saveReport($data, 'Churn_90_'.date('Y-m-s h:i:s'));

			return response()->json(array('url' => $url));*/
		}
	}

	public function decay(){
		$date = MetricsBi2::getLastMetric('HBB','R');

		$html = view('pages.ajax.report_bi.decay', compact('date'))->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getClientsDecay(Request $request){
		if ($request->isMethod('post') && $request->ajax()) {
			$clients = [];
			if (!empty($request->year) && !empty($request->month) && !empty($request->type)) {
				$date = "$request->month/$request->year";
				$type = $request->type;
				$clients = HistoryDC2::getClientsByTag(['D90'], $type, $date);
			}

			return DataTables::eloquent($clients)
								->editColumn('name', '{{$name}} {{$last_name}}')
								->editColumn('phone', function($client){
									if(!empty($client->phone)){
										return $client->phone;
									}else{
										return 'N/A';
									}
								})
								->editColumn('phone_home', function($client){
									if(!empty($client->phone_home)){
										return $client->phone_home;
									}else{
										return 'N/A';
									}
								})
								->editColumn('email', function($client){
									return empty($client->email) ? 'N/A' : $client->email;
								})
								->editColumn('date_event', function($client){
									return date("d-m-Y", strtotime($client->date_event));
								})
								->editColumn('answer', function($client){
									return (empty($client->answer) || $client->answer == 'N') ? 'No' : 'Si';
								})
								->editColumn('acept', function($client){
									return (empty($client->acept) || $client->acept == 'N') ? 'No' : 'Si';
								})
								->editColumn('comment', function($client){
									return empty($client->comment) ? 'N/A' : $client->comment;
								})
								->editColumn('date_call', function($client){
									return empty($client->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($client->date_call));
								})
								->make(true);
		}
	}

	public function getTotalDecay(Request $request) {
		if ($request->isMethod('post') && $request->ajax()) {
			if (!empty($request->month) && !empty($request->year) && !empty($request->type)) {
				$date = "$request->month/$request->year";
				$type = $request->type === 'H' ? 'HBB' : $request->type;

				$data = MetricsBi2::getMetricByDate($date, $type);

	            //Primer dia del mes
	            $begin = Carbon::createFromFormat('m/Y', $date)
	            				->startOfMonth()
	            				->subMonths(3);

	            //ultimo dia del mes
	            $endDate = $begin->copy()->endOfMonth();

				//Altas totales hasta la fecha seleccionada
				$TotalUp = Sale::getConnect('R')
								->select('islim_sales.msisdn')
								->join(
									'islim_client_netweys',
									'islim_client_netweys.msisdn',
									'islim_sales.msisdn'
								)
								->where([
									['islim_sales.date_reg', '>=', $begin->toDateTimeString()],
									['islim_sales.date_reg', '<=', $endDate->toDateTimeString()],
									['islim_sales.type', 'P'],
									['islim_sales.sale_type', $request->type]
								])
								->whereIn('islim_sales.status',['A', 'E'])
								->whereIn('islim_client_netweys.status',['A', 'S'])
								->get();

				if(!empty($data) && !empty($data->D90) && !empty($TotalUp->count())) {

					Log::info($data->D90);
					Log::info($TotalUp->count());


					$por = number_format((($data->D90 / $TotalUp->count()) * 100), 2);



					return response()->json([
										'total' => $data->D90,
										'porcentaje' => $por,
										'date' => $date
									]);
				}

				return response()->json(array('total' => 0, 'porcentaje' => 0, 'date' => $date));
			}
		}
	}

	public function downloadDecay(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$filters = $request->all();

			$report = Reports::getConnect('W');

            $report->name_report = 'decay_90';

            unset($filters['_token']);

            $filters['typeC'] = $request->type;

            $report->filters = json_encode($filters);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));

			/*$clients = HistoryDC2::getClientsByTag(['D90'], 'H')->get();

			$data []= [
						'MSISDN',
						'Nombre',
						'Teléfono',
						'Teléfono Oficina',
						'Email',
						'I.N.E',
						'Fecha del evento',
						'Contactado',
						'Acepto recompra',
						'Comentario',
						'Fecha llamada'
					];

			foreach ($clients as $client){
				$phone = empty($client->phone_home) ? 'N/A' : $client->phone_home;
            	$phone2 = empty($client->phone) ? 'N/A' : $client->phone;
            	$email = empty($client->email) ? 'N/A' : $client->email;
            	$date_eve = !empty($client->date_event) ? date("d-m-Y", strtotime($client->date_event)) : 'N/A';
            	$answer = (empty($client->answer) || $client->answer == 'N') ? 'No' : 'Si';
            	$acept = (empty($client->acept) || $client->acept == 'N') ? 'No' : 'Si';
            	$commnet = empty($client->comment) ? 'N/A' : $client->comment;
            	$call_date = empty($client->date_call) ? 'N/A' : date("d-m-Y H:i", strtotime($client->date_call));

				$data []= [
							$client->msisdn,
							$client->name.' '.$client->last_name,
							$phone,
							$phone2,
							$email,
							$client->dni,
							$date_eve,
							$answer,
							$acept,
							$commnet,
							$call_date
						  ];
			}

			$url = $this->saveReport($data, 'Decay_90_'.date('Y-m-s h:i:s'));

			return response()->json(array('url' => $url));*/
		}
	}

	//Deprecated
	public function arpuUp(){
		$html = view('pages.ajax.report_bi.arpu_up')->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	//Deprecated
	public function getArpuUp(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			if(!empty($request->month) && !empty($request->year)){
				$date = $request->month.'/'.$request->year;

				$metric = Sale::select('msisdn')
								->where([
									[DB::raw("DATE_FORMAT(date_reg, '%m/%Y')"), $date],
									['type', 'P'],
									['islim_sales.sale_type', 'H']
								])
								->whereIn('status', ['A', 'E']);

				$altas = $metric->count();

				if($altas > 0){
					$idsAlt = $metric->get()->pluck('msisdn');

					$dateR = date('m/Y',strtotime('+1 month', strtotime('01-'.$request->month.'-'.$request->year)));

					$recargas = Sale::select('msisdn')
									->where([
										[DB::raw("DATE_FORMAT(date_reg, '%m/%Y')"), $dateR],
										['type', 'R']
									])
									->where('sale_type', 'H')
									->whereIn('status',['A', 'E'])
									->whereIn('msisdn', $idsAlt)
									->sum('amount');

					$val = number_format(($recargas / $altas), 2);

					return response()->json(array('total' => $val, 'date' => $date));
				}

				return response()->json(array('total' => 0, 'date' => $date));
			}
		}
	}

	public function arpuBase(){
		$date = MetricsBi2::getLastMetric('HBB','R');

		$html = view('pages.ajax.report_bi.arpu_base', compact('date'))->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getArpuBase(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			if(!empty($request->month) && !empty($request->year)){
				$date = $request->month.'/'.$request->year;

				$metric = Sale::select('msisdn')
								->where([
									[DB::raw("DATE_FORMAT(date_reg, '%m/%Y')"), $date],
									['type', 'R'],
									['sale_type', 'H']
								])
								->whereIn('status',['A', 'E'])
								->sum('amount');

				$aop = MetricsBi2::getMetricByDate($date, 'HBB');

				if(!empty($aop) && $aop->AOP != 0){
					$val = number_format(($metric / $aop->AOP),2);

					return response()->json(['total' => $val, 'date' => $date]);
				}
				else{
					return response()->json(['total' => 0, 'date' => $date]);
				}
			}
		}
	}

	public function mixRecharge(){
		$html = view('pages.ajax.report_bi.mix_recharge')->render();

		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getMix(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			if(!empty($request->month) && !empty($request->year)){
				$date = $request->month.'/'.$request->year;

				//buscando servicios activos
				$servicios = Service::getActiveServiceByType('H', 'P');

				//Buscando las rargas del mes donde la combinacion del dn y servicio adquirido sean diferentes
				$recharges = Sale::distinct()
								->select('msisdn', 'services_id')
								->where([
									[DB::raw("DATE_FORMAT(date_reg, '%m/%Y')"), $date],
									['type', 'R'],
									['sale_type', 'H']
								])
								->whereIn('status',['A', 'E'])
								->orderBy('msisdn')
								->get();

				//Aqui se comienza a contar cuantos clientes contrataron cada servicio y cuantos son hoppers
				$count = []; //Array de contadores por plan
				$dnBefore = ''; //Buffer de dn anterior consultado
				$hoppers = []; //Array de hoppers
				foreach ($recharges as $recharge) {
					//Contando clientes para cada plan
					$count[$recharge->services_id] = empty($count[$recharge->services_id]) ? 1 : ($count[$recharge->services_id] + 1);

					//Condicion para verificar si el dn antorior es el mismo que el actual "hopper" se cuenta como tal y se descuanta de los otros servicios
					if($dnBefore == $recharge->msisdn){
						$count[$recharge->services_id] = $count[$recharge->services_id] - 1;

						if(!in_array($recharge->msisdn, $hoppers)){
							$count[$sesrvBefore] = $count[$sesrvBefore] - 1;
							$hoppers []= $recharge->msisdn;
						}
					}

					//Almacenando buffer para dn y servicio anterior
					$dnBefore = $recharge->msisdn;
					$sesrvBefore = $recharge->services_id;
				}

				//Obtiene los datos del reporte base recargadora
				$rechargeB = Sale::reportRechargeBase($date)->get()->count();

				if($rechargeB){
					//Asociando los contadores al array de servicios
					foreach ($servicios as $servicio) {
						if(!empty($count[$servicio->id])){
							$servicio->clients = $count[$servicio->id];
							$servicio->porClients = number_format((($count[$servicio->id] / $rechargeB) * 100), 2);
						}
					}

					//Organizando data de los hoppers para enviarla al front
					$dataH['hoppers'] = count($hoppers);
					$dataH['hoppersPorc'] = number_format(((count($hoppers) / $rechargeB) * 100), 2);
				}

				$html = view('pages.ajax.report_bi.mix_plans', compact('servicios', 'dataH'))->render();

				return response()->json(array('html' => $html));
			}
		}
	}

	public function qualityUp(){
		$html = view('pages.ajax.report_bi.quality_up')->render();
		return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
	}

	public function getQualityUp(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			if(!empty($request->month) && !empty($request->year)){
				$date = $request->month.'/'.$request->year;

				$fieldCoor = DB::raw('count(lat) as coord');

				$altas = Sale::select(
									'islim_sales.msisdn',
									'islim_sales.lat',
									'islim_sales.lng',
									'islim_sales.users_email',
									'islim_sales.services_id',
									'islim_sales.date_reg',
									$fieldCoor,
									'islim_users.name',
									'islim_users.last_name',
									'islim_users.phone'
								)
								->join('islim_users', 'islim_users.email', '=', 'islim_sales.users_email')
								->where([
									[DB::raw("DATE_FORMAT(islim_sales.date_reg, '%m/%Y')"), $date],
									['islim_sales.type', 'P'],
									['islim_sales.sale_type', 'H']
								])
								->whereIn('islim_sales.status',['A', 'E'])
								->groupBy('lat')
								->groupBy('lng')
								->orderBy('coord', 'DESC')
								->havingRaw('coord > 1')
								->get();

				$fieldIne = DB::raw('count(islim_client_netweys.clients_dni) as c_ine');

				$altasIne = Sale::select(
									'islim_sales.msisdn',
									'islim_sales.users_email',
									$fieldIne,
									'islim_client_netweys.clients_dni',
									'islim_clients.name as cn',
									'islim_clients.last_name as cln',
									'islim_users.name',
									'islim_users.last_name',
									'islim_users.phone'
								)
								->join(
									'islim_users',
									'islim_users.email',
									'=',
									'islim_sales.users_email'
								)
								->join(
									'islim_client_netweys',
									'islim_client_netweys.msisdn',
									'=',
									'islim_sales.msisdn'
								)
								->join(
									'islim_clients',
									'islim_clients.dni',
									'=',
									'islim_client_netweys.clients_dni'
								)
								->where([
									[DB::raw("DATE_FORMAT(islim_sales.date_reg, '%m/%Y')"), $date],
									['islim_sales.type', 'P'],
									['islim_sales.sale_type', 'H']
								])
								->whereIn('islim_sales.status',['A', 'E'])
								->groupBy('islim_client_netweys.clients_dni')
								->orderBy('c_ine', 'DESC')
								->havingRaw('c_ine > 1')
								->get();

				$html = view('pages.ajax.report_bi.data_quality', compact('altas', 'altasIne'))->render();
				return response()->json(array('success' => true, 'html'=> $html));
			}
		}
	}

	//Deprecated
	//Metodo para descargar reportes almacenados en el directorio de reportes
	//$delete = 1 borra archivo, otro valor deja el archivo en el direcorio
	//recibe por get p = path del archivo ejm "/public/reports/..."
	public function downloadReports(Request $request, $delete){
		$path = $request->get('p');

		return redirect($path);

		/*Deprecated*/

		$headers = array(
			"Pragma" => "no-cache",
			"Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
			"Expires" => "0"
		);

		if($delete == '1')
			return response()->download(storage_path('/app'.$path))->deleteFileAfterSend(true);
		else
			return response()->download(storage_path('/app'.$path),null,$headers);
	}

	//Devuelve los active60 para la fecha dada DEPRECATED
	/*private function getNumberActive90($dateE){
		$data = MetricsBi::select('A90')
								 ->where([
									[DB::raw("DATE_FORMAT(date_reg, '%m/%Y')"), date('m/Y', strtotime($dateE))],
									['status', 'A']
								 ])
								 ->orderBy('date_reg', 'DESC')
								 ->first();

		if(!empty($data)){
			return $data->A90;
		}

		return 0;

		//Hay que revisar el código de abajo para optimizarlo en caso de que se quiera calcular en vivo los active90

		$lastHour = (3600 * 23) + 3599;
		//Fecha para contar active60
		$datedA90 = date('Y-m-d H:i:s', strtotime('-3 month', strtotime($dateE) - $lastHour));
		//Consultando los active60
		$directActive90 = Sale::distinct()
							  ->select('islim_sales.msisdn')
							  ->join('islim_client_netweys','islim_client_netweys.msisdn','islim_sales.msisdn')
							  ->whereIn('islim_sales.status', ['A', 'E'])
							  ->where([
								['islim_sales.date_reg', '>=', $datedA90],
								['islim_sales.date_reg', '<=', $dateE],
								//['islim_client_netweys.status', 'A']
							  ])
							  ->whereIn('islim_sales.type',['R','P'])
							  ->whereIn('islim_client_netweys.status', ['A', 'S'])
							  ->get();

		//Fecha para consultar posibles active60 (contando tiempo del plan)
		$dateA90 = date('Y-m-d H:i:s', strtotime('-4 month', strtotime($dateE) - $lastHour));
		//Consultando todos los clientes con altas o recargas que no son active60 directamente
		//y estan entre -3 meses y -2 meses
		$queryActive90 = Sale::select(
								'islim_sales.msisdn',
								'islim_sales.services_id',
								'islim_sales.date_reg'
							  )
							  ->join('islim_client_netweys','islim_client_netweys.msisdn','islim_sales.msisdn')
							  ->whereIn('islim_sales.status', ['A', 'E'])
							  //->whereNotIn('islim_sales.msisdn', $directActive90->pluck('msisdn'))
							  ->where([
								['islim_sales.date_reg', '>=', $dateA90],
								['islim_sales.date_reg', '<', $datedA90],
								//['islim_client_netweys.status', 'A']
							  ])
							  ->whereIn('islim_client_netweys.status', ['A', 'S'])
							  ->whereIn('islim_sales.type',['R','P'])
							  ->orderBy('islim_sales.msisdn', 'DESC')
							  ->orderBy('islim_sales.date_reg', 'DESC')
							  ->get();

		//Array que va a guardar los active60 que entran a esta categoria por el plan adquirido
		$arrActive90 = [];
		//Fecha desde la cual un cliente pasa a ser active60
		$activeDate = strtotime('-3 month', strtotime($dateE) - $lastHour);

		//Ciclo para extraer los active60
		foreach ($queryActive90 as $client) {
			if(!in_array($client->msisdn, $arrActive90) && !in_array($client->msisdn, $directActive90->pluck('msisdn'))){
				//Consultando tiempo del plan que tiene activo el cliente
				$timeAlta = Service::select('periodicity_id', 'periodicity', 'days')
									 ->join('islim_periodicities', 'islim_periodicities.id', '=', 'islim_services.periodicity_id')
									 ->where('islim_services.id', $client->services_id)
									 ->first();

				$rDate = strtotime('+'.$timeAlta->days.' days', strtotime($client->date_reg));

				//Condicion para ser active60 (Que la fecha calculada 2 meses antes sea menor a la fecha de activacion del plan mas los dias que dura el plan)
				if($activeDate < $rDate){
					$arrActive90 []= $client->msisdn;
				}
			}
		}

		//$arrActive90 = array_diff($arrActive90, $directActive90->pluck('msisdn'));

		return $directActive90->count() + count($arrActive90);
	}

	//Devuelve los decay totales que existen hasta la fecha dada DEPRECATED
	private function getNumberDecay90($date = false){
		if($date){
			$lastHour = (3600 * 23) + 3599;
			// quitando dos meses a la fecha que se esta consultando
			$dateReg = date('Y-m-d H:i:s', strtotime('-3 month', strtotime($date)));
			//Sub-consulta para filtrar altas que nunca hayan hecho una recarga
			$cr = DB::raw("(select count(tc.msisdn) from islim_sales as tc where tc.msisdn = islim_sales.msisdn  and tc.type = 'R' and tc.status in ('A', 'E') AND tc.date_reg <= '".$dateReg."')");

			//Buscando las altas que pueden ser decay
			$altas = Sale::select('islim_sales.msisdn', 'islim_sales.services_id', 'islim_sales.date_reg')
						->join('islim_client_netweys','islim_client_netweys.msisdn','islim_sales.msisdn')
						->where([
							['islim_sales.date_reg', '<=', $dateReg],
							['islim_sales.type', 'P'],
							//['islim_client_netweys.status', 'A'],
							[$cr, '=', 0]
						])
						->whereIn('islim_client_netweys.status', ['A', 'S'])
						->whereIn('islim_sales.status',['A', 'E'])
						->groupBy('islim_sales.msisdn')
						->get();

			$decay90T = 0;
			foreach ($altas as $alta){
				//Consultando dias del plan que tiene contradado el cliente
				$timeAlta = Service::select('periodicity_id', 'periodicity', 'days')
									 ->join('islim_periodicities', 'islim_periodicities.id', '=', 'islim_services.periodicity_id')
									 ->where('islim_services.id', $alta->services_id)
									 ->first();

				//Bencimiento de la fecha de gracia en la que un cliente puede pasar a ser decay
				$decayDate = date('Y-m-d', strtotime('+3 month', strtotime($alta->date_reg)));
				$decayDate = strtotime('+'.$timeAlta->days.' days', strtotime($decayDate) + $lastHour);

				//Si la fecha para ser decay es menor o igual a la actual contamos al cliente como decay
				if($decayDate < strtotime($date)){
					$decay90T ++;
				}
			}
			return $decay90T;
		}
	}*/

	//Deprecated
	/*Guarda los reportes en el server y genera una url para descarga que es enviada al front*/
	private function saveReport($data = false, $filename = 'report'){
		$pathReport = '/public/reports';

		return CommonHelpers::saveFile($pathReport, 'reports_bi', $data, $filename);

		/*Deprecate*/
		//Cargando directorios dentro de public/reports
		$directory = Storage::disk('local')->directories($pathReport);

		$pathReportBI = $pathReport.'/'.'reports_bi';

		//Si no existe el directorio de reporte para las altas se crea
		if(!in_array($pathReportBI, $directory))
			Storage::disk('local')->makeDirectory($pathReportBI);

		\Excel::create($filename, function($excel) use ($data) {
			$excel->sheet('Reporte', function($sheet) use ($data) {
				$sheet->fromArray($data, null, 'A1', false, false);
			});
		})->store('xls',storage_path('/app'.$pathReportBI));

		return $pathReportBI.'/'.$filename.".xls";
	}
}
