<?php

namespace App\Http\Controllers;

use App\ArticlePack;
use App\AssignedSales;
use App\BankDeposits;
use App\Helpers\CommonHelpers;
use App\Inventory;
use App\Organization;
use App\OrgWarehouse;
use App\Pack;
use App\PayInstallment;
use App\Product;
use App\Sale;
use App\SaleMetrics;
use App\SellerInventory;
use App\SellerInventoryTemp;
use App\SellerInventoryTrack;
use App\StockProvaDetail;
use App\SuggestedOrder;
use App\SuggestedOrderDetail;
use App\User;
use App\UserDeposit;
use App\UserWarehouse;
use App\Warehouse;
use App\LowRequest;
use App\LowEvidences;
use App\LowReason;
use App\Profile;
use App\ProfileDetail;
use App\FiberZone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Log;

use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\DataTables;

class SellerInventoryController extends Controller
{

  #busca todos los articulos asociados con el usuario. concatena con productos.
  public function getSellerInventory($user_email)
  {
    $inventory = DB::table('islim_inv_assignments')->select(
      'islim_inv_arti_details.id',
      'islim_inv_articles.title as title',
      'islim_inv_arti_details.msisdn',
      'islim_inv_arti_details.imei',
      'islim_inv_arti_details.price_pay as price',
      'islim_inv_assignments.users_email',
      'islim_users.name',
      'islim_users.last_name',
      'islim_inv_assignments.status'
    )
      ->join('islim_inv_arti_details', function ($join) {
        $join->on('islim_inv_assignments.inv_arti_details_id', '=', 'islim_inv_arti_details.id')
          ->whereIn('islim_inv_arti_details.status', (session('user')->platform == 'admin' ? ['A', 'I'] : ['A']));
      })
      ->join('islim_users', function ($join) {
        $join->on('islim_users.email', '=', 'islim_inv_assignments.users_email')
          ->whereIn('islim_users.status', (session('user')->platform == 'admin' ? ['A', 'I'] : ['A']));
      })
      ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
      ->where('islim_inv_assignments.users_email', $user_email)
      ->whereIn('islim_inv_assignments.status', (session('user')->platform == 'admin' ? ['A', 'I'] : ['A']))
      ->get();
    return $inventory;
  }

  #productos que no posee el vendedor y existe en el wh asociado.
  public function otherproducts($user_email)
  {
    $pro_arr_id = null;
    $user       = User::find($user_email);
    $prodinpack = ArticlePack::where('status', 'A')->distinct('inv_article_id')->get(['inv_article_id']);
    if (!empty($prodinpack)) {
      foreach ($prodinpack as $proid) {
        $pro_arr_id[] = $proid->inv_article_id;
      }
    }
    $user_inv   = SellerInventory::where(['users_email' => $user_email, 'status' => 'A'])->get();
    $user_wh    = UserWarehouse::where(['users_email' => $user_email, 'status' => 'A'])->get();
    $inv_arr_id = array();
    $wh_arr_id  = array();
    foreach ($user_wh as $wh) {
      $wh_arr_id[] = $wh->warehouses_id;
    }
    foreach ($user_inv as $id) {
      $inv_arr_id[] = $id->inv_arti_details_id;
    }
    if (!empty($wh_arr_id)) {
      $otherproducts = Inventory::whereIn('inv_article_id', $pro_arr_id)->whereIn('warehouses_id', $wh_arr_id)->whereNotIn('id', $inv_arr_id)->where('status', 'A')->get();
    }
    if (empty($otherproducts)) {
      $otherproducts = Inventory::where('status', 'A')->get();
    }
    foreach ($otherproducts as $product) {
      $detail         = Product::find($product->inv_article_id);
      $product->title = $detail->title;
    }

    return $otherproducts;
  }

  public function update(Request $request)
  {
    $article = SellerInventory::getConnect('W')
      ->where([
        'users_email'         => $request->email,
        'inv_arti_details_id' => $request->article_list
      ])
      ->update([
        'status'      => $request->status,
        'users_email' => $request->email
      ]);

    return 'El inventario fue actualizado exitosamente';
  }

  public function delete($user_email, $type, $article)
  {
    if (!User::hasPermission(session('user.email'), 'A1V-R1V')) {
      return response()->json(array(
        'success'  => false,
        'msg'      => 'No tienes permisos para realizar esta acción.',
        'numError' => 1,
      ));
    }
    if ($type == 'A') { //assignado
      //Descontando deuda para los usuarios con baja en proceso
      $user = User::getUserByEmail($user_email);
      if(!empty($user) && $user->status == 'D'){
        $userDismissal = LowRequest::getInProcessRequestByUser($user_email);
        $articleDetail = Inventory::getDetailById($article);

        if(!empty($userDismissal) && !empty($articleDetail)){
          if($articleDetail->artic_type == 'H'){
            $userDismissal->cash_hbb = $userDismissal->cash_hbb - $articleDetail->price_pay;
          }
          if($articleDetail->artic_type == 'M'){
            $userDismissal->cash_mifi = $userDismissal->cash_mifi - $articleDetail->price_pay;
          }
          if($articleDetail->artic_type == 'T'){
            $userDismissal->cash_telf = $userDismissal->cash_telf - $articleDetail->price_pay;
          }
          if($articleDetail->artic_type == 'F'){
            $userDismissal->cash_fibra = $userDismissal->cash_fibra - $articleDetail->price_pay;
          }

          $userDismissal->article_request = $userDismissal->article_request - $articleDetail->price_pay;
          $userDismissal->cash_total = $userDismissal->cash_total - $articleDetail->price_pay;
          $userDismissal->save();
        }
      }

      //Consultando asignación para validar si esta en estatus rojo
      $assR = SellerInventory::getAssignByUserAndId($user_email, $article);

      if(!empty($assR) && !empty($assR->date_red)){
        $dpack = false;
      }else{
        $dpack = SellerInventory::getConnect('W')
          ->where([
            'users_email'         => $user_email,
            'inv_arti_details_id' => $article
          ])
          ->where('status', '<>', 'T')
          ->update([
            'status'           => 'T',
            'last_assigned_by' => session('user')->email,
            'last_assignment'  => date('Y-m-d H:i:s', time())
          ]);

        $inventory = Inventory::getConnect('R')
          ->find($article);

        SellerInventoryTrack::setInventoryTrack(
          $article,
          $user_email,
          null,
          null,
          $inventory->warehouses_id,
          session('user')->email
        );
      }
    } else {
      $dpack = SellerInventoryTemp::getConnect('W')
        ->where([
          'user_email'         => $user_email,
          'inv_arti_details_id' => $article
        ])
        ->where('status', '=', 'P')
        ->update([
          'status' => 'T',
          'date_status'  => date('Y-m-d H:i:s', time())
        ]);
    }

    if ($dpack) {
      return response()->json(array('success' => true, 'msg' => 'El registro ha sido eliminado de forma exitosa', 'numError' => 0));
    } else {
      return response()->json(array('success' => false, 'msg' => 'Hubo un error y no se pudo eliminar el registro', 'numError' => 1));
    }
    return response()->json($dpack);
  }

