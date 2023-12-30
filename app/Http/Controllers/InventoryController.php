<?php

namespace App\Http\Controllers;

use App\ArticlePack;
use App\Helpers\CommonHelpers;
use App\Inventory;
use App\Inv_reciclers;
use App\OrgWarehouse;
use App\Product;
use App\ProfileDetail;
use App\Reports;
use App\Sale;
use App\SellerInventory;
use App\SellerInventoryTemp;
use App\SellerInventoryTrack;
use App\StockProva;
use App\StockProvaDetail;
use App\User;
use App\Warehouse;
use App\HistoryInventoryStatus;
use Carbon\Carbon;
use DataTables;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Input;
use Log;
use Illuminate\Support\Facades\Artisan;

class InventoryController extends Controller
{

  /*Funcion para hacer push a array asociativo
   * @param &$arrayDatos parametro por referencia
   * @param $values array a insertar en arrayDatos
   */
  private function asso_array_push(array &$arrayDatos, array $values)
  {
    $arrayDatos = array_merge($arrayDatos, $values);
  }

  private function assigmentDN($inventory, $user_email, $csv = false)
  {

    $user = User::getConnect('R')->find($user_email);
    if (!isset($user)) {
      $msg  = ', pero no se puede asociar al vendedor: ' . $user_email . ' (Vendedor no existe)';
      $assi = false;
    } else {
      switch ($user->status) {
        case 'I':
          $msg  = ', pero no se puede asociar al vendedor: ' . $user->name . ' ' . $user->last_name . ' (Vendedor Inactivo)';
          $assi = false;
          break;
        case 'S':
          $msg  = ', pero no se puede asociar al vendedor: ' . $user->name . ' ' . $user->last_name . ' (Vendedor Suspendido)';
          $assi = false;
          break;
        case 'T':
          $msg  = ', pero no se puede asociar al vendedor: ' . $user->email . ' (Vendedor no existe)';
          $assi = false;
          break;
      }
      if ($user->status == 'A') {

        $countHbb   = 0;
        $countMbb   = 0;
        $countMifi  = 0;
        $countFibra = 0;

        if ($csv) {
          $countqry = SellerInventory::getConnect('R')->join('islim_inv_arti_details', 'islim_inv_arti_details.id', 'islim_inv_assignments.inv_arti_details_id')
            ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
            ->where('islim_inv_assignments.status', 'A')
            ->where('islim_inv_assignments.users_email', $user->email);

          $countHbb   = $countqry->where('islim_inv_articles.artic_type', 'H')->count();
          $countMbb   = $countqry->where('islim_inv_articles.artic_type', 'T')->count();
          $countMifi  = $countqry->where('islim_inv_articles.artic_type', 'M')->count();
          $countFibra = $countqry->where('islim_inv_articles.artic_type', 'F')->count();
        }

        $temp = Inventory::getConnect('R')->select(
          'islim_inv_arti_details.msisdn',
          'islim_inv_articles.artic_type'
        )
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->where('islim_inv_arti_details.id', $inventory->id)
          ->get();

        foreach ($temp as $t) {
          if ($t->artic_type == 'T') {$countMbb = $countMbb + 1;}
          if ($t->artic_type == 'H') {$countHbb = $countHbb + 1;}
          if ($t->artic_type == 'M') {$countMifi = $countMifi + 1;}
          if ($t->artic_type == 'F') {$countFibra = $countFibra + 1;}
        }

        $totalAssigned     = SellerInventory::getTotalInventory($user->email);
        $totalAllowedHbb   = SellerInventory::getTotalPermision($user->email, 'LIV-DSE');
        $totalAllowedMbb   = SellerInventory::getTotalPermision($user->email, 'LIV-DSM');
        $totalAllowedMifi  = SellerInventory::getTotalPermision($user->email, 'LIV-MIF');
        $totalAllowedFibra = SellerInventory::getTotalPermision($user->email, 'LIV-FIB');

        $numberDebt = Sale::getConnect('R')->where(['users_email' => $user->email, 'status' => 'E'])->count();
        $condition  = ($totalAllowedHbb >= $countHbb) && ($totalAllowedMbb >= $countMbb) && ($totalAllowedMifi >= $countMifi) && ($totalAllowedFibra >= $countFibra) && ($numberDebt == 0);

        if (!$csv) {
          if ($user->platform == 'vendor') {
            $condition = $condition && ($totalAssigned == 0);
          }
        }

        if ($condition) {
          $validatePack = false;
          $warehouse    = Warehouse::getConnect('R')->whereIn('status', (session('user')->platform == 'admin') ? ['A', 'I'] : ['A'])->where(['id' => $inventory->warehouses_id]);
          if (isset($warehouse)) {
            $validatePack = true;
          }
          $seteable = false;

          if ($validatePack) {
            $articlePack = ArticlePack::getConnect('R')->where(['inv_article_id' => $inventory->inv_article_id, 'status' => 'A'])->count();
            if ($articlePack > 0) {
              $seteable = true;
            } else {
              $msg  = ', pero no se puede asociar al vendedor: ' . $user->name . ' ' . $user->last_name . ' (El MSISDN no está asociado a un paquete)';
              $assi = false;
            }
          }

          if ($seteable) {

            $is_seller = false;

            $is_seller = ProfileDetail::getConnect('R')
              ->join('islim_profiles', 'islim_profiles.id', 'islim_profile_details.id_profile')
              ->where([
                'islim_profile_details.user_email' => $user->email,
                'islim_profile_details.status'     => 'A',
                'islim_profiles.platform'          => 'vendor',
              ])
              ->count();

            if ($is_seller > 0) {
              //es vendedor, se preasigna el inventario

              $preassigment                      = SellerInventoryTemp::getConnect('W');
              $preassigment->user_email          = $user->email;
              $preassigment->inv_arti_details_id = $inventory->id;
              $preassigment->status              = 'P';
              $preassigment->assigned_by         = session('user')->email;
              $preassigment->date_reg            = date('Y-m-d H:i:s', time());
              $preassigment->date_status         = date('Y-m-d H:i:s', time());
              $preassigment->notification_view   = 'N';
              $preassigment->save();

              $msg  = ' y fue pre-asignado a ' . ($user->name . ' ' . $user->last_name) . ' de forma exitosa';
              $assi = true;

            } else {
              //si no es vendedor se asigna

              $assigment                      = SellerInventory::getConnect('W');
              $assigment->date_reg            = date('Y-m-d H:i:s', time());
              $assigment->inv_arti_details_id = $inventory->id;
              $assigment->users_email         = $user->email;
              $assigment->status              = 'A';
              $assigment->last_assigned_by    = session('user')->email;
              $assigment->last_assignment     = date('Y-m-d H:i:s');
              $assigment->save();

              SellerInventoryTrack::setInventoryTrack(
                $inventory->id,
                null,
                $inventory->warehouses_id,
                $user->email,
                null,
                session('user')->email
              );

              $msg  = ' y fue asignado a ' . $user->name . ' ' . $user->last_name . ' de forma exitosa';
              $assi = true;
            }
          }

        } else {
          $msg  = ', pero no se puede asociar al vendedor: ' . $user->name . ' ' . $user->last_name . ' (El vendedor no puede recibir más productos';
          $assi = false;
          if (!$csv) {
            if ($user->platform == 'vendor') {
              if (!($totalAssigned == 0)) {
                $msg = $msg . ' porque ya tiene un inventario asignado o pre-asignado)';
              } else {
                if (($totalAllowedHbb >= $countHbb) || ($totalAllowedMbb >= $countMbb) || ($totalAllowedMifi >= $countMifi) || ($totalAllowedFibra >= $countFibra)) {
                  $msg = $msg . ', son más artículos los enviados que los que puede recibir)';
                } else {
                  if (!($numberDebt == 0)) {
                    $msg = $msg . ' porque tiene deuda en efectivo)';
                  }

                }
              }
            } else {
              if (($totalAllowedHbb >= $countHbb) || ($totalAllowedMbb >= $countMbb) || ($totalAllowedMifi >= $countMifi) || ($totalAllowedFibra >= $countFibra)) {
                $msg = $msg . ', son más artículos los enviados que los que puede recibir)';
              } else {
                if (!($numberDebt == 0)) {
                  $msg = $msg . ' porque tiene deuda en efectivo)';
                }

              }
            }
          } else {
            if (($totalAllowedHbb >= $countHbb) || ($totalAllowedMbb >= $countMbb) || ($totalAllowedMifi >= $countMifi) || ($totalAllowedFibra >= $countFibra)) {
              $msg = $msg . ', son más artículos los enviados que los que puede recibir)';
            } else {
              if (!($numberDebt == 0)) {
                $msg = $msg . ' porque tiene deuda en efectivo)';
              }

            }
          }
        }
      }
    }
    return array('msg' => $msg, 'assigment' => $assi);
  }



