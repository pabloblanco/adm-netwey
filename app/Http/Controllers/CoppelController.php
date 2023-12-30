<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\User;
use App\Coppel;
use App\Sale;
use App\Pack;
use App\Organization;
use App\ProfileDetail;
use App\AssignedSaleDetails;
use App\AssignedSales;

use DataTables;

// use App\Product;
// use App\ProductsProvider;
// use App\ProductsCategory;
// use App\ArticlePack;
// use App\Pack;

class CoppelController extends Controller {

    public function upsFailsView () {
        $html = view(
            'pages.ajax.coppel.ups_fails'
        )->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function listDT(Request $request){

        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $lists = Coppel::getConnect('R')
                            ->select([
                                'islim_coppel.id',
                                'islim_coppel.msisdn',
                                DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                                DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) as seller'),
                                'islim_inv_articles.title as article',
                                'islim_packs.title as pack',
                                'islim_services.title as service',
                                'islim_coppel.error',
                                DB::raw('DATE_FORMAT(islim_coppel.date_reg,"%d-%m-%Y %H:%i:%s") as date_register')
                            ])
                            ->join('islim_clients','islim_clients.dni','islim_coppel.clients_dni')
                            ->join('islim_users','islim_users.email','islim_coppel.user_email')
                            ->join('islim_inv_articles','islim_inv_articles.id','islim_coppel.articles_id')
                            ->join('islim_packs','islim_packs.id','islim_coppel.pack_id')
                            ->join('islim_services','islim_services.id','islim_coppel.service_id')
                            ->where('islim_coppel.status','EA')
                            ->whereIn('islim_users.id_org', $orgs->pluck('id'))
                            ->orderBy('islim_coppel.date_reg','DESC');


        return DataTables::eloquent($lists)
                            ->editColumn('id', function($list){
                                return $list->id;
                            })
                            ->make(true);
    }