  public function deleteBatch(Request $request)
  {
    if (!User::hasPermission(session('user.email'), 'A1V-R1V')) {
      return response()->json(array(
        'success'  => false,
        'msg'      => 'No tienes permisos para realizar esta acción.',
        'numError' => 1
      ));
    }

    if (!empty($request->ids) && count($request->ids) > 0) {
      $invs = SellerInventory::where('users_email', $request->email)
        ->whereIn('inv_arti_details_id', $request->ids)
        ->where('status', '<>', 'T')
        ->get();

      $invsp = SellerInventoryTemp::where('user_email', $request->email)
        ->whereIn('inv_arti_details_id', $request->ids)
        ->where('status', '=', 'P')
        ->get();

      if (count($invs) > 0) {
        foreach ($invs as $key => $inv) {
          //assignados
          if(empty($inv->date_red)){
            $dpack = SellerInventory::getConnect('W')->where('users_email', $request->email)
            ->where('inv_arti_details_id', $inv->inv_arti_details_id)
            ->where('status', '<>', 'T')
            ->update([
              'status' => 'T'
            ]);

            $inventory = Inventory::getConnect('R')
                                  ->select(
                                    'islim_inv_arti_details.price_pay',
                                    'islim_inv_arti_details.warehouses_id',
                                    'islim_inv_articles.artic_type'
                                  )
                                  ->join(
                                    'islim_inv_articles',
                                    'islim_inv_articles.id',
                                    'islim_inv_arti_details.inv_article_id'
                                  )
                                  ->where('islim_inv_arti_details.id', $inv->inv_arti_details_id)
                                  ->first();

            if(!empty($inventory)){
              //Descontando deuda para los usuarios con baja en proceso
              $user = User::getUserByEmail($request->email);
              if(!empty($user) && $user->status == 'D'){
                $userDismissal = LowRequest::getInProcessRequestByUser($request->email);

                if(!empty($userDismissal)){
                  if($inventory->artic_type == 'H'){
                    $userDismissal->cash_hbb = $userDismissal->cash_hbb - $inventory->price_pay;
                  }
                  if($inventory->artic_type == 'M'){
                    $userDismissal->cash_mifi = $userDismissal->cash_mifi - $inventory->price_pay;
                  }
                  if($inventory->artic_type == 'T'){
                    $userDismissal->cash_telf = $userDismissal->cash_telf - $inventory->price_pay;
                  }
                  if($inventory->artic_type == 'F'){
                    $userDismissal->cash_fibra = $userDismissal->cash_fibra - $inventory->price_pay;
                  }

                  $userDismissal->article_request = $userDismissal->article_request - $inventory->price_pay;
                  $userDismissal->cash_total = $userDismissal->cash_total - $inventory->price_pay;
                  $userDismissal->save();
                }
              }
            }

            SellerInventoryTrack::setInventoryTrack(
              $inv->inv_arti_details_id,
              $request->email,
              null,
              null,
              $inventory->warehouses_id,
              session('user')->email
            );
          }
        }
      }

      if (count($invsp) > 0) {
        //pre-assignados
        $dpackp = SellerInventoryTemp::getConnect('W')
          ->where('user_email', $request->email)
          ->whereIn('inv_arti_details_id', $request->ids)
          ->where('status', '=', 'P')
          ->update([
            'status' => 'T',
            'date_status' => date('Y-m-d H:i:s', time())
          ]);
      }

      $condition = false;
      if (count($invs) > 0 && count($invsp) > 0) {
        $condition = ($dpack && $dpackp);
      } else {
        if (count($invs) > 0) {
          $condition = $dpack;
        } else {
          if (count($invsp) > 0) {
            $condition = $dpackp;
          }
        }
      }


      if ($condition) {
        return response()->json(array(
          'success'  => true,
          'msg'      => 'El registro ha sido eliminado de forma exitosa',
          'numError' => 0
        ));
      } else {
        return response()->json(array(
          'success'  => false,
          'msg'      => 'Hubo un error y no se pudo eliminar el registro',
          'numError' => 1
        ));
      }
    }
  }

  public function store(Request $request)
  {
    $articles  = $request->input('article_list');
    $cangetinv = Sale::where(['users_email' => $request->user_email, 'status' => 'E'])->count();
    if (strlen($articles) > 0) {
      $list = explode(';', $articles);
      if (
        SellerInventory::canRecieveMoreInventory($request->user_email) &&
        ((SellerInventory::getTotalInventory($request->user_email) + count($list)) <= SellerInventory::getTotalPermision($request->user_email)) && ($cangetinv == 0) && (SellerInventory::getTotalInventory($request->user_email) == 0)
      ) {
        $sp = array();
        foreach ($list as $item) {

          $invs = SellerInventory::getConnect('R')
            ->where(['inv_arti_details_id' => $item])
            ->where('status', '<>', 'T')
            ->get();

          SellerInventory::getConnect('W')->where([
            'inv_arti_details_id' => $item
          ])
            ->where('status', '<>', 'T')
            ->update(['status' => 'T']);

          foreach ($invs as $key => $inv) {
            $inventory = Inventory::getConnect('R')->find($inv->inv_arti_details_id);

            SellerInventoryTrack::setInventoryTrack(
              $inv->inv_arti_details_id,
              $inv->users_email,
              null,
              null,
              $inventory->warehouses_id,
              session('user')->email
            );
          }

          $test = SellerInventory::where(['users_email' => $request->user_email, 'inv_arti_details_id' => $item])->count();

          $inventory = Inventory::getConnect('R')->find($item);

          if ($test != 0) {
            SellerInventory::getConnect('W')->where([
              'users_email'         => $request->user_email,
              'inv_arti_details_id' => $item
            ])->update([
              'status'           => 'A',
              'date_red'         => null,
              'date_orange'      => null,
              'date_reg'         => date('Y-m-d H:i:s', time()),
              'last_assigned_by' => session('user')->email,
              'last_assignment'  => date('Y-m-d H:i:s', time())
            ]);
          } else {
            $sellerpack                      = SellerInventory::getConnect('W');
            $sellerpack->users_email         = $request->user_email;
            $sellerpack->inv_arti_details_id = $item;
            $sellerpack->date_reg            = date('Y-m-d H:i:s', time());
            $sellerpack->status              = $request->status;
            $sellerpack->last_assigned_by    = session('user')->email;
            $sellerpack->last_assignment     = date('Y-m-d H:i:s', time());
            $sellerpack->save();
          }

          SellerInventoryTrack::setInventoryTrack(
            $item,
            null,
            $inventory->warehouses_id,
            $request->user_email,
            null,
            session('user')->email
          );
        }
        return 'El inventario fue asignado exitosamente';
      } else {
        return $request->user_email . ' no puede recibir más productos';
      }
    }
  }

