<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Sale;
use App\Comission;
use App\ComissionDetail;

class SellerComissionController extends Controller {
    
    public  function view () {
        $users;
        if (session('user')->platform == 'admin') {
            $users = User::select('email', 'name', 'last_name')->where(['platform' => 'coordinador'])->get();
        } else {
            if (session('user')->platform == 'coordinador') {
                $users = User::select('email', 'name', 'last_name')->where(['platform' => 'vendor', 'parent_email' => session('user')->email])->get();
            }
        }
        $html = view('pages.ajax.seller.comission', compact('users'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public  function viewSalesTable (Request $request, $email) {
        $status = ['E'];
        $type = ['P'];
        $object = Sale::getSale($email, $status, $type, 'N');
        $amount = $object['amount'];
        $sales = $object['sales'];
        $html = view('pages.ajax.seller.comission.sale', compact('amount','sales'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public  function consolidate (Request $request, $ids, $user) {
        try {
            $ids = json_decode($ids);
            $responsable = session('user')->email;
            $comission_prc = User::select('charger_com')->where(['email' => $user])->first()->charger_com;
            $total_amount = Sale::select('amount')->whereIn('id', $ids)->sum('amount');
            $total_comissions = ($total_amount / (1 - $comission_prc));

            $comissions = new Comission();
            $comissions->user_email = $responsable;
            $comissions->user_vendor = $user;
            $comissions->amount_sales = $total_amount;
            $comissions->amount_com = $total_comissions;
            $comissions->status = 'A';
            $comissions->date_reg = date ('Y-m-d H:i:s', time());
            $comissions->save();

            foreach ($ids as $id) {
                Sale::where(['id' => $id])->update(['conciliation' => 'Y']);
                $detail = new ComissionDetail();
                $detail->com_id = $comissions->id;
                $detail->sale_id = $id;
                $detail->save();
            }

            return response()->json(array('success' => true, 'msg'=>'Los registros han sido procesados', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => true, 'msg'=>'Hubo un error actualizando, intente más tarde', 'numError'=>1, 'msgError'=>$e->getMessage()));
        }
    }
}
