<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\SaleReportView;
use App\User;
use App\Bank;
use App\SellerInventory;
use App\Inventory;
use App\Product;
use App\Sale;
use App\Concentrator;
use App\UserWarehouse;
use App\Warehouse;
use App\AssignedSales;
use App\Service;
use App\Client;
use App\ClientNetwey;
use App\Broadband;
use App\Deposit;
use App\OrgWarehouse;
use App\Organization;
use App\mobility;
use App\SimSwap;
use App\Reports;
use App\Financing;
use App\UserDeposit;
use App\BankDeposits;
use App\Balance;
use App\AssignedSaleDetails;
use App\SaleInstallment;
use App\SaleInstallmentDetail;
use App\TokensInstallments;
use App\ExpiredInstallment;
use App\FiberZone;
use DataTables;
use Illuminate\Support\Collection;
use DateTime;
use Illuminate\Support\Facades\Storage;
use App\Helpers\CommonHelpers;

use Illuminate\Support\Facades\Log;

class ReportsController extends Controller {

	public function sellerinv ($user_email) {
        $user = User::find($user_email);
        $user_inv = SellerInventory::where(['users_email'=>$user_email,'status'=>'A'])->get();
        $articles = array();
        $products = array ();
        foreach ($user_inv as $article) {
            $inv = Inventory::find($article->inv_arti_details_id);
            $prod = Product::find($inv->inv_article_id);
            $inv->title = $prod->title;
            $inv->status =$article->status;
            $products[] = $inv;
        }
        $user->products = $products;
        return $user;
    }

