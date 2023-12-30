<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\BillingMasive;
use App\BillingMasiveFile;
use App\BillingMasiveFileDetail;
use Excel;
use DataTables;
use Log;
use Carbon\Carbon;

class BillingMasiveController extends Controller {


    private function createTempRegister($file_id, $row, $billable, $action){

        $bmfiledetail = BillingMasiveFileDetail::getConnect('W');
        $bmfiledetail->file_id = $file_id;
        $bmfiledetail->place = $row->place;
        $bmfiledetail->date_expired = $row->date_expired;
        $bmfiledetail->term = $row->term;
        $bmfiledetail->oxxo_folio_date = $row->oxxo_folio_date;
        $bmfiledetail->oxxo_folio_id = $row->oxxo_folio_id;
        $bmfiledetail->oxxo_folio_nro = $row->oxxo_folio_nro;
        $bmfiledetail->date_pay = $row->date_pay;
        $bmfiledetail->doc_pay = $row->doc_pay;
        $bmfiledetail->status_pay = $row->status_pay;
        $bmfiledetail->sub_total = $row->sub_total;
        $bmfiledetail->tax = $row->tax;
        $bmfiledetail->total = $row->total;
        $bmfiledetail->pay_type = $row->pay_type;
        $bmfiledetail->billable = $billable;
        $bmfiledetail->mk_serie = $row->mk_serie;
        $bmfiledetail->mk_folio = $row->mk_folio;
        $bmfiledetail->action = $action;
        $bmfiledetail->save();
        usleep(15000);

    }

    public function process_file(Request $request){
        $rengs = BillingMasiveFileDetail::getConnect('R')
                        ->where('file_id',$request->id)
                        ->where('action','<>','I')
                        ->get();

        foreach ($rengs as $key => $reng) {

            if($reng->action == 'C'){
                $bmreng = BillingMasive::getConnect('W');
                $bmreng->date_reg = date('Y-m-d H:i:s');
                if($reng->billable == 'Y' && $reng->mk_serie == "" && $reng->mk_folio == ""){
                    $bmreng->status_gen = 'W';
                }
                else{
                    $bmreng->status_gen = 'N';
                }

                $bmreng->mk_serie = $reng->mk_serie;
                $bmreng->mk_folio = $reng->mk_folio;

            }
            if($reng->action == 'U'){
                $bmreng = BillingMasive::getLastRegister('W',$reng->oxxo_folio_id,$reng->oxxo_folio_nro,$reng->doc_pay,$reng->total);

                if(empty($bmreng)){
                    $bmreng = BillingMasive::getLastRegister('W',$reng->oxxo_folio_id,$reng->oxxo_folio_nro,null,$reng->total);
                }

                if($bmreng->mk_serie == "" && $bmreng->mk_folio == ""){
                    $bmreng->mk_serie = $reng->mk_serie;
                    $bmreng->mk_folio = $reng->mk_folio;
                }
            }
            if(!empty($bmreng) && $reng->action == 'C' || $reng->action == 'U'){
                $bmreng->file_id = $request->id;
                $bmreng->place = $reng->place;
                $bmreng->date_expired = $reng->date_expired;
                $bmreng->term = $reng->term;
                $bmreng->oxxo_folio_date = $reng->oxxo_folio_date;
                $bmreng->oxxo_folio_id = $reng->oxxo_folio_id;
                $bmreng->oxxo_folio_nro = $reng->oxxo_folio_nro;
                $bmreng->date_pay = $reng->date_pay;
                $bmreng->doc_pay = $reng->doc_pay;
                $bmreng->status_pay = $reng->status_pay;
                $bmreng->sub_total = $reng->sub_total;
                $bmreng->tax = $reng->tax;
                $bmreng->total = $reng->total;
                $bmreng->pay_type = $reng->pay_type;
                $bmreng->billable = $reng->billable;
                $bmreng->save();
                usleep(15000);
            }
            else{
                Log::info('Error: no se pudo crear ni actualizar un registro de facturacion masiva');
            }
        }

        $file = BillingMasiveFile::getConnect('W')
                        ->where('id',$request->id)
                        ->first();
        $file->status = 'P';
        $file->save();

        return ['success' => true, 'msg' => 'ok'];
    }

