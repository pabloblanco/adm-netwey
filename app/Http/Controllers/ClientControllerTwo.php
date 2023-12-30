<?php

namespace App\Http\Controllers;

use App\ClientBuyBack;
use App\ClientNetwey;
use App\FileBuyBack;
use App\User;
use Carbon\Carbon;
use DataTables;
use Excel;
use Illuminate\Http\Request;

use App\Helpers\CommonHelpers;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Log;

class ClientControllerTwo extends Controller
{
  public function viewBuyBack()
  {
    $html = view('pages.ajax.client.buyback')->render();

    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function processFile(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax() &&
      $request->hasFile('csv') && $request->file('csv')->isValid() &&
      (strtolower($request->csv->extension()) == 'csv' || strtolower($request->csv->extension()) == 'txt')
    ) {
      $data = Excel::load(
        $request->file('csv')->getRealPath(),
        function ($reader) {}
      )->get();

      if (count($data->getheading()) == 4 &&
        in_array('msisdn', $data->getheading()) &&
        in_array('contesto', $data->getheading()) &&
        in_array('acepto', $data->getheading()) &&
        in_array('comentario', $data->getheading())
      ) {
        $ok      = 0;
        $error   = 0;
        $dnError = [];

        $name     = $request->file('csv')->getClientOriginalName();
        $fr       = new FileBuyBack;
        $fr->file = $name;
        $fr->user = session('user')->email;
        $fr->save();

        foreach ($data as $row) {
          if (!empty($row->msisdn)) {
            $exist = ClientNetwey::getConnect('R')
              ->select('msisdn')
              ->where('msisdn', $row->msisdn)
              ->first();

            if (!empty($exist)) {
              ClientBuyBack::resetLastStatus($row->msisdn);

              $line         = new ClientBuyBack;
              $line->msisdn = $row->msisdn;

              $line->answer = 'N';
              if (!empty($row->contesto) && strtolower($row->contesto) == 'x') {
                $line->answer = 'Y';
              }

              $line->acept = 'N';
              if (!empty($row->acepto) && strtolower($row->acepto) == 'x') {
                $line->acept = 'Y';
              }

              if (!empty($row->comentario)) {
                $line->comment = trim($row->comentario);
              }

              $line->is_last  = 'Y';
              $line->file     = $fr->id;
              $line->date_reg = date('Y-m-d H:i:s');
              $line->save();

              $ok++;
            } else {
              $error++;
              $dnError[] = $row->msisdn;
            }
          }
        }

        $fr->clients_ok    = $ok;
        $fr->clients_error = $error;
        $fr->date_reg      = date('Y-m-d H:i:s');
        $fr->save();

        if (count($dnError)) {
          return response()->json([
            'success' => true,
            'msg'     => 'Archivo procesado pero con errores, los siguientes msisdns no estan registrados: ' . implode(',', $dnError),
          ]);
        }

        return response()->json(['success' => true, 'msg' => 'Archivo procesado exitosamente, clientes registrados: ' . $ok]);
      }
    }

    return response()->json(['success' => false, 'msg' => 'archivo no válido. ']);
  }