  private function historyInventoryStatusReg($users_email,$inv_arti_details_id,$status,$reason=null){

    $history = HistoryInventoryStatus::getConnect('W')
            ->where([
              'users_email' => $users_email,
              'inv_arti_details_id' => $inv_arti_details_id,
              'status' => 'C'
            ])
            ->orderBy('id','DESC')
            ->first();

    !empty($history) ? $ret = 1 : $ret = 0;

    if(empty($history) && $status == 'P'){
      $history = HistoryInventoryStatus::getConnect('W');
      $history->users_email = $users_email;
      $history->inv_arti_details_id = $inv_arti_details_id;
      $history->date_reg = date('Y-m-d H:i:s');
      $history->color_destino = 'N';
    }
    if(!empty($history)){
      $history->status = $status;
      if($reason)
        $history->motivo_rechazo = $reason;
      $history->userAutorizador = session('user')->email;
      $history->save();
    }

    return $ret;
  }

  public function move_wh_csv(Request $request)
  {
    $success = array();
    $fail    = array();
    if ($request->hasFile('csv')) {
      $data = Excel::load($request->file('csv')->getRealPath(), function ($reader) {})->get();
      foreach ($data as $row) {
        if ((Inventory::where(['msisdn' => $row->msisdn, 'warehouses_id' => $request->whinifile, 'status' => 'A'])->count() != 0)) {
          $success[] = $row->msisdn;
          Inventory::where(['msisdn' => $row->msisdn, 'warehouses_id' => $request->whinifile])->update(['warehouses_id' => $request->whendfile]);
        } else {
          $fail[] = $row->msisdn;
        }
      }
    }
    return ['success' => $success, 'fail' => $fail];
  }