    public function import_store_csv(Request $request)
    {
        ini_set('max_execution_time', '300');

        if ($request->hasFile('csv') && $request->file('csv')->isValid()) {
            $data = Excel::load($request->file('csv')->getRealPath(), function ($reader) {})->get();

            $line     = 1;
            $errores  = "Por favor verifica que los datos sean cargados correctamente. Errores en las lineas: ";
            $errLines  = [];
            $isError  = false;

            foreach ($data as $row) {
                $line++;

                if(
                    empty($row->place)
                    || empty($row->date_expired)
                    || empty($row->term)
                    || empty($row->oxxo_folio_date)
                    || empty($row->oxxo_folio_id)
                    || empty($row->oxxo_folio_nro)
                    || empty($row->pay_type)
                ){

                    // if(empty($row->place)) $col = "place";
                    // if(empty($row->date_expired)) $col = "date_expired";
                    // if(empty($row->term)) $col = "term";
                    // if(empty($row->oxxo_folio_date)) $col = "oxxo_folio_date";
                    // if(empty($row->oxxo_folio_id)) $col = "oxxo_folio_id";
                    // if(empty($row->oxxo_folio_nro)) $col = "oxxo_folio_nro";
                    // if(empty($row->pay_type)) $col = "pay_type";
                    // $isError = true; array_push($errLines, $line.'('.$col.')');

                    $isError = true; array_push($errLines, $line);
                }
                else{
                    if(
                        strlen(trim((String) $row->place)) == 0
                        || strlen(trim((String) $row->oxxo_folio_id)) == 0
                        || !is_numeric(trim((String) $row->oxxo_folio_id))
                        || strlen(trim((String) $row->oxxo_folio_nro)) == 0
                        || !is_numeric(trim((String) $row->sub_total))
                        || !is_numeric(trim((String) $row->tax))
                        || !is_numeric(trim((String) $row->total))

                    ){
                        $isError = true; array_push($errLines, $line);
                    }

                    switch($row->term){
                        case 'Contado': $row->term = 'C'; break;
                        case '30 dias': $row->term = '30'; break;
                        default:
                            $isError = true; array_push($errLines, $line);
                        break;
                    }

                    switch($row->status_pay){
                        case 'Pago Completo': $row->status_pay = 'Y'; break;
                        case 'No Pagado': $row->status_pay = 'N'; break;
                        default:
                            $isError = true; array_push($errLines, $line);
                        break;
                    }

                    switch($row->pay_type){
                        case 'PUE':
                            $row->pay_type = 'PUE';
                        break;
                        case 'PPD':
                            $row->pay_type = 'PPD';
                        break;
                        default:
                            $isError = true; array_push($errLines, $line);
                        break;
                    }

                    try{
                        $row->date_expired = Carbon::createFromFormat('d/m/Y', $row->date_expired);
                        $row->oxxo_folio_date = Carbon::createFromFormat('d/m/Y', $row->oxxo_folio_date);
                        if(!empty($row->date_pay)){
                            $row->date_pay = Carbon::createFromFormat('d/m/Y', $row->date_pay);
                        }

                    } catch (\Exception $e) {
                        $isError = true; array_push($errLines, $line);
                    }

                    if(empty($row->doc_pay)){
                        $row->doc_pay = null;
                    }
                    else{
                        if($row->doc_pay == ""){
                            $row->doc_pay = null;
                        }
                    }
                }
            }
            if ($isError) {
                $errores.= implode(", ", $errLines);
                $cadena = ($isError) ? $errores . PHP_EOL : '';
                return ['success' => false, 'msg' => $cadena];
            }



            $bmfile = BillingMasiveFile::getConnect('W');
            $bmfile->status = 'C';
            $bmfile->save();


            foreach ($data as $key => $row) {

                //verifico si el registro cumple con las condiciones iniciales para facturar
                $billable = 'N';
                if(empty($row->mk_serie) && empty($row->mk_folio) && $row->status_pay=='N' && substr($row->oxxo_folio_nro, 0, 2) == 'FP'){
                    $billable = 'Y';
                }

                //reviso si el renglon ya existe en BD en file details
                $reng_temp = BillingMasiveFileDetail::getLastRegister('W',$bmfile->id,$row->oxxo_folio_id,$row->oxxo_folio_nro,$row->doc_pay);

                if($reng_temp){ // si existe valido si el que viene en el archivo es un reverso (signo contrario en montos) o si trae alguna actualizacion (Siempre se tomera el mas actual)


                    $upd = 0;
                    //reviso si el renglon es igual pero con monto contrario
                    if($reng_temp->total == (-1*$row->total)){
                        $reng_temp->action = 'I'; //si es asi se marca en I
                        $reng_temp->billable = 'N'; //ahora no cumple con condiciones para ser facturado
                        $reng_temp->save();
                        usleep(15000);

                        //vuelvo a buscar el renglon pero ahora con el mismo signo en monto
                        $reng_temp2 = BillingMasiveFileDetail::getLastRegister('W',$bmfile->id,$row->oxxo_folio_id,$row->oxxo_folio_nro,$row->doc_pay,$row->total);
                        if($reng_temp2){ // el renglon ya existe y lo marco en I
                            $reng_temp2->action = 'I';
                            $reng_temp2->billable = 'N'; //ahora no cumple con condiciones para ser facturado
                            $reng_temp2->save();
                            usleep(15000);
                            $reng_temp = $reng_temp2;
                            $upd = 1; //como existe activo bandera para verificar si debe actualizarse
                        }
                        else{ // como no existe lo creo
                            self::createTempRegister($bmfile->id,$row, 'N', 'I');
                        }
                    }
                    else{ //como existe activo bandera para verificar si debe actualizarse
                        $upd = 1;
                    }

                    if($upd == 1){ // reviso si debe actualizarse en BD con la info del ultimo renglon del CSV
                        $upd = 0;
                        if($reng_temp->place != $row->place){
                            $reng_temp->place = $row->place;
                            $upd = 1;
                        }
                        if($reng_temp->date_expired != $row->date_expired){
                            $reng_temp->date_expired = $row->date_expired;
                            $upd = 1;
                        }
                        if($reng_temp->term != $row->term){
                            $reng_temp->term = $row->term;
                            $upd = 1;
                        }
                        if($reng_temp->oxxo_folio_date != $row->oxxo_folio_date){
                            $reng_temp->oxxo_folio_date = $row->oxxo_folio_date;
                            $upd = 1;
                        }
                        if($reng_temp->date_pay != $row->date_pay){
                            $reng_temp->date_pay = $row->date_pay;
                            $upd = 1;
                        }
                        if($reng_temp->status_pay != $row->status_pay){
                            $reng_temp->status_pay = $row->status_pay;
                            $upd = 1;
                        }
                        if($reng_temp->pay_type != $row->pay_type){
                            $reng_temp->pay_type = $row->pay_type;
                            $upd = 1;
                        }
                        if($reng_temp->mk_serie != $row->mk_serie && $reng_temp->mk_serie==""){
                            $reng_temp->mk_serie = $row->mk_serie;
                            $upd = 1;
                        }
                        if($reng_temp->mk_folio != $row->mk_folio && $reng_temp->mk_folio==""){
                            $reng_temp->mk_folio = $row->mk_folio;
                            $upd = 1;
                        }

                        if($upd == 1){
                            $reng_temp->save();
                            usleep(15000);
                        }
                    }
                }
                else{
                    self::createTempRegister($bmfile->id, $row, $billable, 'C');
                }
            }


            //reviso si el quedo alguno con monto negativo y corresponiente de signo contrario para marcarlo en I

            $reng_rev = BillingMasiveFileDetail::getConnect('W')
                        ->where('file_id',$bmfile->id)
                        ->where('total','<=','0')
                        ->get();

            foreach ($reng_rev as $key => $rev) {

                BillingMasiveFileDetail::getConnect('W')
                    ->where([
                        ['file_id', $rev->file_id],
                        ['oxxo_folio_id', $rev->oxxo_folio_id],
                        ['oxxo_folio_nro', $rev->oxxo_folio_nro],
                        ['doc_pay', $rev->doc_pay],
                        ['action', '!=', 'I']
                    ])
                    ->where(function ($qry) use ($rev) {
                        $qry->where('total', $rev->total)
                            ->orWhere('total', (($rev->total)*(-1)));
                    })
                    ->update([
                        'action' => 'I',
                    ]);
            }

            usleep(15000);

            $reng_temp = BillingMasiveFileDetail::getConnect('W')->where('file_id',$bmfile->id)->where('action','C')->get();

            //reviso si el renglon ya existe en BD para actualizarlo o ignorarlo
            foreach ($reng_temp as $reng) {

                $reng_bm = BillingMasive::getLastRegister('R',$reng->oxxo_folio_id,$reng->oxxo_folio_nro);

                if($reng_bm){ //el renglon existe
                    //reviso si debe actualizarse

                    $upd = 0;

                    if($reng_bm->date_pay != $reng->date_pay){
                        $reng_bm->date_pay = $reng->date_pay;
                        $upd = 1;
                    }
                    if($reng_bm->status_pay != $reng->status_pay){
                        $reng_bm->status_pay = $reng->status_pay;
                        $upd = 1;

                    }
                    if($reng_bm->doc_pay != $reng->doc_pay){
                        $reng_bm->doc_pay = $reng->doc_pay;
                        $upd = 1;
                    }
                    if($reng_bm->mk_serie != $reng->mk_serie && $reng_bm->mk_serie ==""){
                        $reng_bm->mk_serie = $reng->mk_serie;
                        $upd = 1;
                    }
                    if($reng_bm->mk_folio != $reng->mk_folio && $reng_bm->mk_folio ==""){
                        $reng_bm->mk_folio = $reng->mk_folio;
                        $upd = 1;
                    }
                    if($upd == 1){
                        $reng->action = 'U';
                        $reng->save();
                        usleep(15000);
                    }
                    else{
                        $reng->action = 'I';
                        $reng->save();
                        usleep(15000);
                    }
                }
            }
            return ['success' => true, 'msg' => 'ok', 'id_file' => $bmfile->id];
        }
        return ['success' => false, 'msg' => 'Formato de archivo no valido'];
    }


