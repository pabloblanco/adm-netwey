<?php

namespace App\Http\Controllers;

use DataTables;
use App\Reports;
use App\TempCar;
use App\Organization;
use Illuminate\Http\Request;

class ReportOSTwoController extends Controller
{
  public function salesApi(){
    $orgs = Organization::getOrgs();

    $html = view('pages.ajax.report_os.api_sales', compact('orgs'))->render();

    return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
  }

  public function getSalesApi(Request $request){
    if($request->isMethod('post') && $request->ajax()){
      $filters = $request->all();

      $data = TempCar::getReportAPISales($filters)->get();

      if(!empty($filters['status_sale'])){
        if($filters['status_sale'] == 'G'){
          $data =  $data->filter(function ($value, $key) {
            return empty($value->status);
          });
        }

        if($filters['status_sale'] == 'E'){
          $data =  $data->filter(function ($value, $key) {
            return $value->status == 'I';
          });
        }

        if($filters['status_sale'] == 'F'){
          $data =  $data->filter(function ($value, $key) {
            return $value->status == 'A';
          });
        }
      }

      return DataTables::of($data)
              ->editColumn('seller', function($data){
                return $data->name.' '.$data->last_name;
              })
              ->editColumn('pack_type', function($data){
                if($data->pack_type == 'H'){
                  return 'HBB';
                }
                if($data->pack_type == 'M'){
                  return 'MIFI';
                }
                if($data->pack_type == 'MH'){
                  return 'Mifi huella alatan';
                }
                if($data->pack_type == 'T'){
                  return 'Telefonía';
                }
              })
              ->editColumn('msisdn', function($data){
                return !empty($data->msisdn) ? $data->msisdn : 'N/A';
              })
              ->editColumn('client', function($data){
                return $data->name_client.' '.$data->last_name_client;
              })
              ->editColumn('sub_monto', function($data){
                return '$'.number_format($data->sub_monto,2,'.',',');
              })
              ->editColumn('logic', function($data){
                if(!empty($data->folio_99)){
                  return '99min';
                }
                if(!empty($data->folio_voy)){
                  return 'Voywey';
                }
                if(!empty($data->folio_pro)){
                  return 'Prova';
                }

                return 'N/F';
              })
              ->editColumn('last_status', function($data){
                if(!empty($data->last_status)){
                  return $data->last_status;
                }
                return 'N/A';
              })
              ->editColumn('date_status', function($data){
                if(!empty($data->date_status)){
                  return date("d-m-Y", strtotime($data->date_status));
                }

                return 'N/A';
              })
              ->editColumn('monto_envio', function($data){
                return '$'.number_format($data->monto_envio,2,'.',',');
              })
              ->editColumn('delivery_orden', function($data){
                if(!empty($data->folio_99)){
                  return $data->folio_99;
                }
                if(!empty($data->folio_voy)){
                  return $data->folio_voy;
                }
                if(!empty($data->folio_pro)){
                  return $data->folio_pro;
                }

                return 'N/F';
              })
              ->editColumn('postal_code', function($data){
                if(!empty($data->postal_code_99)){
                  return $data->postal_code_99;
                }
                if(!empty($data->postal_code_v)){
                  return $data->postal_code_v;
                }

                return 'N/A';
              })
              ->editColumn('state', function($data){
                if(!empty($data->state_99)){
                  return $data->state_99;
                }
                if(!empty($data->state_v)){
                  return $data->state_v;
                }

                return 'N/A';
              })
              ->editColumn('city', function($data){
                if(!empty($data->city_99)){
                  return $data->city_99;
                }
                if(!empty($data->city_v)){
                  return $data->city_v;
                }

                return 'N/A';
              })
              ->editColumn('cod_prom', function($data){
                return !empty($data->cod_prom) ? $data->cod_prom : 'N/A';
              })
              ->editColumn('discount', function($data){
                return '$'.number_format($data->discount,2,'.',',');
              })
              ->editColumn('sale_date', function($data){
                return date("d-m-Y", strtotime($data->sale_date));
              })
              ->editColumn('del_date', function($data){
                return !empty($data->del_date) ? date("d-m-Y", strtotime($data->del_date)) : 'N/A';
              })
              ->editColumn('active_days', function($data){
                if($data->status == 'A'){
                  return $data->active_days;
                }

                return 'N/A';
              })
              ->editColumn('status_sale', function($data){
                if(empty($data->status)){
                  return 'Generada';
                }

                if($data->status == 'A'){
                  return 'Finalizada';
                }

                if($data->status == 'I'){
                  return 'Entregada';
                }
              })
              ->make(true);
    }
  }

  public function downloadSalesAPIReport(Request $request){
    if($request->isMethod('post')){
        $inputs = $request->all();

        $report = Reports::getConnect('W');
        $report->name_report = 'reporte_ventas_api';

        unset($inputs['emails']);
        unset($inputs['_token']);

        $report->filters = json_encode($inputs);
        $report->user_profile = session('user')->profile->type;
        $report->user = session('user')->email;
        $report->status = 'C';
        $report->date_reg = date('Y-m-d H:i:s');

        $report->save();

        return response()->json(array('error' => false));
    }
}
}