  public function import_store_csv(Request $request)
  {
    $success       = array();
    $fail          = array();
    $assigment     = array();
    $no_assigment  = array();
    $error_product = array();
    $error_wh      = array();
    $invalid       = array();
    $error_dnag    = array();
    $error_imeimacl= array();
    $error_imeimacd= array();

    if ($request->hasFile('csv') && $request->file('csv')->isValid()) {
      $data = Excel::load($request->file('csv')->getRealPath(), function ($reader) {})->get();

      $line     = 0;
      $errores  = "Verifica por favor que no tenga espacios vacio o que el dato sea cargado correctamente. Errores en las lineas: ";
      $errores2 = "MSISDN invalido en linea: ";
      $isError  = false;
      $isError2 = false;

      $artictypes = array();

      foreach ($data as $row) {
        $line++;
        $row->msisdn = trim((String) $row->msisdn);
        $row->iccid  = trim((String) $row->iccid);

        if (empty($row->warehouses_id) || empty($row->inv_article_id)
          || empty($row->price_pay)
          /*|| empty($row->iccid)*/) {
          $isError = true;
          $errores .= $line . ', ';
        } else{
          if (empty($artictypes[$row->inv_article_id])) {
            $prod = Product::where(['id' => $row->inv_article_id])->first();
            if(!empty($prod)){
              $artictypes[$row->inv_article_id] = $prod->artic_type;
            }
            else{
              $isError = true;
              $errores .= $line . ', ';
            }
          }
          if (!empty($artictypes[$row->inv_article_id])) {

            $isfiberautogen = false;
            if($artictypes[$row->inv_article_id] == 'F' && strlen($row->msisdn) == 0){
              $isfiberautogen = true;
            }

            if (strlen($row->msisdn) != 10 && $isfiberautogen == false) {
              $isError2 = true;
              $errores2 .= $line . ', ';
            }
          }
        }
      }

      if ($isError || $isError2) {
        $cadena = ($isError) ? $errores . PHP_EOL : '';
        $cadena .= ($isError2) ? $errores2 : '';
        return ['success' => false, 'msg' => $cadena];
      }

      $user_permit = array();
      foreach ($data as $key => $row) {
        if (!empty($row->user_email)) {
          if (empty($user_permit[$row->user_email])) {
            $user = User::find($row->user_email);
            if ($user) {
              $totalAssigned = SellerInventory::getTotalInventory($user->email);
              if ($user->platform == 'vendor') {
                if ($totalAssigned == 0) {
                  self::asso_array_push($user_permit, array($row->user_email => 'Y'));
                } else {
                  self::asso_array_push($user_permit, array($row->user_email => 'N'));
                }
              } else {
                self::asso_array_push($user_permit, array($row->user_email => 'Y'));
              }
            } else {
              self::asso_array_push($user_permit, array($row->user_email => 'N'));
            }
          }
        }
      }
      $line = 0;
      foreach ($data as $row) {
        $bd = 1;
        $line++;
        $autogen = 'N';
        if($artictypes[$row->inv_article_id] == 'F' && strlen($row->msisdn) == 0){
          $ct = 0;
          $bd = 0;
          do{
            sleep(1);
            $dn = Inventory::getAvailableDnAutogen();
            $invDN  = Inventory::existDN($dn);
            if(!empty($invDN)){
              $ct++;
            }
            else{
              $bd = 1;
            }
          }while($bd==0 && $ct < 10);
          if($bd == 1){
            $row->msisdn = $dn;
            $autogen = 'Y';
          }
        }

        if($bd==1) {

          $row->msisdn         = trim((String) $row->msisdn);
          $row->iccid          = trim((String) $row->iccid);
          $row->inv_article_id = trim($row->inv_article_id);
          $row->warehouses_id  = trim($row->warehouses_id);
          $row->price_pay      = trim($row->price_pay);

          if (!empty($row->serial)) {
            $row->serial = trim($row->serial);
          }

          if (Product::where([['islim_inv_articles.id', $row->inv_article_id], ['islim_inv_articles.status', 'A']])->count() == 0) {
            $error_product[] = $row->msisdn;
          } elseif (Warehouse::where([['islim_warehouses.id', $row->warehouses_id], ['islim_warehouses.status', 'A']])->count() == 0) {
            $error_wh[] = $row->msisdn;
          } elseif (ctype_digit($row->msisdn) && strlen($row->msisdn) != 10) {
            $invalid[] = $row->msisdn;
          } else {
            $inv = Inventory::getConnect('W')
              ->where('islim_inv_arti_details.msisdn', $row->msisdn)
              ->first();

            if ((!empty($inv) && $inv->status == 'T') || empty($inv)) {

              $sw=0;
              if(!empty($inv)){
                $invMACIMEI = Inventory::getConnect('R')
                  ->select('id')
                  ->where([
                    ['status', '!=', 'T'],
                    ['imei', $row->imei]
                  ])
                  ->get();

                if(count($invMACIMEI) > 1){
                  $sw=1;
                }
                else{
                  if(count($invMACIMEI) == 1){
                    $invMACIMEI = $invMACIMEI[0];
                    if($invMACIMEI->id != $inv->id)
                      $sw=1;
                  }
                }
              }
              else{
                $invMACIMEI = Inventory::existMACIMEI($row->imei);
                if(!empty($invMACIMEI)){
                  $sw=1;
                }
              }

              if($sw==0){
                if (empty($inv)) {
                  $inventory = Inventory::getConnect('W');
                } else {
                  $inventory = $inv;
                }

                $inventory->warehouses_id  = $row->warehouses_id;
                $inventory->inv_article_id = $row->inv_article_id;
                $inventory->msisdn         = $row->msisdn;
                $inventory->price_pay      = $row->price_pay;
                $inventory->status         = 'A';
                $inventory->date_reg       = date('Y-m-d H:i:s', time());
                $inventory->imsi           = null;
                $inventory->date_reception = null;
                $inventory->date_sending   = null;
                $inventory->obs            = null;
                $inventory->dn_autogen     = $autogen;

                if (!empty($row->imei)) {
                  $inventory->imei = (String) $row->imei;
                }
                if (!empty($row->serial)) {
                  $inventory->serial = $row->serial;
                }
                if (!empty($row->iccid)) {
                  $inventory->iccid = $row->iccid;
                }

                $inventory->save();

                $success[] = $row->msisdn;

                if (!empty($user_permit[$row->user_email])) {
                  if ($user_permit[$row->user_email] == 'Y') {
                    $resp = self::assigmentDN($inventory, $row->user_email, true);
                    if ($resp['assigment'] == true) {
                      $assigment[] = $row->msisdn;
                    } else {
                      $no_assigment[] = $row->msisdn . " " . $resp['msg'];
                    }
                  } else {
                    $no_assigment[] = $row->msisdn;
                  }
                }
              }
              else{
                if($artictypes[$row->inv_article_id] == 'F')
                  $error_imeimacl[] = $line;
                if($artictypes[$row->inv_article_id] != 'F')
                  $error_imeimacd[] = $row->msisdn;
              }

            } else {
              $fail[]    = $row->msisdn;
              $arrayData = json_decode(json_encode($row), true);
              //Chequeo de Reciclaje de DN en el archivo
              $InventoryReciclaje = Inv_reciclers::Verify_msisdn_recicler($arrayData, 'file');
            }
          }
        }
        else{
          $error_dnag[] = $line;
        }
      }
      return ['success' => true, 'DNsuccess' => $success, 'fail' => $fail, 'error_product' => $error_product, 'error_wh' => $error_wh, 'assigment' => $assigment, 'no_assigment' => $no_assigment, 'invalid' => $invalid,'error_dnag' => $error_dnag,'error_imeimacl'=>$error_imeimacl,'error_imeimacd'=>$error_imeimacd];
    }
    return ['success' => false, 'msg' => 'Formato de archivo no valido'];
  }

  public function index()
  {
    $inventories = Inventory::all();
    foreach ($inventories as $inventory) {
      $parents = Inventory::find($inventory->parent_id);
      if (isset($parents)) {
        foreach ($parents as $parent) {
          $inventory->parent = $parent;
        }
      }
      $subinventories            = Inventory::whereNotIn('id', [$inventory->id])->get();
      $inventory->subinventories = $subinventories;

      $products = Product::where('id', $inventory->inv_article_id)->get();
      foreach ($products as $product) {
        $inventory->product = $product;
      }
      $warehouses = Warehouse::where('id', $inventory->warehouses_id)->get(['id', 'name']);
      foreach ($warehouses as $warehouse) {
        $inventory->warehouse = $warehouse;
      }
    }
    return response()->json($inventories);
  }

  public function show($id)
  {
    $inventory = Inventory::find($id);
    return response()->json($inventory);
  }

  public function store(Request $request)
  {
    $inventoryMSISDN  = Inventory::existDN($request->msisdn);
    $inventoryMACIMEI = Inventory::existMACIMEI($request->imei);
    //where(['msisdn' => $request->msisdn])->whereNotIn('status', ['T'])->first();
    $msg = '';

    $prod = Product::getProduct($request->inv_article_id);

    if (!empty($inventoryMSISDN) && $prod->artic_type != 'F') {
      //inconvenientes de creacion con el DN

      $InventoryReciclaje = Inv_reciclers::Verify_msisdn_recicler($request, 'one');
      $msg                = $InventoryReciclaje['msg'];

    } else {
      if (!empty($inventoryMACIMEI)) {

        $msg = 'No se puede crear el artículo, porque la MAC o IMEI ya se encuentra asignado a otro artículo';
      } else {

        if (!empty($inventoryMSISDN) && $prod->artic_type == 'F') {
          $msisdn=Inventory::getAvailableDnAutogen();
        }
        else{
          $msisdn = $request->msisdn;
        }

        if(!empty($msisdn)){
          $inventory = Inventory::getConnect('W')
            ->select('id')
            ->where([
              ['status', '=', 'T'],
              ['msisdn', $request->msisdn],
            ])
            ->first();

          if ($inventory) {
            $inventory->inv_article_id = $request->inv_article_id;
            $inventory->warehouses_id  = $request->warehouses_id;
            $inventory->serial         = !empty($request->serial) ? $request->serial : null;
            $inventory->msisdn         = $msisdn;
            $inventory->iccid          = $request->iccid;
            $inventory->imsi           = !empty($request->imsi) ? $request->imsi : null;
            $inventory->imei           = !empty($request->imei) ? $request->imei : null;
            $inventory->date_reception = !empty($request->date_reception) ? $request->date_reception : null;
            $inventory->date_sending   = !empty($request->date_sending) ? $request->date_sending : null;
            $inventory->price_pay      = $request->price_pay;
            $inventory->obs            = !empty($request->obs) ? $request->obs : null;
            $inventory->status         = 'A';
            $inventory->save();
          } else {
            $inventory           = Inventory::getConnect('W')->create($request->input());
            $inventory->date_reg = date('Y-m-d H:i:s', time());
            if($prod->artic_type == 'F')
              $inventory->dn_autogen     = 'Y';
            $inventory->save();
          }

          $msg = 'El artículo MSISDN: "' . $inventory->msisdn . '" se ha creado con exito ';

          if ($request->user_email) {
            $resp = self::assigmentDN($inventory, $request->user_email);
            $msg  = $msg . $resp['msg'];
          }
        }
        else{
          $msg = 'No se puede crear el artículo, No se pudo generar el DN';
        }
      }
    }
    return $msg;
  }