    public function view () {

        $html = view(
            'pages.ajax.masive_billings'
        )->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function listFileDetailsDT(Request $request){


        $lists = BillingMasiveFileDetail::where('file_id',$request->id)->where('action','<>','I');

        return DataTables::of($lists)
                            ->editColumn('date_expired', function($list){
                                     return !empty($list->date_expired) ? date("d-m-Y", strtotime($list->date_expired)) : "";
                            })
                            ->editColumn('term', function($list){
                                if($list->term == 'C') return 'Contado';
                                if($list->term == '30') return '30 dias';
                                return $list->term;
                            })
                            ->editColumn('oxxo_folio_date', function($list){
                                     return !empty($list->oxxo_folio_date) ? date("d-m-Y", strtotime($list->oxxo_folio_date)) : "";
                            })
                            ->editColumn('date_pay', function($list){
                                     return !empty($list->date_pay) ? date("d-m-Y", strtotime($list->date_pay)) : "";
                            })
                            ->editColumn('status_pay', function($list){
                                if($list->status_pay == 'Y') return 'Pago Completo';
                                return 'No Pagado';
                            })
                            ->make(true);
    }

    public function report_masive_billing_view () {

        $places = BillingMasive::getConnect('R')->select('place')->groupBy('place')->get();

        $html = view(
            'pages.ajax.report_masive_billing_view',
            ['places' => $places]
        )->render();

        return response()->json(array('success' => true, 'msg'=>$html, 'places'=>$places, 'numError'=>0));
    }

    public function billing_masive_detail_report(Request $request)
    {
        $filters = $request->all();

        $billings = BillingMasive::getReportBillingsMasive($filters);

        return DataTables::of($billings)
            ->editColumn('date_expired', function($billing){
                $date = date_create($billing->date_expired);
                return date_format($date, 'd/m/Y');
            })
            ->editColumn('term', function($billing){
                
                if ($billing->term == '30') {
                    return "30 dias";
                }
                else if ($billing->term == 'C') {
                    return "Contado";
                }

            })
            ->editColumn('oxxo_folio_date', function($billing){
                $date = date_create($billing->oxxo_folio_date);
                return date_format($date, 'd/m/Y');
            })
            ->editColumn('status_pay', function($billing){
                if ($billing->status_pay == 'N') {
                    return "No Pagado";
                }

                if ($billing->status_pay == 'Y') {
                    return "Pago Completo";
                }
            })
            ->editColumn('date_pay', function($billing){
                $date = date_create($billing->date_pay);
                return date_format($date, 'd/m/Y');
            })
            ->make(true);
    }
}