  #vista de los vendedores en el sistema.
  public function view()
  {
    $html = view('pages.ajax.seller_inventory')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function getDNsAvailable(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $msisdn = $request->input('msisdn');

      $ambiente = env('APP_ENV')=='production'?'P':'QA';
      // ->leftJoin('islim_fiber_article_zone', function ($join) {
      //       $join->on('islim_fiber_article_zone.article_id', '=', 'islim_inv_articles.id')
      //         ->where('islim_fiber_article_zone.status', 'A');
      //     })
      //     ->leftJoin('islim_fiber_zone', function ($join) use ($ambiente) {
      //       $join->on('islim_fiber_zone.id', '=', 'islim_fiber_article_zone.fiber_zone_id')
      //         ->where('islim_fiber_zone.status', 'A')
      //         ->where('islim_fiber_zone.ambiente', $ambiente);
      //     })

      if (!empty($msisdn)) {
        $inventories = Inventory::getConnect('R')
          ->select(
            'islim_inv_arti_details.id',
            'islim_inv_articles.title as title',
            'islim_inv_arti_details.msisdn',
            'islim_inv_articles.artic_type as type',
            'islim_inv_categories.title as category',
            DB::raw('CONCAT(islim_inv_articles.title, ": ", islim_inv_arti_details.msisdn) as product'),
            'islim_inv_articles.id as art_id'
          )
          ->join(
            'islim_inv_articles',
            'islim_inv_articles.id',
            'islim_inv_arti_details.inv_article_id'
          )
          ->join(
            'islim_inv_categories',
            'islim_inv_categories.id',
            'islim_inv_articles.category_id'
          )
          ->where([
            ['islim_inv_arti_details.status', 'A'],
            ['islim_inv_arti_details.msisdn', 'like', $msisdn . '%']
          ]);

        if (!empty(session('user')->id_org) && session('user.profile.id') >= 8) {
          $wh = OrgWarehouse::select('id_wh')->where('id_org', session('user')->id_org)->get();
          $inventories=$inventories->whereIn('warehouses_id', $wh->pluck('id_wh'));
        }

        $inventories=$inventories->limit(10)->get();

        foreach ($inventories as $key => $inventory) {
          $zones = FiberZone::getConnect('R')
                      ->select('islim_fiber_zone.name')
                      ->join('islim_fiber_article_zone','islim_fiber_article_zone.fiber_zone_id','islim_fiber_zone.id')
                      ->where('islim_fiber_zone.ambiente',$ambiente)
                      ->where('islim_fiber_article_zone.status','A')
                      ->where('islim_fiber_zone.status','A')
                      ->where('islim_fiber_article_zone.article_id',$inventory->art_id);


           // $query = vsprintf(str_replace('?', '%s', $zones->toSql()), collect($zones->getBindings())->map(function ($binding) {
           //      return is_numeric($binding) ? $binding : "'{$binding}'";
           //  })->toArray());

           // Log::info($query);


          $zones = $zones->get();
          if($zones){
            $inventory->zones = $zones;
          }
          else{
            $inventory->zones = null;
          }
        }



        return response()->json(array('success' => true, 'inventory' => $inventories));
      }

      return response()->json(array('success' => false));
    }
  }

