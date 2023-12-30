<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Concentrator;
use App\APIKey;
use App\ServerIP;
use App\User;

class ServersController extends Controller
{
    public function index() {
        $servers =ServerIP::getConnect('R')->all();
        foreach ($servers as $server) {
            $apikey = APIKey::getConnect('R')->find($server->api_key);
            $server->concentrator = $apikey->concentrators_id;
            $server->type = $apikey->type;
        }
        return response()->json($servers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        if(User::hasPermission (session('user.email'), 'ACS-CCS')){
            $apikey = APIKey::getConnect('R')->where('concentrators_id', $request->input('concentrator'))->where('type', $request->input('type'))->get();
            foreach ($apikey as $ak) {
                $server = ServerIP::getConnect('W');
                $server->api_key = $ak->api_key;
                $server->ip = $request->input('ip');
                $server->status = $request->input('status');
                $server->date_reg = date ('Y-m-d H:i:s', time());
                $server->save();

            }
            return 'El servidor se agrego exitosamente';
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
        $server = ServerIP::getConnect('R')->find($id);
        $apikey = APIKey::getConnect('R')->find($server->api_key);

        $server->concentrator = $apikey->concentrators_id;
        $server->type = $apikey->type;
        return response()->json($server);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        if(User::hasPermission (session('user.email'), 'ACS-UCS')){
            $server = ServerIP::getConnect('W')->find($id);
            $apikeys = APIKey::getConnect('R')->where('concentrators_id', $request->input('concentrator'))->where('type', $request->input('type'))->get();
            foreach ($apikeys as $apikey) {
                $server->api_key = $apikey->api_key;
            }
            $server->ip = $request->ip;
            $server->status = $request->status;
            $server->save();
            return 'El servidor se actualizo exitosamente';
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
        if(User::hasPermission (session('user.email'), 'ACS-DCS')){
            $server = ServerIP::getConnect('W')->find($id)->update(['status'=>'T']);
            return response()->json($server);
        }else{
            return 'Usted no posee permisos para realizar esta operación';
        }
    }

    public  function view($concentratorid){
        $concentrators = Concentrator::getConnect('R')->where(['id'=>$concentratorid])->get();
        foreach ($concentrators as $concentrator) {
            $apikeys = APIKey::getConnect('R')->where('concentrators_id', $concentrator->id)->orderBy('type')->get();
            foreach ($apikeys as $apikey) {
                $server = ServerIP::getConnect('R')->where(['api_key'=> $apikey->api_key,'status'=>'A'])->get();
                foreach ($server as $s) {
                    $s->concentrator = $concentrator->id;
                    $s->type = $apikey->type;
                }
                $apikey->servers = $server;
            }
            $concentrator->apikeys = $apikeys;
        }
        $html = view('pages.ajax.servers', compact('concentrators'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }
}