    public function status_seller_view () {
    	if(session('user')->platform == 'admin'){
            $users = User::whereIn('status',['A'])->where('platform','vendor')->get();
        } else {
            $users = User::where(['status'=>'A','parent_email' => session('user.email')])->where('platform','vendor')->get();
        }
    	$html = view('pages.ajax.report.sellerstatus', compact('users'))->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function seller_detail_view (Request $request) {
    	$inventories = SellerInventory::getSellerInventoryReport($request->seller, null, $request->date_ini, $request->date_end);
        $sells = Sale::getSaleReport (null, null, $request->seller, $request->date_ini, $request->date_end, ['E'], null);
    	$html = view('pages.ajax.report.sellerstatusDetail', compact('inventories', 'sells'))->render();
    	return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    //Retorna usuarios segun filtro indicado
    public function getFilterUsersUR(Request $request){
        if($request->isMethod('post')){
            $org = $request->input('org');
            $coord = $request->input('coord');

            $supervisors = User::select('name','last_name','email');
            $sellers = User::select('name','last_name','email');

            if(session('user')->platform == 'admin' || session('user')->platform == 'call'){
                if(session('user')->profile->type == "master"){
                    $supervisors->where(['platform' => 'coordinador'])->whereIn('status',['A']);
                    $sellers->where(['platform' => 'vendor'])->whereIn('status',['A']);
                }else{
                    $supervisors->where(['platform' => 'coordinador'])->where([['status',['A']],['id_org',session('user')->id_org]]);
                    $sellers->where(['platform' => 'vendor'])->where([['status',['A']],['id_org',session('user')->id_org]])->get();
                }
            }else{
                $supervisors->where(['email' => session('user')->email]);
                $sellers->where(['platform' => 'vendor', 'parent_email' => session('user')->email])->whereIn('status',['A']);
            }

            if(!empty($org)){
                $supervisors->where('id_org',$org);
                $sellers->where('id_org',$org);
            }

            if(!empty($coord)){
                $sellers->where('parent_email',$coord);
            }

            return response()->json(array('cs' => $supervisors->get(), 'ss' => $sellers->get()));
        }
    }

    public function viewUpsOrRecharges(Request $request, $view) {
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $services = Service::getActiveServiceByType();

        $products = Product::getActiveProduct();

        $coverage = FiberZone::getfiberZone();

        $serviceabilities = Broadband::getConnect('R')->whereNotIn('status',['T'])->get();

        $html = view('pages.ajax.report.uporrecharge', compact('orgs', 'services', 'products', 'serviceabilities', 'view', 'coverage'))->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewUpsOrRechargesDetail(Request $request, $view) {
        $html = view('pages.ajax.report.uporrechargeDetail', compact('view'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function detailDtUP(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $filters = $request->all();
            $filters['user_profile'] = session('user')->profile->type;
            $filters['user'] = session('user')->email;

            $sale_type=[
                'T'=>'Telefonía',
                'H'=>'Internet Hogar',
                'M'=>'MIFI Nacional',
                'MH'=>'MIFI Altan',
                'F'=>'Fibra'
            ];

            $report = Sale::getSaleReportUps($filters);

            return DataTables::of($report)
                               ->editColumn('business_name', function($data){
                                    return !empty($data->business_name)? $data->business_name : 'N/A';
                                })
                               ->editColumn('user_name', function($data){
                                    if($data->sale_type == 'F'){
                                        return $data->sellerf_name.' '.$data->sellerf_last_name;
                                    }
                                    return $data->user_name.' '.$data->user_last_name;
                                })
                               ->editColumn('coord_name', function($data){
                                    return !empty($data->coord_name) ? $data->coord_name.' '.$data->coord_last_name : $data->user_name.' '.$data->user_last_name;
                                })
                               ->editColumn('installer_name', function($data){
                                    return !empty($data->installer_name) ? $data->installer_name.' '.$data->installer_last_name : 'N/A';
                                })
                               ->editColumn('iccid', function($data){
                                    return !empty($data->iccid)?$data->iccid : 'N/A';
                                })
                               ->editColumn('client_name', function($data){
                                    return $data->client_name.' '.$data->client_lname;
                                })
                               ->editColumn('client_phone', function($data){
                                    return !empty($data->client_phone)?$data->client_phone : 'N/A';
                                })
                               ->editColumn('client_phone2', function($data){
                                    return !empty($data->client_phone2)?$data->client_phone2 : 'N/A';
                                })
                               ->editColumn('amount', function($data){
                                    return number_format($data->amount,2,'.',',');
                                })
                               ->editColumn('type_buy', function($data){
                                    return $data->type_buy == 'CO' ? 'Contado' : 'Crédito';
                                })
                               ->editColumn('lat', function($data){
                                    return !empty($data->lat) ? $data->lat : 'N/A';
                                })
                               ->editColumn('lng', function($data){
                                    return !empty($data->lng) ? $data->lng : 'N/A';
                                })
                               ->editColumn('sale_type', function($data) use ($sale_type){
                                    if(!empty($sale_type[$data->sale_type])){
                                        return $sale_type[$data->sale_type];
                                    }
                                    else{
                                        return $sale_type['H'];
                                    }
                                })
                               ->editColumn('billing', function($data){
                                    return !empty($data->billing)? $data->billing : 'No Facturado';
                                })
                               ->editColumn('from', function($data){
                                    return !empty($data->from) && $data->from == 'A'? 'API' : 'Seller';
                                })
                               ->editColumn('campaign', function($data){
                                    if(empty($data->campaign)){
                                        return 'N/A';
                                    }

                                    return $data->campaign;
                                })
                                ->editColumn('user_email', function($data){
                                    if($data->sale_type == 'F'){
                                        return $data->sellerf_email;
                                    }
                                    return $data->user_email;
                                })
                                ->editColumn('coord_email', function($data){
                                     return !empty($data->coord_email) ? $data->coord_email : $data->user_email;
                                })
                                ->editColumn('installer_email', function($data){
                                     return !empty($data->installer_email) ? $data->installer_email : 'N/A';
                                })
                                ->editColumn('user_locked', function($data){
                                      return (!empty($data->user_locked) && $data->user_locked == 'Y') ? 'Si' : 'No';
                                })
                                ->editColumn('typePayment', function($data){
                                    return !empty($data->typePayment) ? $data->typePayment : 'S/I';
                                })
                                ->editColumn('zone_name', function($data){
                                    return !empty($data->zone_name) ? $data->zone_name : 'N/A';
                                })
                               ->make(true);
        }
    }

    public function detailDTRecharge(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $filters = $request->all();
            $filters['user_profile'] = session('user')->profile->type;
            $filters['user'] = session('user')->email;

            $sale_type=[
                'T'=>'Telefonía',
                'H'=>'Internet Hogar',
                'M'=>'MIFI Nacional',
                'MH'=>'MIFI Altan',
                'F' => 'Fibra'
            ];

            $report = Sale::getSaleReportRecharge($filters);

            return DataTables::of($report)
                               ->editColumn('folio', function($data){
                                    return !empty($data->folio) && (!empty($data->concentrator) && $data->concentrator == 'OXXO')? $data->folio : 'N/A';
                               })
                               ->editColumn('concentrator', function($data){
                                    return !empty($data->concentrator)? $data->concentrator : 'N/A';
                                })
                               ->editColumn('user_name', function($data){
                                    return !empty($data->user_name)? ($data->user_name.' '.$data->user_last_name) : 'N/A';
                                })
                               ->editColumn('installer_name', function($data){
                                    return !empty($data->installer_name) ? $data->installer_name.' '.$data->installer_last_name : 'N/A';
                                })
                               ->editColumn('client_name', function($data){
                                    return $data->client_name.' '.$data->client_lname;
                                })
                               ->editColumn('client_phone', function($data){
                                    return !empty($data->client_phone)?$data->client_phone : 'N/A';
                                })
                               ->editColumn('client_phone2', function($data){
                                    return !empty($data->client_phone2)?$data->client_phone2 : 'N/A';
                                })
                               ->editColumn('amount', function($data){
                                    return number_format($data->amount,2,'.',',');
                                })
                               ->editColumn('type_buy', function($data){
                                    return $data->type_buy == 'CO' ? 'Contado' : 'Crédito';
                                })
                               ->editColumn('lat', function($data){
                                    return !empty($data->lat) ? $data->lat : 'N/A';
                                })
                               ->editColumn('lng', function($data){
                                    return !empty($data->lng) ? $data->lng : 'N/A';
                                })
                               ->editColumn('sale_type', function($data) use ($sale_type){
                                    if(!empty($sale_type[$data->sale_type])){
                                        return $sale_type[$data->sale_type];
                                    }
                                    else{
                                        return $sale_type['H'];
                                    }
                                })
                               ->editColumn('billing', function($data){
                                    return !empty($data->billing)? $data->billing : 'No Facturado';
                                })
                               ->editColumn('user_email', function($data){
                                     return !empty($data->user_email) ? $data->user_email : 'N/A';
                                 })
                               ->editColumn('installer_email', function($data){
                                     return !empty($data->installer_email) ? $data->installer_email : 'N/A';
                                 })
                               ->editColumn('iccid', function($data){
                                    return !empty($data->iccid)?$data->iccid : 'N/A';
                                })
                                ->editColumn('zone_name', function($data){
                                    return !empty($data->zone_name) ? $data->zone_name : 'N/A';
                                })
                               ->make(true);
        }
    }

    public function downloadCSVReportUR(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            $report = Reports::getConnect('W');

            if($request->view == 'ups')
                $report->name_report = 'reporte_altas';
            else
                $report->name_report = 'reporte_recargas';

            $report->email = $inputs['emails'];

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

    //Metodo para descargar reportes almacenados en el directorio de reportes ejm "downloadCSVReportUR"
    //$delete = 1 borra archivo, otro valor deja el archivo en el direcorio
    //recibe por get p = path del archivo ejm "/public/reports/..."
    public function downloadReports(Request $request, $delete = false, $id = false){
        $path = $request->get('p');

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

    /*
        Descarga los reportes que son enviados por email.
    */
    public function downloadReportsEmail(Request $request, $delete = false, $id = false){
        if($id){
            $id = base64_decode($id);

            $report = Reports::select('id', 'download_url')->where([['id', $id], ['status', '!=', 'T']])->first();

            if(!empty($report)){
                $headers = array(
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );

                $url = $report->download_url;

                $report->status = 'D';
                $report->save();

                if($delete == '1')
                    return response()->download(storage_path('/app'.$url))->deleteFileAfterSend(true);
                else
                    return response()->download(storage_path('/app'.$url),null,$headers);
            }
        }
    }

    public function viewSales(Request $request) {
        $concentrators = Concentrator::getConcentrators();
        $coverage = FiberZone::getfiberZone();

        $html = view('pages.ajax.report.sales', compact('concentrators', 'coverage'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewSalesDetail(Request $request){
        $html = view('pages.ajax.report.salesDetail')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getSalesDT(Request $request) {
        $filters = $request->all();
        $filters['user_profile'] = session('user')->profile->type;
        $filters['user'] = session('user')->email;

        $report = Sale::getSaleReportAll($filters);

        return DataTables::of($report)
                            ->editColumn('concentrator', function($dato){
                                return !empty($dato->concentrator)? $dato->concentrator : 'N/A';
                            })
                            ->editColumn('type', function($dato){
                                return $dato->type == 'P'? 'Alta' : 'Recarga';
                            })
                            ->editColumn('pack', function($dato){
                                return $dato->type == 'P'? $dato->pack : 'N/A';
                            })
                            ->editColumn('article', function($dato){
                                return $dato->type == 'P'? $dato->article : 'N/A';
                            })
                            ->editColumn('order_altan', function($dato){
                                return !empty($dato->order_altan)? $dato->order_altan : 'N/A';
                            })
                            ->editColumn('amount', function($dato){
                                return number_format($dato->amount,2,'.',',');
                            })
                            ->editColumn('user_name', function($data){
                                return !empty($data->user_name)? ($data->user_name.' '.$data->user_last_name) : 'N/A';
                            })
                            ->editColumn('installer_name', function($data){
                                return !empty($data->installer_name)? ($data->installer_name.' '.$data->installer_last_name) : 'N/A';
                            })
                            ->editColumn('name', function($dato){
                                return $dato->client_name.' '.$dato->client_lname;
                            })
                            ->editColumn('client_phone', function($dato){
                                return !empty($dato->client_phone)? $dato->client_phone : 'N/A';
                            })
                            ->editColumn('sale_type', function($dato){

                                switch ($dato->sale_type) {
                                    case 'T':  $ret = 'Telefonía'; break;
                                    case 'F':  $ret = 'Fibra'; break;
                                    case 'M':  $ret = 'MIFI'; break;
                                    default: $ret = 'Internet Hogar'; break;
                                }

                                return $ret;
                            })
                            ->editColumn('from', function($dato){
                                if($dato->type == 'P'){
                                    return !empty($dato->from) && $dato->from == 'A'? 'API' : 'Seller';
                                }else{
                                    return 'N/A';
                                }
                            })
                            ->editColumn('campaign', function($data){
                                if(empty($data->campaign) || $data->type != 'P'){
                                    return 'N/A';
                                }

                                return $data->campaign;
                            })
                            ->editColumn('zone_name', function($data){
                                return !empty($data->zone_name) ? $data->zone_name : 'N/A';
                            })
                            ->editColumn('isPhoneRef', function($data){
                                if($data->sale_type == 'T'){
                                    if($data->isPhoneRef == 'Y'){
                                        return 'SI';
                                    }
                                    else{
                                        return 'NO';
                                    }
                                }
                                else{
                                    return 'NO';
                                }
                            })
                            ->editColumn('phoneRefBy', function($data){
                                if($data->sale_type == 'T'){
                                    if($data->isPhoneRef == 'Y'){
                                        return $data->phoneRefBy;
                                    }
                                    else{
                                        return 'N/A';
                                    }
                                }
                                else{
                                    return 'N/A';
                                }
                            })
                            ->make(true);
    }

    public function downloadXLSReportSales(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            //if(!empty($inputs['emails'])){
            //$report = new Reports;
            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_ventas';

            $report->email = $inputs['emails'];

            unset($inputs['emails']);
            unset($inputs['_token']);

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
            //}
        }

        return response()->json(array('error' => true));
    }

    public function viewConcentrators(Request $request) {
        $concentrators = Concentrator::getConcentrators();
        $html = view('pages.ajax.report.concentrators', compact('concentrators'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewConcentratorsDetail(Request $request) {
        $html = view('pages.ajax.report.concentratorsDetail')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function detailDtConc(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $filters = $request->all();
            $filters['user_profile'] = session('user')->profile->type;
            $filters['user'] = session('user')->email;

            $report = Sale::getSaleReportConcentrator($filters);

            return DataTables::of($report)
                                ->editColumn('concentrator', function($dato){
                                    return !empty($dato->concentrator)? $dato->concentrator : 'N/A';
                                })
                                ->editColumn('type', function($dato){
                                    return $dato->type == 'P'? 'Alta' : 'Recarga';
                                })
                                ->editColumn('pack', function($dato){
                                    return $dato->type == 'P'? $dato->pack : 'N/A';
                                })
                                ->editColumn('article', function($dato){
                                    return $dato->type == 'P'? $dato->article : 'N/A';
                                })
                                ->editColumn('amount', function($dato){
                                    return number_format($dato->amount,2,'.',',');
                                })
                                ->editColumn('sale_type', function($dato){
                                    switch ($dato->sale_type) {
                                        case 'T':  $ret = 'Telefonía'; break;
                                        case 'F':  $ret = 'Fibra'; break;
                                        case 'M':  $ret = 'MIFI'; break;
                                        default: $ret = 'Internet Hogar'; break;
                                    }

                                    return $ret;
                                })
                               ->make(true);
        }
    }

    public function downloadXLSReportConc(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            //if(!empty($inputs['emails'])){
            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_concentradores';

            //$report->email = $inputs['emails'];

            unset($inputs['emails']);
            unset($inputs['_token']);

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
            //}
        }
        return response()->json(array('error' => true));
    }

    public function getWarehouses($warehouse = false){
        if($warehouse == "ALL"){
            $warehouses = Warehouse::select('id', 'name')->where('status', 'A')->get()->toArray();
        }elseif($warehouse){
            $warehouses = OrgWarehouse::select(
                                        'islim_warehouses.id',
                                        'islim_warehouses.name'
                                      )
                                      ->join(
                                        'islim_warehouses',
                                        'islim_warehouses.id',
                                        '=',
                                        'islim_wh_org.id_wh'
                                      )
                                      ->where([
                                        ['islim_warehouses.status', 'A'],
                                        ['islim_wh_org.id_org', $warehouse]
                                      ])
                                      ->get()
                                      ->toArray();
            /*$warehouses = Warehouse::select('id', 'name')
                                     ->where([['status', 'A'], ['id', $warehouse]])
                                     ->get()
                                     ->toArray();*/
        }

        return response()->json($warehouses);
    }

    public function viewWarehouses(Request $request) {
        $ids = array();
        $status = ['A'];

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        // if(session('user')->profile->type != "master"){
            $userWarehouses = OrgWarehouse::select('islim_warehouses.name','islim_warehouses.id')
                                    ->select('id_wh')
                                    ->join('islim_warehouses', 'islim_warehouses.id', '=', 'islim_wh_org.id_wh')
                                    ->whereIn('id_org',$orgs->pluck('id'))
                                    ->get();
        // }else{
        //     $userWarehouses = UserWarehouse::whereIn('status', $status)->where(['users_email' => session('user')->email])->get();
        //     //$orgs = Organization::getOrgs();
        // }

        foreach ($userWarehouses as $item) {
            if(!empty($item->warehouses_id))
                $ids[] = $item->warehouses_id;
            if(!empty($item->id_wh))
                $ids[] = $item->id_wh;
        }

        $warehouses = Warehouse::getConnect('R')->select('id', 'name')->whereIn('status', $status);
        if (count($ids) > 0) {
            $warehouses = $warehouses->whereIn('id', $ids);
        }
        $warehouses = $warehouses->get();

        $products = Product::getActiveProduct();

        $html = view('pages.ajax.report.warehouses', compact('warehouses', 'products', 'orgs'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewWarehousesDetail(Request $request){
        $report = Warehouse::getReport($request->warehouse, $request->product, $request->org);
        $totalxpro = array();
        /*foreach ($report as $item) {
            foreach ($totalxpro as $value) {
                if($value->name == $item->pro_name){
                    $value->total += count($item->inv);
                }
            }
            $obj = new class{};
            $obj->name = $item->pro_name;
            $obj->total = count($item->inv);
            $totalxpro[] = $obj;

        }*/
        $html = view('pages.ajax.report.warehousesDetail', compact('report','totalxpro'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function filterByTypeUser(Request $request){
        if($request->isMethod('post')){
            if(session('user')->profile->type == "master"){
                $users = User::select('email', 'platform', 'name', 'last_name');

                if(!empty($request->org)){
                    $users->where('id_org', $request->org);
                }
            }
            else{
                $users = User::select('email', 'platform', 'name', 'last_name')
                                ->where('id_org',session('user')->id_org);
            }

            if(!empty($request->coor)){
                $users->where('parent_email', $request->coor);
            }

            $users->whereIn('platform', array('vendor', 'coordinador'))
                  ->orderBy('name', 'desc');

            return response()->json(array('users' => $users->get()));
        }
    }

    public function filterUserByType(Request $request){
        if(!empty($request->q)){
            $users = User::getConnect('R')->select(
                            'islim_users.name',
                            'islim_users.last_name',
                            'islim_users.email',
                            DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username')
                         )
                         ->where(DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name)'), 'like', '%'.$request->q.'%')
                         /*->where(function($query) use($request){
                            $query->where('islim_users.name', 'like', '%'.$request->q.'%')
                                    ->orWhere('islim_users.last_name', 'like', '%'.$request->q.'%');
                         })*/
                         //->whereIn('islim_users.platform', ['vendor', 'coordinador', 'admin'])
                         //->whereIn('islim_profile_details.id_profile', [17, 18, 19, 11, 10])
                         ->where('islim_users.status', 'A');

            if(session('user')->profile->type == "master"){
                if(!empty($request->org)){
                    $users->where('id_org', $request->org);
                }
            }else{
                $users->where('id_org', session('user')->id_org);
            }

            if($request->call == 'regional'){
                $users->join(
                            'islim_profile_details', 
                            'islim_profile_details.user_email', 
                            'islim_users.email'
                        )
                        ->where([
                            ['islim_users.platform', 'admin'],
                            ['islim_profile_details.status', 'A'],
                            ['islim_profile_details.id_profile', 17]
                        ]);            
            }

            if($request->call == 'coord'){
                if(!empty($request->regional)){
                    $users->where('islim_users.parent_email', $request->regional);
                }
                $users->where('islim_users.platform', 'coordinador');
            }

            if($request->call == 'seller'){
                if(!empty($request->coord)){
                    $users->where('islim_users.parent_email', $request->coord);
                }
                $users->where('islim_users.platform', 'vendor');
            }
            
            $users = $users->limit(10)->get();
            
            return response()->json(array('success' => true, 'users' => $users));
        }
        return response()->json(array('success' => false));
    }

    public function viewSellerInventory(Request $request){

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        /*$users = User::getConnect('R')
                    ->select(
                        'islim_users.email', 
                        'islim_users.platform', 
                        'islim_users.name', 
                        'islim_users.last_name'
                    )
                    ->join(
                        'islim_profile_details',
                        'islim_profile_details.user_email',
                        'islim_users.email'
                    )
                    ->whereIn('islim_users.platform', ['vendor', 'coordinador', 'admin'])
                    ->whereIn('islim_profile_details.id_profile', [17, 18, 19, 11, 10])
                    ->whereIn('islim_users.id_org', $orgs->pluck('id'))
                    ->orderBy('islim_users.name', 'desc');

        $status = array('A');

        if(session('user')->platform != 'admin'){
            $users = $users->where(['islim_users.parent_email' => session('user')->email]);
        }

        $users = $users->whereIn('islim_users.status', $status)->get();*/

        $products = Product::getConnect('R')->select('id', 'title')->whereIn('status', ['A'])->get();
        //'users',
        $html = view('pages.ajax.report.sellerInventory', compact('products', 'orgs'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    protected function String2Hex($string){
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }

    protected function sellermutator ($user = null, $product = null, $org = null) {
        $report = array();
        $obj2 = new Collection;
        $temp = SellerInventory::getSellerInventoryReport($user, $product, null, null)['inventory'];

        if(session('user')->profile->type != "master"){
            $status = array('A');
            $types = array('vendor', 'coordinador');

            $emails = array();
            if (isset($user))
                $emails[] = $user;

            $usersOrg = User::getReport($emails,null,$status,$types);

            foreach ($usersOrg as $user) {
                $cc = SellerInventory::getSellerInventoryReport($user->email, $product, null, null)['inventory'];
                if(count($cc) > 0){
                    foreach ($cc as $x)
                        $obj2->push($x);
                }
            }
            $temp = $obj2;
        }

        if(session('user')->profile->type == "master" && !empty($org)){
            $status = array('A');
            $types = array('vendor', 'coordinador', 'admin');

            $emails = array();
            if (isset($user))
                $emails[] = $user;

            $usersOrg = User::getReport($emails,null,$status,$types,$org);

            $product = null;
            if (isset($request->product)) {
                $product = $request->product;
            }

            //$temp = [];
            foreach ($usersOrg as $user) {
                $cc = SellerInventory::getSellerInventoryReport($user->email, $product, null, null)['inventory'];
                if(count($cc) > 0){
                    foreach ($cc as $x)
                        $obj2->push($x);
                }
            }
            $temp = $obj2;
        }

        foreach ($temp as $item){
            $key = $this->String2Hex($item->article.$item->users_email);
            if (array_key_exists($key,$report)) {
                $report[$key]->inv[] = $item;
            } else {
                $obj = new class{};
                $obj->user_email = $item->users_email;
                $obj->user_name = $item->user_name.' '.$item->user_lname;
                $obj->coordinador = !empty($item->parent_email) ? $item->parent_email : $item->users_email;
                $obj->pro_id = Product::getConnect('R')->find(Inventory::getConnect('R')->find($item->inv_arti_details_id)->inv_article_id)->id;
                $obj->pro_name = $item->article;
                $obj->inv[] = $item;
                $report[$key] = $obj;
            }
        }

        $obj = new Collection;
        foreach ($report as $value){
            if(!empty($value->coordinador))
                $coordinador = User::getConnect('R')->select('name', 'last_name')->where('email', $value->coordinador)->first();

            $obj->push([
                'user_email' => $value->user_email,
                'user_name' => $value->user_name,
                'coordinador' => !empty($coordinador) ? $coordinador->name.' '.$coordinador->last_name : $value->user_name,
                'pro_id' => $value->pro_id,
                'pro_name' => $value->pro_name,
                'inv' => json_encode($value->inv)

            ]);
        }

        return $obj;
    }

    public function sellerinvdt($user,$product,$org) {
        $report = $this->sellermutator($user == '0' ? null : $user, $product == '0' ? null : $product ,$org == '0' ? null : $org);
        return DataTables::of($report)
            ->editColumn('user_email', '{{$user_email}}')
            ->editColumn('user_name', '{{$user_name}}')
            ->editColumn('coordinador', '{{$coordinador}}')
            ->editColumn('pro_id', '{{$pro_id}}')
            ->editColumn('pro_name', '{{$pro_name}}')
            ->editColumn('count', '{{count(json_decode($inv))}}')
            ->editColumn('inv', '{{$inv}}')
            ->toJson();
    }

    public function downloadInvSCReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $report = $this->sellermutator(
                                $request->user == '0' ? null : $request->user,
                                $request->product == '0' ? null : $request->product,
                                $request->org == '0' ? null : $request->org
                            );

            $dataxls []= [
                'Usuario',
                'Email',
                'Supervisor',
                'Id Producto',
                'Producto',
                'MSISDN',
                'IMEI',
                'ICCID',
                'Fecha creacion',
                'Fecha primera asignacion',
                'Fecha ultima asignacion'
            ];

            foreach ($report as $data){
                $data['inv'] = json_decode($data['inv'], true);
                foreach ($data['inv'] as $inv){
                    $dataxls []= [
                        $data['user_name'],
                        $data['user_email'],
                        $data['coordinador'],
                        $data['pro_id'],
                        $data['pro_name'],
                        $inv['msisdn'],
                        $inv['imei'],
                        $inv['iccid'],
                        $inv['birth_modem'],
                        $inv['first_assignment'],
                        $inv['date_reg']
                    ];
                }
            }

            $url = CommonHelpers::saveFile('/public/reports', 'reports', $dataxls, 'inv_asig_cv_'.date('d-m-Y'));

            return response()->json(['success' => true, 'url' => $url]);
        }
    }

    public function viewSellerInventoryDetail(Request $request) {
        //$status = array('A');
        //$types = array('vendor', 'coordinador');

        /*$emails = array();
        if (isset($request->user))
            $emails[] = $request->user;*/

        //$report = $this->sellermutator($request->user, $request->product, $request->org);
        //, compact('report')

        $html = view('pages.ajax.report.sellerInventoryDetail')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewClients(Request $request) {
        $services = Service::getActiveServiceByType();

        $html = view('pages.ajax.report.clients', compact('services'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getDNForReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->q)){
                $numbers = ClientNetwey::select('msisdn')
                                        ->where([
                                            ['status', '!=', 'T'],
                                            ['msisdn', 'like', $request->q.'%']
                                        ])
                                        ->limit(10);

                if (session('user.platform') != 'admin'){
                    $users_email = User::select('email')
                                         ->where('parent_email', session('user.email'))
                                         ->get()
                                         ->pluck('email');

                    $users_email[] = session('user.email');

                    $client = Client::select('dni')
                                      ->whereIn('reg_email', $users_email)
                                      ->orWhereIn('user_mail', $users_email)
                                      ->get()
                                      ->pluck('dni');

                    $numbers = $numbers->whereIn('clients_dni', $client);
                }

                $numbers = $numbers->get();

                return response()->json(array('success' => true, 'clients' => $numbers));
            }

            return response()->json(array('success' => false));
        }
    }

    public function viewClientsDetail(Request $request) {
        $html = view('pages.ajax.report.clientsDetail')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function dtClient(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $msisdns = [];

            if($request->hasFile('msisdns_file')){
                if($request->file('msisdns_file')->isValid()){
                    $file = $request->file('msisdns_file');

                    $path = base_path('uploads');
                    $file_name = $file->getClientOriginalName();
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $file->move($path, $file_name);

                    ini_set('auto_detect_line_endings',TRUE);
                    if (($gestor = fopen($path.'/'.$file_name, "r")) !== FALSE) {
                        while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                            foreach($datos as $item) {
                                $msisdns[] = $item;
                            }
                        }
                        fclose($gestor);
                    } else {

                    }
                    ini_set('auto_detect_line_endings',FALSE);

                    unlink($path.'/'.$file_name);
                }else{
                    $errors[] = 'El archivo no puede ser validado';
                }
            }

            if ($request->has('msisdn_select')) {
                $msisdns = explode(",", $request->msisdn_select);
            }

            $report = ClientNetwey::getReport(
                                        !empty($request->service)? [$request->service] : [],
                                        ['A', 'S'],
                                        $request->date_ini,
                                        $request->date_end,
                                        $msisdns,
                                        $request->type_line
                                    );

            return DataTables::of($report)
                                ->editColumn('name', function($dato){
                                    return $dato->name.' '.$dato->last_name;
                                })
                                ->editColumn('email', function($dato){
                                    return !empty($dato->email)? $dato->email : 'N/A';
                                })
                                ->editColumn('dn_type', function($dato){
                                    switch ($dato->dn_type) {
                                        case 'T':  $ret = 'Telefonía'; break;
                                        case 'F':  $ret = 'Fibra'; break;
                                        case 'M':  $ret = 'MIFI'; break;
                                        default: $ret = 'Internet Hogar'; break;
                                    }
                                    return $ret;
                                })
                                ->editColumn('speed', function($dato){
                                    return !empty($dato->speed) ? $dato->speed : 'N/A';
                                })
                                ->editColumn('typePayment', function($data){
                                    return !empty($data->typePayment) ? $data->typePayment : 'S/I';
                                })
                               ->make(true);
        }
    }

    public function downloadXLSClient(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            //if(!empty($inputs['emails'])){
            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_clientes';

            //$report->email = $inputs['emails'];

            unset($inputs['emails']);
            unset($inputs['_token']);
            unset($inputs['order']);
            unset($inputs['search']);
            unset($inputs['start']);
            unset($inputs['length']);
            unset($inputs['draw']);
            unset($inputs['columns']);

            if($request->hasFile('msisdns_file')){
                if($request->file('msisdns_file')->isValid()){
                    $file = $request->file('msisdns_file');

                    $path = base_path('uploads');
                    $file_name = $file->getClientOriginalName();
                    if (!file_exists($path)) {
                        mkdir($path, 0777, true);
                    }
                    $file->move($path, $file_name);

                    ini_set('auto_detect_line_endings',TRUE);
                    if (($gestor = fopen($path.'/'.$file_name, "r")) !== FALSE) {
                        $msisdns = '';
                        while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) {
                            $msisdns .= implode(",", $datos);
                        }
                        fclose($gestor);
                        $inputs['msisdn_select'] = $msisdns;
                    }
                    ini_set('auto_detect_line_endings',FALSE);

                    unlink($path.'/'.$file_name);
                }
            }

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
            //}
        }
        return response()->json(array('error' => true));
    }

    public function viewClientsExport(Request $request) {
        $status = array('A', 'S');

        $services = array();
        if (isset($request->service))
            $services[] = $request->service;

        $data = ClientNetwey::getReport($services, $status, $request->date_ini, $request->date_end);
        return $this->downloadReport($data, 'Reporte de clientes');
    }

    public function viewProspects(Request $request) {
        $sellers = User::getConnect('R')->select('email', 'name', 'last_name');
        $status = array('A');
        $sellers = $sellers->whereIn('status', $status)->get();

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        // if(session('user')->profile->type == "master")
        //     $orgs = Organization::select('id','business_name')->where('status','A')->get();

        $html = view('pages.ajax.report.prospects', compact('sellers', 'orgs'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    //Retorna usuarios segun la organizacion seleccionada
    public function getSellerProspect(Request $request){
        if($request->isMethod('post')){
            $org = $request->input('org');

            $sellers = User::getConnect('R')->select('email', 'name', 'last_name')
                             ->where('status', 'A');

            if(!empty($org))
                $sellers = $sellers->where('id_org', $org);
            else{
                $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                $sellers = $sellers->whereIn('id_org', $orgs->pluck('id'));
            }

            return response()->json(array('data' => $sellers->get()));
        }
    }

    public function viewProspectsDetail(Request $request) {
        /*$sellers = array();
        if (isset($request->seller))
            $sellers[] = $request->seller;

        $report = Client::getReport($sellers, $request->date_ini, $request->date_end, $request->org);*/

        $html = view('pages.ajax.report.prospectsDetail')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function dtProspect(Request $request){
        if($request->isMethod('post') && $request->ajax()){

            $sellers = array();
            if (isset($request->seller))
                $sellers[] = $request->seller;

            $report = Client::getReport(
                                $sellers,
                                $request->date_ini,
                                $request->date_end,
                                $request->org,
                                true
                            );

            return DataTables::of($report)
                                ->editColumn('name', function($dato){
                                    return $dato->name.' '.$dato->last_name;
                                })
                                ->editColumn('note', function($dato){
                                    return !empty($dato->note)? $dato->note : 'N/A';
                                })
                                ->editColumn('contact_date', function($dato){
                                    return !empty($dato->contact_date)? $dato->contact_date : 'N/A';
                                })
                                ->editColumn('seller_name', function($dato){
                                    return !empty($dato->seller_name)? $dato->seller_name.' '.$dato->seller_last_name : 'N/A';
                                })
                                ->editColumn('name_coord', function($dato){
                                    return !empty($dato->name_coord)? $dato->name_coord.' '.$dato->last_name_coord : 'N/A';
                                })
                                ->editColumn('business_name', function($dato){
                                    return !empty($dato->business_name)? $dato->business_name : 'N/A';
                                })
                                ->editColumn('campaign', function($dato){
                                    return !empty($dato->campaign)? $dato->campaign : 'N/A';
                                })
                               ->make(true);
        }
    }

    public function downloadXLSProspect(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            //if(!empty($inputs['emails'])){
            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_prospectos';

            $report->email = $inputs['emails'];

            unset($inputs['emails']);
            unset($inputs['_token']);

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
            //}
        }
        return response()->json(array('error' => true));
    }

    public function ProspectsDetailexport(Request $request) {
        $sellers = array();
        if (isset($request->seller))
            $sellers[] = $request->seller;

        $data = Client::getReport($sellers, $request->date_ini, $request->date_end, $request->org);
        return $this->downloadReport($data, 'Reporte de prospectos');
    }

    public function test() {
       $data = Client::getReport(null, null, null);
       return $this->downloadReport($data, 'Reporte de prueba');
    }

    private function downloadReport($data, $report) {
        $data = json_decode(json_encode($data), true);
        return \Excel::create($report, function($excel) use ($data) {
            $excel->sheet('Reporte', function($sheet) use ($data) {
                $sheet->fromArray($data);
            });
        })->download('csv');
    }

    public function warehouseDn(){
        $html = view('pages.ajax.report.warehouseDn')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function warehouseDnSearch($search = false){
        $dns = [];
        if($search){
            $dns = Inventory::getConnect('R')->select('msisdn')
                            ->where([['msisdn', 'like', $search.'%'], ['status', 'A']])
                            ->limit(11);

            $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

            $wh = OrgWarehouse::getConnect('R')->select('id_wh')->whereIn('id_org', $orgs->pluck('id'))->get();

            $wh = $wh->pluck('id_wh');

            $dns = $dns->whereIn('warehouses_id', $wh);

            $dns = $dns->get();
        }

        return response()->json($dns);
    }

    public function warehouseDnSearchDetail($dn = false){
        if($dn){
            $dns = Inventory::getConnect('R')->select(
                                    'islim_inv_arti_details.msisdn',
                                    'islim_warehouses.name',
                                    'islim_warehouses.phone',
                                    'islim_inv_assignments.users_email',
                                    'islim_users.name as uname',
                                    'islim_users.last_name',
                                    'islim_users.phone as psell',
                                    'islim_inv_articles.artic_type'
                              )
                            ->join(
                                'islim_inv_articles',
                                'islim_inv_articles.id',
                                'islim_inv_arti_details.inv_article_id'
                            )
                            ->leftJoin(
                                'islim_warehouses',
                                'islim_warehouses.id',
                                'islim_inv_arti_details.warehouses_id'
                            )
                            ->leftJoin(
                                'islim_inv_assignments', function($join){
                                $join->on(
                                    'islim_inv_assignments.inv_arti_details_id',
                                    'islim_inv_arti_details.id'
                                    )
                                    ->where('islim_inv_assignments.status','A');
                            })
                            ->leftJoin(
                                'islim_users',
                                'islim_users.email',
                                'islim_inv_assignments.users_email'
                            )
                            ->where([
                                ['islim_inv_arti_details.msisdn', $dn],
                                ['islim_inv_arti_details.status', 'A']
                            ]);

            $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

            $wh = OrgWarehouse::select('id_wh')->whereIn('id_org', $orgs->pluck('id'))->get();

            $wh = $wh->pluck('id_wh');

            $dns = $dns->whereIn('warehouses_id', $wh);

            $dns = $dns->first();

            if(!empty($dns)){

                 switch ($dns->artic_type) {
                                        case 'T':  $artic_type = 'Telefonía'; break;
                                        case 'F':  $artic_type = 'Fibra'; break;
                                        case 'M':  $artic_type = 'MIFI'; break;
                                        default: $artic_type = 'Internet Hogar'; break;
                                    }


                return response()->json([
                                    'find' => true,
                                    'data' => [
                                                "dn" => $dn,
                                                "whi" => $dns->name,
                                                "pwh" => $dns->phone,
                                                "usri" => $dns->users_email,
                                                "usrni" => $dns->uname.''.$dns->last_name,
                                                "psell" => $dns->psell,
                                                "type" => $artic_type
                                            ]
                                ]);
            }
        }
        return response()->json($dns);
    }

    public function viewOrgEstruct(){
        $organizations = Organization::getConnect('R')->where('status', 'A');

        // if(!empty(session('user')->id_org))
        //     $organizations = $organizations->where('id', session('user')->id_org);

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $organizations = $organizations->whereIn('id', $orgs->pluck('id'));
        $organizations = $organizations->get();

        $html = view('pages.ajax.report.organizationEstruct', compact('organizations'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function organizationEstructDownload(Request $request){
        $fileName = 'usuarios_'.date('Ymd');
        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=".$fileName.".csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        $columns = array('Jerarquía', 'Organización', 'Nombre', 'Teléfono', 'Cargo', 'Perfil');

        $users = User::getReportStructOrg($request->org);

        $callback = function() use ($users, $columns)
        {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach($users as $user) {
                $data = [
                            $user['pos'],
                            $user['org'],
                            $user['name'],
                            $user['phone'],
                            $user['position'],
                            $user['profile']
                        ];

                fputcsv($file, $data);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    public function viewOrganizationEstructDetail(Request $request){
        $reports = User::getReportStructOrg($request->org);

        return Datatables::of($reports)
                            ->setRowClass(function ($report) {
                                return 'bc-hierarchy-'.$report['hierarchy'];
                            })
                            ->make(true);
    }

    public function viewUsers(Request $request) {
        if (session('user')->profile->type == "master"){

            $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

            $sellers = User::getConnect('R')
                            ->select('email', 'name', 'last_name')
                            ->whereIn('platform',['vendor','coordinador'])
                            ->whereIn('id_org', $orgs->pluck('id'));
        }
        else
            $sellers = User::getConnect('R')
                            ->select('email', 'name', 'last_name')
                            ->whereIn('platform',['vendor','coordinador'])
                            ->where('id_org',session('user')->id_org);
        $status = array('T');
        $sellers = $sellers->whereNotIn('status', $status);
        if (!(session('user.platform')=='admin'))
            $sellers = $sellers->whereIn('parent_email', session('user.email'));
        $sellers = $sellers->get();

        $types = (session('user.platform')=='admin') ? array(
            //array('code' => 'admin', 'description' => 'Administrador'),
            array('code' => 'coordinador', 'description' => 'Coordinador'),
            array('code' => 'vendor', 'description' => 'Vendedor'),
            array('code' => 'promotor', 'description' => 'Promotor')
        ) : array(
            array('code' => 'coordinador', 'description' => 'Coordinador'),
            array('code' => 'vendor', 'description' => 'Vendedor'),
            array('code' => 'promotor', 'description' => 'Promotor')
        );

        $status = (session('user.platform')=='admin') ? array(
            array('code' => 'A', 'description' => 'Activo'),
            array('code' => 'I', 'description' => 'Inactivo'),
            array('code' => 'T', 'description' => 'Eliminado')
        ) : array(
            array('code' => 'A', 'description' => 'Activo'),
            array('code' => 'I', 'description' => 'Inactivo')
        );

        $html = view('pages.ajax.report.users', compact('sellers', 'types', 'status'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    //Retorna usuarios segun filtro indicado
    public function getFilterUsers(Request $request){
        if($request->isMethod('post')){
            $type = $request->input('type');
            $status = $request->input('status');
            $name = $request->input('name');
            $email = $request->input('email');

            if (session('user')->profile->type == "master"){
                $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                $sellers = User::getConnect('R')
                                ->select('email', 'name', 'last_name')
                                ->whereIn('id_org', $orgs->pluck('id'));;
            }
            else
                $sellers = User::getConnect('R')
                                ->select('email', 'name', 'last_name')
                                ->where('id_org',session('user')->id_org);

            if(!empty($type)){
                $sellers = $sellers->where('platform', $type);
            }

            if(!empty($status)){
                $sellers = $sellers->where('status', $status);
            }

            if(!empty($name)){
                $wh = DB::raw("CONCAT(name, CONCAT(' ',last_name))");
                $sellers = $sellers->where($wh, $name);
            }

            if(!empty($email) && count($email)){
                $sellers = $sellers->where('email', $email);
            }

            return response()->json(array('users' => $sellers->get()));
        }
    }

    public function viewUsersDetail(Request $request) {
        /*$seller_email = null;
        if (isset($request->seller_email))
            $seller_email = explode(",", $request->seller_email);*/

        $seller_email = null;
        $seller_name = null;
        if (isset($request->seller_name))
            $seller_email = explode(",", $request->seller_name);

        $status = null;
        if (isset($request->status))
            $status = explode(",", $request->status);

        $type = null;
        if (isset($request->type))
            $type = explode(",", $request->type);

        $report = User::getReport($seller_email, $seller_name, $status, $type);

        $html = view('pages.ajax.report.usersDetail', compact('report'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewUsersDetailReport(Request $request, $email) {
        $inventories = SellerInventory::getSellerInventoryReport($email, null, null, null);
        $sells = Sale::getSaleReport (null, null, $email, null, null, ['E'], null);

        //Consultas para totales
        //Monto en efectivo que tiene el vendedor
        $data = new \stdClass;
        $data->total_mount_e = 0;
        $data->total_sales = 0;

        $now = getdate();

        $now['mon'] = $now['mon'] < 10 ? '0'.$now['mon'] : $now['mon'];

        $dateA = $now['year'].'-'.$now['mon'].'-01 00:00:00';

        /*$mes = ($now['mon'] + 1);
        if($mes > 12)
            $mes=1;
        $dateB = new DateTime($now['year'].'-'.$mes.'-01');*/

        if($now['mon'] < 12)
            $dateB = new DateTime($now['year'].'-'.($now['mon'] + 1).'-01');
        else
            $dateB = new DateTime(($now['year'] + 1).'-'.$now['mon'].'-01');

        $dateB = $dateB->getTimestamp();
        $dateB = $dateB - 86400;
        $dateB = getdate($dateB);
        $dateB['mon'] = $dateB['mon'] < 10 ? '0'.$dateB['mon'] : $dateB['mon'];
        $dateB = $dateB['year'].'-'.$dateB['mon'].'-'.$dateB['mday'].' 23:59:59';

        $ventasTE = Sale::getConnect('R')->select(DB::raw('SUM(amount) as total_mount'))
                        ->groupBy('users_email')
                        ->where([['users_email',$email], ['status', 'E']])
                        ->first();

        if(!empty($ventasTE)){
            $data->total_mount_e = $ventasTE->total_mount;
        }

        $assignSales = AssignedSales::getConnect('R')
                                    ->select(DB::raw('SUM(amount) as total_due_assig'))
                                    ->groupBy('parent_email')
                                    ->where([['parent_email', $email], ['status', 'P']])
                                    ->first();

        if(!empty($assignSales))
            $data->total_mount_e += $assignSales->total_due_assig;
            //$data->due_coord = $assignSales->total_due_assig;

        //Calculando deuda del usuario
        $invAssig = SellerInventory::getConnect('R')
                       ->select('islim_inv_arti_details.msisdn','islim_inv_articles.title', 'islim_inv_arti_details.imei', 'islim_inv_arti_details.price_pay')
                       ->join('islim_inv_arti_details',
                          function($join){
                              $join->on('islim_inv_assignments.inv_arti_details_id', '=', 'islim_inv_arti_details.id')
                               ->where([
                                  ['islim_inv_arti_details.status', 'A']
                              ]);
                          })
                       ->join('islim_inv_articles','islim_inv_articles.id','=','islim_inv_arti_details.inv_article_id')
                       ->where([['islim_inv_assignments.users_email',$email], ['islim_inv_assignments.status', 'A']])
                       ->get();
        $due = 0;
        foreach ($invAssig as $article) {
            if(!empty($article->price_pay))
                $due += $article->price_pay;
        }
        $data->due = $due;

        //Calculando ventas realizadas en un mes
        $ventasT = Sale::getConnect('R')->select(DB::raw('COUNT(users_email) as total_sales'), DB::raw('SUM(amount) as total_mount'))
                        ->groupBy('users_email')
                        ->whereBetween('date_reg',[$dateA, $dateB])
                        ->where(function ($query) use ($email){
                          $query->where('users_email', $email)
                                ->whereIn('type', ['P', 'R']);
                        })
                        ->where(function ($query){
                          $query->orWhere('status', 'A')
                                ->orWhere('status', 'E');
                        })
                        ->first();

        if(!empty($ventasT)){
            $data->total_sales = $ventasT->total_sales;
            $data->total_mount = $ventasT->total_mount;
        }

        $html = view('pages.ajax.report.usersDetailReport', compact('inventories', 'sells', 'data'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewBalance (Request $request) {
        $banks = Bank::getConnect('R')->select('name','numAcount')->get();

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $users = User::getConnect('R')
                    ->select('email', 'name', 'last_name')
                    ->where(['platform' => 'coordinador'])
                    ->where('status','!=','T')
                    ->whereIn('id_org',$orgs->pluck('id'))
                    ->get();

        $html = view('pages.ajax.report.balance', compact('banks','users'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewBalanceDetail ($type , Request $request) {
        $report = Deposit::getReportBalance($type,$request->user_email,$request->bank,$request->date_ini,$request->date_end);
        $html = view('pages.ajax.report.balanceDetail', compact('report'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewSellDeposit (Request $request){
        $vendor = User::where('platform','vendor')->get();
        $coordinator = User::where('platform','coordinador')->get();
        $html = view('pages.ajax.report.sellDeposit', compact('vendor','coordinator'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }
    private function reportSellDepositDetail($parent_email=null,$users_email=null,$process=null,$n_tranfer=null){
        $report = AssignedSales::when($parent_email,
                function($query, $parent_email){
                    return $query->where('parent_email',$parent_email);
                }
            )->when($users_email,
                function($query, $users_email){
                    return $query->where('users_email',$users_email);
                }
            )->when($process,
                function($query, $process){
                    return $query->where('status',$process);
                }
            )->when($n_tranfer,
                function($query, $n_tranfer){
                    return $query->where('n_tranfer',$n_tranfer);
                }
        )->whereIn('status',['A','P'])->orderBy('id', 'asc')->get();
        return $report;
    }

    public function viewSellDepositDetail(Request $request){
        $parent_email = $request->parent_email;
        $users_email  = $request->user_email;
        $process = $request->process;
        $n_tranfer = $request->n_tranfer;
        $report = $this->reportSellDepositDetail($parent_email,$users_email,$process,$n_tranfer);
        $html = view('pages.ajax.report.sellDepositDetail', compact('report'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function mobilityReport(){
        $html = view('pages.ajax.report.mobilitySuspend')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function mobilityReportDT(Request $request){
        if($request->isMethod('post')){
            $clientSuspend = mobility::getClientSuspend();

            return Datatables::of($clientSuspend)
                                ->editColumn('name', '{{$name}} {{$last_name}}')
                                ->editColumn('date', function($client){
                                    return date("d-m-Y", strtotime($client->date_affec));
                                })
                                ->make(true);
        }
    }

    public function mobilityReportDTDownload(Request $request){
        if($request->isMethod('post')){
            $fileName = 'clientes_suspendidos_'.date('Ymd');
            $headers = array(
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=".$fileName.".csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );

            $columns = array('Nombre', 'Teléfono', 'MSISDN', 'Latitud', 'Longitud', 'Fecha');

            $clients = mobility::getClientSuspend();

            $callback = function() use ($clients, $columns)
            {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach($clients as $client) {
                    $data = [
                                $client['name'].' '.$client['last_name'],
                                $client['phone_home'],
                                $client['msisdn'],
                                $client['lat'],
                                $client['lng'],
                                date("d-m-Y", strtotime($client['date_affec']))
                            ];

                    fputcsv($file, $data);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
    }

    public function simswap(){
        $html = view('pages.ajax.report.simSwapReport')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getSwapReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $data = SimSwap::getConnect('R')->select(
                                'islim_sim_swap.*',
                                'islim_clients.name',
                                'islim_clients.last_name'
                             )
                             ->join(
                                'islim_client_netweys',
                                'islim_client_netweys.msisdn',
                                '=',
                                'islim_sim_swap.msisdn_origin'
                             )
                             ->join(
                                'islim_clients',
                                'islim_clients.dni',
                                '=',
                                'islim_client_netweys.clients_dni'
                             );

            return DataTables::eloquent($data)
                                ->editColumn('name', '{{$name}} {{$last_name}}')
                                ->editColumn('msisdn_origin', function($swap){
                                    if(!empty($swap->msisdn_origin)){
                                        return $swap->msisdn_origin;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('imei_origin', function($swap){
                                    if(!empty($swap->imei_origin)){
                                        return $swap->imei_origin;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('iccid_origin', function($swap){
                                    if(!empty($swap->iccid_origin)){
                                        return $swap->iccid_origin;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('msisdn_dest', function($swap){
                                    if(!empty($swap->msisdn_dest)){
                                        return $swap->msisdn_dest;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('imei_dest', function($swap){
                                    if(!empty($swap->imei_dest)){
                                        return $swap->imei_dest;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('iccid_dest', function($swap){
                                    if(!empty($swap->iccid_dest)){
                                        return $swap->iccid_dest;
                                    }else{
                                        return 'N/A';
                                    }
                                })
                                ->editColumn('tipo', function($swap){
                                    if($swap->tipo == 'S'){
                                        return 'SIM';
                                    }else{
                                        return 'SIM+MODEM';
                                    }
                                })
                                ->editColumn('date_reg', function($swap){
                                    return date("d-m-Y", strtotime($swap->date_reg));
                                })
                                ->make(true);
        }
    }

    public function downloadSwapReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $swaps = SimSwap::getConnect('R')->select(
                                'islim_sim_swap.*',
                                'islim_clients.name',
                                'islim_clients.last_name'
                             )
                             ->join(
                                'islim_client_netweys',
                                'islim_client_netweys.msisdn',
                                '=',
                                'islim_sim_swap.msisdn_origin'
                             )
                             ->join(
                                'islim_clients',
                                'islim_clients.dni',
                                '=',
                                'islim_client_netweys.clients_dni'
                             )
                             ->get();

            $data []= ['Cliente', 'DN Origen', 'Imei Origen', 'Iccid Origen', 'DN Destino', 'Imei Destino', 'Iccid Destino', 'Numero de Orden', 'Tipo', 'Fecha'];

            foreach ($swaps as $client) {
                $data []= [
                            $client->name.' '.$client->last_name,
                            empty($client->msisdn_origin) ? 'N/A' : $client->msisdn_origin,
                            empty($client->imei_origin) ? 'N/A' : $client->imei_origin,
                            empty($client->iccid_origin) ? 'N/A' : $client->iccid_origin,
                            empty($client->msisdn_dest) ? 'N/A' : $client->msisdn_dest,
                            empty($client->imei_dest) ? 'N/A' : $client->imei_dest,
                            empty($client->iccid_dest) ? 'N/A' : $client->iccid_dest,
                            $client->id_order,
                            $client->tipo == 'S' ? 'SIM' : 'SIM+MODEM',
                            date("d-m-Y", strtotime($client->date_reg))
                          ];
            }

            $url = CommonHelpers::saveFile('/public/reports', 'sim_swap', $data, 'sim_swap_report_'.date('d-m-Y'));

            return response()->json(array('url' => $url));
        }
    }

    public function invBrightstar(){
        $articulos = Product::select('sku', 'title', 'description')
                              ->where('status', 'A')
                              ->whereNotNull('sku')
                              ->get();

        $error = true;
        $devices = [];

        if($articulos->count() > 0){
            $data = ['data' => []];
            $titles = [];
            foreach ($articulos as $articulo) {
                $data['data'] []= ['sku' => $articulo->sku];
                $titles[$articulo->sku] = [
                                            'title' => $articulo->title,
                                            'desc' => $articulo->description
                                          ];
            }
            $data['real'] = true;
            $data = json_encode($data);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => env('API_BRIGHTSTAR')."get-inventary-brightstar",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                )
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if (!$err) {
                $response = json_decode($response);
                if(!$response->error){
                    $error = false;

                    foreach ($response->data as $articulo) {
                        $articulo->title = $titles[$articulo->sku]['title'];
                        $articulo->desc = $titles[$articulo->sku]['desc'];
                        $devices []= $articulo;
                    }
                }
            }
        }

        $html = view('pages.ajax.report.invBrightstar', compact('error', 'devices'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function saleArtic(){
        $services = Service::getConnect('R')->where('status', '!=', 'T')->get();
        $products = Product::getConnect('R')->where('status', '!=', 'T')->get();

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        if(session('user')->platform == 'admin' || session('user')->platform == 'call'){

            $supervisors = User::getConnect('R')->where('platform', 'coordinador')
                            ->where('status', 'A')
                            ->whereIn('id_org',$orgs->pluck('id'))
                            ->get();

            $sellers = User::getConnect('R')->where('platform', 'vendor')
                            ->where('status', 'A')
                            ->whereIn('id_org',$orgs->pluck('id'))
                            ->get();
            // if(session('user')->profile->type == "master"){
            //     $supervisors = User::where('platform', 'coordinador')->where('status', 'A')->get();
            //     $sellers = User::where('platform', 'vendor')->where('status', 'A')->get();
            //     $orgs = Organization::select('id', 'business_name')->where('status','A')->get();
            // }else{
            //     $supervisors = User::where('platform', 'coordinador')
            //                          ->where([
            //                             ['status', 'A'],
            //                             ['id_org', session('user')->id_org]
            //                          ])
            //                          ->get();

            //     $sellers = User::where('platform', 'vendor')
            //                      ->where([
            //                         ['status', 'A'],
            //                         ['id_org', session('user')->id_org]
            //                      ])
            //                      ->get();
            // }
        }else{
            $supervisors = User::getConnet('R')->where('email', session('user')->email)->get();
            $sellers = User::getConnet('R')->where([
                                ['platform', 'vendor'],
                                ['parent_email', session('user')->email]
                            ])
                            ->where('status', 'A')
                            ->get();
        }

        $html = view(
                    'pages.ajax.report.artic_sale_report',
                    compact('supervisors', 'sellers', 'orgs', 'services', 'products')
                )->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function saleArticF(Request $request){
        if($request->isMethod('post') && $request->ajax()){

            $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

            // if(session('user')->platform == 'admin' || session('user')->platform == 'call'){
                $supervisors = User::getConnect('R')->where('platform', 'coordinador')
                                    ->where('status', 'A');

                $sellers = User::getConnect('R')->where('platform', 'vendor')
                                ->where('status', 'A');

                if(!empty($request->org)){
                    $supervisors = $supervisors->where('id_org', $request->org);
                    $sellers = $sellers->where('id_org', $request->org);
                }
                else{
                    $supervisors = $supervisors->whereIn('id_org', $orgs->pluck('id'));
                    $sellers = $sellers->whereIn('id_org', $orgs->pluck('id'));
                }

                if(!empty($request->coo)){
                    $sellers = $sellers->where('parent_email', $request->coo);
                }

                return response()->json([
                                            'success' => true,
                                            'cs'=> $supervisors->get(),
                                            'ss'=> $sellers->get()
                                        ]);
            // }
        }
        return response()->json([
                                    'success' => false
                                ]);
    }

    public function saleArticDT(Request $request){
        $sales = Sale::getSalesNotActiveReport($request->all());

        return DataTables::eloquent($sales)
                            ->editColumn('seller', '{{$name}} {{$last_name}}')
                            ->editColumn('supervisor', function($sale){
                                if(!empty($sale->namecoo) && !empty($sale->lastnamecoo))
                                    return $sale->namecoo.' '.$sale->lastnamecoo;
                                else
                                    return $sale->name.' '.$sale->last_name;
                            })
                            ->editColumn('date_reg', function($sale){
                                return date("d-m-Y", strtotime($sale->date_reg));
                            })
                            ->make(true);
    }

    public function saleArticDW(Request $request){
        if($request->isMethod('post')){

            $report = Reports::getConnect('W');

            $inputs = $request->all();

            $report->name_report = 'reporte_articulos_no_activos';
            $report->email = $inputs['emails'];

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

    public function saleArticActive(){
        $services = Service::getConnect('R')->where('status', '!=', 'T')->get();
        $products = Product::getConnect('R')->where('status', '!=', 'T')->get();

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        if(session('user')->platform == 'admin' || session('user')->platform == 'call'){

            $supervisors = User::getConnect('R')->where('platform', 'coordinador')
                            ->where('status', 'A')
                            ->whereIn('id_org',$orgs->pluck('id'))
                            ->get();

            $sellers = User::getConnect('R')->where('platform', 'vendor')
                            ->where('status', 'A')
                            ->whereIn('id_org',$orgs->pluck('id'))
                            ->get();

            // if(session('user')->profile->type == "master"){
            //     $supervisors = User::where('platform', 'coordinador')->where('status', 'A')->get();
            //     $sellers = User::where('platform', 'vendor')->where('status', 'A')->get();
            //     $orgs = Organization::select('id', 'business_name')->where('status','A')->get();
            // }else{
            //     $supervisors = User::where('platform', 'coordinador')
            //                          ->where([
            //                             ['status', 'A'],
            //                             ['id_org', session('user')->id_org]
            //                          ])
            //                          ->get();

            //     $sellers = User::where('platform', 'vendor')
            //                      ->where([
            //                         ['status', 'A'],
            //                         ['id_org', session('user')->id_org]
            //                      ])
            //                      ->get();
            // }
        }else{
            $supervisors = User::getConnet('R')->where('email', session('user')->email)->get();
            $sellers = User::getConnet('R')->where([
                                ['platform', 'vendor'],
                                ['parent_email', session('user')->email]
                            ])
                            ->where('status', 'A')
                            ->get();
        }

        $html = view(
                    'pages.ajax.report.artic_active_sale_report',
                    compact('supervisors', 'sellers', 'orgs', 'services', 'products')
                )->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function saleArticActiveDT(Request $request){
        $sales = Sale::getSalesActiveReport($request->all());

        return DataTables::eloquent($sales)
                            ->editColumn('seller', '{{$name}} {{$last_name}}')
                            ->editColumn('supervisor', function($sale){
                                if(!empty($sale->namecoo) && !empty($sale->lastnamecoo))
                                    return $sale->namecoo.' '.$sale->lastnamecoo;
                                else
                                    return $sale->name.' '.$sale->last_name;
                            })
                            ->editColumn('email', function($sale){
                                if(!empty($sale->email))
                                    return $sale->email;
                                return 'N/A';
                            })
                            ->editColumn('client', function($sale){
                                return $sale->cliname.' '.$sale->clilastname;
                            })
                            ->editColumn('phone_home', function($sale){
                                if(!empty($sale->phone_home))
                                    return $sale->phone_home;
                                return 'N/A';
                            })
                            ->editColumn('phone', function($sale){
                                if(!empty($sale->phone))
                                    return $sale->phone;
                                return 'N/A';
                            })
                            ->editColumn('date', function($sale){
                                return date("d-m-Y H:i:s", strtotime($sale->date_reg));
                            })
                            ->editColumn('date_act', function($sale){
                                if(!empty($sale->date_sale))
                                    return date("d-m-Y H:i:s", strtotime($sale->date_sale));

                                return date("d-m-Y H:i:s", strtotime($sale->date_reg));
                            })
                            ->make(true);
    }

    public function saleArticActiveDW(Request $request){
        if($request->isMethod('post')){

            $report = Reports::getConnect('W');

            $inputs = $request->all();

            $report->name_report = 'reporte_articulos_activos';
            $report->email = $inputs['emails'];

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

    public function financing(){
        // if(session('user')->profile->type == "master")
            // $orgs = Organization::select('id', 'business_name')->where('status','A')->get();
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $financing = Financing::select('id', 'name')->where('status', 'A')->get();

        $html = view(
                    'pages.ajax.report.report_financing',
                    compact('financing', 'orgs')
                )->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function financingDT(Request $request){
        $salesF = ClientNetwey::getFinancingReport($request->all());

        return DataTables::eloquent($salesF)
                        ->editColumn('price_remaining', function($sale){
                            return number_format($sale->price_remaining,2,'.',',');
                        })
                        ->editColumn('amount_financing', function($sale){
                            return number_format($sale->amount_financing,2,'.',',');
                        })
                        ->editColumn('total_amount', function($sale){
                            return number_format($sale->total_amount,2,'.',',');
                        })
                        ->editColumn('pay', function($sale){
                            return number_format($sale->pay,2,'.',',');
                        })
                        ->editColumn('date_reg', function($sale){
                            return date("d-m-Y H:i:s", strtotime($sale->date_reg));
                        })
                        ->make(true);
    }

    public function financingDW(Request $request){
        if($request->isMethod('post')){

            $report = Reports::getConnect('W');

            $inputs = $request->all();

            $report->name_report = 'reporte_clientes_financiados';
            $report->email = $inputs['emails'];

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

    public function conciliations(){
        $html = view('pages.ajax.report.concilations')->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function getReportConc(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $user = User::getConnect('R')->select('name', 'last_name')->where('email', $request->userS)->first();

            if(!empty($user)){
                $bAmount = Balance::getConnect('R')->select('amount','balance', 'date_reg', 'id')
                                    ->where([
                                        ['status', 'A'],
                                        ['type', 'I'],
                                        ['user', $request->userS]
                                    ]);

                if(!empty($request->dateb)){
                    $dateBa = date('Y-m-d H:i:s', strtotime($request->dateb) + (3600 * 23) + 3599);

                    $bAmount = $bAmount->where('date_reg', '<=', $dateBa);

                    $dateBE = date('Y-m-d H:i:s', strtotime('-1 day', strtotime($dateBa)));

                    $vald = Balance::getConnect('R')->select('id')
                                     ->where([
                                        ['date_reg', '<=', $dateBE],
                                        ['status', 'A'],
                                        ['type', 'I'],
                                        ['user', $request->userS]
                                    ])
                                     ->get();

                    if($vald->count() > 0)
                        $bAmount = $bAmount->orderBy('id', 'DESC');
                    else
                        $bAmount = $bAmount->orderBy('id', 'ASC');
                }else{
                    $bAmount = $bAmount->orderBy('id', 'ASC');
                }

                $bAmount = $bAmount->first();

                if(!empty($bAmount)){
                    if(!empty($request->dateb))
                        $dateBegin = date('Y-m-d H:i:s', strtotime($request->dateb));

                    if(!empty($request->datee))
                        $dateEnd = date('Y-m-d H:i:s', strtotime($request->datee) + (3600 * 23) + 3599);

                    $deposits = Balance::getConnect('R')->select('amount')
                                         ->where([
                                            ['type', 'I'],
                                            ['status', 'A'],
                                            ['user', $request->userS],
                                            //['id', '!=', $bAmount->id]
                                         ]);

                    if(!empty($request->dateb))
                        $deposits = $deposits->where('date_reg', '>=', $dateBegin);

                    if(!empty($request->datee))
                        $deposits = $deposits->where('date_reg', '<=', $dateEnd);

                    $deposits = $deposits->sum('amount');

                    $conc = Balance::getConnect('R')->select('amount')
                                         ->where([
                                            ['type', 'E'],
                                            ['status', 'A'],
                                            ['user', $request->userS]
                                         ]);

                    if(!empty($request->dateb))
                        $conc = $conc->where('date_reg', '>=', $dateBegin);

                    if(!empty($request->datee))
                        $conc = $conc->where('date_reg', '<=', $dateEnd);

                    $conc = $conc->sum('amount');

                    if(empty($request->dateb))
                        $request->dateb = date('d-m-Y', strtotime($bAmount->date_reg));

                    $lastDate = Balance::getConnect('R')->select('balance', 'date_reg')
                                            ->where([
                                                ['status', 'A'],
                                                ['user', $request->userS]
                                            ])
                                            ->orderBy('id', 'DESC');

                    if(!empty($request->datee))
                        $lastDate = $lastDate->where('date_reg', '<=', $dateEnd);

                    $lastDate = $lastDate->first();

                    if(empty($request->datee))
                        $request->datee = date('d-m-Y', strtotime($lastDate->date_reg));



                    return response()->json([
                                                'error' => false,
                                                'ini' => number_format($bAmount->balance,2,'.',','),//number_format(($bAmount->balance - $bAmount->amount),2,'.',','),
                                                'dep' => number_format($deposits,2,'.',','),
                                                'conc' => number_format($conc,2,'.',','),
                                                'final' => number_format($lastDate->balance,2,'.',','),
                                                'dateb' => $request->dateb,
                                                'datee' => $request->datee,
                                                'usuario' => $user->name.' '.$user->last_name,
                                                'email' => $request->userS
                                            ]);
                }else{
                    return response()->json([
                                                'error' => true,
                                                'msg' => 'No se puede calcular el reporte para la fecha seleccionada.'
                                            ]);
                }
            }else{
                return response()->json([
                                        'error' => true,
                                        'msg' => 'No se consiguio el usuario.'
                                    ]);
            }
        }

        return response()->json(['error' => true, 'msg' => 'No se pudo procesar la consulta']);
    }

    public function getDetailDeposits(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                $dateBegin = date('Y-m-d H:i:s', strtotime($request->dateb));
                $dateEnd = date('Y-m-d H:i:s', strtotime($request->datee) + (3600 * 23) + 3599);

                $bankUser = UserDeposit::getConnect('R')->select(
                                            'islim_user_deposit_id.id_deposit',
                                            'islim_users.name',
                                            'islim_users.last_name'
                                         )
                                         ->join(
                                            'islim_users',
                                            'islim_users.email',
                                            'islim_user_deposit_id.email'
                                         )
                                         ->where([
                                            ['islim_user_deposit_id.email', $request->email],
                                            ['islim_user_deposit_id.status', 'A']
                                         ])
                                         ->first();

                if(!empty($bankUser)){
                    $deposists = BankDeposits::getConnect('R')->select(
                                            'islim_bank_deposits.cod_auth',
                                            'islim_bank_deposits.amount',
                                            'islim_bank_deposits.date_dep',
                                            'islim_bank_deposits.date_reg',
                                            'islim_bank_deposits.status',
                                            'islim_banks.name'
                                        )
                                        ->join(
                                            'islim_banks',
                                            'islim_banks.id',
                                            'islim_bank_deposits.bank'
                                        )
                                        ->where([
                                            ['islim_bank_deposits.email', $request->email],
                                            ['islim_bank_deposits.date_reg', '>=', $dateBegin],
                                            ['islim_bank_deposits.date_reg', '<=', $dateEnd]

                                        ])
                                        ->orderBy('islim_bank_deposits.date_reg', 'DESC')
                                        ->get();

                    if($deposists->count()){
                        $html = view('pages.ajax.conciliation.lastDep', compact('deposists', 'bankUser'))->render();
                        return response()->json(array('success' => true, 'html' => $html));
                    }else{
                        return response()->json(['success' => false, 'msg' => 'El usuario no tiene depositos registrados en el rango de fechas seleccionado.']);
                    }
                }else{
                    return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
                }
            }else{
                return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
            }
        }

        return response()->json(['success' => true, 'msg' => 'No se pudo consultar el detalle.']);
    }

    public function getDetailConc(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                $dateBegin = date('Y-m-d H:i:s', strtotime($request->dateb));
                $dateEnd = date('Y-m-d H:i:s', strtotime($request->datee) + (3600 * 23) + 3599);

                $bankUser = UserDeposit::getConnect('R')->select(
                                            'islim_user_deposit_id.id_deposit',
                                            'islim_users.name',
                                            'islim_users.last_name'
                                         )
                                         ->join(
                                            'islim_users',
                                            'islim_users.email',
                                            'islim_user_deposit_id.email'
                                         )
                                         ->where([
                                            ['islim_user_deposit_id.email', $request->email],
                                            ['islim_user_deposit_id.status', 'A']
                                         ])
                                         ->first();

                if(!empty($bankUser)){
                    $details = AssignedSales::getConnect('R')->select(
                                            'islim_asigned_sales.id',
                                            'islim_users.email',
                                            'islim_asigned_sales.amount',
                                            'islim_asigned_sales.date_process',
                                            'islim_users.name',
                                            'islim_users.last_name'
                                        )
                                        ->join(
                                            'islim_users',
                                            'islim_users.email',
                                            'islim_asigned_sales.users_email'
                                        )
                                        ->where([
                                            ['islim_asigned_sales.status', 'A'],
                                            ['islim_asigned_sales.parent_email', $request->email],
                                            ['islim_asigned_sales.date_process', '>=', $dateBegin],
                                            ['islim_asigned_sales.date_process', '<=', $dateEnd]
                                        ])
                                        ->orderBy('islim_asigned_sales.date_process', 'ASC')
                                        ->get();

                    if($details->count()){
                        foreach ($details as $detail) {
                            $salesDetail = AssignedSaleDetails::getConnect('R')->select(
                                                                'islim_asigned_sale_details.unique_transaction',
                                                                'islim_sales.amount',
                                                                'islim_sales.date_reg',
                                                                'islim_sales.msisdn',
                                                                'islim_packs.title as pack',
                                                                'islim_inv_articles.title as arti'
                                                              )
                                                              ->join(
                                                                'islim_sales',
                                                                'islim_sales.unique_transaction',
                                                                'islim_asigned_sale_details.unique_transaction'
                                                              )
                                                              ->join(
                                                                'islim_packs',
                                                                'islim_packs.id',
                                                                'islim_sales.packs_id'
                                                              )
                                                              ->join(
                                                                'islim_inv_arti_details',
                                                                'islim_inv_arti_details.id',
                                                                'islim_sales.inv_arti_details_id'
                                                              )
                                                              ->join(
                                                                'islim_inv_articles',
                                                                'islim_inv_articles.id',
                                                                'islim_inv_arti_details.inv_article_id'
                                                              )
                                                              ->where(
                                                                'islim_asigned_sale_details.asigned_sale_id', $detail->id
                                                              )
                                                              ->get();

                            $detail->salesDetail = $salesDetail;
                        }

                        $html = view('pages.ajax.conciliation.detailDebt', compact('details', 'bankUser'))->render();

                        return response()->json(array('success' => true, 'html' => $html));
                    }else{
                        return response()->json(array('success' => false, 'msg' => 'El usuario no ventas conciliadas para el rango de fecha selecionado.'));
                    }
                }else{
                   return response()->json(array('success' => false, 'msg' => 'No se consiguio el usuario.'));
                }
            }else{
                return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de las conciliaciones.'));
            }
        }

        return response()->json(['error' => true, 'msg' => 'No se pudo consultar el detalle.']);
    }

    public function getOpeEfec(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->q)){
                $users = User::getConnect('R')->select(
                                'islim_users.name',
                                'islim_users.last_name',
                                'islim_users.email',
                                DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username')
                             )
                             //->join('islim_profile_details', 'islim_profile_details.user_email', 'islim_users.email')
                             ->where(function($query) use($request){
                                        $query->where('islim_users.name', 'like', '%'.$request->q.'%')
                                              ->orWhere('islim_users.last_name', 'like', '%'.$request->q.'%');
                                    })
                             //->where('islim_users.platform', 'coordinador')
                             ->where([
                                ['islim_users.status', 'A'],
                                //['islim_profile_details.status', 'A'],
                                //['islim_profile_details.id_profile', 8]
                             ]);

                // if(session('user')->profile->type != "master")
                //     $users = $users->where('islim_users.id_org', session('user')->id_org);
                // else
                if(!empty($request->org))
                    $users = $users->where('islim_users.id_org', $request->org);
                else{
                    $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                    $users = $users->whereIn('islim_users.id_org', $orgs->pluck('id'));
                }

                if(!empty($request->coord))
                    $users = $users->where('islim_users.parent_email', $request->coord);

                $users = $users->limit(10)->get();

                return response()->json(array('success' => true, 'users' => $users));
            }
            return response()->json(array('success' => false));
        }
    }

    public function conciliationsRep(){
        $html = view('pages.ajax.report.concilations_rep')->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function getRepConc(Request $request){
        $deposits = AssignedSales::getReportConciliations($request->all());

        return DataTables::of($deposits)
                        ->editColumn('dep', function($deposit){
                            return $deposit->id;
                        })
                        ->editColumn('ope_user', function($deposit){
                            return $deposit->ope_name.' '.$deposit->ope_last_name;
                        })
                        ->editColumn('amount', function($deposit){
                            return '$'.number_format($deposit->amount,2,'.',',');
                        })
                        ->editColumn('sup_name', function($deposit){
                            if(!empty($deposit->sup_name)){
                                return $deposit->sup_name.' '.$deposit->sup_last_name;
                            }
                            return 'N/A';
                        })
                        ->editColumn('coord', function($deposit){
                            return $deposit->name.' '.$deposit->last_name;
                        })
                        ->editColumn('cod_dep', function($deposit){
                            return $deposit->id_deposit;
                        })
                        ->editColumn('date', function($deposit){
                            return $deposit->date_process;
                        })
                        ->editColumn('bank', function($deposit){
                            return !empty($deposit->bank) ? $deposit->bank : 'Otro';
                        })
                        ->editColumn('reason_deposit', function($deposit){
                            return !empty($deposit->reason_deposit) ? $deposit->reason_deposit : 'N/A';
                        })
                        ->make(true);
    }

    public function downloadRepConc(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            //if(!empty($inputs['emails'])){
            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_conciliaciones';

            $report->email = $inputs['emails'];

            unset($inputs['emails']);
            unset($inputs['_token']);

            $report->filters = json_encode($inputs);
            $report->user_profile = session('user')->profile->type;
            $report->user = session('user')->email;
            $report->status = 'C';
            $report->date_reg = date('Y-m-d H:i:s');

            $report->save();

            return response()->json(array('error' => false));
            //}
        }
        return response()->json(array('error' => true));
    }

    public function rre(){

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));


        // if(session('user')->profile->type == "master")
        //     $orgs = Organization::select('id', 'business_name')->where('status','A')->get();
        // else
        //     $orgs = Organization::select('id', 'business_name')
        //                           ->where([
        //                             ['status','A'],
        //                             ['id', session('user')->id_org]
        //                           ])
        //                           ->get();

        $html = view('pages.ajax.report.rre', compact('orgs'))->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function repRre(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $rres = AssignedSales::getReportRRE($request->all());

            return DataTables::eloquent($rres)
                            ->editColumn('seller', function($rre){
                                return $rre->seller_name.' '.$rre->seller_last_name;
                            })
                            ->editColumn('date_reg', function($rre){
                                return date('d-m-Y', strtotime($rre->date_reg));
                            })
                            ->editColumn('amount', function($rre){
                                return '$'.number_format($rre->amount,2,'.',',');
                            })
                            ->editColumn('coord', function($rre){
                                return $rre->name.' '.$rre->last_name;
                            })
                            ->editColumn('status', function($rre){
                                if($rre->status == 'V')
                                    return 'EA';
                                if($rre->status == 'P')
                                    return 'A';
                                if($rre->status == 'I')
                                    return 'R';
                                if($rre->status == 'A')
                                    return 'C';

                                return 'N/A';
                            })
                            ->editColumn('date_step2', function($rre){
                                if(!empty($rre->date_accepted))
                                    return date('d-m-Y', strtotime($rre->date_accepted));

                                if(!empty($rre->date_reject))
                                    return date('d-m-Y', strtotime($rre->date_reject));

                                return 'N/A';
                            })
                            ->editColumn('alert', function($rre){
                                if($rre->status == 'V'){
                                    $now = strtotime(date('Y-m-d H:i:s'));
                                    $dater = strtotime($rre->date_reg);

                                    $dr = strtotime('+ 12 hours', $dater);
                                    if($dr <= $now)
                                        return 'Rojo';

                                    $do = strtotime('+ 6 hours', $dater);
                                    if($do <= $now)
                                        return 'Naranja';

                                    return 'Azul';
                                }
                                if($rre->status == 'P')
                                    return 'Verde';
                                if($rre->status == 'I')
                                    return 'Gris';

                                return 'N/A';
                            })
                            ->editColumn('date_process', function($rre){
                                if(!empty($rre->date_process))
                                    return date('d-m-Y', strtotime($rre->date_process));

                                return 'N/A';
                            })
                            ->make(true);
        }
    }

    public function getSalesDetail(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->id)){
                $id = $request->id;
                $salesDetail = AssignedSaleDetails::getConnect('R')->select(
                                                'islim_asigned_sale_details.unique_transaction',
                                                'islim_asigned_sale_details.amount',
                                                'islim_sales.date_reg',
                                                'islim_sales.msisdn',
                                                'islim_packs.title as pack',
                                                'islim_inv_articles.title as arti'
                                              )
                                              ->join(
                                                'islim_sales',
                                                'islim_sales.unique_transaction',
                                                'islim_asigned_sale_details.unique_transaction'
                                              )
                                              ->join(
                                                'islim_packs',
                                                'islim_packs.id',
                                                'islim_sales.packs_id'
                                              )
                                              ->join(
                                                'islim_inv_arti_details',
                                                'islim_inv_arti_details.id',
                                                'islim_sales.inv_arti_details_id'
                                              )
                                              ->join(
                                                'islim_inv_articles',
                                                'islim_inv_articles.id',
                                                'islim_inv_arti_details.inv_article_id'
                                              )
                                              ->where([
                                                ['islim_asigned_sale_details.asigned_sale_id', $id],
                                                //['islim_sales.type', 'P']
                                              ])
                                              ->orderBy('islim_sales.amount', 'DESC')
                                              ->groupBy('islim_sales.unique_transaction')
                                            ->get();

                $html = view('pages.ajax.report.list_sales', compact('salesDetail', 'id'))->render();

                return response()->json(['success' => true, 'msg'=> 'No se pudo cargar el detalle de la venta.', 'html' => $html]);
            }

            return response()->json(['success' => false, 'msg'=> 'No se pudo cargar el detalle de la venta.']);
        }
    }

    /*Devuelve los ultimos reportes con estatus G y D generado por el usuario logueado*/
    public function notificationReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty(session('user'))){
                $reports = Reports::select('id', 'name_report', 'download_url', 'status', 'date_reg')
                                    ->where('user', session('user')->email)
                                    ->whereIn('status', ['G', 'D', 'P', 'E'])
                                    ->orderBy('id', 'DESC')
                                    ->limit(env('LIMIT_REPORT', 5))
                                    ->get();

                return response()->json(['success' => true, 'data' => $reports]);
            }

            return response()->json(['success' => false, 'msg'=> 'No se pudo verificar los reportes.']);
        }
    }

    /*Marca un reporte como descargado*/
    public function checkReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty(session('user'))){
                $reports = Reports::where([['user', session('user')->email], ['id', $request->report]])
                                    ->update(['status' => 'D']);

                return response()->json(['success' => true]);
            }

            return response()->json(['success' => false]);
        }
    }

    /*
        Vista principal del reporte de ventas en abono
    */
    public static function installmentSales(){
        // if(session('user')->profile->type == "master"){
        //     $orgs = Organization::getOrgs();
        // }else{
        //     $orgs = Organization::getOrgs(session('user')->id_org);
        // }

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $services = Service::getConnect('R')->select('id', 'description')
                             ->where('type', 'A')
                             ->whereNotIn('status',['T'])
                             ->get();

        $products = Product::getConnect('R')->select('id', 'title')
                             ->whereNotIn('status',['T'])
                             ->get();

        $html = view('pages.ajax.report.installmentSales', compact('orgs', 'services', 'products'))->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    //Retorna usuarios vendedores o coordinadores
    public function getFilterUsersSellers(Request $request){
        if($request->isMethod('post')  && $request->ajax()){
            $type = $request->input('type');
            $name = $request->input('name');
            $coord = $request->input('coord');

            if(!empty($name)){
                $users = User::getConnect('R')
                             ->select(
                                'islim_users.name',
                                'islim_users.last_name',
                                'islim_users.email',
                                DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username')
                             )
                             ->where(function($query) use($name){
                                        $query->where('islim_users.name', 'like', $name.'%')
                                              ->orWhere('islim_users.last_name', 'like', $name.'%');
                                    })
                             ->where('islim_users.status', 'A');

                if(!empty($type)){
                    $users->where('platform', $type);
                }

                if(!empty($request->org))
                    $users = $users->where('islim_users.id_org', $request->org);
                else{
                    $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                    $users = $users->whereIn('islim_users.id_org', $orgs->pluck('id'));
                }

                if(!empty($coord))
                    $users = $users->where('islim_users.parent_email', $coord);

                $users = $users->limit(10)->get();

                return response()->json(array('success' => true, 'users' => $users));
            }

            return response()->json(array('success' => false));
        }
    }

    /*
        Retorna datatable para el reporte de ventas en abono
    */
    public static function getSalesInstDT(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $sales = SaleInstallment::getSalesReport($request->all());

            return DataTables::of($sales)
                            ->editColumn('unique_transaction', function($sale){
                                return $sale->unique_transaction;
                            })
                            ->editColumn('org', function($sale){
                                return $sale->business_name;
                            })
                            ->editColumn('seller', function($sale){
                                return $sale->name_seller.' '.$sale->last_name_seller;
                            })
                            ->editColumn('coordinador', function($sale){
                                return $sale->name_coord.' '.$sale->last_name_coord;
                            })
                            ->editColumn('pack', function($sale){
                                return $sale->pack;
                            })
                            ->editColumn('product', function($sale){
                                return $sale->product;
                            })
                            ->editColumn('msisdn', function($sale){
                                return $sale->msisdn;
                            })
                            ->editColumn('imei', function($sale){
                                return !empty($sale->imei) ? $sale->imei : 'N/A';
                            })
                            ->editColumn('service', function($sale){
                                return $sale->service;
                            })
                            ->editColumn('client', function($sale){
                                return $sale->name_client.' '.$sale->last_name_client;
                            })
                            ->editColumn('phone_home', function($sale){
                                if(!empty($sale->phone_home))
                                    return $sale->phone_home;
                                else
                                    return 'N/A';
                            })
                            ->editColumn('sell_date_reg', function($sale){
                                return date('d-m-Y H:i:s', strtotime($sale->date_reg_alt));
                            })
                            ->editColumn('date_exp', function($sale){
                                return $sale->date_expired;
                            })
                            ->editColumn('status_quote', function($sale){
                                return $sale->expired? 'Vencida' : 'Al día';
                            })
                            ->editColumn('quote', function($sale){
                                return $sale->quotes.'/'.$sale->total_quotes;
                            })
                            ->editColumn('amount', function($sale){
                                return '$'.number_format($sale->amount,2,'.',',');
                            })
                            ->editColumn('artic_type', function($sale){
                                switch ($sale->artic_type) {
                                    case 'T':  $ret = 'Telefonía'; break;
                                    case 'F':  $ret = 'Fibra'; break;
                                    case 'M':  $ret = 'MIFI'; break;
                                    default: $ret = 'Internet Hogar'; break;
                                }
                                return $ret;
                            })
                            ->make(true);
        }
    }

    public static function getQuoteDetail(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->unique)){
                $detail = SaleInstallmentDetail::getDetailByTransaction($request->unique);

                if(count($detail)){
                    $html = view('pages.ajax.report.installmentSalesQuoteDetail', compact('detail'))
                        ->render();

                    return response()->json(['success' => true, 'html' => $html]);
                }
            }

            return response()->json(['success' => false, 'msg' => 'No se pudo cargar el detalle de las cuotas.']);
        }

    }


    public static function downloadRepSalesInst(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_venta_abono';

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
        return response()->json(array('error' => true));
    }

    //Reporte RRE Abono
    public static function installmentRRE(){
        /*if(session('user')->profile->type == "master")
            $orgs = Organization::select('id', 'business_name')->where('status','A')->get();
        else
            $orgs = Organization::select('id', 'business_name')
                                  ->where([
                                    ['status','A'],
                                    ['id', session('user')->id_org]
                                  ])
                                  ->get();
        */

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $html = view('pages.ajax.report.installmentRRE', compact('orgs'))->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public static function getRREInstDT(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $rres = SaleInstallmentDetail::getRREReport($request->all());

            return DataTables::of($rres)
                            ->editColumn('unique_transaction', function($rre){
                                return $rre->unique_transaction;
                            })
                            ->editColumn('msisdn', function($rre){
                                return $rre->msisdn;
                            })
                            ->editColumn('org', function($rre){
                                return $rre->business_name;
                            })
                            ->editColumn('seller', function($rre){
                                return $rre->name_seller.' '.$rre->last_name_seller;
                            })
                            ->editColumn('coordinador', function($rre){
                                return $rre->name_coord.' '.$rre->last_name_coord;
                            })
                            ->editColumn('quote', function($rre){
                                return $rre->n_quote;
                            })
                            ->editColumn('amount', function($rre){
                                return '$'.number_format($rre->amount,2,'.',',');
                            })
                            ->editColumn('date_sell', function($rre){
                                return date('d-m-Y H:i:s', strtotime($rre->date_sell));
                            })
                            ->editColumn('date_reg', function($rre){
                                if(!empty($rre->date_reg))
                                    return date('d-m-Y H:i:s', strtotime($rre->date_reg));

                                return 'N/A';
                            })
                            ->editColumn('status', function($rre){
                                if(!empty($rre->status_rre)){
                                    if($rre->status_rre == 'V')
                                        return 'EA';
                                    if($rre->status_rre == 'C')
                                        return 'A';
                                    if($rre->status_rre == 'P')
                                        return 'C';
                                    if($rre->status_rre == 'R')
                                        return 'R';
                                }

                                return 'N/A';
                            })
                            ->editColumn('date_proc', function($rre){
                                if(!empty($rre->status_rre)){
                                    if($rre->status_rre == 'C')
                                        return date('d-m-Y H:i:s', strtotime($rre->date_acept));

                                    if($rre->status_rre == 'R')
                                        return date('d-m-Y H:i:s', strtotime($rre->date_update));
                                }

                                return 'N/A';
                            })
                            ->editColumn('alert', function($rre){
                                if(!empty($rre->status_rre)){
                                    if($rre->status_rre == 'V'){
                                        $now = time();
                                        $dater = strtotime($rre->date_reg);

                                        $dr = strtotime('+ 12 hours', $dater);
                                        if($dr <= $now)
                                            return 'Rojo';

                                        $do = strtotime('+ 6 hours', $dater);
                                        if($do <= $now)
                                            return 'Naranja';

                                        return 'Azul';
                                    }
                                    if($rre->status_rre == 'C')
                                        return 'Verde';
                                    if($rre->status_rre == 'R')
                                        return 'Gris';
                                }

                                return 'N/A';
                            })
                            ->editColumn('date_conc', function($rre){
                                if($rre->status_rre == 'P')
                                    return date('d-m-Y H:i:s', strtotime($rre->date_update));

                                return 'N/A';
                            })
                            ->make(true);
        }
    }

    public static function downloadRepRREInst(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            $report = Reports::getConnect('W');

            $report->name_report = 'reporte_rre_abono';

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

        return response()->json(array('error' => true));
    }

    private static function getReporModemsInstallments(){
        $coords = TokensInstallments::select(
                    'islim_tokens_installments.tokens_assigned',
                    'islim_tokens_installments.tokens_available',
                    'islim_tokens_installments.assigned_user',
                    'coord.name as coord_name',
                    'coord.last_name as coord_last_name',
                    'sup.name as sup_name',
                    'sup.last_name as sup_last_name'
                  )
                  ->join(
                    'islim_users as coord',
                    'coord.email',
                    'islim_tokens_installments.assigned_user'
                  )
                  ->leftJoin(
                    'islim_users as sup',
                    'sup.email',
                    'islim_tokens_installments.process_user'
                  )
                  ->where('islim_tokens_installments.status', 'A')
                  ->get();

        $date30 = date('Y-m-d', strtotime('- 30 days', time()));
        $today = time();
        foreach ($coords as $coord){
            $coord->expIntH = ExpiredInstallment::select(
                                'islim_expired_installments.amount'
                                )
                                ->join(
                                    'islim_sales_installments',
                                    'islim_sales_installments.id',
                                    'islim_expired_installments.id_sale_installment'
                                )
                                ->where([
                                    ['islim_expired_installments.status', 'A'],
                                    ['islim_sales_installments.coordinador', $coord->assigned_user]
                                ])
                                ->groupBy(
                                    'islim_expired_installments.id_sale_installment'
                                )
                                ->get()
                                ->count();

            $coord->expInt = ExpiredInstallment::select(
                                'islim_expired_installments.amount'
                                )
                                ->join(
                                    'islim_sales_installments',
                                    'islim_sales_installments.id',
                                    'islim_expired_installments.id_sale_installment'
                                )
                                ->where([
                                    ['islim_expired_installments.status', 'A'],
                                    ['islim_sales_installments.coordinador', $coord->assigned_user],
                                    ['islim_expired_installments.date_expired', '>=', $date30.'00:00:00']
                                ])
                                ->groupBy(
                                    'islim_expired_installments.id_sale_installment'
                                )
                                ->get()
                                ->count();

            $coord->saleInsTH = SaleInstallment::where(
                                    'coordinador', $coord->assigned_user
                                 )
                                 ->whereIn('status', ['F', 'P'])
                                 ->count();



            $coord->saleInsTM = SaleInstallment::where([
                                    ['coordinador', $coord->assigned_user],
                                    ['date_reg', '>=', $date30.'00:00:00']
                                 ])
                                 ->whereIn('status', ['F', 'P'])
                                 ->count();

            $sales = SaleInstallment::select(
                                        'islim_sales_installments.quotes',
                                        'islim_sales_installments.date_reg_alt',
                                        'islim_sales_installments.first_pay',
                                        'islim_sales_installments.amount',
                                        'islim_config_installments.quotes as cq',
                                        'islim_config_installments.days_quote'
                                    )
                                    ->join(
                                        'islim_config_installments',
                                        'islim_config_installments.id',
                                        'islim_sales_installments.config_id'
                                    )
                                    ->where([
                                        ['islim_sales_installments.status', 'P'],
                                        ['islim_sales_installments.coordinador', $coord->assigned_user]
                                    ])
                                    ->orderBy('islim_sales_installments.date_reg_alt', 'ASC');

            $sales = $sales->get();

            $ct = 0;
            $ce = 0;
            $cok = 0;
            $coord->daysOld = 0;
            $coord->totalAmountI = 0;
            $coord->totalPending = 0;
            $coord->totalExp = 0;

            foreach($sales as $sale){
                $dateSale = strtotime(
                            '+ '.($sale->days_quote * $sale->quotes).' days',
                             strtotime($sale->date_reg_alt)
                             );

                //No esta vencido
                if($today <= $dateSale){
                    $cok++;
                    $coord->totalAmountI += $sale->first_pay;
                    $coord->totalPending += $sale->amount - $sale->first_pay;
                }else{
                    $ce++;
                    //$coord->totalExp += $sale->amount - $sale->first_pay;
                    $coord->totalExp += (($sale->amount - $sale->first_pay) / ($sale->cq - 1)) * ($sale->cq - $sale->quotes);
                    $date1 = new DateTime(date('Y-m-d H:i:s', $dateSale));
                    $date2 = new DateTime(date('Y-m-d H:i:s', $today));
                    $diff = $date1->diff($date2);
                    $diffd = $diff->days === 0 ? 1 : $diff->days;

                    if($coord->daysOld < $diffd)
                        $coord->daysOld = $diffd;
                }

                $ct++;
            }

            $coord->saleInsT = $ct;
            $coord->saleInsOK = $cok;
            $coord->saleInsEX = $ce;
        }

        return $coords;
    }

    public static function modemsInstallments(){
        $coords = self::getReporModemsInstallments();

        $html = view('pages.ajax.report.installmentModems', compact('coords'))
                ->render();

        return response()->json(['success' => true, 'msg'=>$html, 'numError'=>0]);
    }

    public function downloadModInsReport(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $coords = self::getReporModemsInstallments();

            $dataxls []= [
                'Coordinador',
                'Modems autorizados por',
                'Modems autorizados',
                'Modems colocados vigentes',
                'Pago inicial',
                'Saldo por cobrar',
                'Modems vencidos',
                'Saldo vencido',
                'Adeudo mas antiguo (Dias)',
                'Total modems colocados',
                'Modems disponibles',
                'Total modems colocados (Historico)',
                'Total modems no recuperados (Historico)',
                'Total modems colocados (Ultimos 30d)',
                'Total modems vencidos (Ultimos 30d)'
            ];

            foreach ($coords as $coord){
                $dataxls []= [
                    $coord->coord_name.' '.$coord->coord_last_name,
                    empty($coord->sup_name) ? 'N/A' : $coord->sup_name.' '.$coord->sup_last_name,
                    (String)$coord->tokens_assigned,
                    (String)$coord->saleInsOK,
                    '$'.number_format($coord->totalAmountI,2,'.',','),
                    '$'.number_format($coord->totalPending,2,'.',','),
                    (String)$coord->saleInsEX,
                    '$'.number_format($coord->totalExp,2,'.',','),
                    (String)$coord->daysOld,
                    (String)$coord->saleInsT,
                    (String)$coord->tokens_available,
                    (String)$coord->saleInsTH,
                    (String)$coord->expIntH,
                    (String)$coord->saleInsTM,
                    (String)$coord->expInt
                ];
            }

            $url = CommonHelpers::saveFile('/public/reports', 'reports', $dataxls, 'modems_abono_'.date('d-m-Y'));

            return response()->json(['success' => true, 'url' => $url]);
        }
    }

    //logica reporte status inventario
    public function downloadCSVReportStatusInv(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            $report = Reports::getConnect('W');
            $report->name_report = 'reporte_estatus_inventario';

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

    //logica reporte status inventario
    public function downloadReportMermaOldEquipment(Request $request){
        if($request->isMethod('post')){
            $inputs = $request->all();

            $report = Reports::getConnect('W');
            $report->name_report = 'reporte_bodega_merma_equipos_viejos';

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

     //logica reporte de facturas masivas
    public function downloadBillingsMasiveReport(Request $request){

        if($request->isMethod('post')){
            $inputs = $request->all();

            $report = Reports::getConnect('W');
            $report->name_report = 'reporte_facturas_masiva';

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
