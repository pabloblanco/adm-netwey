<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CommonHelpers;
use App\Sale;
use App\ClientNetwey;
use App\Client;
use App\Service;
use App\SalesBrightstar;
use App\MercadoPago;
use App\TempCar;
use App\TokenApiSeller;
use App\Reports;
use App\StepRecord;
use DataTables;
use DateTime;
use App\NinetyNineMinutes;
use App\Transaction;
use App\ProvaDelivery;
use App\OrderStatus;
use Carbon\Carbon;
use Log;

class ReportOSController extends Controller
{
	public function viewSales(){
    	$html = view('pages.ajax.report_os.sales')->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getSalesForReportOSV2(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $clients = SalesBrightstar::getSalesOnline(
                            !empty($request->date_ini) ? (Carbon::createFromFormat('d/m/Y H:i:s', $request->date_ini.' '.$request->time_ini.':00')->format('Y-m-d H:i:s')) : false,
                            !empty($request->date_end) ? (Carbon::createFromFormat('d/m/Y H:i:s', $request->date_end.' '.$request->time_end.':59')->format('Y-m-d H:i:s')) : false,
                            $request->cod_prom
                        );

            return DataTables::of($clients)
                            ->editColumn('Campaña', function($client){
                                if(!empty($client->Campaña)){
                                    return $client->Campaña;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('cod_prom', function($client){
                                if(!empty($client->cod_prom)){
                                    return $client->cod_prom;
                                }else{
                                    return 'N/A';
                                }
                            })

                            ->editColumn('phone_home', function($client){
                                if(!empty($client->Telefono)){
                                    return $client->Telefono;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Direccion_Entrega', function($client){
                                if(strpos($client->Nro_Orden, 'NTW') !== false){//Birgthstar
                                    $del = Transaction::getDelivery($client->Orden_id);

                                    if(!empty($del)){
                                        return $del->city.', '.$del->colonia.', '.$del->state.', '.$del->address.', '.$del->codigozip;
                                    }else{
                                        return 'S/I';
                                    }
                                }elseif(strpos($client->Nro_Orden, 'NET') !== false){//Prova
                                    $del = ProvaDelivery::getDelivery($client->Nro_Orden);

                                    if(!empty($del)){
                                        return $del->street.', '.$del->colony.', '.$del->state;
                                    }else{
                                        return 'S/I';
                                    }
                                }else{//99min
                                    $del = NinetyNineMinutes::getDelivery($client->Nro_Orden);

                                    if(!empty($del)){
                                        return $del->route.', '.$del->neighborhood.', '.$del->state;
                                    }else{
                                        return 'S/I';
                                    }
                                }
                            })
                            ->editColumn('Id_Estafeta', function($client){
                                if(strpos($client->Nro_Orden, 'NTW') !== false){
                                    return 'Estafeta';
                                }elseif(strpos($client->Nro_Orden, 'NET') !== false){
                                    return 'Prova ('.$client->courier_g.')';
                                }else{
                                    return '99 minutos';
                                }
                            })
                            ->editColumn('price_del', function($client){
                                if(strpos($client->Nro_Orden, 'NTW') !== false){
                                    return '$0';
                                }elseif(strpos($client->Nro_Orden, 'NET') !== false){
                                    return '$'.$client->price_prova;
                                }else{
                                    return '$100';
                                }
                            })
                            ->editColumn('Estado', function($client){
                                if(!empty($client->Estado) && $client->Estado == 'A'){
                                    return 'Activo';
                                }else{
                                    $st = OrderStatus::getLastStatus($client->Orden_id);

                                    if(!empty($st)){
                                        return $st->description;
                                    }else{
                                        return 'S/I';
                                    }
                                }
                            })
                            ->editColumn('Plan', function($client){
                                if(!empty($client->Plan)){
                                    return $client->Plan;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Fecha_Entrega', function($client){
                                if(!empty($client->Fecha_Entrega)){
                                    return $client->Fecha_Entrega;
                                }else{
                                    return 'En Camino...';
                                }
                            })
                            ->editColumn('MSISDN', function($client){
                                if(!empty($client->MSISDN)){
                                    return $client->MSISDN;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Dias_en_Activar', function($client){
                                if(!empty($client->Estado) && $client->Estado == 'A'){
                                    return $client->Dias_en_Activar != 0 ? $client->Dias_en_Activar : '0';
                                }
                                return 'N/A';
                            })
                            ->editColumn('Fecha_Activacion', function($client){
                                if(!empty($client->Estado) && $client->Estado == 'A'){
                                    return date("d-m-Y H:i:s", strtotime($client->Fecha_Activacion));
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('date_reg', function($client){
                                return date("d-m-Y", strtotime($client->date_reg));
                            })
                            ->editColumn('Tipo_Persona', function($client){
                                if(!empty($client->Tipo_Persona)){
                                    if($client->Tipo_Persona=="M")
                                        return 'Moral';
                                    else
                                        return 'Fisica';
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Requiere_Factura', function($client){
                                if(!empty($client->Requiere_Factura)){
                                    if($client->Requiere_Factura=="Y")
                                        return 'Si';
                                    else
                                        return 'No';
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('RFC', function($client){
                                if(!empty($client->RFC)){
                                    return $client->RFC;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Metodo_Pago', function($client){
                                $pay = MercadoPago::getPayment($client->Orden_id);

                                if(!empty($pay)){
                                    return $pay->payment_method;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Ciudad', function($client){
                                if(!empty($client->Ciudad)){
                                    return $client->Ciudad;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('Codigo_Postal', function($client){
                                if(!empty($client->Codigo_Postal)){
                                    return $client->Codigo_Postal;
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->make(true);

        }
    }

    public function downloadSalesForReportOSV2(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $clients = SalesBrightstar::getSalesOnline(
                            !empty($request->date_ini) ? (Carbon::createFromFormat('d/m/Y H:i:s', $request->date_ini.' '.$request->time_ini.':00')->format('Y-m-d H:i:s')) : false,
                            !empty($request->date_end) ? (Carbon::createFromFormat('d/m/Y H:i:s', $request->date_end.' '.$request->time_end.':59')->format('Y-m-d H:i:s')) : false,
                            $request->cod_prom
                        )->get();

            $data []= ['Orden_Proveedor','Nombre', 'Apellido', 'Telefono', 'ciudad', 'Codigo Postal', 'RFC', 'Email', 'Fecha Registro', 'Equipo Comprado', 'Plan','Fecha de Compra','Fecha de Entrega','Dias en Entregar','Metodo de Pago', 'Id_Orden','MSISDN','Direccion de Entrega', 'Proveedor', 'Precio delivery', 'Estado','Campaña','Codigo Promo','Fecha de Activacion','Dias en Activar'];

            foreach ($clients as $client) {
                $address = 'S/I';
                if(strpos($client->Nro_Orden, 'NTW') !== false){//Birgthstar
                    $del = Transaction::getDelivery($client->Orden_id);

                    if(!empty($del)){
                        $address = $del->city.', '.$del->colonia.', '.$del->state.', '.$del->address.', '.$del->codigozip;
                    }
                }elseif(strpos($client->Nro_Orden, 'NET') !== false){//Prova
                    $del = ProvaDelivery::getDelivery($client->Nro_Orden);

                    if(!empty($del)){
                        $address = $del->street.', '.$del->colony.', '.$del->state;
                    }
                }else{//99min
                    $del = NinetyNineMinutes::getDelivery($client->Nro_Orden);

                    if(!empty($del)){
                        $address = $del->route.', '.$del->neighborhood.', '.$del->state;
                    }
                }

                $pay = MercadoPago::getPayment($client->Orden_id);
                $mtp = 'N/A';
                if(!empty($pay)){
                    $mtp =  $pay->payment_method;
                }

                $proveedor = '99 minutos';
                if(strpos($client->Nro_Orden, 'NTW') !== false){
                    $proveedor =  'Estafeta';
                }elseif(strpos($client->Nro_Orden, 'NET') !== false){
                    $proveedor =  'Prova ('.$client->courier_g.')';
                }

                $priceDel = '$100';
                if(strpos($client->Nro_Orden, 'NTW') !== false){
                    $priceDel = '$0';
                }elseif(strpos($client->Nro_Orden, 'NET') !== false){
                    $priceDel = '$'.$client->price_prova;
                }

                $statusD = 'S/I';
                if(!empty($client->Estado) && $client->Estado == 'A'){
                    $statusD = 'Activo';
                }else{
                    $st = OrderStatus::getLastStatus($client->Orden_id);

                    if(!empty($st)){
                        $statusD = $st->description;
                    }
                }

                $actDate = 'N/A';
                if(!empty($client->Estado) && $client->Estado == 'A'){
                    $actDate = date("d-m-Y H:i:s", strtotime($client->Fecha_Activacion));
                }

                $da = 'N/A';
                if(!empty($client->Estado) && $client->Estado == 'A'){
                    $da = $client->Dias_en_Activar != 0 ? $client->Dias_en_Activar : '0';
                }

                $data []= [
                            empty($client->Nro_Orden) ? 'N/A' : $client->Nro_Orden,
                            $client->Nombre,
                            $client->Apellido,
                            empty($client->Telefono) ? 'N/A' : $client->Telefono,
                            empty($client->Ciudad) ? 'N/A' : $client->Ciudad,
                            empty($client->Codigo_Postal) ? 'N/A' : $client->Codigo_Postal,
                            // empty($client->Tipo_Persona) ? 'N/A' : $client->Tipo_Persona=='M' ? 'Moral':'Física',
                            // empty($client->Requiere_Factura) ? 'N/A' : $client->Requiere_Factura=='Y' ? 'Si':'No',
                            empty($client->RFC) ? 'N/A' : $client->RFC,
                            empty($client->Email) ? 'N/A' : $client->Email,
                            empty($client->Fecha_Registro) ? 'N/A' : $client->Fecha_Registro,
                            empty($client->Equipo_Comprado) ? 'N/A' : $client->Equipo_Comprado,
                            empty($client->Plan) ? 'N/A' : $client->Plan,
                            empty($client->Fecha_Compra) ? 'N/A' : $client->Fecha_Compra,
                            empty($client->Fecha_Entrega) ? 'En Camino...' : $client->Fecha_Entrega,
                            $client->Dias_en_Entregar,
                            $mtp,
                            empty($client->Orden_id) ? 'N/A' : $client->Orden_id,
                            empty($client->MSISDN) ? 'N/A' : $client->MSISDN,
                            $address,
                            $proveedor,
                            $priceDel,
                            $statusD,
                            empty($client->Campaña) ? 'N/A' : $client->Campaña,
                            empty($client->cod_prom) ? 'N/A' : $client->cod_prom,
                            $actDate,
                            $da
                        ];
            }

            $url = CommonHelpers::saveFile('/public/reportsOS', 'sales', $data, 'online_sales_report_'.date('d-m-Y'));

            return response()->json(array('url' => $url));

        }
    }

    /*Deprecated*/
    public function getSalesForReportOS(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		if($request->date_ini!="" && $request->date_end!=""){
                $clients = SalesBrightstar::getSales(
                                                $request->date_ini,
                                                $request->date_end,
                                                $request->time_ini,
                                                $request->time_end,
                                                $request->cod_prom
                                            );

    			return DataTables::of($clients)
                                ->editColumn('Campaña', function($client){
                                    if(!empty($client->Campaña)){
                                        return $client->Campaña;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('cod_prom', function($client){
                                    if(!empty($client->cod_prom)){
                                        return $client->cod_prom;
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
                                ->editColumn('Direccion_Entrega', function($client){
                                    if(empty($client->id_99)){
                                        return $client->city.', '.$client->colonia.', '.$client->state.', '.$client->Direccion_Entrega.', '.$client->codigozip;
                                    }else{
                                        return $client->route.', '.$client->neighborhood.', '.$client->state_99;
                                    }
                                })
                                ->editColumn('Id_Estafeta', function($client){
                                    if(!empty($client->Id_Estafeta)){
                                        return $client->Id_Estafeta;
                                    }else{
                                        return '99 minutos';
                                    }
                                })
                                ->editColumn('Plan', function($client){
                                    if(!empty($client->Plan)){
                                        return $client->Plan;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('MSISDN', function($client){
                                    if(!empty($client->MSISDN)){
                                        return $client->MSISDN;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Dias_en_Activar', function($client){
                                    if(!empty($client->Fecha_Activacion)){
                                        if($client->Dias_en_Activar != 'N/A'){
                                            return $client->Dias_en_Activar != 0 ? $client->Dias_en_Activar : '0';
                                        }else{
                                            return $client->Dias_en_Activar_MP != 0 ? $client->Dias_en_Activar_MP : '0';
                                        }
                                    }
                                    return 'N/A';
                                })
                                ->editColumn('Fecha_Activacion', function($client){
                                    if(!empty($client->Fecha_Activacion)){
                                        return $client->Fecha_Activacion;
                                    }else{
                                        return 'N/A';
                                    }
                                })
					            ->editColumn('date_reg', function($client){
                                    return date("d-m-Y", strtotime($client->date_reg));
                                })
                                ->editColumn('Tipo_Persona', function($client){
                                    if(!empty($client->Tipo_Persona)){
                                        if($client->Tipo_Persona=="M")
                                            return 'Moral';
                                        else
                                            return 'Fisica';
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Requiere_Factura', function($client){
                                    if(!empty($client->Requiere_Factura)){
                                        if($client->Requiere_Factura=="Y")
                                            return 'Si';
                                        else
                                            return 'No';
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('RFC', function($client){
                                    if(!empty($client->RFC)){
                                        return $client->RFC;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Metodo_Pago', function($client){
                                    if(!empty($client->Metodo_Pago)){
                                        return $client->Metodo_Pago;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Ciudad', function($client){
                                    if(!empty($client->Ciudad)){
                                        return $client->Ciudad;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Codigo_Postal', function($client){
                                    if(!empty($client->Codigo_Postal)){
                                        return $client->Codigo_Postal;
                                    }else{
                                        return 'N/A';
                                    }
                                })
					            ->make(true);

			}
    	}
    }

    /*Deprecated*/
    public function downloadSalesForReportOS(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $request->date_ini = !empty($request->date_ini) ? $request->date_ini : '01/01/1900';
            $request->date_end = !empty($request->date_end) ? $request->date_end : date('d/m/Y');
            $dini=substr($request->date_ini,6,4).substr($request->date_ini,3,2).substr($request->date_ini,0,2);
        	$dend=substr($request->date_end,6,4).substr($request->date_end,3,2).substr($request->date_end,0,2);

            $clients = SalesBrightstar::getSales(
                                            $request->date_ini,
                                            $request->date_end,
                                            $request->time_ini,
                                            $request->time_end,
                                            $request->cod_prom
                                        )->get();

            $data []= ['Nombre', 'Apellido', 'Telefono', 'ciudad', 'Codigo Postal', 'Tipo de Persona','Requiere Factura', 'RFC', 'Email', 'Fecha Registro', 'Equipo Comprado', 'Plan','Fecha de Compra','Metodo de Pago','Orden_Brighstar', 'Id_Orden','MSISDN','Direccion de Entrega', 'Id_Estafeta','Estado','Campaña','Codigo Promo','Fecha de Activacion','Dias en Activar'];

            foreach ($clients as $client) {
                if(empty($client->id_99))
                    $da = $client->Dias_en_Activar;
                else
                    $da = $client->Dias_en_Activar_MP;

                $address = 'N/A';
                if(empty($client->id_99))
                    $address = $client->Direccion_Entrega;
                else
                    $address = $client->route.' '.$client->neighborhood.' '.$client->state_99;

                $data []= [
                            $client->Nombre,
                            $client->Apellido,
                            empty($client->Telefono) ? 'N/A' : $client->Telefono,
                            empty($client->Ciudad) ? 'N/A' : $client->Ciudad,
                            empty($client->Codigo_Postal) ? 'N/A' : $client->Codigo_Postal,
                            empty($client->Tipo_Persona) ? 'N/A' : $client->Tipo_Persona=='M' ? 'Moral':'Física',
                            empty($client->Requiere_Factura) ? 'N/A' : $client->Requiere_Factura=='Y' ? 'Si':'No',
                            empty($client->RFC) ? 'N/A' : $client->RFC,
                            empty($client->Email) ? 'N/A' : $client->Email,
                            empty($client->Fecha_Registro) ? 'N/A' : $client->Fecha_Registro,
                            empty($client->Equipo_Comprado) ? 'N/A' : $client->Equipo_Comprado,
                            empty($client->Plan) ? 'N/A' : $client->Plan,
                            empty($client->Fecha_Compra) ? 'N/A' : $client->Fecha_Compra,
                            empty($client->Metodo_Pago) ? 'N/A' : $client->Metodo_Pago,
                            empty($client->Nro_Orden) ? 'N/A' : $client->Nro_Orden,
                            empty($client->Orden_id) ? 'N/A' : $client->Orden_id,
                            empty($client->MSISDN) ? 'N/A' : $client->MSISDN,
							$address,
                            empty($client->Id_Estafeta) ? '99 minutos' : $client->Id_Estafeta,
							empty($client->Estado) ? 'N/A' : $client->Estado,
                            empty($client->Campaña) ? 'N/A' : $client->Campaña,
                            empty($client->cod_prom) ? 'N/A' : $client->cod_prom,
							empty($client->Fecha_Activacion) ? 'N/A' : $client->Fecha_Activacion,
							$da
                        ];
            }

            $url = CommonHelpers::saveFile('/public/reportsOS', 'sales', $data, 'online_sales_report_'.date('d-m-Y'));

            return response()->json(array('url' => $url));

        }
    }

    public function unsoldRecords(){
    	$html = view('pages.ajax.report_os.unsold_records')->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

   	public function getClientsUnSoldRecords(Request $request){
    	if($request->isMethod('post') && $request->ajax()){
    		//if(!empty($request->date_ini) && !empty($request->date_end)){
    		if($request->date_ini!="" && $request->date_end!=""){
    			$clients = Client::getClientsUnSoldRecordsForReportsOS($request->date_ini, $request->date_end);
    			//$clients = Sale::getSalesForReportOS($request->date_ini, $request->date_end);

    			return DataTables::eloquent($clients)
					            ->editColumn('phone', function($client){
					                if(!empty($client->phone)){
					                    return $client->phone;
					                }else{
					                    return '';
					                }
					            })
					            ->editColumn('date_reg', function($client){
                                    return date("d-m-Y", strtotime($client->date_reg));
                                })
                                 ->editColumn('Campaña', function($client){
                                    if(!empty($client->Campaña)){
                                        return $client->Campaña;
                                    }else{
                                        return 'N/A';
                                    }
                                })
					            ->make(true);

			}
    	}
    }

    public function downloadClientsUnSoldRecordsForReportOS(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini==""){
                $dini="01/01/1900";
            }
            else{
                $dini=$request->date_ini;
            }

            if($request->date_end==""){
                $dend=date("d/m/Y");
            }
            else{
                $dend=$request->date_end;
            }

        	//if($request->date_ini!="" && $request->date_end!=""){

        		// DB::connection()->enableQueryLog();

    			$clients = Client::getClientsUnSoldRecordsForReportsOS($dini, $dend)->get();

    			// $queries = DB::getQueryLog();

    			$data []= ['Nombre', 'Apellido', 'Telefono', 'Email', 'Direccion', 'Fecha Registro','Campaña'];

	            foreach ($clients as $client) {
	                $data []= [
	                            $client->Nombre,
	                            $client->Apellido,
	                            empty($client->Telefono) ? '' : $client->Telefono,
	                            empty($client->Email) ? '' : $client->Email,
                                empty($client->Direccion) ? '' : $client->Direccion,
	                            empty($client->Fecha_Registro) ? '' : $client->Fecha_Registro,
                                empty($client->Campaña) ? 'N/A' : $client->Campaña,
	                          ];
	            }

	            $url = CommonHelpers::saveFile('/public/reportsOS', 'unsoldRecords', $data, 'unsold_records_report_'.date('d-m-Y'));

	            return response()->json(array('url' => $url));

    		//}
        }
    }

    public static function convertia(){
        $html = view('pages.ajax.report_os.convertia', compact('orgs'))->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public static function getDTconvertiaSales(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $filters = $request->all();
            $filters['key'] = env('TOKEN_CONVERTIA');
            $sales = TempCar::getSalesReport($filters);

            return DataTables::of($sales)
                            ->editColumn('name', function($sale){
                                return $sale->name.''.$sale->last_name;
                            })
                            ->editColumn('phone_home', function($sale){
                                if(!empty($sale->phone_home))
                                    return $sale->phone_home;

                                return 'N/A';
                            })
                            ->editColumn('email', function($sale){
                                if(!empty($sale->email))
                                    return $sale->email;

                                return 'N/A';
                            })
                            ->editColumn('invoice', function($sale){
                                return $sale->require_invoice == 'Y' ? 'Si' : 'No';
                            })
                            ->editColumn('dni', function($sale){
                                if(!empty($sale->rfc))
                                    return $sale->rfc;

                                return $sale->dni;
                            })
                            ->editColumn('msisdn', function($sale){
                                if(!empty($sale->msisdn))
                                    return $sale->msisdn;

                                return 'N/A';
                            })
                            ->editColumn('pack', function($sale){
                                return $sale->title;
                            })
                            ->editColumn('date_buy', function($sale){
                                if(!empty($sale->date))
                                    return date("d-m-Y", strtotime($sale->date));
                                return 'N/A';
                            })
                            ->editColumn('order', function($sale){
                                return $sale->order;
                            })
                            ->editColumn('order_del', function($sale){
                                if(!empty($sale->order99))
                                    return $sale->order99;

                                return 'Error';
                            })
                            ->editColumn('status_del', function($sale){
                                if(!empty($sale->description))
                                    return strtolower($sale->description);
                                return 'N/A';
                            })
                            ->editColumn('status_dn', function($sale){
                                if(!empty($sale->status_dn))
                                    return $sale->status_dn == 'A'? 'Activo' : 'Inactivo';

                                return 'N/A';
                            })
                            ->editColumn('amount_del', function($sale){
                                return '$'.number_format($sale->amount_del,2,'.',',');
                            })
                            ->editColumn('amount', function($sale){
                                return '$'.number_format($sale->price_pack,2,'.',',');
                            })
                            ->make(true);
        }
    }

    public static function downloadDTconvertiaSales(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $inputs = $request->all();

            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_ventas_convertia';

            unset($inputs['emails']);
            unset($inputs['_token']);

            $inputs['key'] = env('TOKEN_CONVERTIA');

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
        }

        return response()->json(array('error' => true));
    }

    public static function inconcert(){
        $html = view('pages.ajax.report_os.inconcert')->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public static function getDTInconcertSales(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $filters = $request->all();
            $filters['key'] = env('TOKEN_INCONCERT');
            $sales = TempCar::getSalesReport($filters);

            return DataTables::of($sales)
                            ->editColumn('name', function($sale){
                                return $sale->name.''.$sale->last_name;
                            })
                            ->editColumn('phone_home', function($sale){
                                if(!empty($sale->phone_home))
                                    return $sale->phone_home;

                                return 'N/A';
                            })
                            ->editColumn('email', function($sale){
                                if(!empty($sale->email))
                                    return $sale->email;

                                return 'N/A';
                            })
                            ->editColumn('invoice', function($sale){
                                return $sale->require_invoice == 'Y' ? 'Si' : 'No';
                            })
                            ->editColumn('dni', function($sale){
                                if(!empty($sale->rfc))
                                    return $sale->rfc;

                                return $sale->dni;
                            })
                            ->editColumn('msisdn', function($sale){
                                if(!empty($sale->msisdn))
                                    return $sale->msisdn;

                                return 'N/A';
                            })
                            ->editColumn('pack', function($sale){
                                return $sale->title;
                            })
                            ->editColumn('date_buy', function($sale){
                                if(!empty($sale->date))
                                    return date("d-m-Y", strtotime($sale->date));
                                return 'N/A';
                            })
                            ->editColumn('order', function($sale){
                                return $sale->order;
                            })
                            ->editColumn('order_del', function($sale){
                                if(!empty($sale->order99))
                                    return $sale->order99;

                                return 'Error';
                            })
                            ->editColumn('status_del', function($sale){
                                if(!empty($sale->description))
                                    return strtolower($sale->description);
                                return 'N/A';
                            })
                            ->editColumn('status_dn', function($sale){
                                if(!empty($sale->status_dn))
                                    return $sale->status_dn == 'A'? 'Activo' : 'Inactivo';

                                return 'N/A';
                            })
                            ->editColumn('amount_del', function($sale){
                                return '$'.number_format($sale->amount_del,2,'.',',');
                            })
                            ->editColumn('amount', function($sale){
                                return '$'.number_format($sale->price_pack,2,'.',',');
                            })
                            ->make(true);
        }
    }

    public static function downloadDTInconcertSales(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $inputs = $request->all();

            $report = new Reports;

            $report->name_report = 'reporte_ventas_inconcert';

            unset($inputs['emails']);
            unset($inputs['_token']);

            $inputs['key'] = env('TOKEN_INCONCERT');

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
        }

        return response()->json(array('error' => true));
    }

    public function leadsPromoEC(){
        $html = view('pages.ajax.report_os.leads_promo_ec')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getLeadsPromoECForReportOS(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            //if(!empty($request->date_ini) && !empty($request->date_end)){
            if($request->date_ini!="" && $request->date_end!=""){

                $dini=substr($request->date_ini,6,4)."-".substr($request->date_ini,3,2)."-".substr($request->date_ini,0,2)."%2000:00:00";
                $dend=substr($request->date_end,6,4)."-".substr($request->date_end,3,2)."-".substr($request->date_end,0,2)."%2023:59:59";


                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://mautic.netwey.com.mx/api/contacts?search=email:!%22%22%20and%20origin:%22EC%22%20and%20isregister:%22N%22&limit=0&where%5B0%5D%5Bexpr%5D=gte&where%5B0%5D%5Bcol%5D=dateAdded&where%5B0%5D%5Bval%5D=".$dini."&where%5B1%5D%5Bexpr%5D=lte&where%5B1%5D%5Bcol%5D=dateAdded&where%5B1%5D%5Bval%5D=".$dend."",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic YWRtaW46TFlQQHZRSzs4RDc4QlVpOg==",
                    "Cookie: 659488090dd5f2926bfca2796ea50e33=fdrkadr5lcg6iipbbfglvt79tq"
                  ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);

                $array=[];
                if (!$err) {

                    $response = json_decode($response,true);

                      $i=0;
                      foreach ($response['contacts'] as $key => $contact) {

                        $i++;


                         //$user=DB::table('islim_clients')
                         $user=Client::getConnect('R')
                                ->where('email',$contact['fields']['all']['email'])
                                ->first();
                            if ($user==null or $user=="") {

                                $contactarr= [
                                    "Nombre" => $contact['fields']['all']['firstname'],
                                    "Apellido" => $contact['fields']['all']['lastname'],
                                    "Telefono" => $contact['fields']['all']['phone'],
                                    "Email" => $contact['fields']['all']['email'],
                                    "Ciudad" => $contact['fields']['all']['city'],
                                    "Fecha_Registro" => date_format(date_create($contact['dateAdded']),'d/m/Y H:i:s'),
                                ];
                            }
                            else{


                                $curl2 = curl_init();

                                curl_setopt_array($curl2, array(
                                  CURLOPT_URL => "https://mautic.netwey.com.mx/api/contacts/".$contact['fields']['all']['id']."/edit",
                                  CURLOPT_RETURNTRANSFER => true,
                                  CURLOPT_ENCODING => "",
                                  CURLOPT_MAXREDIRS => 10,
                                  CURLOPT_TIMEOUT => 0,
                                  CURLOPT_FOLLOWLOCATION => true,
                                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                  CURLOPT_CUSTOMREQUEST => "PATCH",
                                  CURLOPT_POSTFIELDS =>"{\r\n  \"isregister\":\"Y\"\r\n}",
                                  CURLOPT_HTTPHEADER => array(
                                    "Content-Type: application/json",
                                    "Authorization: Basic YWRtaW46TFlQQHZRSzs4RDc4QlVpOg==",
                                    "Content-Type: application/json",
                                    "Cookie: 659488090dd5f2926bfca2796ea50e33=oq89ad9t2imrcf6jk9far0npan"
                                  ),
                                ));

                                $res = curl_exec($curl2);
                                // Log::debug("res--->>>".$res);
                                curl_close($curl2);


                            }
                         array_push($array,$contactarr);
                      }

                }
                return DataTables::of($array)
                        ->toJson();
            }
        }
    }


    public function downloadLeadsPromoECForReportOS(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini==""){
                $dini="1900-01-01%2000:00:00";
            }
            else{
                 $dini=substr($request->date_ini,6,4)."-".substr($request->date_ini,3,2)."-".substr($request->date_ini,0,2)."%2000:00:00";
            }

            if($request->date_end==""){
                $dend=date("Y-m-d")."%2023:59:59";;
            }
            else{
                $dend=substr($request->date_end,6,4)."-".substr($request->date_end,3,2)."-".substr($request->date_end,0,2)."%2023:59:59";
            }

            //if($request->date_ini!="" && $request->date_end!=""){

                // DB::connection()->enableQueryLog();

                //$clients = Client::getClientsUnSoldRecordsForReportsOS($dini, $dend)->get();

                // $queries = DB::getQueryLog();

                $data []= ['Nombre', 'Apellido', 'Telefono', 'Email', 'Ciudad','Fecha Registro'];

                $curl = curl_init();

                curl_setopt_array($curl, array(
                  CURLOPT_URL => "https://mautic.netwey.com.mx/api/contacts?search=email:!%22%22%20and%20origin:%22EC%22%20and%20isregister:%22N%22&limit=0&where%5B0%5D%5Bexpr%5D=gte&where%5B0%5D%5Bcol%5D=dateAdded&where%5B0%5D%5Bval%5D=".$dini."&where%5B1%5D%5Bexpr%5D=lte&where%5B1%5D%5Bcol%5D=dateAdded&where%5B1%5D%5Bval%5D=".$dend."",
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_ENCODING => "",
                  CURLOPT_MAXREDIRS => 10,
                  CURLOPT_TIMEOUT => 0,
                  CURLOPT_FOLLOWLOCATION => true,
                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                  CURLOPT_CUSTOMREQUEST => "GET",
                  CURLOPT_HTTPHEADER => array(
                    "Authorization: Basic YWRtaW46TFlQQHZRSzs4RDc4QlVpOg==",
                    "Cookie: 659488090dd5f2926bfca2796ea50e33=fdrkadr5lcg6iipbbfglvt79tq"
                  ),
                ));

                $response = curl_exec($curl);
                $err = curl_error($curl);

                curl_close($curl);


                if (!$err) {

                    $response = json_decode($response,true);

                      $i=0;
                      foreach ($response['contacts'] as $key => $contact) {

                        $i++;


                         //$user=DB::table('islim_clients')
                         $user=Client::getConnect('R')
                                ->where('email',$contact['fields']['all']['email'])
                                ->first();
                            if ($user==null or $user=="") {

                                $data []= [
                                    $contact['fields']['all']['firstname'],
                                    $contact['fields']['all']['lastname'],
                                    empty($contact['fields']['all']['phone']) ? '' : $contact['fields']['all']['phone'],
                                    empty($contact['fields']['all']['email']) ? '' : $contact['fields']['all']['email'],
                                    empty($contact['fields']['all']['city']) ? '' : $contact['fields']['all']['city'],
                                    empty(date_format(date_create($contact['dateAdded']),'d/m/Y H:i:s')) ? '' : date_format(date_create($contact['dateAdded']),'d/m/Y H:i:s'),

                                ];

                            }
                            else{


                                $curl2 = curl_init();

                                curl_setopt_array($curl2, array(
                                  CURLOPT_URL => "https://mautic.netwey.com.mx/api/contacts/".$contact['fields']['all']['id']."/edit",
                                  CURLOPT_RETURNTRANSFER => true,
                                  CURLOPT_ENCODING => "",
                                  CURLOPT_MAXREDIRS => 10,
                                  CURLOPT_TIMEOUT => 0,
                                  CURLOPT_FOLLOWLOCATION => true,
                                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                  CURLOPT_CUSTOMREQUEST => "PATCH",
                                  CURLOPT_POSTFIELDS =>"{\r\n  \"isregister\":\"Y\"\r\n}",
                                  CURLOPT_HTTPHEADER => array(
                                    "Content-Type: application/json",
                                    "Authorization: Basic YWRtaW46TFlQQHZRSzs4RDc4QlVpOg==",
                                    "Content-Type: application/json",
                                    "Cookie: 659488090dd5f2926bfca2796ea50e33=oq89ad9t2imrcf6jk9far0npan"
                                  ),
                                ));

                                $res = curl_exec($curl2);
                                curl_close($curl2);


                            }
                      }

                }


                $url = CommonHelpers::saveFile('/public/reportsOS', 'Leads EnvioCero', $data, 'leads_enviocero_report_'.date('d-m-Y'));

                return response()->json(array('url' => $url));

            //}
        }
    }


    public function penddingPaymentRef(){
        $html = view('pages.ajax.report_os.pendding_payment_ref')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getPenddingPaymentRefForReportOS(Request $request){
       if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini!="" && $request->date_end!=""){
                $clients = MercadoPago::getPenddingPaymentRef(
                                                $request->date_ini,
                                                $request->date_end
                                            );

                return DataTables::of($clients)
                                ->editColumn('phone_home', function($client){
                                    if(!empty($client->phone_home)){
                                        return $client->phone_home;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Metodo', function($client){
                                    if(!empty($client->Metodo)){
                                        return $client->Metodo;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('Estado', function($client){
                                    if(!empty($client->Estado)){
                                        if($client->Estado=="pending")
                                            return 'Pendiente';
                                        else
                                            return 'Cancelado';
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->make(true);

            }
        }
    }

    public function downloadPenddingPaymentRefForReportOS(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini==""){
                $dini="01/01/1900";
            }
            else{
                $dini=$request->date_ini;
            }

            if($request->date_end==""){
                $dend=date("d/m/Y");
            }
            else{
                $dend=$request->date_end;
            }

            //if($request->date_ini!="" && $request->date_end!=""){

                // DB::connection()->enableQueryLog();

                 $clients = MercadoPago::getPenddingPaymentRef($dini, $dend)->get();

                // $queries = DB::getQueryLog();

                $data []= ['Nombre','Apellido','Telefono','Email','Fecha_Registro','Equipo','Metodo','Estado'];

                foreach ($clients as $client) {
                    $data []= [
                                $client->Nombre,
                                $client->Apellido,
                                empty($client->Telefono) ? '' : $client->Telefono,
                                empty($client->Email) ? '' : $client->Email,
                                empty($client->Fecha_Registro) ? '' : $client->Fecha_Registro,
                                empty($client->Equipo) ? 'N/A' : $client->Equipo,
                                empty($client->Metodo) ? 'N/A' : $client->Metodo,
                                empty($client->Estado) ? 'N/A' : $client->Estado,
                              ];
                }

                $url = CommonHelpers::saveFile('/public/reportsOS', 'unsoldRecords', $data, 'pendding_payment_references_'.date('d-m-Y'));

                return response()->json(array('url' => $url));

            //}
        }
    }

    public function coverageStats(){
        $html = view('pages.ajax.report_os.coverage_stats')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getCoverageStats(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            //if(!empty($request->date_ini) && !empty($request->date_end)){
            if($request->date_ini!="" && $request->date_end!=""){
                $clients = StepRecord::getClientsCoverageConsult($request->date_ini, $request->date_end, $request->client_type, $request->result_type);
                //$clients = Sale::getSalesForReportOS($request->date_ini, $request->date_end);

                return DataTables::eloquent($clients)
                                ->editColumn('Cliente', function($client){
                                    if(!empty($client->Cliente)){
                                        return $client->Cliente;
                                    }else{
                                        return 'Anónimo';
                                    }
                                })
                                ->editColumn('Email', function($client){
                                    if(!empty($client->Email)){
                                        return $client->Email;
                                    }else{
                                        return '';
                                    }
                                })
                                ->editColumn('Telefono', function($client){
                                    if(!empty($client->Telefono)){
                                        return $client->Telefono;
                                    }else{
                                        return '';
                                    }
                                })
                                ->editColumn('Resultado', function($client){
                                    if(!empty($client->Resultado)){
                                        switch ($client->Resultado) {
                                            case 'NC': return 'Direcciones no Coinciden'; break;
                                            case 'DI': return 'Dirección Inválida'; break;
                                            case 'SD': return 'Sin Entrega a Domicilio'; break;
                                            case 'SC': return 'Sin Cobertura'; break;
                                            case 'OK': return 'Consulta Exitosa'; break;
                                        }
                                    }else{
                                        return '';
                                    }
                                })
                                ->editColumn('Fecha_Consulta', function($client){
                                    return date("d-m-Y H:i:s", strtotime($client->Fecha_Consulta));
                                })
                                ->make(true);

            }
        }
    }

    public function getCoverageStatsCharts(Request $request){

        if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini!="" && $request->date_end!=""){

                $stats = StepRecord::getCoverageStatsCharts($request->date_ini, $request->date_end);

                return response()->json(array('success' => true, 'data' => $stats));
            }
            return response()->json(array('success' => false));
        }

    }

    public function getNotCoverageStatsCharts(Request $request){

        if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini!="" && $request->date_end!=""){

                $stats = StepRecord::getNotCoverageStatsCharts($request->date_ini, $request->date_end);

                return response()->json(array('success' => true, 'data' => $stats));
            }
            return response()->json(array('success' => false));
        }

    }

    public function downloadCoverageStats(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if($request->date_ini==""){
                $dini="01/01/1900";
            }
            else{
                $dini=$request->date_ini;
            }

            if($request->date_end==""){
                $dend=date("d/m/Y");
            }
            else{
                $dend=$request->date_end;
            }

            //if($request->date_ini!="" && $request->date_end!=""){

                // DB::connection()->enableQueryLog();

                $clients = StepRecord::getClientsCoverageConsult($dini, $dend)->orderBy('Fecha_Consulta','DESC')->get();

                // $queries = DB::getQueryLog();

                $data []= ['Cliente', 'Email', 'Telefono', 'Resultado', 'Fecha Consulta'];

                foreach ($clients as $client) {

                    $Resultado = '';
                    switch ($client->Resultado) {
                        case 'NC': $Resultado = 'Direcciones no Coinciden'; break;
                        case 'DI': $Resultado = 'Dirección Inválida'; break;
                        case 'SD': $Resultado = 'Sin Entrega a Domicilio'; break;
                        case 'SC': $Resultado = 'Sin Cobertura'; break;
                        case 'OK': $Resultado = 'Consulta Exitosa'; break;
                    }
                    $data []= [
                                empty($client->Cliente) ? 'Anónimo' : $client->Cliente,
                                empty($client->Email) ? '' : $client->Email,
                                empty($client->Telefono) ? '' : $client->Telefono,
                                $Resultado,
                                empty($client->Fecha_Consulta) ? '' : $client->Fecha_Consulta,
                              ];
                }

                $url = CommonHelpers::saveFile('/public/reportsOS', 'coverageStats', $data, 'coverage_stats_report_'.date('d-m-Y'));

                return response()->json(array('url' => $url));

            //}
        }
    }
}