  public function update(Request $request, $id)
  {
    $inventoryMSISDN  = Inventory::existDN($request->msisdn);
    $inventoryMACIMEI = Inventory::existMACIMEI($request->imei);    

    $update = true;
    $msg    = '';
    if (!empty($inventoryMSISDN)) {
      if ($id != $inventoryMSISDN->id) {
        $update = false;
        $msg    = 'No se puede actualizar la información porque el msisdn ya se encuentra asignado a otro artículo';
      }
    }

    if (!empty($inventoryMACIMEI)) {
      $detail = Inventory::getDetailById($id);
      //Solo se restringe el campo imei repetido para fibra
      if ($id != $inventoryMACIMEI->id && $detail->artic_type == 'F') {
        $update = false;
        $msg    = 'No se puede actualizar la información porque el MAC o IMEI ya se encuentra asignado a otro artículo';
      }
    }

    if ($update) {
      $inventory = Inventory::getConnect('W')->find($id);
      $inventory->inv_article_id = $request->inv_article_id;
      $inventory->warehouses_id  = $request->warehouses_id;
      $inventory->serial         = $request->serial;
      $inventory->msisdn         = $request->msisdn;
      $inventory->iccid          = $request->iccid;
      $inventory->imsi           = $request->imsi;
      $inventory->imei           = $request->imei;
      $inventory->date_reception = $request->date_reception;
      $inventory->date_sending   = $request->date_sending;
      $inventory->price_pay      = $request->price_pay;
      $inventory->obs            = $request->obs;
      $inventory->save();
      $msg = 'El detalle de inventario se ha actualizado con exito';
    }
    return $msg;
  }