    public function validSustitute(Request $request){

        if(!empty($request->msisdn) && !empty($request->id)){
            $ups_fail=Coppel::getConnect('R')->find($request->id);
            if(!empty($ups_fail)){
                $pack=Pack::getConnect('R')
                            ->find($ups_fail->pack_id);

                $sale=Sale::getConnect('R')
                            ->select([
                                DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                                DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) as seller'),
                                'islim_inv_articles.title as article',
                                'islim_packs.title as pack',
                                'islim_services.title as service',
                                DB::raw('DATE_FORMAT(islim_sales.date_reg,"%d-%m-%Y %H:%i:%s") as date_register')
                            ])
                            ->join('islim_client_netweys','islim_client_netweys.msisdn','islim_sales.msisdn')
                            ->join('islim_clients','islim_clients.dni','islim_client_netweys.clients_dni')
                            ->join('islim_users','islim_users.email','islim_sales.users_email')
                            ->join('islim_inv_arti_details','islim_inv_arti_details.id','islim_sales.inv_arti_details_id')
                            ->join('islim_inv_articles','islim_inv_articles.id','islim_inv_arti_details.inv_article_id')
                            ->join('islim_packs','islim_packs.id','islim_sales.packs_id')
                            ->join('islim_services','islim_services.id','islim_sales.services_id')
                            ->where('islim_sales.msisdn',$request->msisdn)
                            ->where('islim_sales.users_email',$ups_fail->user_email)
                            ->where('islim_sales.sale_type',$pack->pack_type)
                            ->where('islim_sales.packs_id',$ups_fail->pack_id)
                            ->where('islim_sales.type','P')
                            ->where('islim_sales.status','<>','T')
                            ->where('islim_sales.date_reg','>=',$ups_fail->date_reg);

                $sale=$sale->first();

                if(!empty($sale)){
                    return response()->json(array(
                        'success' => true,
                        'msg' => 'Es válido',
                        'data' => $sale
                    ));
                }
            }

        }
        return response()->json(array(
            'success' => false,
            'msg' => 'DN Sustituto no es válido'
        ));
    }



    public function associateSustitute(Request $request){


        if(!empty($request->msisdn) && !empty($request->id)){

            $ups_fail=Coppel::getConnect('W')->find($request->id);
            if(!empty($ups_fail)){

                $pack=Pack::getConnect('R')
                            ->find($ups_fail->pack_id);

                $sale=Sale::getConnect('W')
                            ->where('islim_sales.msisdn',$request->msisdn)
                            ->where('islim_sales.users_email',$ups_fail->user_email)
                            ->where('islim_sales.sale_type',$pack->pack_type)
                            ->where('islim_sales.packs_id',$ups_fail->pack_id)
                            ->where('islim_sales.type','P')
                            ->where('islim_sales.status','<>','T')
                            ->where('islim_sales.date_reg','>=',$ups_fail->date_reg);

                $sale=$sale->first();


                if(!empty($sale)){

                    $profdet = ProfileDetail::getConnect('R')->where('user_email',$sale->users_email)->where('status','A')->first();

                    $is_seller=false;
                    if(!empty($profdet)){
                        if($profdet->id_profile == 11){
                           $is_seller=true;
                        }
                    }

                    try{


                        Sale::getConnect('W')
                        ->where([
                            ['msisdn', $request->msisdn],
                            ['status', '!=', 'T']
                        ])
                        ->whereIn('type',['P','V'])
                        ->update([
                            'status' => 'A',
                            'conciliation' => 'Y'
                        ]);

                        $ups_fail->msisdn = $request->msisdn;
                        //$ups_fail->sales_id = $sale->id;
                        $ups_fail->status = 'S';
                        $ups_fail->date_associated = date('Y-m-d H:i:s');
                        $ups_fail->user_associated = session('user')->email;
                        $ups_fail->save();

                        if(!$is_seller){

                            $dataAssigneSales = array(
                                'parent_email' => $ups_fail->user_email,
                                'users_email' => $ups_fail->user_email,
                                'amount' => $ups_fail->amount,
                                'amount_text' => $ups_fail->amount,
                                'date_accepted' => date('Y-m-d H:i:s'),
                                'date_reg' => date('Y-m-d H:i:s'),
                                'status' => 'A'
                            );

                            $idAssig = AssignedSales::getConnect('W')->insertGetId($dataAssigneSales);

                            $dataDetailsAssig = array(
                                'asigned_sale_id' => $idAssig,
                                'amount' => $ups_fail->amount,
                                'amount_text' => $ups_fail->amount,
                                'unique_transaction' => $sale->unique_transaction
                            );

                            AssignedSaleDetails::getConnect('W')->insert($dataDetailsAssig);

                        }

                        return response()->json(array(
                            'success' => true,
                            'msg' => 'DN sustituto asociado con exito',
                            'is_seller' => $is_seller
                        ));

                    } catch (Exception $e) {
                        return response()->json(array(
                            'success' => false,
                            'msg'=>'Ocurrio un error',
                            'numError'=>1,
                            'msgError'=>$e->getMessage()
                        ));
                    }
                }
            }

        }
        return response()->json(array(
            'success' => false,
            'msg' => 'DN Sustituto no es válido'
        ));
    }


    /*

                    // $query = vsprintf(str_replace('?', '%s', $sale->toSql()), collect($sale->getBindings())->map(function ($binding) {
                //     return is_numeric($binding) ? $binding : "'{$binding}'";
                // })->toArray());

                // //Log::info($query);
                // print_r($query);



    */

   // public function upsFails(){
        // $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        // $ups_fails = Coppel::getConnect('R')
        //                     ->select([
        //                         'islim_coppel.id',
        //                         'islim_coppel.msisdn',
        //                         DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
        //                         DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) as seller'),
        //                         'islim_inv_articles.title as article',
        //                         'islim_packs.title as pack',
        //                         'islim_services.title as service',
        //                         'islim_coppel.error',
        //                         DB::raw('DATE_FORMAT(islim_coppel.date_reg,"%d-%m-%Y %H:%i:%s") as date_register')
        //                     ])
        //                     ->join('islim_clients','islim_clients.dni','islim_coppel.clients_dni')
        //                     ->join('islim_users','islim_users.email','islim_coppel.user_email')
        //                     ->join('islim_inv_articles','islim_inv_articles.id','islim_coppel.articles_id')
        //                     ->join('islim_packs','islim_packs.id','islim_coppel.pack_id')
        //                     ->join('islim_services','islim_services.id','islim_coppel.service_id')
        //                     ->where('islim_coppel.status','EA')
        //                     ->whereIn('islim_users.id_org', $orgs->pluck('id'))
        //                     ->orderBy('islim_coppel.date_reg','DESC');


        // $query = vsprintf(str_replace('?', '%s', $ups_fails->toSql()), collect($ups_fails->getBindings())->map(function ($binding) {
        //         return is_numeric($binding) ? $binding : "'{$binding}'";
        //     })->toArray());

        // return $query;

        //Log::info($query);


        // $ups_fails = $ups_fails->get();


        // $object = array(
        //     'ups_fails' => !empty($ups_fails) ? $ups_fails : null
        // );

    //     $html = view('pages.ajax.coppel.ups_fails')->render();
    //     return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    // }





}
