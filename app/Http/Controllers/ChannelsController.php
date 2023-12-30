<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ListDns;
use App\ClientNetwey;
use App\Channel;
use App\Concentrator;
use App\ServiceChanel;
use DataTables;

class ChannelsController extends Controller
{
    /*Devuelve vista de listas de servico por dn*/
    public function viewSellerService(){
        $html = view('pages.ajax.service_dn.lists')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    /*Devuelve el json para el datatable de la lista de servicios por dn*/
    public function listsDT(Request $request){

        $lists = ListDns::where('status', 'A')
            ->where(function ($query) {
                $query->where('lifetime', 0)->orWhere('lifetime', null);
            });

        return DataTables::eloquent($lists)
                            ->editColumn('date_reg', function($list){
                                return date("d-m-Y", strtotime($list->date_reg));
                            })
                            ->make(true);
    }

    /*Crea lista de servicio por dn*/
    public function createList(Request $request){
        if(!empty($request->name)){
            $list = new ListDns;
            $list->name = $request->name;
            $list->date_reg = date('Y-m-d H:i:s');
            $list->status = 'A';
            $list->save();

            return response()->json(array('success' => true));
        }
    }

    /*Retorna el html de los dns asociados a una lista*/
    public function getDatalist(Request $request){
        if($request->list){
            $data = ListDns::getServices($request->list);

            if($data){
                $dataList = $data['lista'];
                $datadn = $data['dns'];

                $html = view('pages.ajax.service_dn.table_dns', compact('dataList', 'datadn'))->render();
                return response()->json(array('success' => true, 'html' => $html));
            }
        }

        return response()->json(array('success' => false));
    }

    /*Consulta los Dns dada una pista que se envia por post y los retornae*/
    public function getDNs(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->q) && !empty($request->list)){
                $dns = ClientNetwey::select('msisdn')
                                 ->where('msisdn', 'like', $request->q.'%')
                                 ->where(function($query) use ($request){
                                    $query->where('id_list_dns', '!=', $request->list)
                                          ->orWhereNull('id_list_dns');
                                 })
                                 ->limit(10)
                                 ->get();

                return response()->json(array('success' => true, 'dns' => $dns));
            }
            return response()->json(array('success' => false));
        }
    }

    /*Agrega Dns a una lista, edita datos de la lista*/    
    public function saveEdit(Request $request){
        if(!empty($request->list)){
            if(!empty($request->nameList)){
                ListDns::where('id', $request->list)->update(['name' => $request->nameList]);
            }

            if($request->hasFile('msisdn_file')){
                if($request->file('msisdn_file')->isValid() && strtolower($request->msisdn_file->extension()) == 'txt' || strtolower($request->msisdn_file->extension()) == 'csv'){
                    
                    $file = fopen($request->msisdn_file->path(), "r");

                    if($file !== false){
                        ini_set('auto_detect_line_endings', true);
                        $msisdns = [];
                        while (($datos = fgetcsv($file, ",")) !== false) {
                            foreach($datos as $item) {
                                $msisdns []= $item;
                            }
                        }

                        ini_set('auto_detect_line_endings', false);

                        fclose($file);
                    }else{
                        return response()->json(array('success' => false, 'msg' => 'Ocurrio un error leyendo el archivo.'));
                    }
                }else{
                    return response()->json(array('success' => false, 'msg' => 'Ocurrio un error cargando el archivo.'));
                }
            }else{
                $msisdns = $request->dns;
            }

            if(!empty($msisdns) && count($msisdns)){
                $dnsInList = ClientNetwey::select('msisdn')
                                           ->whereNotNull('id_list_dns')
                                           ->whereIn('msisdn', $msisdns)
                                           ->get();
                $list = array_diff($msisdns, $dnsInList->pluck('msisdn')->toArray());
                if(count($list))
                    ClientNetwey::whereIn('msisdn', $list)->update(['id_list_dns' => $request->list]);
            }
            
            $data = ListDns::getServices($request->list);

            if($data){
                $dataList = $data['lista'];
                $datadn = $data['dns'];

                $html = view('pages.ajax.service_dn.table_dns', compact('dataList', 'datadn'))->render();
                return response()->json([
                                            'success' => true,
                                            'html' => $html,
                                            'noUpdate' => !empty($dnsInList)? $dnsInList->pluck('msisdn')->toArray() : []
                                        ]);
            }
        }
        return response()->json(array('success' => false));
    }

    /*Borra un dn de la lista*/
    public function deleteDn(Request $request){
        if(!empty($request->dn) && !empty($request->list)){
            ClientNetwey::where('msisdn', $request->dn)->update(['id_list_dns' => null]);

            $data = ListDns::getServices($request->list);

            if($data){
                $dataList = $data['lista'];
                $datadn = $data['dns'];

                $html = view('pages.ajax.service_dn.table_dns', compact('dataList', 'datadn'))->render();
                return response()->json(array('success' => true, 'html' => $html));
            }
        }
        return response()->json(array('success' => false));
    }

    /*Borra una lista y a su vez la relacion de servicio con la lista asi como tambien desasocia los dns asignados a la lista que se esta eliminando */
    public function deleteList(Request $request){
        if(!empty($request->list)){
        	ServiceChanel::where('id_list_dns', $request->list)->update(['status' => 'I']);
            ClientNetwey::where('id_list_dns', $request->list)->update(['id_list_dns' => null]);
            ListDns::where('id', $request->list)->update(['status' => 'I']);
        }
        return response()->json(array('success' => true));
    }

    /*Muestra listado de canales*/
    public function viewChannels(){
    	$html = view('pages.ajax.service_dn.channels')->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    /*Devuelve el json para el datatable de la lista de servicios por dn*/
    public function channelsDT(Request $request){
        $channles = Channel::where('status', 'A');

        return DataTables::eloquent($channles)
                            ->editColumn('date_reg', function($ch){
                                return date("d-m-Y", strtotime($ch->date_reg));
                            })
                            ->make(true);
    }

    /*Borra un canal y todo lo asociado a el*/
    public function deleteChannel(Request $request){
    	if(!empty($request->channel)){
    		ServiceChanel::where('id_channel', $request->channel)->update(['status' => 'I']);
            Concentrator::where('id_channel', $request->channel)->update(['id_channel' => null]);
            Channel::where('id', $request->channel)->update(['status' => 'I']);
        }
        return response()->json(array('success' => true));
    }

    /*Crea un canal*/
    public function createChannel(Request $request){
    	if(!empty($request->name)){
            $ch = new Channel;
            $ch->name = $request->name;
            $ch->date_reg = date('Y-m-d H:i:s');
            $ch->status = 'A';
            $ch->save();

            return response()->json(array('success' => true));
        }
    }

    /*Edita un canal*/
    public function editChannel(Request $request){
    	if(!empty($request->channel) && !empty($request->name)){
    		Channel::where('id', $request->channel)->update(['name' => $request->name]);

            return response()->json(array('success' => true));
        }
    }
}