  public function destroy($id)
  {
    if ((SellerInventory::getConnect('R')->where(['inv_arti_details_id' => $id])->count()) == 0 || (SellerInventory::getConnect('R')->where(['inv_arti_details_id' => $id, 'status' => 'T'])->count()) != 0) {
      $provider = Inventory::getConnect('W')->find($id)->update(['status' => 'T']);
      return 'El articulo se ha eliminado exitosamente';
    } else {
      return 'No se puede procesar su solicitud. El detalle de articulo se encuentra asignado a un vendedor';
    }
  }
  public function userwh()
  {
    $data = User::getUserWH(session('user.email'));

    if (count($data)) {
      return $data->pluck('id')->toArray();
    }

    return [];

    /*$wh_arr_id = array();
  $usr_arr_email = array();
  $users = User::select('email')->where('parent_email',session('user.email'))->get();
  foreach ($users as $user) {
  $usr_arr_email[] = $user->email;
  }
  $usr_arr_email[] = session('user.email');
  $userwhs = UserWarehouse::where('status','A')->whereIn('users_email', $usr_arr_email)->get();
  foreach ($userwhs as $uwh) {
  $wh_arr_id[] = $uwh->warehouses_id;
  }

  return $wh_arr_id;*/
  }

#mover productos de bodegas
  public function mpwhs(Request $request)
  {
    if ($request->hasFile('csv')) {
      $data = Excel::load($request->file('csv')->getRealPath(), function ($reader) {
        $reader->noHeading();
        $reader->setSeparator(',');
      })->all();

      $newWH = $request->whendfile;

      foreach ($data as $row) {
        $ids[] = $row[0];
      }

      $ids = Inventory::select('id')
        ->whereIn('msisdn', $ids)
      //->where('warehouses_id', $request->whinifile)
        ->get();

      if ($ids->count()) {
        $ids = $ids->pluck('id')->toArray();
      } else {
        return 'No se encontraton dns válidos para el movimiento entre bodegas';
      }
    } else {
      $ids   = json_decode($request->ids);
      $newWH = $request->whend;
    }

    $invs = Inventory::getConnect('R')->select('id', 'msisdn', 'warehouses_id')->whereIn('id', $ids)->get();
    foreach ($invs as $key => $inv) {
      //Retirando inventario
      if (!empty($request->removeInv) && $request->removeInv == 'Y') {
        $assig = SellerInventory::getActiveAssignedByIdArtic($inv->id);

        if (!empty($assig)) {
          SellerInventoryTrack::setInventoryTrack(
            $inv->id,
            $assig->users_email,
            null,
            null,
            $inv->warehouses_id,
            session('user')->email
          );

          $assig->status = 'T';
          $assig->save();
        }
      }

      if ($inv->warehouses_id != $newWH) {
        SellerInventoryTrack::setInventoryTrack(
          $inv->id,
          null,
          $inv->warehouses_id,
          null,
          $newWH,
          session('user')->email
        );
      }

    }

    Inventory::whereIn('id', $ids)->update(['warehouses_id' => $newWH]);

    return 'Los productos se movieron exitosamente';
  }
#productos existentes en un wh
  public function proinwh($whid)
  {
    $pro_arr_id  = array();
    $productosid = Inventory::where(['warehouses_id' => $whid, 'status' => 'A'])->distinct('inv_article_id')->get(['inv_article_id']);
    foreach ($productosid as $item) {
      if (SellerInventory::where(['inv_arti_details_id' => $item->inv_article_id])->whereIn('status', ['A'])->count() == 0) {
        $pro_arr_id[] = $item->inv_article_id;
      }

    }
    $productos = Product::whereIn('id', $pro_arr_id)->select('title', 'id')->get();
    return $productos;
  }

#detalles de un producto especifico
  public function detailpro($whid)
  {
    $sql = DB::raw('(select count(*) from islim_inv_assignments where islim_inv_assignments.inv_arti_details_id = islim_inv_arti_details.id and islim_inv_assignments.status != "T")');

    return Inventory::select(
      'islim_inv_arti_details.*'
    )
      ->where([
        ['islim_inv_arti_details.warehouses_id', $whid],
        ['islim_inv_arti_details.status', 'A'],
        [$sql, 0]])
      ->get();
  }

#movimiento entre bodegas
  public function movewhview()
  {
    $userwh = $this->userwh();
    if (session('user.platform') == 'admin') {
      if (session('user')->profile->type != "master") {
        $warehouses = OrgWarehouse::select('islim_warehouses.name', 'islim_warehouses.id')
          ->join('islim_warehouses', 'islim_warehouses.id', '=', 'islim_wh_org.id_wh')
          ->where('id_org', session('user')->id_org)
          ->get();
      } else {
        $warehouses = Warehouse::select('name', 'id')->whereIn('status', ['A', 'I'])->get();
      }

    } else {
      $warehouses = Warehouse::select('name', 'id')->where('status', 'A')->whereIn('id', $userwh)->get();
    }
    $html = view('pages.ajax.movewh', compact('warehouses'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function viewproducts($whid)
  {
    $proinwh = $this->proinwh($whid);
    $html    = view('pages.ajax.movewh.productmwh', compact('proinwh'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function viewdetail($whid)
  {
    $detailinwh = $this->detailpro($whid);
    $html       = view('pages.ajax.movewh.detailmwh', compact('detailinwh'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function view()
  {
    if (session('user.platform') == 'admin') {
      $inventories = Inventory::getInventaryDetail(['A', 'I'])->get();
    } else {
      $userwh      = $this->userwh();
      $inventories = Inventory::getInventaryDetail(['A'], $userwh)->get();
    }

    $products   = Product::getConnect('R')->where('status', 'A')->get();
    $warehouses = Warehouse::getConnect('R')->where('status', 'A')->get();

    $object = array(
      'inventories' => !empty($inventories) ? $inventories : null,
      'products'    => !empty($products) ? $products : null,
      'warehouses'  => !empty($warehouses) ? $warehouses : null,
    );

    $html = view('pages.ajax.inventory', compact('object'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

#estatus de inventarios

  public function status_view()
  {
    $is_val = false;
    $html   = view('pages.ajax.status_inv', compact('is_val'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }
  public function val_status_view()
  {
    $is_val = true;
    $html   = view('pages.ajax.status_inv', compact('is_val'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }


  public function getDNForFilter(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->q)){
                $numbers = Inventory::select('msisdn')
                                        ->where([
                                            ['status', '!=', 'T'],
                                            ['msisdn', 'like', $request->q.'%']
                                        ])
                                        ->limit(10);
                $numbers = $numbers->get();
                return response()->json(array('success' => true, 'clients' => $numbers));
            }

            return response()->json(array('success' => false));
        }
    }


  public function getAvailableDnAutogen(Request $request){
    if($request->isMethod('post') && $request->ajax()){
      $msisdn=Inventory::getAvailableDnAutogen();
      return response()->json(array('success' => true, 'msisdn' => $msisdn));
    }
  }

  //////////////////////////////////////
  public function getDtStatusInv(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
      }

      $data = SellerInventory::getStatusInv($filters);

      return DataTables::of($data)
        ->editColumn('date_color', function ($c) {
          return (!empty($c->date_color) ? Carbon::createFromFormat('Y-m-d H:i:s', $c->date_color)->format('d-m-Y H:i:s') : "N/A");
        })
        ->editColumn('artic_type', function ($c) {
          if ($c->artic_type == 'H') { return "Internet Hogar"; }
          if ($c->artic_type == 'T') { return "Telefonia"; }
          if ($c->artic_type == 'M') { return "Mifi"; }
          if ($c->artic_type == 'F') { return "Fibra"; }
        })
        ->editColumn('esquema', function ($c) {
          (empty($c->esquema)) ? $ret = 'N/A' : $ret =  $c->esquema;
          return $ret;
        })
        ->make(true);

    }
  }
  //////////////////////////////////

  public function setValidMotive(Request $request, $request2 = false)
  {
    if ($request2) {
      $request = $request2;
    }

    if (!empty($request->users_email) && !empty($request->inv_arti_details_id)) {

      $assigment = SellerInventory::getConnect('R')
        ->where([
          'users_email'         => $request->users_email,
          'inv_arti_details_id' => $request->inv_arti_details_id
        ])
        ->orderBy('date_reg','DESC')
        ->first();

      if ($assigment) {
        try {
          $date_reg = Carbon::now()->copy()->subDays(20)->format('Y-m-d H:i:s');

          //si los users son diferentes es porque la solicitud la hace el vendedor pero el inventario fue pasado a su coordinador por estatus rojo
          if($assigment->user_red != $assigment->users_email){

            $usrhistory = $assigment->user_red;

            //retiro asignacion del coordinador
            SellerInventory::getConnect('W')
            ->where([
              'users_email'         => $request->users_email,
              'inv_arti_details_id' => $request->inv_arti_details_id
            ])
            ->update([
              'obs' => null,
              'status' => 'T'
            ]);

            //registro movimiento de inventario
            SellerInventoryTrack::setInventoryTrack(
              $request->inv_arti_details_id,
              $request->users_email,
              null,
              $assigment->user_red,
              null,
              session('user')->email
            );

          }
          else{
            $usrhistory = $assigment->users_email;
          }


          //marco el historico como procesado
          self::historyInventoryStatusReg(
            $usrhistory,
            $request->inv_arti_details_id,
            'P'
          );

          //paso a naranja el inventario y lo asigno al usuario que hizo la solucitud
          SellerInventory::getConnect('W')
            ->where([
              'users_email'         => $usrhistory,
              'inv_arti_details_id' => $request->inv_arti_details_id
            ])
            ->update([
              'date_orange' => date('Y-m-d H:i:s'),
              'date_reg'    => $date_reg,
              'date_red'    => null,
              'user_red'    => null,
              'last_assigned_by' => session('user')->email,
              'last_assignment' => date('Y-m-d H:i:s'),
              'red_notification_view' => null,
              'obs' => null,
              'status' => 'A'
            ]);

          return response()->json(array(
            'success' => true,
          ));

        } catch (QueryException $e) {
          Log::error($e->getMessage());
        }
      }

    }
    return response()->json(array('success' => false));
  }
  public function setInvalidMotive(Request $request, $request2 = false)
  {
    if ($request2) {
      $request = $request2;
    }


    if (!empty($request->users_email) && !empty($request->inv_arti_details_id)) {

      $assigment = SellerInventory::getConnect('R')
        ->where([
          'users_email'         => $request->users_email,
          'inv_arti_details_id' => $request->inv_arti_details_id,
        ])
        ->orderBy('date_reg','DESC')
        ->first();

      if ($assigment) {
        try {


          //si los users son diferentes es porque la solicitud la hace el vendedor pero el inventario fue pasado a su coordinador por estatus rojo
          if($assigment->user_red != $assigment->users_email){
            $usrhistory = $assigment->user_red;
          }
          else{
            $usrhistory = $assigment->users_email;
          }

          $reason = $request->reason;
          $existhist = self::historyInventoryStatusReg(
            $usrhistory,
            $request->inv_arti_details_id,
            'R',
            $request->reason
          );

          if($existhist == 0){//no existia solicitud en la tabla de inventario lo marco para pasar a merma
            SellerInventory::getConnect('W')
              ->where([
                'users_email'         => $request->users_email,
                'inv_arti_details_id' => $request->inv_arti_details_id,
              ])
              ->update(['status' => "ME"]);
          }

          return response()->json(array(
            'success' => true,
          ));

        } catch (QueryException $e) {
          Log::error($e->getMessage());
        }
      }

    }
    return response()->json(array('success' => false));
  }

  public function setTheftMotive(Request $request, $request2 = false)
  {
    if ($request2) {
      $request = $request2;
    }

    if (!empty($request->users_email) && !empty($request->inv_arti_details_id)) {
      //print_r("R");
      //dd($request);
      // exit;
      $assigment = SellerInventory::getConnect('R')
        ->where([
          'users_email'         => $request->users_email,
          'inv_arti_details_id' => $request->inv_arti_details_id,
        ])->get();

      if ($assigment) {
        try {

          SellerInventory::getConnect('W')
            ->where([
              'users_email'         => $request->users_email,
              'inv_arti_details_id' => $request->inv_arti_details_id,
            ])
            ->where('status', '<>', 'T')
            ->update([
              'status'           => "T",
              'last_assignment'  => date('Y-m-d H:i:s'),
              'last_assigned_by' => session('user')->email
            ]);

          //Reiniciando los estaus rojo y naranja a todas las asignaciones del dn
          SellerInventory::getConnect('W')
          ->where('inv_arti_details_id', $request->inv_arti_details_id)
          ->update([
              'date_red' => null,
              'date_orange' => null,
              'user_red' => null
          ]);

          Inventory::getConnect('W')
            ->where([
              'id' => $request->inv_arti_details_id,
            ])
            ->update([
              'warehouses_id' => env('WHEREHOUSE_THETF'),
            ]);

          SellerInventoryTrack::setInventoryTrack(
            $request->inv_arti_details_id,
            $request->users_email,
            null,
            null,
            env('WHEREHOUSE_THETF'),
            session('user')->email
          );

          return response()->json(array(
            'success' => true,
          ));

        } catch (QueryException $e) {
          Log::error($e->getMessage());
        }
      }

    }
    return response()->json(array(
      'success' => false,
    ));
  }

  public function loadStatusMasive_csv(Request $request)
  {
    $msisdnSuccess  = array();
    $msisdnFail     = array();
    $msisdnOrange   = array();
    $msisdnNotFound = array();
    $linePost       = 0;
    if ($request->hasFile('file_csv')) {
      $data = Excel::load($request->file('file_csv')->getRealPath(), function ($reader) {})->get();

      foreach ($data as $row) {
        $linePost++;
        if (empty($row->msisdn) || empty($row->motivo) || count($row) != 2) {
          return ['success' => false, 'msg' => "Formato erroneo. Error en linea (" . $linePost . ")." . PHP_EOL . "Verifica que el archivo cuenta con las columnas:" . PHP_EOL . " [ msisdn, motivo(V,I,R) ]" . PHP_EOL . "Por favor corrige el archivo y vuelve a intentar."];
        }
        $msisdn     = $row->msisdn;
        $newMotivo  = strtoupper($row->motivo);
        $invAsignad = SellerInventory::getStatusInv_fromcsv($msisdn);

        if (!empty($invAsignad)) {

          if ($invAsignad->color == 'red' && $invAsignad->msisdn == $msisdn) {
            //Se procesa
            $info                      = new \stdClass;
            $info->users_email         = $invAsignad->assigned;
            $info->inv_arti_details_id = $invAsignad->inv_arti_details_id;
            $info->reason              = "Carga Masiva";

            if ($newMotivo == 'V') {
              $motiv = self::setValidMotive($request, $info);
            } elseif ($newMotivo == 'I') {
              $motiv = self::setInvalidMotive($request, $info);
            } else {
              //$newMotivo=='R'
              $motiv = self::setTheftMotive($request, $info);
            }
            $motiv = json_decode(json_encode($motiv->original));
            if ($motiv->success) {
              array_push($msisdnSuccess, $msisdn);
            } else {
              Log::info('Update masive motivos: Linea del archivo ' . $linePost . '. No se pudo actualizar el registro en BD el DN ' . $msisdn . ' con motivo ' . $newMotivo);
              array_push($msisdnFail, $msisdn);
            }
          } elseif ($invAsignad->color == 'orange' && $invAsignad->msisdn == $msisdn) {
            //Se alerta que aun falta
            array_push($msisdnOrange, $msisdn);
          }
        } else {
          //No existe
          array_push($msisdnNotFound, $msisdn);
        }
      }

    }

    return ['success' => true,
      'msisdnSuccess'   => $msisdnSuccess,
      'msisdnFail'      => $msisdnFail,
      'msisdnOrange'    => $msisdnOrange,
      'msisdnNotFound'  => $msisdnNotFound];
  }

  public function moveToMerma(Request $request){
    if ($request->isMethod('post') && $request->ajax()) {
      Artisan::call('command:moveInventoryToMermaOldAutomatic');
      
      return response()->json(['success' => true]);
    }
  }


  public function status_history_view()
  {
    $html   = view('pages.ajax.history_status_inv')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function getDTStatusHistoryInv(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
      }

      $data = SellerInventory::getHistoryStatusInv($filters);

      return DataTables::of($data)
        ->editColumn('date_color', function ($c) {
          if(!empty($c->date_color)){
            return Carbon::createFromFormat('Y-m-d H:i:s', $c->date_color)
            ->format('d-m-Y H:i:s');
          }
          else{
            return "";
          }
        })
        ->editColumn('last_date_orange', function ($c) {
          if(!empty($c->last_date_orange)){
            return Carbon::createFromFormat('Y-m-d H:i:s', $c->last_date_orange)
            ->format('d-m-Y H:i:s');
          }
          else{
            return "";
          }
        })
        ->editColumn('artic_type', function ($c) {
          if ($c->artic_type == 'H') { return "Internet Hogar"; }
          if ($c->artic_type == 'T') { return "Telefonia"; }
          if ($c->artic_type == 'M') { return "Mifi"; }
          if ($c->artic_type == 'F') { return "Fibra"; }
        })
        ->make(true);
    }
  }

/**
 * [downloadDTStatusHistoryInv Descarga de archivos del reporte historico de inventarios]
 * @param Request $request [description]
 */
  public function downloadDTStatusHistoryInv(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();

       //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
      }

      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'reporte_historico_de_estatus_de_inventarios';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }

  public function pendding_orders_view()
  {
    $html = view('pages.ajax.pendding_orders')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function getDtPenddingOrders(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $parent = session('user')->email;

      $data = StockProva::getPenddingOrders($parent);

      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
           return (!empty($c->date_reg) ? Carbon::createFromFormat('Y-m-d H:i:s', $c->date_reg)->format('Y-m-d H:i:s') : "N/A");
        })
        ->make(true);
    }
  }

  public function getDtPenddingOrderDetails(Request $request)
  {
    $report = StockProvaDetail::getPenddingOrderDetails($request->folio, session('user')->email);

    $html = '

        <table id="" class="table table-striped dataTable no-footer my-0 p-0 comps-detail" role="grid" aria-describedby="" style=" margin-left:5.915% !important; width: CALC(100% - 5.915%) !important;">
            <thead>
                <tr role="row" style="background:#FFF">
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        MSISDN
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        SKU
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Artículo
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        ICCID
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        IMEI
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Coordinador
                    </th>
                    <th tabindex="0" aria-controls="" rowspan="1" colspan="1" aria-label="" style="">
                        Comentario
                    </th>
                </tr>
            </thead>
            <tbody>
        ';

    foreach ($report as $key => $item) {

      $html .= '<tr role="row" class="odd">';
      $html .= '<td>' . $item->msisdn . '</td>';
      $html .= '<td>' . $item->sku . '</td>';
      $html .= '<td>' . $item->article_name . '</td>';
      $html .= '<td>' . $item->iccid . '</td>';
      $html .= '<td>' . $item->imei . '</td>';
      $html .= '<td>' . $item->name . '</td>';
      $html .= '<td>' . $item->comment . '</td>';
      $html .= '</td>';
    }

    $html .= '</tbody></table>';

    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function actionPenddingOrders(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      $pendding_arts = StockProvaDetail::getPenddingOrderDetails($request->folio, session('user')->email);
      $dn_errors     = '';
      $dn_reciclers  = '';

      foreach ($pendding_arts as $key => $pendding_art) {

        $validUsr = User::where([
          'status'       => 'A',
          'parent_email' => session('user')->email,
          'email'        => $pendding_art->users,
        ])
          ->get();

        if (count($validUsr) > 0) {

          if ($request->type == 'A') {
            $email_assigment = $pendding_art->users;
            $status          = 'P';
          } else {
            if ($request->type == 'R') {
              $email_assigment = session('user')->email;
              $status          = 'AS';
            }
          }

          if ($status == 'P' || $status == 'AS') {

            $inv = Inventory::getConnect('W')->where('msisdn', $pendding_art->msisdn)->first();
            if ($inv) {

              //reviso si el DN esta en espera de reciclaje
              $ReciclerArt = Inv_reciclers::get_reciclerStop($pendding_art->msisdn, false);

              if (!empty($ReciclerArt)) {

                if ($status == 'AS') {
                  StockProvaDetail::getConnect('W')
                    ->where('id', $pendding_art->id)
                    ->update([
                      'status'           => 'PR',
                      'statusRecycling'  => 'P',
                      'user_assignment'  => $email_assigment,
                      'last_user_action' => session('user')->email,
                      'reg_date_action'  => date('Y-m-d H:i:s'),
                      'comment'          => 'El msisdn se encuentra en proceso de reciclaje']);

                  $dn_reciclers != '' ? $dn_reciclers .= ', ' . $pendding_art->msisdn : $dn_reciclers .= $pendding_art->msisdn;

                }

                if ($status == 'P') {
                  StockProvaDetail::getConnect('W')
                    ->where('id', $pendding_art->id)
                    ->update([
                      'status'           => $status,
                      'last_user_action' => session('user')->email,
                      'reg_date_action'  => date('Y-m-d H:i:s')]);
                }
                $status = '';
              } else {
                StockProvaDetail::getConnect('W')
                  ->where('id', $pendding_art->id)
                  ->update([
                    'status'           => 'E',
                    'last_user_action' => session('user')->email,
                    'reg_date_action'  => date('Y-m-d H:i:s'),
                    'comment'          => 'El msisdn ya se encontraba iluminado']);

                $dn_errors .= $pendding_art->msisdn . ', ';
              }
            } else {
              $error = true;
              if ($status == 'AS') {
                try {
                  $inv                 = Inventory::getConnect('W');
                  $inv->inv_article_id = $pendding_art->article_id;
                  $inv->warehouses_id  = env('WHEREHOUSE');
                  $inv->msisdn         = $pendding_art->msisdn;
                  $inv->iccid          = $pendding_art->iccid;
                  $inv->imei           = $pendding_art->imei;
                  $inv->price_pay      = $pendding_art->price;
                  $inv->date_reg       = date('Y-m-d H:i:s');
                  $inv->save();

                  $inv_id = $inv->id;

                } catch (Exception $e) {
                  $inv_id = false;
                  $dn_errors .= $pendding_art->msisdn . ', ';
                }

                if ($inv_id) {
                  $error = false;

                  try {
                    $assigment                      = SellerInventory::getConnect('W');
                    $assigment->users_email         = $email_assigment;
                    $assigment->inv_arti_details_id = $inv_id;
                    $assigment->date_reg            = date('Y-m-d H:i:s');
                    $assigment->first_assignment    = date('Y-m-d H:i:s');
                    $assigment->status              = 'A';
                    $assigment->last_assigned_by    = session('user')->email;
                    $assigment->last_assignment     = date('Y-m-d H:i:s');
                    $assigment->save();

                    //$inventory = Inventory::getConnect('R')->find($inv_id);
                    //sleep(0.25);
                    SellerInventoryTrack::setInventoryTrack(
                      $inv_id,
                      null,
                      $inv->warehouses_id,
                      $email_assigment,
                      null,
                      session('user')->email
                    );

                  } catch (Exception $e) {

                    $error = true;
                    $dn_errors .= $pendding_art->msisdn . ', ';
                  }
                }
              }

              if ($status == 'P' || !$error) {
                StockProvaDetail::getConnect('W')
                  ->where('id', $pendding_art->id)
                  ->update([
                    'status'           => $status,
                    'last_user_action' => session('user')->email,
                    'reg_date_action'  => date('Y-m-d H:i:s'),
                  ]);
              }
              $status = '';
            }
          } else {
            $dn_errors != '' ? $dn_errors .= ', ' . $pendding_art->msisdn : $dn_errors .= $pendding_art->msisdn;
            // $dn_errors .= $pendding_art->msisdn . ', ';
          }
        } else {
          $dn_errors != '' ? $dn_errors .= ', ' . $pendding_art->msisdn : $dn_errors .= $pendding_art->msisdn;
          // $dn_errors .= $pendding_art->msisdn . ', ';
          StockProvaDetail::getConnect('W')
            ->where('id', $pendding_art->id)
            ->update([
              'status'           => 'E',
              'last_user_action' => session('user')->email,
              'reg_date_action'  => date('Y-m-d H:i:s'),
              'comment'          => 'no se puede assignar,' . $pendding_art->users . ' no es subordinado de ' . session('user')->email]);
        }
      }

      // $guia         = StockProva::getConnect('W')->find($request->id);
      // $guia->status = 'P';
      // $guia->save();

      $msg = "";
      if ($dn_errors != '') {
        $msg .= 'Guia parcialmente procesada, los siguientes msisdns no se pudieron asignar: ' . $dn_errors;
        $totsw = 'N';
      } else {
        $msg .= 'Guia procesada con exito';
        $totsw = 'Y';
      }

      if ($dn_reciclers != '') {
        if ($totsw == 'N') {
          $msg .= ' y ';
        } else {
          $msg .= ', pero ';
        }
        $msg .= 'los siguientes msisdns se encuentran en proceso de reciclaje: ' . $dn_reciclers . ' y serán asignados al inventario en un periodo no mayor a 24 horas';
      }

      return response()->json(array('success' => true, 'msg' => $msg, 'total' => $totsw));

    }
    return response()->json(array('success' => false, 'msg' => 'Ocurrio un error procesando la guia'));
  }

  public function merma_old_equipment_view()
  {
    $html = view('pages.ajax.merma_old_equipment')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function getMermaOldEquipment(Request $request)
  {

    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();

      //Validando que vengan los dos rangos de fechas y formateando fecha
      if (empty($filters['dateb']) && empty($filters['datee'])) {
        $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
        $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
      } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
        $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
      } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
      } else {
        $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
        $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
      }

      $data = Inventory::getMermaWarehouseOldEquipment($filters);

      return DataTables::of($data)
        ->make(true);
    }
  }

/**
 * [inventoryRecicler vista inicial de reporte de reciclaje de DN]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function inventoryRecicler()
  {
    $codeHBB  = env('OFERT_HBB');
    $codeMifi = env('OFERT_MIFI');
    $codTelf  = env('OFERT_TELF');
    $html     = view('pages.ajax.inventory_recicler', compact('codeHBB', 'codeMifi', 'codTelf'))->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }
/**
 * [getDtInventoryRecicler vista de la consulta del reporte de reciclaje de DN]
 * @param  Request $request [description]
 * @return [type]           [description]
 */
  public function getDtInventoryRecicler(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      //Validando que vengan los dos rangos de fechas y formateando fecha
      $filters = CommonHelpers::validateDate($filters);
      $data    = Inv_reciclers::searchReportRecicler($filters);
      return DataTables::of($data)
        ->editColumn('date_reg', function ($c) {
          return !empty($c->date_reg) ? date("d-m-Y H:i:s", strtotime($c->date_reg)) : '';
        })
        ->editColumn('status', function ($c) {
          if (!empty($c->status)) {

            switch ($c->status) {
              case "C":
                return 'Solicicitado';
                break;
              case "M":
                return 'Solicicitado';
                break;
              case "F":
                return 'Procesado';
                break;
              case "P":
                return 'Procesado';
                break;
              case "E":
                return 'Error';
                break;
              case "R":
                return 'Rechazado';
                break;
              default:
                return 'Desconocido';
                break;
            }
          }
          return 'N/A';
        })
        ->editColumn('origin_netwey', function ($c) {
          if (!empty($c->origin_netwey)) {

            switch ($c->origin_netwey) {
              case "one":
                return 'Carga manual';
                break;
              case "file":
                return 'Archivo masivo';
                break;
              case "seller":
                return 'Peticion del seller';
                break;
              case "call_center":
                return 'Peticion de Call Center';
                break;
              case "sftp":
                return 'Sftp prova';
                break;

              default:
                return 'Desconocido';
                break;
            }
          }
          return 'N/A';
        })
        ->editColumn('user_netwey', function ($c) {
          if (isset($c->user_netwey) && !empty($c->user_netwey)) {
            return $c->user_netwey;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('codeOffert', function ($c) {
          if (isset($c->codeOffert) && !empty($c->codeOffert)) {
            return $c->codeOffert;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('obs', function ($c) {
          if (!empty($c->obs)) {
            return $c->obs;
          } else {
            return 'N/A';
          }
        })
        ->editColumn('statusClient', function ($c) {
          if (!empty($c->statusClient)) {

            switch ($c->statusClient) {
              case "A":
                return 'Activo';
                break;
              case "I":
                return 'Inactivo';
                break;
              case "S":
                return 'Suspendido';
                break;
              case "T":
                return 'Eliminado';
                break;

              default:
                return 'S/N';
                break;
            }
          } else {
            return 'N/A';
          }
        })
        ->editColumn('dias_recharge', function ($c) {
          if (!empty($c->dias_recharge)) {
            return $c->dias_recharge;
          } else {
            return '-';
          }
        })
        ->addColumn('detail_error', function ($c) {
          if (!empty($c->detail_error)) {
            return $c->detail_error;
          } else {
            return 'N/A';
          }
        })
        ->addColumn('checkOffert', function ($c) {
          return $c->checkOffert;
        })
        ->addColumn('checkAltan', function ($c) {
          return $c->checkAltan;
        })
        ->addColumn('id', function ($c) {
          return $c->id;
        })
        ->addColumn('ReciclerType', function ($c) {
          return $c->ReciclerType;
        })
        ->addColumn('loadInventary', function ($c) {
          return $c->loadInventary;
        })
        ->make(true);
    }
    return redirect()->route('root');
  }
/**
 * [InventoryReciclerDownload Descarga de archivos del reporte de reciclaje de DN]
 * @param Request $request [description]
 */
  public function InventoryReciclerDownload(Request $request)
  {
    if ($request->isMethod('post')) {
      $filters = $request->all();
      $filters = CommonHelpers::validateDate($filters);
      //Log::info( $filters);
      $report = new Reports;
      $report = Reports::getConnect('W');

      $report->name_report = 'report_inv_recicler';

      $report->email = session('user')->email;

      unset($filters['_token']);
      $report->filters      = json_encode($filters);
      $report->user_profile = session('user')->profile->type;
      $report->user         = session('user')->email;
      $report->status       = 'C';
      $report->date_reg     = date('Y-m-d H:i:s');

      $report->save();

      return response()->json(array('success' => true));
    }
    return response()->json(array('success' => false));
  }

/**
 * [setProcessRecicler description]
 * @param Request $request [description]
 */
  public function setProcessRecicler(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {
      $filters = $request->all();
      //aca se envia:
      // filters['status'] == 'C' => Procesar
      // filters['status'] == 'R' => Rechazar
      return Inv_reciclers::setReciclerItem($filters);
    }
    return redirect()->route('root');
  }

  public function getDTInventoryDetails(Request $request)
  {
    if ($request->isMethod('post') && $request->ajax()) {

      if (session('user.platform') == 'admin') {
        $data = Inventory::getInventaryDetail(['A', 'I']);
      } else {
        $userwh = $this->userwh();
        $data   = Inventory::getInventaryDetail(['A'], $userwh);
      }

      return DataTables::of($data)
        ->editColumn('status', function ($c) {
          switch ($c->status) {
            case 'A':
              return "Activo";
            case 'I':
              return "Inactivo";
            default:
              return "Eliminado";
          }
        })
        ->make(true);

    }
  }

  public function updateIdsProductsView()
  {
    $html = view('pages.ajax.update_masive_id_products')->render();
    return response()->json(array('success' => true, 'msg' => $html, 'numError' => 0));
  }

  public function updateIdsProductsAction(Request $request)
  {

    $file = $request->file('ids_products');
    $ext  = $file->getClientOriginalExtension();

    $rowDetail = array();
    $docData   = array();

    if ($ext == 'csv') {
      $data = Excel::load($request->file('ids_products')->getRealPath(), function ($reader) {})->get();

      $errorObj = array();

      foreach ($data as $row) {
        array_push($docData, $row);
        $row->msisdn = trim((String) $row->msisdn);
        $row->id     = trim((String) $row->id);

        $productDetail = Inventory::getConnect('R')
          ->select('islim_inv_arti_details.id', 'islim_inv_arti_details.inv_article_id', 'islim_inv_arti_details.msisdn')
          ->where('msisdn', '=', $row->msisdn)
          ->where('status', '=', 'A')
          ->first();

        if ($productDetail && $productDetail->inv_article_id != $row->id) {
          $product = Product::getConnect('R')->select('id', 'status')
            ->where('id', '=', $row->id)
            ->where('status', '=', 'A')
            ->first();

          if ($product) {

            $productDetail->inv_article_id = $row->id;

            try {

              $productDetail->save();
              $detailObj['error'] = 0;
              $detailObj['row']   = $row;
              $detailObj['msg']   = "Id actualizado con exito.!";

            } catch (Exception $e) {

              $detailObj['error'] = 1;
              $detailObj['row']   = $row;
              $detailObj['msg']   = "Error al actualizar id";
            }
          } else {
            $detailObj['error'] = 1;
            $detailObj['row']   = $row;
            $detailObj['msg']   = "Producto no encontrado";
          }
        } else {
          $detailObj['error'] = 1;
          $detailObj['row']   = $row;
          $detailObj['msg']   = "MSISDN no encontrado";
        }

        array_push($rowDetail, $detailObj);
      }

      return response()->json(array('status' => 200, 'data' => $rowDetail, 'docData' => $docData, 'numError' => 0), 200);
    }

    return response()->json(['status' => 400, 'msg' => 'Formato de archivo invalido.!'], 400);
  }
}
