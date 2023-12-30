<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\APIKey;
use App\Concentrator;
use App\User;
use App\Bank;
use App\Channel;
use App\Deposit;
use DataTables;
use HelpersS3;

class ConcentratorController extends Controller
{
    public function index() {
        $concentrators =Concentrator::all();
        foreach ($concentrators as $concentrator) {
            $apikeys = APIKey::where('concentrators_id', $concentrator->id)->all();
            $concentrator->apikeys = $apikeys;
        }
        return response()->json($concentrators);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {

        if(User::hasPermission (session('user.email'), 'AMC-CCO')){

            if(empty($request->second_pass) || !Hash::check($request->second_pass, session('user.second_password'))){
                return 'Segunda contraseña no válida. No se actualizó el consentrador';
            }

            $concentrator = Concentrator::getConnect('W')->create($request->input());
            $concentrator->date_reg = date ('Y-m-d H:i:s', time());
            $concentrator->save();

            $apikey = APIKey::getConnect('W');
            $apikey->api_key = hash('SHA256',md5(openssl_random_pseudo_bytes(128)));
            $apikey->concentrators_id = $concentrator->id;
            $apikey->type = 'prod';
            $apikey->date_reg = date ('Y-m-d H:i:s', time());
            $apikey->status = $concentrator->status;
            $apikey->save();

            $apikeydev = APIKey::getConnect('W');
            $apikeydev->api_key = hash('SHA256',md5(openssl_random_pseudo_bytes(128)));
            $apikeydev->concentrators_id = $concentrator->id;
            $apikeydev->type = 'test';
            $apikeydev->date_reg = date ('Y-m-d H:i:s', time());
            $apikeydev->status = $concentrator->status;
            $apikeydev->save();

            $concentrator->apikeys = [0 => $apikey, 1 => $apikeydev];

            return 'El consentrador se ha creado con exito';
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $concentrator = Concentrator::find($id);
        $apikeys = APIKey::where('concentrators_id', $id)->all();
        $concentrator->apikeys = $apikeys;
        return response()->json($concentrator);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        if(User::hasPermission (session('user.email'), 'AMC-UCO')){
            $secondPass = User::getUserByEmail(session('user.email'));
            
            if(empty($request->second_pass) || empty($secondPass->second_password) || !Hash::check($request->second_pass, $secondPass->second_password)){
                return 'Segunda contraseña no válida. No se actualizó el consentrador';
            }

            $concentrator = Concentrator::getConnect('W')->find($id);
            $concentrator->name = $request->name;
            $concentrator->rfc = $request->rfc;
            $concentrator->email = $request->email;
            $concentrator->dni = $request->dni;
            $concentrator->business_name = $request->business_name;
            $concentrator->phone = $request->phone;
            $concentrator->balance = $request->balance;
            $concentrator->address = $request->address;
            $concentrator->commissions = $request->commissions;
            $concentrator->status = $request->status;

            if(!empty($request->id_channel)){
                $concentrator->id_channel = $request->id_channel;
            }else{
                $concentrator->id_channel = null;
            }

            if($request->postpaid == 'N'){
                $concentrator->postpaid = 'N';
                $concentrator->amount_alert = null;
                $concentrator->amount_allocate = null;
            }else{
                $concentrator->postpaid = 'Y';
                $concentrator->amount_alert = $request->amount_alert;
                $concentrator->amount_allocate = $request->amount_allocate;
            }
            $concentrator->save();
            return 'El consentrador se ha actualizado con exito';
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        if(User::hasPermission (session('user.email'), 'AMC-DCO')){
            APIKey::getConnect('W')->where('concentrators_id', $id)->update(['status'=>'T']);
            $concentrator = Concentrator::getConnect('W')->find($id)->update(['status'=>'T']);
            return response()->json($concentrator);
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
    }

    public  function view(){
        $concentrators = Concentrator::getConnect('R')->where('status','A')->get();
        $channels = Channel::getConnect('R')->where('status', 'A')->get();

        foreach ($concentrators as $concentrator) {
            $apikeys = APIKey::getConnect('R')->where('concentrators_id', $concentrator->id)->get();
            $concentrator->apikeys = $apikeys;
            if(!empty($concentrator->id_channel)){
                $concentrator->channel = Channel::getConnect('R')->where([
                                                    ['id', $concentrator->id_channel],
                                                    ['status', 'A']
                                                ])
                                                ->first();
            }
        }
        $html = view('pages.ajax.concentrator', compact('concentrators', 'channels'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function datatable(){
        $concentrators = null;
        if(session('user.platform')=='admin'){
            $concentrators = Concentrator::getConnect('R')->whereIn('status',['A','I'])->get();
        } else {
            $concentrators = Concentrator::getConnect('R')->whereIn('status',['A'])->get();
        }
        foreach ($concentrators as $concentrator) {
            $apikeys = APIKey::getConnect('R')->where('concentrators_id', $concentrator->id)->get();
            $concentrator->apikeys = $apikeys;
        }
        return DataTables::of($concentrators)
            ->editColumn('name', function(Concentrator $concentrators){
                if(!empty($concentrators->name)){
                    return $concentrators->name;
                }else{
                    return 'N/A';
                }
            })
            ->editColumn('email', function(Concentrator $concentrators){
                if(!empty($concentrators->email)){
                    return $concentrators->email;
                }else{
                    return 'N/A';
                }
            })
            ->editColumn('phone', function(Concentrator $concentrators){
                if(!empty($concentrators->phone)){
                    return $concentrators->phone;
                }else{
                    return 'N/A';
                }
            })
            ->addColumn('balance_txt', function(Concentrator $concentrators){
                if(!empty($concentrators->balance)){
                    return number_format($concentrators->balance,2,'.',',');
                }else{
                    return 0;
                }
            })
            ->addColumn('action', function(Concentrator $concentrators){
                return true;
            })
        ->toJson();
    }

    public  function balanceView(){
        $concentrators = Concentrator::getConnect('R')->where('status','A')->get();
        $banks = Bank::getConnect('R')->all();
        foreach ($concentrators as $concentrator) {
            $apikeys = APIKey::getConnect('R')->where('concentrators_id', $concentrator->id)->get();
            $concentrator->apikeys = $apikeys;
        }
        $html = view('pages.ajax.concentrators.balance', compact('concentrators', 'banks'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function balanceAssign(Request $request, $id){
        try {
            if(empty($request->second_pass) || !Hash::check($request->second_pass, session('user.second_password'))){
                return response()->json(array('success' => false, 'msg'=>'Segunda contraseña no válida. No se asigno el saldo', 'numError'=>0, 'invalidPass' => true));
            }

            $name = '';
            if ($request->hasFile('image')){
                $image = $request->file('image');
                $file = \File::get($image);
                $name = $id.' '.date ('Y-m-d H:i:s', time()).'.'.$image->extension();
                $success = HelpersS3::insertImage($name, 'DepositConcentrator', $file);
                if(!$success){
                    return response()->json(array('success' => false, 'msg'=>'Error al guardar la imagen', 'numError'=>0));
                }
            }

            $concentrador = Concentrator::getConnect('W')->find($id);
            $deposit = Deposit::getConnect('W');
            $deposit->concentrators_id = $id;
            $deposit->date_deposit = $request->date_deposit;
            $deposit->description = $request->nro_deposit;
            $deposit->photo = $name;
            $deposit->bank_id = $request->bank;
            $deposit->amount = round($request->amount, 2);
            $deposit->real_amout = round(($request->amount / (1 - $concentrador->commissions)), 2);
            $deposit->date_asigned = date ('Y-m-d H:i:s', time());
            $deposit->date_reg = date ('Y-m-d H:i:s', time());
            $deposit->status = 'A';
            $deposit->user_process = session('user.email');
            $deposit->save();

            $concentrador->balance = round(($concentrador->balance + $deposit->real_amout), 2);;
            $concentrador->save();

            return response()->json(array('success' => true, 'msg'=>'Se guardaron los datos correctamente', 'numError'=>0));
        } catch (Exception $e) {
            return response()->json(array('success' => true, 'msg'=>'Hubo un error actualizando, intente más tarde', 'numError'=>1, 'msgError'=>$e->getMessage()));
        }
    }
}