  public function getTable(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $data = FileBuyBack::getListFromUser(session('user')->email);

      return DataTables::eloquent($data)
        ->editColumn('date_reg', function ($c) {
          return Carbon::createFromFormat('Y-m-d H:i:s', !empty($c->date_reg) ? $c->date_reg : $c->date_reg_rec)
            ->format('Y-m-d');
        })
        ->toJson();
    }
  }

  public function getLastContact(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      if (!empty($request->msisdn)) {
        $data = ClientBuyBack::getLastContact($request->msisdn);

        return response()->json([
          'status' => 'success',
          'data'   => [
            'showLastCall' => !empty($data),
            'acept'        => !empty($data) ? $data->acept : 'N',
            'comment'      => !empty($data) ? $data->comment : 'S/I',
            'date'         => !empty($data) ? Carbon::createFromFormat('Y-m-d H:i:s', $data->date_reg)->format('Y-m-d H:i') : 'S/I',
          ],
        ]);
      }

      return response()->json(['status' => 'error', 'msg' => 'No se puede procesar la solicitud']);
    }
  }

  public function saveContact(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $inputs = $request->all();

      if (!empty($inputs['msisdn'])) {
        $file = FileBuyBack::createRegCallCenter(session('user')->email);

        ClientBuyBack::resetLastStatus($inputs['msisdn']);

        $line         = new ClientBuyBack;
        $line->msisdn = $inputs['msisdn'];

        $line->answer = 'N';
        if (!empty($inputs['answer-buyback'])) {
          $line->answer = 'Y';
        }

        $line->acept = 'N';
        if (!empty($inputs['acept-buyback'])) {
          $line->acept = 'Y';
        }

        if (!empty($inputs['comment-buyback'])) {
          $line->comment = trim($inputs['comment-buyback']);
        }

        $line->is_last  = 'Y';
        $line->file     = $file->id;
        $line->date_reg = date('Y-m-d H:i:s');
        $line->save();

        $file->clients_ok = $file->clients_ok + 1;
        $file->save();

        return response()->json(['status' => 'success', 'msg' => 'Se guardo exitosamente.']);
      }

      return response()->json(['status' => 'error', 'msg' => 'No se puedieron guardar los datos.']);
    }
  }


  //*****metodo para obtener planes que puede recargar un DN  desde mp*********//

  public function getviewtablesPlansMercadoPago(Request $request)
  {

    if ($request->isMethod('post')) {
      $msisdn = $request->get('msisdn');

      $data        = new \stdClass;
      $data->error = false;
      if (!empty($msisdn)) {

        $data->msisdn  = $msisdn;

        $servAuth = CommonHelpers::executeCurl(
          env('URL_RECHARGER') . "/auth",
          'POST',
          [
            'Content-Type: application/json',
            "Content-Length: 0",
            'Authorization: basic ' . base64_encode(env('API_KEY_ALTAM'))]
        );

        if (isset($servAuth['data']->status) && $data->error == false) {
          if ($servAuth['data']->status == "OK") {

            $tokenRecarga = $servAuth['data']->response->token;
            $paramStep1   = [
              'msisdn' => $msisdn,
              'seller' => "INTERNET"];

            $servStep1 = CommonHelpers::executeCurl(
              env('URL_RECHARGER') . "/step1",
              'POST',
              [
                'Content-Type: application/json',
                "cache-control: no-cache",
                'Authorization: Bearer ' . $tokenRecarga],
                $paramStep1
            );

            if (isset($servStep1['data']->status)) {
              if ($servStep1['data']->status == "OK") {
                $transactionId = $servStep1['data']->response->transaction;
                $listServ      = $servStep1['data']->response->services;
                foreach ($listServ as $servItem) {
                  $data->services[] = ['title' => $servItem->title, 'id' => $servItem->id, 'price_pay' => $servItem->price, 'description' => $servItem->description];
                }
                $data->transId = $transactionId;
                DB::table('islim_tmp_sales')
                  ->where([
                    ['unique_transaction', $transactionId],
                    ['status', 'E']])
                  ->delete();
              } else {
                $data->error = $servStep1['data']->msg; //"Problema al obtener los planes, intente nuevamente";
              }
            } else {
              $data->error = "Problema al obtener los planes, intente nuevamente";
            }
          } else {
            $data->error = $servAuth['data']->msg; //"Problemas de Comunicación, Intente nuevamente.";
          }
        } else {
          $data->error = "Problemas de Comunicación, intente mas tarde.";
        }
      } else {
        $data->error = "Datos no válidos.";
      }

      if (!$data->error) {
        return DataTables::of($data->services)
          ->make(true);
      }
      else{
        Log::alert("No se pudo consultar planes de recargas disponibles para el  DN: ".$msisdn." -> ".$data->error);
        return DataTables::of([])
          ->make(true);
      }
    }
  }


}