  //Retorna usuarios vendedores o coordinadores
  public function getUsersInv(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $name      = $request->input('name');
      $hierarchy = session('user.profile.hierarchy');

      if (!empty($name) && !empty($hierarchy) && ($hierarchy >= 4 || session('user.profile.id') == 1)) {
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $users = User::getConnect('R')
          ->select(
            'islim_users.name',
            'islim_users.last_name',
            'islim_users.email',
            'islim_users.platform',
            DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username')
          )
          ->join(
            'islim_profile_details',
            'islim_profile_details.user_email',
            'islim_users.email'
          )
          ->join(
            'islim_profiles',
            'islim_profiles.id',
            'islim_profile_details.id_profile'
          )
          ->where(function($query) use ($name) {
                $query->where([
                  [DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name)'), 'like', '%' . str_replace(' ', '%', $name) . '%']
                ])
                ->orWhere('islim_users.email', 'like', '%' . str_replace(' ', '%', $name) . '%');
          })
          ->where([
            ['islim_profile_details.status', 'A'],
            ['heredity', 'Y']])
          ->whereIn('islim_users.status', ['A', 'I', 'D'])
          ->whereIn('islim_users.platform', ['admin', 'vendor', 'coordinador'])
          ->whereIn('islim_profiles.platform', ['coordinador', 'admin', 'vendor']);

        if ($orgs->count()) {
          $users->whereIn('islim_users.id_org', $orgs->pluck('id'));
        }

        if ($hierarchy >= 4) {
          $users->where('islim_users.parent_email', session('user.email'));
        }

        return response()->json(array('success' => true,'users' => $users->limit(10)->get()));
      }

      return response()->json(array('success' => false));
    }
  }

  public function getUsersSelect(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $name = $request->input('name');

      if (!empty($name)) {

        $users = User::getConnect('R')
          ->select(
            'islim_users.name',
            'islim_users.last_name',
            'islim_users.email',
            'islim_users.platform',
            DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username')
          )
          ->where(function($query) use ($name) {
                $query->where([
                  [DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name)'), 'like', '%' . str_replace(' ', '%', $name) . '%']
                ])
                ->orWhere('islim_users.email', 'like', '%' . str_replace(' ', '%', $name) . '%');
          })
          ->whereIn('islim_users.status', ['A', 'I', 'D']);

        return response()->json(array('success' => true,'users' => $users->limit(10)->get()));
      }

      return response()->json(array('success' => false));
    }
  }

  #vista del inventario del vendedor
  public function viewinv($user_email)
  {
    $inventory = SellerInventory::getUserInventory($user_email);
    $user = User::getUserByEmail($user_email);
    $html      = view('pages.ajax.seller_inventory.detail', compact('inventory'))->render();

    $response = array(
      'success'   => true,
      'msg'       => $html,
      'inventory' => $inventory,
      'user_status' => !empty($user) ? $user->status : 'T',
      'errorMsg'  => '',
      'numError'  => 0
    );
    return response()->json($response);
  }

  public function sellerpacks($user_email)
  {
    $user      = User::find($user_email);
    $userpacks = SellerInventory::where('users_email', $user->email)->distinct('packs_id')->get(['packs_id']);
    $upf       = array();
    foreach ($userpacks as $userpack) {
      $upf[] = Pack::find($userpack->packs_id);
    }
    $user->packs = $upf;
    foreach ($user->packs as $upfd) {
      $dp  = SellerInventory::where(['users_email' => $user->email, 'packs_id' => $upfd->id])->get();
      $dpf = array();
      foreach ($dp as $sdp) {
        $detail                  = Inventory::find($sdp->inv_arti_details_id);
        $product                 = Product::find($detail->inv_article_id);
        $product->detail_product = $detail;
        $dpf[]                   = $product;
      }
      $upfd->products = $dpf;
    }

    return $user;
  }
  public function otherpacks($user_email)
  {
    $userpacks = SellerInventory::where('users_email', $user_email)->distinct('packs_id')->get(['packs_id']);
    $idp       = array();
    foreach ($userpacks as $p) {
      $idp[] = $p->packs_id;
    }
    $otherpacks = Pack::whereNotIn('id', $idp)->get();
    return $otherpacks;
  }

  public function associateSellerInventory(Request $request)
  {
    $response;
    $article;
    $fileFlag = false;
    $user;
    $ids      = array();
    $checkIds = array();
    $errors   = array();
    if (session('user')->platform == 'admin') {
      $user = User::find($request->seller);
      if (!isset($user)) {
        $response = array('success' => false, 'msg' => 'No há elegido un vendedor al cual asociar el producto', 'numError' => 1);
      }
    } else {
      $user = User::where(['email' => $request->seller, 'parent_email' => session('user.email')])->first();
      if (!isset($user)) {
        $response = array('success' => false, 'msg' => 'El vendedor debe ser supervisado por usted para poder asignarle productos', 'numError' => 2);
      }
    }
    if ($request->hasFile('inventory_file')) {
      $fileFlag = true;
      if ($request->file('inventory_file')->isValid()) {
        $file = $request->file('inventory_file');

        $path      = base_path('uploads');
        $file_name = $file->getClientOriginalName();
        if (!file_exists($path)) {
          mkdir($path, 0777, true);
        }
        $file->move($path, $file_name);

        ini_set('auto_detect_line_endings', true);
        if (($gestor = fopen($path . '/' . $file_name, "r")) !== false) {
          $countHbb   = 0;
          $countMbb   = 0;
          $countMifi  = 0;
          $countFibra = 0;

          while (($datos = fgetcsv($gestor, 1000, ",")) !== false) {
            $temp = Inventory::select(
              'islim_inv_arti_details.msisdn',
              'islim_inv_articles.artic_type'
            )
              ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
              ->whereIn('islim_inv_arti_details.msisdn', $datos)
              ->get();

            foreach ($temp as $item) {
              if ($item->artic_type == 'T') {
                $countMbb = $countMbb + 1;
              }
              if ($item->artic_type == 'H') {
                $countHbb = $countHbb + 1;
              }
              if ($item->artic_type == 'M') {
                $countMifi = $countMifi + 1;
              }
              if ($item->artic_type == 'F') {
                $countFibra = $countFibra + 1;
              }
              $checkIds[] = $item->msisdn;
            }
          }
          fclose($gestor);
        } else {
        }
        ini_set('auto_detect_line_endings', false);

        unlink($path . '/' . $file_name);
      } else {
        $errors[] = 'El archivo no puede ser validado';
      }
    } else {
      if ($request->has('inventory_select')) {
        $countHbb   = 0;
        $countMbb   = 0;
        $countMifi  = 0;
        $countFibra = 0;

        $temp = Inventory::select(
          'islim_inv_arti_details.msisdn',
          'islim_inv_articles.artic_type'
        )
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->whereIn('islim_inv_arti_details.id', explode(",", $request->inventory_select))
          ->get();

        foreach ($temp as $t) {
          if ($t->artic_type == 'T') {
            $countMbb = $countMbb + 1;
          }
          if ($t->artic_type == 'H') {
            $countHbb = $countHbb + 1;
          }
          if ($t->artic_type == 'M') {
            $countMifi = $countMifi + 1;
          }
          if ($t->artic_type == 'F') {
            $countFibra = $countFibra + 1;
          }
          $checkIds[] = $t->msisdn;
        }
      } else {
        $errors[] = 'Debe seleccionar un archivo CSV con la lista de MSISDN o alguno de los artículos de la lista, intente nuevamente';
      }
    }

    if (isset($user) && $user->status == 'A') {
      if (isset($checkIds) && (count($checkIds) > 0)) {
        $totalAssigned     = SellerInventory::getTotalInventory($user->email);
        $totalAllowedHbb   = SellerInventory::getTotalPermision($user->email, 'LIV-DSE');
        $totalAllowedMbb   = SellerInventory::getTotalPermision($user->email, 'LIV-DSM');
        $totalAllowedMifi  = SellerInventory::getTotalPermision($user->email, 'LIV-MIF');
        $totalAllowedFibra = SellerInventory::getTotalPermision($user->email, 'LIV-FIB');

        $numberDebt = Sale::getConnect('R')->where(['users_email' => $request->user_email, 'status' => 'E'])->count();
        $condition  = ($totalAllowedHbb >= $countHbb) && ($totalAllowedMbb >= $countMbb) && ($totalAllowedMifi >= $countMifi) && ($totalAllowedFibra >= $countFibra) && ($numberDebt == 0);

        if ($user->platform == 'vendor') {
          $condition = $condition && ($totalAssigned == 0);
        }
        if ($condition) {
          $line = 1;
          foreach ($checkIds as $item) {
            $inventory = Inventory::whereIn('status', ['A'])->where(['msisdn' => $item]);

            if (!empty(session('user')->id_org) && session('user.profile.id') >= 8) {
              $wh        = OrgWarehouse::select('id_wh')->where('id_org', session('user')->id_org)->get();
              $wh        = $wh->pluck('id_wh');
              $inventory = $inventory->whereIn('warehouses_id', $wh);
            }

            $inventory = $inventory->first();

            if (isset($inventory)) {
              $parentItem   = SellerInventory::whereIn('status', (session('user')->platform == 'admin') ? ['A', 'I'] : ['A'])->where(['users_email' => $user->parent_email, 'inv_arti_details_id' => $inventory->id])->first();
              $validatePack = false;
              if (isset($parentItem)) { // el articulo lo tiene el coordinador del vendedor al que se le asignara
                $validatePack = true;
              } else {  // el articulo no lo tiene el coordinador del vendedor al que se le asignara, se verifica la bodega a la que pertenece
                $warehouse = Warehouse::whereIn('status', (session('user')->platform == 'admin') ? ['A', 'I'] : ['A'])->where(['id' => $inventory->warehouses_id]);
                if (isset($warehouse)) {
                  $validatePack = true;
                } else {
                  if ($fileFlag) {
                    $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' no está entre los productos disponibles';
                  } else {
                    $errors[] = 'El MSISDN ' . $item . ' no está entre los productos disponibles';
                  }
                }
              }
              $seteable = false;
              if ($validatePack) {
                $articlePack = ArticlePack::where(['inv_article_id' => $inventory->inv_article_id, 'status' => 'A'])->count();
                if ($articlePack > 0) {
                  $sellerInventory = SellerInventory::whereIn('status', ['P'])->where(['inv_arti_details_id' => $inventory->id])->count();
                  if ($sellerInventory == 0) {
                    $seteable = true;
                  } else {
                    if ($fileFlag) {
                      $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' ya fue vendido';
                    } else {
                      $errors[] = 'El MSISDN ' . $item . ' ya fue vendido';
                    }
                  }
                } else {
                  if ($fileFlag) {
                    $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' no está asociado a un paquete';
                  } else {
                    $errors[] = 'El MSISDN ' . $item . ' no está asociado a un paquete';
                  }
                }
              }

              //Verificando si el dn esta en estatus rojo
              $assigOld = SellerInventory::getActiveAssignedByIdArtic($inventory->id);
              if(!empty($assigOld) && !empty($assigOld->date_red)){
                $errors[] = 'El MSISDN ' . $item . ' se encuentra en estatus rojo, debe completar la validación de estatus para que se pueda asignar a un usuario';
                $seteable = false;
              }

              if ($seteable) {

                //$is_available_art = false;
                //$is_available_art2 = false;

                //verifico si esta asignado a otro usuario que no sea el supervisor
                /*$iaa = SellerInventory::getConnect('R')
                  ->where(['inv_arti_details_id' => $inventory->id])
                  ->whereIn('status', ['A'])
                  ->where('users_email','<>',$user->parent_email)
                  ->count();

                if ($iaa == 0) {
                  $is_available_art = true;
                } else {
                  if ($fileFlag) {
                    $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' ya fue asignado a otro usuario o no lo tienen el supervisor del usuario al que se le quiere asignar ';
                  } else {
                    $errors[] = 'El MSISDN ' . $item . ' ya fue asignado a otro usuario o no lo tienen el supervisor del usuario al que se le quiere asignar ';
                  }
                }*/

                //verifico si esta pre-asignado a un vendedor

                // $iaa2 = SellerInventoryTemp::getConnect('R')
                //   ->where(['inv_arti_details_id' => $inventory->id])
                //   ->whereIn('status', ['P'])
                //   ->count();

                // if ($iaa2 == 0) {
                //   $is_available_art2 = true;
                // } else {
                //   if ($fileFlag) {
                //     $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' ya fue pre-asignado a un vendedor ';
                //   } else {
                //     $errors[] = 'El MSISDN ' . $item . ' ya fue pre-asignado a un vendedor ';
                //   }
                // }


                // if (/*$is_available_art &&*/$is_available_art2) {

                //si existen pre-asignaciones para ese articulo se pasan a T

                  SellerInventoryTemp::getConnect('W')
                      ->where([
                        ['inv_arti_details_id', $inventory->id],
                        ['status', 'P']
                      ])
                      ->update(['status' => 'T']);

                  $is_seller = false;

                  $is_seller=ProfileDetail::getConnect('R')
                    ->join('islim_profiles','islim_profiles.id','islim_profile_details.id_profile')
                    ->where([
                      'islim_profile_details.user_email' => $user->email,
                      'islim_profile_details.status' => 'A',
                      'islim_profiles.platform' => 'vendor'
                    ])
                    ->count();

                  if ($is_seller > 0) { //es vendedor, se preasigna el inventario

                    //verifico si ya fue asignado a otro vendedor
                    $is_assigned =  SellerInventory::getConnect('R')
                      ->where([
                        ['inv_arti_details_id', $inventory->id],
                        ['status', 'A'],
                        ['users_email', '!=', $user->email]
                      ])
                      ->first();

                    //si fue asignado a otro vendedor
                    if($is_assigned){
                      //quito la asignacion y devuelvo el inventario a bodega
                      SellerInventory::getConnect('W')
                      ->where([
                        ['inv_arti_details_id', $inventory->id],
                        ['status', 'A'],
                        ['users_email', '!=', $user->email]
                      ])
                      ->update(['status' => 'T']);

                      //regisro el movimiento

                      $inventorie = Inventory::getConnect('R')
                          ->find($inventory->id);

                      SellerInventoryTrack::setInventoryTrack(
                          $inventory->id,
                          $is_assigned->users_email,
                          null,
                          null,
                          $inventorie->warehouses_id,
                          session('user')->email
                        );
                    }

                    //verifico si ya fue asignado al mismo vendedor
                    $is_assigned =  SellerInventory::getConnect('R')
                      ->where([
                        ['inv_arti_details_id', $inventory->id],
                        ['status', 'A'],
                        ['users_email', '=', $user->email]
                      ])
                      ->first();

                    //si no fue asignado hago la preasignacion
                    if(!$is_assigned){

                      $preassigment = SellerInventoryTemp::getConnect('W');
                      $preassigment->user_email = $user->email;
                      $preassigment->inv_arti_details_id  = $inventory->id;
                      $preassigment->status = 'P';
                      $preassigment->assigned_by = session('user')->email;
                      $preassigment->date_reg = date('Y-m-d H:i:s', time());
                      $preassigment->date_status = date('Y-m-d H:i:s', time());
                      $preassigment->notification_view = 'N';
                      $preassigment->save();

                      if ($fileFlag) {
                        $ids[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' fue pre-asignado a ' . ($user->name . ' ' . $user->last_name . ' de forma exitosa');
                      } else {
                        $ids[] = 'El MSISDN ' . $item . ' fue pre-asignado a ' . ($user->name . ' ' . $user->last_name) . ' de forma exitosa';
                      }
                    }
                  } else {

                    /*lo tiene el supervisor?*/
                    /*$isparent = SellerInventory::getConnect('R')
                    ->where(['inv_arti_details_id' => $inventory->id])
                    ->whereIn('status', ['A'])
                    ->where('users_email','=',$user->parent_email)
                    ->count();*/

                    $assigment = SellerInventory::where([
                      'users_email'         => $user->email,
                      'inv_arti_details_id' => $inventory->id
                    ])->first();

                    $invs = SellerInventory::getConnect('R')
                      ->where([
                        ['inv_arti_details_id', $inventory->id],
                        ['status', 'A'],
                        ['users_email', '!=', $user->email]
                      ])
                      ->first();

                    SellerInventory::getConnect('W')
                      ->where([
                        ['inv_arti_details_id', $inventory->id],
                        ['status', 'A'],
                        ['users_email', '!=', $user->email]
                      ])
                      ->update(['status' => 'T']);

                    /*if($isparent == 0){ // no lo tiene el supervisor, muevo el inventario a odega
                      foreach ($invs as $key => $inv) {
                        $inventorie = Inventory::getConnect('R')
                          ->find($inv->inv_arti_details_id);

                        SellerInventoryTrack::setInventoryTrack(
                          $inv->inv_arti_details_id,
                          $inv->users_email,
                          null,
                          null,
                          $inventorie->warehouses_id,
                          session('user')->email
                        );
                      }
                    }*/



                    //Verificando si el dn que se esta asignando se encuentra en estatus no asignado en la tabla de prova
                    $isNotA = StockProvaDetail::getDetailByDN($item);
                    if (!empty($isNotA) && $isNotA->status != 'T' && $isNotA->status != 'E' && $isNotA->status != 'AS') {
                      $isNotA->status = 'T';
                      $isNotA->last_user_action = session('user')->email;
                      $isNotA->comment = (!empty($isNotA->comment) ? $isNotA->comment : '').'Eliminado desde otro flujo por asignación de inventario';
                      $isNotA->save();
                    }

                    $trackFlag = false;
                    if (isset($assigment)) {
                      if ($assigment->status != 'A') {
                        SellerInventory::getConnect('W')->where(['users_email' => $user->email, 'inv_arti_details_id' => $inventory->id])
                          ->update([
                            'status'           => 'A',
                            'date_red'         => null,
                            'date_orange'      => null,
                            'date_reg'         => date('Y-m-d H:i:s', time()),
                            'last_assigned_by' => session('user')->email,
                            'last_assignment'  => date('Y-m-d H:i:s', time())
                          ]);
                        $trackFlag = true;
                      }
                    } else {
                      $assigment                      = SellerInventory::getConnect('W');
                      $assigment->date_reg            = date('Y-m-d H:i:s', time());
                      $assigment->inv_arti_details_id = $inventory->id;
                      $assigment->users_email         = $user->email;
                      $assigment->status              = 'A';
                      $assigment->obs                 = null;
                      $assigment->last_assigned_by    = session('user')->email;
                      $assigment->last_assignment     = date('Y-m-d H:i:s', time());
                      $assigment->save();

                      $trackFlag = true;
                    }

                    if ($trackFlag == true) {

                      $band = true;
                      if(!empty($invs)){
                        if($invs->users_email == $user->email){
                          $band = false;
                        }
                      }

                      if($band){

                        SellerInventoryTrack::setInventoryTrack(
                          $inventory->id,
                          empty($invs) ? null : $invs->users_email,
                          empty($invs) ? $inventory->warehouses_id : null,
                          $user->email,
                          null,
                          session('user')->email
                        );

                      }
                    }

                    if ($fileFlag) {
                      $ids[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' fue asignado a ' . ($user->name . ' ' . $user->last_name . ' de forma exitosa');
                    } else {
                      $ids[] = 'El MSISDN ' . $item . ' fue asignado a ' . ($user->name . ' ' . $user->last_name) . ' de forma exitosa';
                    }
                  }
                // }
              }
            } else {
              if ($fileFlag) {
                $temporalObject = Inventory::whereIn('status', ['V'])->where(['msisdn' => $item])->first();
                if (isset($temporalObject)) {
                  $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' ya fue vendido.';
                } else {
                  $temporalObject = Inventory::whereNotIn('status', ['A'])->where(['msisdn' => $item])->first();
                  if (isset($temporalObject)) {
                    $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' no esta disponible para la venta.';
                  } else {
                    $errors[] = 'linea ' . $line . ' - El MSISDN ' . $item . ' no existe entre los productos registrados.';
                  }
                }
              } else {
                $temporalObject = Inventory::whereIn('status', ['V'])->where(['msisdn' => $item])->first();
                if (isset($temporalObject)) {
                  $errors[] = 'El MSISDN ' . $item . ' ya fue vendido.';
                } else {
                  $temporalObject = Inventory::whereNotIn('status', ['A'])->where(['msisdn' => $item])->first();
                  if (isset($temporalObject)) {
                    $errors[] = 'El MSISDN ' . $item . ' no esta disponible para la venta.';
                  } else {
                    $errors[] = 'El MSISDN ' . $item . ' no existe entre los productos registrados.';
                  }
                }
              }
            }
            $line++;
          }
        } else {
          $errorMsg = $user->name . ' ' . $user->last_name . ' no puede recibir más productos.';
          if ($user->platform == 'vendor') {
            if (!($totalAssigned == 0)) {
              $errorMsg = $errorMsg . ' El vendedor tiene un inventario ya asignado o pre-asignado.';
            } else {
              if (($totalAllowedHbb >= $countHbb) || ($totalAllowedMbb >= $countMbb) || ($totalAllowedMifi >= $countMifi) || ($totalAllowedFibra >= $countFibra)) {
                $errorMsg = $errorMsg . ' Son más artículos los enviados que los que puede recibir.';
              } else {
                if (!($numberDebt == 0)) {
                  $errorMsg = $errorMsg . ' El vendedor tiene deuda en efectivo.';
                }
              }
            }
          } else {
            if (($totalAllowedHbb >= $countHbb) || ($totalAllowedMbb >= $countMbb) || ($totalAllowedMifi >= $countMifi) || ($totalAllowedFibra >= $countFibra)) {
              $errorMsg = $errorMsg . ' Son más artículos los enviados que los que puede recibir.';
            } else {
              if (!($numberDebt == 0)) {
                $errorMsg = $errorMsg . ' El vendedor tiene deuda en efectivo.';
              }
            }
          }
          $errors[] = $errorMsg;
        }
      } else {
        $errors[] = 'No se enviaron MSISDN, revisa el archivo CSV o selecciona un(os) artículo(s) de la lista';
      }
    } else {
      $errors[] = 'El vendedor no puede recibir productos';
    }
    if (isset($ids) && (count($ids) > 0)) {
      if (isset($errors) && (count($errors) > 0)) {
        $response = array('success' => false, 'msg' => 'Algunos MSISDN no pudieron ser asignados o pre-asignados', 'numError' => 3, 'itemError' => $errors, 'itemSuccess' => $ids);
      } else {
        $response = array('success' => true, 'msg' => 'Los MSISDN fueron asignados o pre-asignados de forma exitosa', 'numError' => 0, 'itemError' => $errors, 'itemSuccess' => $ids);
      }
    } else {
      if (isset($errors) && (count($errors) > 0)) {
        $response = array('success' => false, 'msg' => 'Los MSISDN no pudieron ser asignados o pre-asignados', 'numError' => 4, 'itemError' => $errors, 'itemSuccess' => $ids);
      }
    }

    if(empty($response))
      $response = array('success' => true, 'msg' => 'No se registraron cambios', 'numError' => 0, 'itemError' => $errors, 'itemSuccess' => $ids);

    return response()->json($response);
  }

  /*
   * Vista para pedido sugerido
   */
  public function suggestedOrder(Request $request)
  {
    //Consultando usuarios coordinadores
    $usersL = User::getCoordUsers();

    //agrupando productos versionados
    $ex1 = explode(',', env('INV_MW41', ''));
    $awt = $ex1;

    $ex2 = explode(',', env('INV_M4', ''));
    if (!empty($ex2[0])) {
      $awt = array_merge($awt, $ex2);
    }

    $ex3 = explode(',', env('INV_MW45', ''));
    if (!empty($ex3[0])) {
      $awt = array_merge($awt, $ex3);
    }

    $ex4 = explode(',', env('INV_S10', ''));
    if (!empty($ex4[0])) {
      $awt = array_merge($awt, $ex4);
    }

    $ex5 = explode(',', env('INV_SH30', ''));
    if (!empty($ex5[0])) {
      $awt = array_merge($awt, $ex5);
    }

    $ex6 = explode(',', env('INV_MW45MF', ''));
    if (!empty($ex6[0])) {
      $awt = array_merge($awt, $ex6);
    }

    $ex7 = explode(',', env('INV_HH42', ''));
    if (!empty($ex7[0])) {
      $awt = array_merge($awt, $ex7);
    }

    $ex8 = explode(',', env('INV_A11', ''));
    if (!empty($ex8[0])) {
      $awt = array_merge($awt, $ex8);
    }

    //Consultando productos
    $articles = Product::getProductsWT($awt);

    //Calculando fechas tomadas en cuenta para las ventas (últimas 4 semanas tomadas de lunes a domingo)
    $lastDay  = Carbon::createFromTimeStamp(strtotime("last sunday", time()))->endOfDay();
    $firstDay = $lastDay->copy()->subDays(27)->startOfDay();

    $users = new Collection;

    foreach ($usersL as $user) {
      $sellers = User::getParentUsers($user->email);
      foreach ($articles as $article) {
        $temp            = new Collection;
        $temp->name      = $user->name;
        $temp->last_name = $user->last_name;
        $temp->email     = $user->email;
        $temp->phone     = $user->phone;
        $temp->esquema   = $user->esquema;
        $temp->article   = $article->title;
        $temp->product   = $article->id;

        //Caluclando ventas de las últimas 4 semanas
        $arts = [];
        if ($article->id == 9) {
          $arts = array_merge($arts, $ex1);
        }
        if ($article->id == 31) {
          $arts = array_merge($arts, $ex2);
        }
        if ($article->id == 35) {
          $arts = array_merge($arts, $ex3);
        }
        if ($article->id == 53) {
          $arts = array_merge($arts, $ex4);
        }
        if ($article->id == 54) {
          $arts = array_merge($arts, $ex5);
        }
        if ($article->id == 67) {
          $arts = array_merge($arts, $ex6);
        }
        if ($article->id == 79) {
          $arts = array_merge($arts, $ex7);
        }
        if ($article->id == 81) {
          $arts = array_merge($arts, $ex8);
        }

        $arts[]           = $article->id;
        $temp->totalSales = Sale::getSalesCountByDate(
          $user->email,
          $sellers->pluck('email')->toArray(),
          $arts,
          $firstDay->format('Y-m-d H:i:s'),
          $lastDay->format('Y-m-d H:i:s')
        );

        $temp->promSales = ceil($temp->totalSales / 28);

        //Consultando stock disponible
        $temp->availableStock = SellerInventory::getInvAvailable($user->email, $sellers->pluck('email')->toArray(), $arts);

        $temp->sug = (env('STOCK_DAYS', 15) * $temp->promSales) - ($temp->availableStock - (env('GAP_DAYS', 5) * $temp->promSales));
        $temp->sug = $temp->sug < 0 ? 0 : $temp->sug;

        $users->push($temp);
      }
    }

    $dateB = $firstDay->format('d-m-Y');
    $dateE = $lastDay->format('d-m-Y');

    $html = view('pages.ajax.seller.suggested_order', compact('users', 'dateB', 'dateE'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function suggestedOrderSave(Request $request)
  {
    $sugs = $request->data;

    if (count($sugs)) {
      $newSug           = new SuggestedOrder;
      $newSug->user_reg = session('user.email');
      $newSug->date_reg = date('Y-m-d H:i:s');
      $newSug->status   = 'A';
      $newSug->save();

      $dataxls[] = [
        'Nombre',
        'Email',
        'Teléfono',
        'Coordinación',
        'Producto',
        'Ventas Totales',
        'Ventas diarias (promedio)',
        'Stock disponible',
        'Días de desfase',
        'Pedido sugerido'
      ];

      foreach ($sugs as $sug) {
        $newSugD                     = new SuggestedOrderDetail;
        $newSugD->suggested_order_id = $newSug->id;
        $newSugD->user               = $sug['user'];
        $newSugD->articles_id        = $sug['product'];
        $newSugD->total_sales        = $sug['totalsales'];
        $newSugD->avg_sales          = $sug['promsales'];
        $newSugD->stock              = $sug['avstock'];
        $newSugD->gap                = $sug['gap'];
        $newSugD->suggested          = $sug['suggested'];
        $newSugD->status             = 'A';
        $newSugD->save();

        $dataxls[] = [
          $sug['name'],
          $sug['user'],
          $sug['phone'],
          $sug['coord'],
          $sug['productname'],
          $sug['totalsales'],
          $sug['promsales'],
          $sug['avstock'],
          $sug['gap'],
          $sug['suggested']
        ];
      }

      $url = CommonHelpers::saveFile('/public/reports', 'suggested_order', $dataxls, 'suggested_order_' . date('dmYHis'), 100000, 'csv');

      return response()->json(['success' => true, 'url' => $url]);
    }

    return response()->json(['success' => false]);
  }

  public function leave_request_view()
  {
    $reason_dimisal = LowReason::all();
    $html = view('pages.ajax.leave_request', compact('reason_dimisal'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function getUserRequestLeave()
  {
    $users = User::getConnect('R')->select(
        'email',
        DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) AS fullname'),
        'platform',
        'islim_request_dismissal.status AS status_req_dimissal'
      )
      ->leftJoin('islim_request_dismissal', 'islim_users.email', '=', 'islim_request_dismissal.user_dismissal')
      ->where('parent_email', '=', session('user')->email)
      ->groupBy('islim_users.email')
      ->get();
    
    return DataTables::of($users)
      ->addColumn('action', function($row){
        $startDiv = '<div class="btn-group" role="group">';

        if ( $row->status_req_dimissal == 'R' || $row->status_req_dimissal == 'P' ) {
          $btn = $startDiv.'<button onclick="showModal(`'. $row->email .'`, 1);" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Solicitar Baja" disabled> Solicitar Baja </button></div>';
        }
        else {
          $btn = $startDiv.'<button onclick="showModal(`'. $row->email .'`, 1);" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Solicitar Baja"> Solicitar Baja </button></div>';
        }
        return $btn;
      })
      ->make(true);
  }

  public function getUserDetail(Request $request)
  {
    $check_user_req = LowRequest::getConnect('R')->where('user_dismissal', '=', $request->email)->where('status', '=', 'R')->get();

    if ( $check_user_req->count() ) {
      
      $check_user_req = 1;
    }
    else {
      $check_user_req =  0;
    }

    switch ($request->typeSearch) {
      case 1:
        //logica para obtener las ventas de las ultimas 2 semanas
        $date = Carbon::now()->subDays(15);

        $sales = SaleMetrics::getConnect('R')->select(
          'islim_inv_articles.title', 'islim_inv_articles.artic_type', 'islim_inv_arti_details.msisdn',
          'islim_sales.amount', 'islim_sales.date_reg'
        )
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_sales.inv_arti_details_id')
          ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
          ->where('islim_sales.users_email', '=', $request->email)
          ->where('islim_sales.type', '=', 'V')
          ->where('islim_sales.date_reg', '>=', $date->format('Y-m-d'))
          ->get();

        if ( $sales->count() > 0 ) {
          $tr = '<tr>';
          foreach ($sales as $sale) {

            if ( $sale->artic_type == 'H' ) {
              $artic_type = "Internet Hogar";
            }

            if ( $sale->artic_type == 'T' ) {
              $artic_type = "Telefonia";
            }

            if ( $sale->artic_type == 'M' ) {
              $artic_type = "Mifi";
            }

            if ( $sale->artic_type == 'F' ) {
              $artic_type = "Fibra";
            }

            $td = $tr.'<td>'. $sale->title .'</td><td>'. $artic_type .'</td><td>'. $sale->msisdn .'</td><td>'. $sale->amount .'</td><td>'. $sale->date_reg .'</td>';
            $tr = $td.'</tr>';
          }
        }
        else {
          $tr = "<tr><th colspan='5' style='text-align:center;'>No posee ventas realizadas</th></tr>";
        }

        return response()->json(['data' => $tr], 200);
      break;

      case 2:
        //query get the user request dimissal
        $users = User::getAllDebt([
          'user_email' => $request->email,
          'status' => 'A'
        ]);

        //Query for field cash_request  creo que se debera quitar de aqui
        $cash_request = User::getTotalDebt([
          'user_email' => $request->email,
          'status' => 'A'
        ]);

        if ( $cash_request->count() ) {
          $cash_request = $cash_request[0]->debt;
        } else {
          $cash_request = 0;
        }

        $day_cash_request = 0;
        $cash_abonos = 0;
        $cant_abonos = 0;
        $article_request = 0;
        $cash_hbb = 0;
        $cash_telf = 0;
        $cash_mifi = 0;
        $cash_fibra = 0;

        foreach ($users as $user){
          //query field day_cash_request
          $day_cash_request = AssignedSales::getDays_deb_old($user);

          if ( $day_cash_request ) {
            $day_cash_request = $day_cash_request->days_old_deb;
          }
          else {
            $day_cash_request = 0;
          }

          //query field cash_abonos
          $cash_abonos = PayInstallment::getDebUser($user->email);

          //query cant_abonos
          $bankUser = UserDeposit::BankUser($user->email);
          if(!empty($bankUser)){
            $details = PayInstallment::getGroupDetailDeb($user->email);
            if($details->count()) {
              foreach ($details as $detail) {
                $detail->salesDetail = PayInstallment::getDetailReport($detail->id_report);

                if ( count($detail->salesDetail) > 0 ) {
                  foreach ($detail->salesDetail as $salesDetail) {
                    if ( $salesDetail->quote > 0 ) {
                      $cant_abonos += 1;
                    }
                  }
                }
              }
            }
          }

          //query article request
          $articles = SellerInventory::getConnect('R')
            ->select('islim_inv_articles.id', 'islim_inv_arti_details.id','islim_inv_arti_details.inv_article_id',
              'islim_inv_arti_details.price_pay', 'islim_inv_articles.artic_type'
            )
            ->join('islim_inv_arti_details', 'islim_inv_assignments.inv_arti_details_id', '=', 'islim_inv_arti_details.id')
            ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
            ->where('users_email', '=', $user->email)->where('islim_inv_assignments.status', '=', 'A')->get();

          foreach ($articles as $article) {
            $article_request += $article['price_pay'];

            if ( $article['artic_type'] == 'H' ) {
              $cash_hbb += $article['price_pay'];
            }

            if ( $article['artic_type'] == 'T' ) {
              $cash_telf += $article['price_pay'];
            }

            if ( $article['artic_type'] == 'M' ) {
              $cash_mifi += $article['price_pay'];
            }

            if ( $article['artic_type'] == 'F' ) {
              $cash_fibra += $article['price_pay'];;
            }
          }
        }


        //Obtener el listado de equipos asignados a el usuario a solicitar la baja
        $articles = SellerInventory::getConnect('R')->select(
          'islim_inv_articles.title', 'islim_inv_arti_details.msisdn', 'islim_inv_articles.artic_type',
          'islim_inv_assignments.date_reg'
        )
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', '=', 'islim_inv_assignments.inv_arti_details_id')
          ->join('islim_inv_articles', 'islim_inv_articles.id', '=', 'islim_inv_arti_details.inv_article_id')
          ->where('islim_inv_assignments.users_email', '=', $request->email)
          ->where('islim_inv_assignments.status', '=', 'A')
          ->get();

        if ( $articles->count() > 0 ) {
          $tr_articles = '<tr>';
          foreach ($articles as $article) {

            if ( $article->artic_type == 'H' ) {
              $artic_type = "Internet Hogar";
            }

            if ( $article->artic_type == 'T' ) {
              $artic_type = "Telefonia";
            }

            if ( $article->artic_type == 'M' ) {
              $artic_type = "Mifi";
            }

            if ( $article->artic_type == 'F' ) {
              $artic_type = "Fibra";
            }

            $td = $tr_articles.'<td>'. $article->title .'</td><td>'. $article->msisdn .'</td><td>'. $artic_type .'</td><td>'. $article->date_reg .'</td>';
            $tr_articles = $td.'</tr>';
          }
        } else {
          $tr_articles = "<tr><th colspan='4' style='text-align:center;'>No posee inventario asignado</th></tr>";
        }

        return response()->json([
          'cash_request' => $cash_request,
          'days_cash_request' => $day_cash_request,
          'cash_abonos' => $cash_abonos,
          'cant_abonos' => $cant_abonos,
          'article_request' => $article_request,
          'cash_hbb' => $cash_hbb,
          'cash_telf' => $cash_telf,
          'cash_mifi' => $cash_mifi,
          'cash_fibra' => $cash_fibra,
          'cash_total' => $article_request + $cash_request,
          'tr_articles' => $tr_articles,
          'check_user_req' => $check_user_req
        ], 200);

      break;
    }
  }

  public function requestLeave(Request $request)
  {
    $req_leave = LowRequest::getConnect('R')->where('user_dismissal', '=', $request->email)->where('status', '=', 'R')->get();

    if ( $req_leave->count() ) {
      
      return response()->json(['message' => 'Este usuario ya posee una solicitud activa','success' => false]); 
    }
    else{

      if ( $request->type == 'request' ) {
        $req_leave = LowRequest::getConnect('W');

        $req_leave->user_req = session('user')->email;
        $req_leave->user_dismissal = $request->email;
        $req_leave->id_reason = $request->reason;
        $req_leave->status = 'R';
        $req_leave->date_reg = Carbon::now()->format('Y-m-d H:m:s');
        $req_leave->cash_request = $request->cash_request + $request->cash_abonos;
        $req_leave->days_cash_request = $request->day_cash_request;
        $req_leave->cash_abonos = $request->cash_abonos;
        $req_leave->cant_abonos = $request->cant_abonos;
        $req_leave->article_request = $request->article_request;
        $req_leave->cash_hbb = $request->cash_hbb;
        $req_leave->cash_telf = $request->cash_telf;
        $req_leave->cash_mifi = $request->cash_mifi;
        $req_leave->cash_fibra = $request->cash_fibra;
        $req_leave->cash_total = $request->article_request + $request->cash_request;
        $req_leave->save();

        return response()->json(['success' => true, 'req_leave_id' => $req_leave->id]);
      }
      else {

        $req_leave_id = $request->req_leave_id;
        $path    = 'low/evidence-photo';

        for ( $i=1; $i<=3; $i++ ) {
          $file = $request->file('file'.$i);

          if ( $file ) {

            $ext = strtoupper($file->getClientOriginalExtension());

            if ( $ext != 'PNG' || $ext != 'JPG' || $ext != 'PDF' || $ext != 'GIF' ) {
              $filePath = $path . uniqid() . time() . '.' . $file->getClientOriginalExtension();

              //Subiendo el excel a s3
              Storage::disk('s3')->put($filePath, file_get_contents($file->getPathname()), 'public');

              $urlFile = (String) Storage::disk('s3')->url($filePath);
          
              $evidenceObj = LowEvidences::getConnect('W'); 
              $evidenceObj->url = $urlFile;
              $evidenceObj->id_req_dismissal = $req_leave_id;
              $evidenceObj->date_reg = Carbon::now();
              $evidenceObj->save();
            }
            else {
              return response()->json(['Formato de archivo no permitido', 'success' => false]);

            }
          }
        }

        return response()->json(['success' => true], 200);
      }
    }
  }

  public function listRequestLeaveView()
  {
    $html = view('pages.ajax.list_leave_request_process')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function listRequestLeave(Request $request)
  {

    if ( !empty($request->status) ) {
      $status = $request->status;
    }
    else {
      $status = ['R', 'P', 'D'];
    }

    $list_req_leave = LowRequest::getConnect('R')
      ->select(
        'islim_request_dismissal.date_reg as date_reg_req',
        'islim_request_dismissal.user_dismissal',
        DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) AS fullname'),
        'islim_reason_dismissal.reason',
        'islim_request_dismissal.cash_request',
        'islim_request_dismissal.article_request',
        'islim_request_dismissal.cash_total',
        'islim_request_dismissal.status AS status_req'
      )
      ->join('islim_users', 'islim_users.email', '=', 'islim_request_dismissal.user_dismissal')
      ->join('islim_reason_dismissal', 'islim_reason_dismissal.id', '=', 'islim_request_dismissal.id_reason')
      ->where('islim_request_dismissal.user_req', '=', session('user')->email)
      ->whereIn('islim_request_dismissal.status', $status)->get();
    
    return DataTables::of($list_req_leave)
      ->editColumn('status_req', function($row){

        if( $row->status_req == 'R' ) {
          return  "Solicitada";
        }

        if( $row->status_req == 'P' ) {
          return  "En Proceso";
        }

        if( $row->status_req == 'D' ) {
          return  "Rechazado";
        }

      })
      ->addColumn('action', function($row){
        $startDiv = '<div class="btn-group" role="group">';

        if ( $row->status_req_dimissal == 'R' || $row->status_req_dimissal == 'R' ) {
          $btn = $startDiv.'<a onclick="showModal(`'. $row->email .'`, 1);" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Solicitar Baja" disabled>Solicitar Baja</a></div>';
        }
        else {
          $btn = $startDiv.'<a onclick="showModal(`'. $row->email .'`, 1);" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Solicitar Baja">Solicitar Baja</a></div>';
        }
        return $btn;
      })
      ->make(true);
  }
}
