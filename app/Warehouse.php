<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\UserWarehouse;
use App\User;
use App\Inventory;
use App\Organization;
use App\SellerInventory;
use App\Product;

class Warehouse extends Model {
	protected $table = 'islim_warehouses';

	protected $fillable = [
		'name',
        'address',
        'phone',
        'lat',
        'lng',
        'position',
        'date_reg',
        'status',
        'group',
        'route',
        'street_number',
        'neighborhood',
        'locality',
        'subLocality',
        'state',
        'cp'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Warehouse;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getAssignedUser ($warehouse) {
        $emails = UserWarehouse::select('users_email')
                                 ->where(['warehouses_id' => $warehouse, 'status'=>'A'])
                                 ->get();

        $users = User::find($emails);
        return $users;
    }

    public static function getAssignedUsers () {
        $emails = UserWarehouse::select('users_email')
                                 ->distinct()
                                 ->get();

        $users = array();
        foreach ($emails as $email) {
             $users[] = User::getUser($email);
        }
        return $users;
    }

    public static function getReport($id = null, $productId = null, $org = null){
        $product = array();
        #array de id asignados a vendedores (no se encuentran literal en la bodega)
        /*$invAssigId = SellerInventory::where('status','!=','T')
        ->get(['inv_arti_details_id'])->pluck('inv_arti_details_id');*/
        #buscamos todas o una wh en especifica
        $whsId = Warehouse::getConnect('R')->where('status','!=','T')
                            ->when($id,function($query,$id){
                                return $query->where('id',$id);
                            })
                            ->get(['id'])
                            ->pluck('id');


        #buscando organizaciones permitidas segun la organizacion a la que pertenece el usuario
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        #buscando ids de bodega por organizacion
        $idWs = OrgWarehouse::getConnect('R')->select(
                                    'islim_warehouses.name',
                                    'islim_warehouses.id',
                                    'islim_wh_org.id_wh'
                                )
                                ->join(
                                    'islim_warehouses',
                                    'islim_warehouses.id',
                                    'islim_wh_org.id_wh'
                                );
        if(!empty($org))
            $idWs = $idWs->where('islim_wh_org.id_org',$org);
        else
            $idWs = $idWs->whereIn('islim_wh_org.id_org',$orgs->pluck('id'));
        $idWs = $idWs->get()->pluck('id_wh');


        if(count($idWs) > 0){
            $whsId = Warehouse::getConnect('R')->where('status','!=','T')
                                    ->when($idWs,function($query,$id){
                                        return $query->whereIn('id',$id);
                                    })
                                    ->get(['id'])
                                    ->pluck('id');

            if(!empty($id)){
                $whsId = [$id];
            }
        }else{
            $whsId = [];
        }



        #buscando ids de bodega por organizacion
        // if(session('user')->profile->type != "master"){
        //     $idWs = OrgWarehouse::getConnect('R')->select(
        //                             'islim_warehouses.name',
        //                             'islim_warehouses.id',
        //                             'islim_wh_org.id_wh'
        //                         )
        //                         ->join(
        //                             'islim_warehouses',
        //                             'islim_warehouses.id',
        //                             'islim_wh_org.id_wh'
        //                         )
        //                         ->where('islim_wh_org.id_org',session('user')->id_org)
        //                         ->get()
        //                         ->pluck('id_wh');

        //     if(count($idWs) > 0){
        //         $whsId = Warehouse::getConnect('R')->where('status','!=','T')
        //                             ->when($idWs,function($query,$id){
        //                                 return $query->whereIn('id',$id);
        //                             })
        //                             ->get(['id'])
        //                             ->pluck('id');
        //     }else
        //         $whsId = [];
        // }

        // if(!empty($org) && session('user')->profile->type == "master"){
        //     $idWs = OrgWarehouse::getConnect('R')->select(
        //                             'islim_warehouses.name',
        //                             'islim_warehouses.id',
        //                             'islim_wh_org.id_wh'
        //                         )
        //                         ->join(
        //                             'islim_warehouses',
        //                             'islim_warehouses.id',
        //                             'islim_wh_org.id_wh'
        //                         )
        //                         ->where('islim_wh_org.id_org', $org)
        //                         ->get()
        //                         ->pluck('id_wh');

        //     if(count($idWs) > 0){
        //         $whsId = Warehouse::getConnect('R')->where('status','!=','T')
        //                             ->when($idWs,function($query,$id){
        //                                 return $query->whereIn('id',$id);
        //                             })
        //                             ->get(['id'])
        //                             ->pluck('id');

        //         if(!empty($id)){
        //             $whsId = [$id];
        //         }
        //     }else{
        //         $whsId = [];
        //     }
        // }

        #buscamos todos o un producto en especifico
        $sub = DB::raw('(select invs.inv_arti_details_id from islim_inv_assignments as invs where invs.status != "T" and invs.inv_arti_details_id = islim_inv_arti_details.id)');
        $inv = Inventory::getConnect('R')->where('status', 'A')
                          //->whereNotIn('id', $invAssigId)
                          ->whereIn('warehouses_id', $whsId)
                          ->when($productId,function($query, $productId){
                            return $query->where('inv_article_id', $productId);
                          })
                          ->whereNull($sub)
                          ->get();

        #vamos articulo por articulo creando el objeto
        foreach ($inv as $arti) {
            $key = $arti->warehouses_id.$arti->inv_article_id;
        #verificamos que exista un producto de una wh y le ingresamos el articulo o creamos el registro
           if(array_key_exists($arti->warehouses_id.$arti->inv_article_id,$product)){
            $product[$key]->inv[] = $arti;
           }else{
            $pr = Product::getConnect('R')->find($arti->inv_article_id);

            $obj = new class{};
            $obj->wh_id = $arti->warehouses_id;
            $obj->wh_name = Warehouse::getConnect('R')->find($arti->warehouses_id)->name;
            $obj->pro_id = $arti->inv_article_id;
            $obj->pro_name = $pr->title;
            $obj->artic_type = $pr->artic_type;
            $obj->inv[] = $arti;
            $product[$key] = new class{};
            $product[$key] = $obj;
           }
        }
        return $product;
    }

    /*public static function getReport ($id, $productId, $status) {
        $report = Warehouse::select('id','name','address')->whereIn('id',
            ((!isset($id)) ? Warehouse::select('id')->whereIn('status', $status)->get() : array($id))
        );
        $report = $report->get();
        foreach($report as $warehouse) {
            $warehouse->supervisors = DB::table('islim_users')->distinct()
                ->join('islim_wh_users', 'islim_wh_users.users_email', '=', 'islim_users.email')
                ->where('islim_wh_users.warehouses_id', $warehouse->id)
                ->whereIn('islim_wh_users.status', $status)
                ->whereIn('islim_users.status', $status)
                ->select(
                    'islim_users.email',
                    'islim_users.name',
                    'islim_users.last_name')->get();

            $assignedSQL = DB::table('islim_inv_assignments')->distinct()
                ->join('islim_inv_arti_details', 'islim_inv_assignments.inv_arti_details_id', '=', 'islim_inv_arti_details.id')
                ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id');

            $warehouse->inventory = DB::table('islim_warehouses')->distinct()
                ->join('islim_inv_arti_details', 'islim_warehouses.id', '=', 'islim_inv_arti_details.warehouses_id')
                ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
                ->select(
                    'islim_inv_arti_details.msisdn',
                    'islim_inv_arti_details.iccid',
                    'islim_inv_arti_details.imei',
                    'islim_inv_articles.title',
                    'islim_inv_arti_details.inv_article_id',
                    'islim_inv_arti_details.id')
                ->where('islim_warehouses.id', $warehouse->id)
                ->whereIn('islim_inv_articles.status', $status)
                ->whereIn('islim_inv_arti_details.status', $status)
                ->whereIn('islim_warehouses.status', $status);
            if (isset($productId)) {
                $warehouse->inventory = $warehouse->inventory->where('islim_inv_articles.id', $productId);
            }
            $warehouse->inventory = $warehouse->inventory->get();

            foreach($warehouse->inventory as $item) {
                $user = User::select('name', 'last_name')->whereIn('email', SellerInventory::select('users_email')->where(['inv_arti_details_id' => $item->id])->whereIn('status', $status)->get())->first();
                if (isset($user)) {
                    $item->assignedTo = $user->name.' '.$user->last_name;
                } else {
                    $item->assignedTo = 'N/A';
                }
            }
        }

        return $report;
    }*/
}