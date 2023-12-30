<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Bank;
use App\Deposit;
use App\Policy;
use App\UserRole;
use App\ProfileDetail;
use DataTables;
use HelpersS3;


class SellerBalanceController extends Controller {

    public function view (){
        $banks = Bank::all();
        $html = view('pages.ajax.seller.balance', compact('banks'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public  function datatable (Request $request) {
        $users = User::select('email', 'name', 'last_name', 'parent_email', 'platform', 'phone', 'charger_com', 'charger_balance')->where('islim_users.status', 'A')->orderBy('platform');
        if (session('user')->platform == 'admin') {

            $grts = ProfileDetail::whereIn('id_profile',['16','17'])->get();
            $users = $users->whereIn('platform', ['coordinador', 'vendor'])
                            ->orWhereIn('email',$grts->pluck('user_email'));
        } else {
            if (session('user')->platform == 'coordinador') {
                $users = $users->where(['platform' => 'vendor', 'parent_email' => session('user')->email]);
            }
        }
        $policy = Policy::getConnect('R')->select('id', 'roles_id')->where('status','A')->where(['code' => 'RSC-DSE'])->first();
        $users = $users->join('islim_user_roles', function ($join) use ($policy) {
            $join->on('islim_user_roles.user_email', '=', 'islim_users.email')
                 ->where('islim_user_roles.policies_id', '=', $policy->id)
                 ->where('islim_user_roles.roles_id', '=', $policy->roles_id)
                 ->where('islim_user_roles.value', '>', 0);
        });

         // $query = vsprintf(str_replace('?', '%s', $users->toSql()), collect($users->getBindings())->map(function ($binding) {
         //        return is_numeric($binding) ? $binding : "'{$binding}'";
         //    })->toArray());

         //    Log::info($query);

        $users = $users->get();
        return DataTables::of($users)
            ->addColumn('action', function(User $user){
                return true;
            })
            ->editColumn('email', function(User $user){
                if(!empty($user->email)){
                    return $user->email;
                }else{
                    return 'N/A';
                }
            })
            ->editColumn('phone', function(User $user){
                if(!empty($user->phone)){
                    return $user->phone;
                }else{
                    return 'N/A';
                }
            })
            ->addColumn('full_name', function(User $user){
                $name = 'N/A';
                if(!empty($user->name) && !empty($user->last_name)){
                    $name = $user->name.' '.$user->last_name;
                }else{
                    if(!empty($user->name)){
                        $name = $user->name;
                    }else{
                        if(!empty($user->last_name)){
                            $name = $user->last_name;
                        }
                    }
                }
                return $name;
            })
            ->addColumn('parent', function(User $user){
                $parent_name = 'N/A';
                if ($user->platform == 'vendor') {
                    $parent = User::select('name', 'last_name')->where('email', $user->parent_email)->first();
                    if (isset($parent) && !empty($parent)) {
                        $parent_name = $parent->name.' '.$parent->last_name;
                    }
                }
                return $parent_name;
            })
            ->addColumn('balance_txt', function(User $user){
                if(!empty($user->charger_balance)){
                    return number_format($user->charger_balance,2,'.',',');
                }else{
                    return 0;
                }
            })
        ->toJson();
    }

    public  function assignBalance (Request $request, $user) {
        try {
            if(empty($request->second_pass) || !Hash::check($request->second_pass, session('user.second_password'))){
                return response()->json(array('success' => false, 'msg'=>'Segunda contraseña no válida. No se asigno el saldo', 'numError'=>0));
            }

            $name = '';
            if ($request->hasFile('image')){
                $image = $request->file('image');
                $file = \File::get($image);
                $name = $user.' '.date ('Y-m-d H:i:s', time()).'.'.$image->extension();
                $success = HelpersS3::insertImage($name, 'DepositSeller', $file);
                if(!$success){
                    return response()->json(array('success' => false, 'msg'=>'Error al guardar la imagen', 'numError'=>0));
                }
            }

            $user = User::find($user);
            $deposit = new Deposit();
            $deposit->date_deposit = $request->date_deposit;
            $deposit->description = $request->nro_deposit;
            $deposit->photo = $name;
            $deposit->bank_id = $request->bank;
            $deposit->amount = round($request->amount, 2);
            $deposit->real_amout = round(($request->amount / (1 - $user->charger_com)), 2);
            $deposit->date_asigned = date ('Y-m-d H:i:s', time());
            $deposit->date_reg = date ('Y-m-d H:i:s', time());
            $deposit->status = 'A';
            $deposit->user_process = session('user.email');
            $deposit->save();

            $user->charger_balance = round($user->charger_balance + $deposit->real_amout, 2);
            $user->save();

            return response()->json(array('success' => true, 'msg'=>'Se guardaron los datos correctamente', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => true, 'msg'=>'Hubo un error actualizando, intente más tarde', 'numError'=>1, 'msgError'=>$e->getMessage()));
        }
    }
}