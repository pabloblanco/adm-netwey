<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\AssignedSales;
use App\AssignedSaleDetails;
use App\User;
use App\Sale;
use App\Bank;
use App\Balance;
use App\LowRequest;
use App\UserLocked;
use App\UserDeposit;
use App\BankDeposits;
use App\Organization;
use App\PayInstallment;
use App\SaleInstallmentDetail;
use App\ConfigIstallments;
use HelpersS3;
use DataTables;
use App\Helpers\CommonHelpers;

use Illuminate\Support\Facades\Log;

class SellerReceptionController extends Controller {

    public function view () {  //(por revisar perfiles)
        $users;
        if (session('user.profile.id')== '1') {
            $users = User::select('email', 'name', 'last_name')->where(['platform' => 'coordinador'])->where('status','!=','T')->get();
        } elseif (session('user.profile.id')== '8') {
        	$users = User::select('email', 'name', 'last_name')->where(['platform' => 'coordinador', 'id_org' => session('user')->id_org])->where('status','!=','T')->get();
    	}

        $html = view('pages.ajax.seller_reception', compact('users'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewSalesTable (Request $request, $email) {
        $status = ['E'];
        $type = ['P'];
    	$object = Sale::getSale($email, $status, $type);
		$amount = Sale::where(['users_email' => $email, 'type' => 'P'])->whereIn('status', $status)->sum('amount');
		$sales = $object['sales'];
		$ids = json_encode($object['ids']);
    	$html = view('pages.ajax.seller_reception.sales', compact('amount','sales','ids'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function loadDeposit($status='A'){
        $banks = Bank::getConnect('R')->all();
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $html = view('pages.ajax.conciliation.load_deposit', compact('banks', 'orgs','status'))->render();
        return response()->json(array('success' => true, 'msg'=> $html, 'numError'=> 0));
    }

    public function coordDebt(){
        //$banks = Bank::getConnect('R')->all();
        //$orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $html = view('pages.ajax.conciliation.debtCoord')->render();
        return response()->json(array('success' => true, 'msg'=> $html, 'numError'=> 0));
    }

    public static function loadDepositNA(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $data = BankDeposits::getNotAssignedDeposit();

            $res = [];

            foreach($data as $dep){
                $res []= [
                    'id' => $dep->id,
                    'concepto' => $dep->concept,
                    'amount_txt' => '$'.number_format($dep->amount,2,'.',','),
                    'date_dep' => date('d-m-Y', strtotime($dep->date_dep)),
                    'date_load' => date('d-m-Y h:i', strtotime($dep->date_reg)),
                    'reason' => $dep->reason,
                    'bank' => $dep->name.' ('. substr($dep->numAcount, (strlen($dep->numAcount) - 4)) .')'
                ];
            }

            return response()->json([
                'success' => true,
                'deposits' => $res
            ]);
        }
    }

    public function associateDeposit(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $res = [
                'success' => false,
                'msg' => 'No se pudo asignar el depósito'
            ];

            if(!empty($request->id) && !empty($request->user)){
                //Consultando deposito
                $dep = BankDeposits::getDepositNotAssignedById($request->id);

                $cod = UserDeposit::getCodeByUser($request->user, $dep->bank);
                
                if(!empty($dep) && !empty($cod)){
                    $totalDebt = AssignedSales::getTotalDebtByUser($request->user);

                    $totalDebtIns = PayInstallment::getTotalDebtByUser($request->user);

                    //Asignando depósito a usuario
                    if(($totalDebt + $totalDebtIns + 10) >= $dep->amount){
                        BankDeposits::getConnect('W')
                                    ->where('id', $request->id)
                                    ->update([
                                        'id_deposit' => $cod->id_deposit,
                                        'email' => $request->user,
                                        'user_load' => session('user')->email,
                                        'date_reg' => date('Y-m-d H:i:s'),
                                        'status' => 'P'
                                    ]);

                        Balance::createLine($request->user, $dep->amount, 'I', $request->id);

                        return response()->json([
                            'success' => true,
                            'cod' => str_replace('=', '', base64_encode($cod->id)),
                            'amount' => $dep->amount
                        ]);
                    }else{
                        $res['msg'] = 'Monto excede el permitido';
                    }
                }else{
                    $res['msg'] = 'Depósito o usuario no válidos';
                }
            }

            return response()->json($res);
        }
    }

    public function loadDepositCSV(Request $request){
        if($request->hasFile('deposits') && !empty($request->bank)){
            if($request->file('deposits')->isValid() && strtolower($request->deposits->extension()) == 'txt' || strtolower($request->deposits->extension()) == 'csv'){

                $path = $request->deposits->path();
                $nameFile = $request->deposits->getClientOriginalName();

                $bank = Bank::getBankById($request->bank);

                //Validando nombre del archivo
                if(!empty($bank) && strpos($nameFile, $bank->numAcount) === 0){
                    //Obteniendo data del csv
                    $res = CommonHelpers::getCSVData($path, $bank, ',');

                    if($res['success']){
                        $notOK = $res['data']['NOT_OK'];
                        $OK = [];

                        foreach($res['data']['OK'] as $dep){
                            //Verificando si el depósito ya fue procesado
                            $exist = BankDeposits::existDeposit($dep['hash'], $request->bank);

                            if(!$exist){
                                $cod = UserDeposit::getUserByCode($dep['cod'], $request->bank);

                                if(!empty($cod)){
                                    $totalDebt = AssignedSales::getTotalDebtByUser($cod->email);

                                    $totalDebtIns = PayInstallment::getTotalDebtByUser($cod->email);

                                    //Asignando depósito a usuario
                                    if(($totalDebt + $totalDebtIns + 10) >= $dep['amount']){
                                        $objdep = BankDeposits::getConnect('W');
                                        $objdep->id_deposit = $dep['cod'];
                                        $objdep->email = $cod->email;
                                        $objdep->user_load = session('user')->email;
                                        $objdep->cod_auth = $dep['hash'];
                                        $objdep->amount = $dep['amount'];
                                        $objdep->bank = $request->bank;
                                        $objdep->line = $dep['line'];
                                        $objdep->date_dep = date("Y-m-d", strtotime($dep['date_dep']));
                                        $objdep->date_reg = date('Y-m-d H:i:s');
                                        $objdep->status = 'P';
                                        $objdep->concept = $dep['concepto'];
                                        $objdep->save();

                                        Balance::createLine($cod->email, $dep['amount'], 'I', $objdep->id);

                                        if(!empty($OK[$dep['cod']])){
                                            $OK[$dep['cod']]['amount'] += $dep['amount'];
                                            $OK[$dep['cod']]['amount_txt'] = '$'.number_format($OK[$dep['cod']]['amount'],2,'.',',');
                                            $OK[$dep['cod']]['n_dep'] = $OK[$dep['cod']]['n_dep'] + 1;
                                        }else{
                                            $OK[$dep['cod']] = [
                                                'user' => $cod->email,
                                                'user_name' => $cod->name.' '.$cod->last_name,
                                                'n_dep' => 1,
                                                'amount' => $dep['amount'],
                                                'amount_txt' => $dep['amount_txt']
                                            ];
                                        }
                                    }else{
                                        //No se puede procesar el depósito, monto excede el permitido.
                                        $notOK []= [
                                            'hash' => $dep['hash'],
                                            'concepto' => $dep['concepto'],
                                            'amount' => $dep['amount'],
                                            'date_dep' => $dep['date_dep'],
                                            'reason' => 'Monto excede el permitido, depósito asociado al código: '.$dep['cod'],
                                            'line' => $dep['line'],
                                            'amount_txt' => $dep['amount_txt'],
                                            'date_load' => date('Y-m-d H:i:s'),
                                            'bank' => $dep['bank']
                                        ];
                                    }
                                }else{
                                    //No se consiguio el usuario asociado al código
                                    $notOK []= [
                                        'hash' => $dep['hash'],
                                        'concepto' => $dep['concepto'],
                                        'amount' => $dep['amount'],
                                        'date_dep' => $dep['date_dep'],
                                        'reason' => 'No se consiguio el usuario asociado al código: '.$dep['cod'],
                                        'line' => $dep['line'],
                                        'amount_txt' => $dep['amount_txt'],
                                        'date_load' => date('Y-m-d H:i:s'),
                                        'bank' => $dep['bank']
                                    ];
                                }
                            }else{
                                //Se actualiza estatus en bd solo si esta eliminado y no ha sido asociado a ningún usuario
                                $dpot = BankDeposits::getDeposit($dep['hash'], $request->bank);

                                if($dpot->status == 'T' && empty($dpot->email)){
                                    BankDeposits::getConnect('W')
                                                ->where('id', $dpot->id)
                                                ->update([
                                                    'user_load' => session('user')->email,
                                                    'date_reg' => date('Y-m-d H:i:s'),
                                                    'status' => 'PA',
                                                    'reason' => $dpot->reason
                                                ]);
                                }
                            }
                        }

                        //Guardando los depositos que no se pudieron asignar
                        foreach($notOK as $dep){
                            //Verificando si el depósito ya fue procesado
                            $exist = BankDeposits::existDeposit($dep['hash'], $request->bank);

                            if(!$exist){
                                $objdep = BankDeposits::getConnect('W');
                                $objdep->user_load = session('user')->email;
                                $objdep->cod_auth = $dep['hash'];
                                $objdep->amount = $dep['amount'];
                                $objdep->bank = $request->bank;
                                $objdep->line = $dep['line'];
                                $objdep->date_dep = date("Y-m-d", strtotime($dep['date_dep']));
                                $objdep->date_reg = date('Y-m-d H:i:s');
                                $objdep->status = 'PA';
                                $objdep->concept = $dep['concepto'];
                                $objdep->reason = $dep['reason'];
                                //$objdep->save();
                            }else{
                                $dpot = BankDeposits::getDeposit($dep['hash'], $request->bank);

                                if($dpot->status == 'T' && empty($dpot->email)){
                                    BankDeposits::getConnect('W')
                                                ->where('id', $dpot->id)
                                                ->update([
                                                    'user_load' => session('user')->email,
                                                    'date_reg' => date('Y-m-d H:i:s'),
                                                    'status' => 'PA',
                                                    'reason' => $dep['reason']
                                                ]);
                                }
                            }
                        }

                        return response()->json([
                            'success' => true, 
                            'msg' => 'Archivo procesado',
                            'OK' => $OK
                            //'notOK' => $notOK //Borrar esto despues de la prueba
                        ]);
                    }
                }else{
                    return response()->json([
                        'success' => false, 
                        'msg' => 'Nombre de archivo no incorrecto.'
                    ]);
                }

                return response()->json([
                    'success' => false, 
                    'msg' => $res['msg']
                ]);
            }else{
                return response()->json([
                    'success' => false, 
                    'msg' => 'Ocurrio un error cargando el archivo.'
                ]);
            }
        }else{
            return response()->json([
                'success' => false, 
                'msg' => 'Debe seleccionar un archivo csv.']
            );
        }
    }

    public function deleteDepositNotAssigned(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->id)){
                BankDeposits::deleteDepNotAs($request->id, session('user')->email);

                return response()->json([
                    'success' => true
                ]);
            }

            return response()->json([
                'success' => false,
                'msg' => 'No se pudo eliminar el depósito'
            ]);
        }

        
    }

    public function loadManualDeposit(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(strtotime('-2 day', strtotime(date('Y-m-d'))) <= strtotime($request->date)){
                $cod = base64_decode($request->cod);

                $cod = UserDeposit::getConnect('R')
                                    ->select('id_deposit', 'email')
                                    ->where('id', $cod)
                                    ->first();

                if(!empty($cod)){
                    $totalDebt = AssignedSales::getConnect('R')
                                          ->select('amount')
                                          ->where([
                                            ['status','P'],
                                            ['parent_email', $cod->email]
                                          ])
                                          ->sum('amount');

                    $totalDebtIns = PayInstallment::getConnect('R')
                                          ->select('islim_pay_installments.amount')
                                          ->join(
                                            'islim_sales_installments_detail',
                                            'islim_sales_installments_detail.id',
                                            'islim_pay_installments.sale_installment_detail'
                                          )
                                          ->join(
                                            'islim_sales_installments',
                                            'islim_sales_installments.unique_transaction',
                                            'islim_sales_installments_detail.unique_transaction'
                                          )
                                          ->where([
                                            ['islim_pay_installments.status','C'],
                                            ['islim_sales_installments.coordinador', $cod->email]
                                          ])
                                          ->sum('islim_pay_installments.amount');

                    if(($totalDebt + $totalDebtIns + 10) >= $request->amount){
                        $dep = BankDeposits::getConnect('W');
                        $dep->id_deposit = $cod->id_deposit;
                        $dep->email = $cod->email;
                        $dep->user_load = session('user')->email;
                        $dep->cod_auth = uniqid('MA_');
                        $dep->amount = $request->amount;

                        if(!empty($request->bankMod) && $request->bankMod != 'OTHER'){
                            $dep->bank = $request->bankMod;
                        }

                        $dep->line = 'Cargado de forma manual por: '.session('user')->email;
                        $dep->date_dep = date("Y-m-d", strtotime($request->date));
                        $dep->date_reg = date('Y-m-d H:i:s');
                        $dep->status = 'P';
                        $dep->reason_deposit = !empty($request->reason_other) ? $request->reason_other : null;
                        $dep->save();

                        Balance::createLine($cod->email, $request->amount, 'I', $dep->id);

                        //Esta linea hace la conciliacion luego de cargar del depósito
                        //$response = $this->doConcilation($cod->email);

                        return response()->json(['success' => true]);
                    }else{
                        return response()->json(array('success' => false, 'msg' => 'No se puede procesar el depósito, monto excede el permitido.'));
                    }

                }else{
                    return response()->json(array('success' => false, 'msg' => 'Error procesando deposito.'));
                }
            }else{
                return response()->json(array('success' => false, 'msg' => 'No se pueden procesar depositos de mas de 2 días de antiguedad.'));
            }
        }
    }

    public function bashConciliate(Request $request){
        foreach ($request->users as $user) {
            $response = $this->doConcilation($user);
        }

        return response()->json(['success' => true]);
    }

    private function doConcilation($coord = false){
        if($coord){
            $user = User::getConnect('R')
                                   ->select('residue_amount', 'status')
                                   ->where('email', $coord)
                                   ->first();

            if(!empty($user)){
                $amountdep = BankDeposits::getConnect('R')
                                         ->where([
                                            ['email', $coord],
                                            ['status', 'P']
                                         ])
                                         ->sum('amount');

                $Totalamount = $user->residue_amount + $amountdep;

                $pendings = AssignedSales::getConnect('W')
                                          ->select('id', 'amount')
                                          ->where([
                                            ['status','P'],
                                            ['parent_email', $coord]
                                          ])
                                          ->orderBy('date_reg', 'ASC')
                                          ->get();

                $pendingsInst = PayInstallment::getDebInst($coord);

                if($pendings->count() || $pendingsInst->count()){
                    //Conciliando deuda de ventas en abono
                    foreach($pendingsInst as $pendingInst){
                        if($Totalamount >= $pendingInst->amount){
                            $date = date('Y-m-d H:i:s');

                            $pendingInst->user_process = session('user')->email;
                            $pendingInst->date_update = $date;
                            $pendingInst->status = 'P';
                            $pendingInst->save();

                            SaleInstallmentDetail::getConnect('W')
                                                ->where(
                                                    'id',
                                                    $pendingInst->sale_installment_detail
                                                )
                                                ->update([
                                                    'conciliation_status' => 'P',
                                                    'date_update' => $date
                                                ]);

                            //Descontando monto del saldo a favor
                            $Totalamount -= $pendingInst->amount;

                            //Marcando la venta como conciliada si ya se pagaron todas las cuotas
                            $tc = SaleInstallmentDetail::getConnect('R')
                                                   ->where([
                                                    ['unique_transaction', $pendingInst->unique_transaction],
                                                    ['conciliation_status', 'p']
                                                   ])
                                                   ->count();

                            if($tc == $pendingInst->quotes){
                                Sale::getConnect('W')->where('unique_transaction', $pendingInst->unique_transaction)
                                      ->update(['conciliation' => 'Y']);
                            }

                            //Agregando linea a balance del usuario
                            Balance::createLine(
                                        $coord,
                                        $pendingInst->amount,
                                        'E',
                                        $pendingInst->sale_installment_detail,
                                        'I'
                                    );

                            //Descontando deuda para los usuarios con baja en proceso
                            if($user->status == 'D'){
                                $userDismissal = LowRequest::getInProcessRequestByUser($coord);

                                if(!empty($userDismissal)){
                                    $userDismissal->cash_abonos = $userDismissal->cash_abonos - $pendingInst->amount;
                                    $userDismissal->cash_total = $userDismissal->cash_total - $pendingInst->amount;
                                    $userDismissal->save();
                                }
                            }
                        }
                    }

                    //Conciliando deuda de ventas normales
                    foreach($pendings as $pending){
                        if($Totalamount >= $pending->amount){
                            $pending->user_process = session('user')->email;
                            $pending->date_process = date('Y-m-d H:i:s');
                            $pending->status = 'A';
                            $pending->save();

                            //Marcando como conciliadas las ventas relacionadas a la asignacion
                            $da = AssignedSaleDetails::getConnect('R')
                                                    ->select(
                                                        'unique_transaction'
                                                    )
                                                    ->where(
                                                        'asigned_sale_id',
                                                        $pending->id
                                                    )
                                                    ->get()
                                                    ->pluck('unique_transaction');

                            Sale::getConnect('W')
                                    ->whereIn('unique_transaction', $da)
                                    ->update(['conciliation' => 'Y']);

                            $Totalamount -= $pending->amount;

                            Balance::createLine($coord, $pending->amount, 'E', $pending->id, 'N');

                            //Descontando deuda para los usuarios con baja en proceso
                            if($user->status == 'D'){
                                $userDismissal = LowRequest::getInProcessRequestByUser($coord);

                                if(!empty($userDismissal)){
                                    $userDismissal->cash_request = $userDismissal->cash_request - $pending->amount;
                                    $userDismissal->cash_total = $userDismissal->cash_total - $pending->amount;
                                    $userDismissal->save();
                                }
                            }
                        }else{
                            break;
                        }
                    }

                    BankDeposits::getConnect('W')
                                    ->where([
                                        ['email', $coord],
                                        ['status', 'P']
                                    ])
                                    ->update([
                                        'status' => 'A',
                                        'user_process' => session('user')->email,
                                        'date_process' => date('Y-m-d H:i:s')
                                    ]);


                    User::getConnect('W')->where('email', $coord)->update(['residue_amount' => $Totalamount]);

                    return ['success' => true];
                }else{
                    return ['success' => false, 'msg' => 'Usuario sin deuda pendiente.'];
                }
            }else{
                return ['success' => false, 'msg' => 'No se encontro al usuario.'];
            }
        }
    }

    public function getCoordDebt(Request $request){
        $userEmail = session('user.email');
        $today = now()->subDay()->startOfDay()->toDateTimeString(); 
        $status = 'A';

        $users = User::getAllDebt([
            'parent' => $userEmail,
            'status' => $status
        ]);
        
        $deb_old = User::getTotalDebt([
            'parent' => $userEmail,
            'status' => $status,
            'date_end' => $today
        ]);
        
        $deb_today = User::getTotalDebt([
            'parent' => $userEmail,
            'status' => $status,
            'date_begin' => $today
        ]);

        foreach ($users as $user){
            $user->deposits = BankDeposits::getTotalDebt($user->email);

            $user->last_deposit = BankDeposits::getLastDeposit($user->email);

            $user->last_concilation = AssignedSales::getLastConciliation($user->email);

            $user->debt_today = $deb_today->first(function ($item, $key) use ($user){
                                    return $item->email == $user->email;
                                });

            $user->debt_today = !empty($user->debt_today) ? $user->debt_today->debt : 0;

            $user->debt = $deb_old->first(function ($item, $key) use ($user){
                                    return $item->email == $user->email;
                                });

            $user->debt = !empty($user->debt) ? $user->debt->debt : 0;

            $user = AssignedSales::getDays_deb_old($user);

            $user->debt_sellers_old = Sale::getTotalDebtFromSellers([
                'user' => $user->email,
                'date_end' => $today
            ]);

            $user->debt_sellers_today = Sale::getTotalDebtFromSellers([
                'user' => $user->email,
                'date_begin' => $today
            ]);
        }

        return DataTables::of($users)
                            ->setRowId(function($user){
                                return str_replace('=', '', base64_encode($user->id));
                            })
                            ->editColumn('name', function($user){
                                return $user->name.' '.$user->last_name;
                            })
                            ->editColumn('residue_amount', function($user){
                                if($user->residue_amount > 0)
                                    return '$'.number_format($user->residue_amount,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt', function($user){
                                if($user->debt > 0)
                                    return '$'.number_format($user->debt,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt_today', function($user){
                                if($user->debt_today > 0)
                                    return '$'.number_format($user->debt_today,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt_sellers_old', function($user){
                                if($user->debt_sellers_old > 0)
                                    return '$'.number_format($user->debt_sellers_old,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt_sellers_today', function($user){
                                if($user->debt_sellers_today > 0)
                                    return '$'.number_format($user->debt_sellers_today,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debtIns', function($user){
                                $di = PayInstallment::getDebUser($user->email);
                                return '$'.number_format($di,2,'.',',');
                            })
                            ->editColumn('deposits', function($user){
                                if($user->deposits > 0)
                                    return '$'.number_format($user->deposits,2,'.',',');
                                else
                                    return 'N/A';
                            })
                            ->editColumn('cod', function($user){
                                return str_replace('=', '', base64_encode($user->id));
                            })
                            ->editColumn('bank', function($user){
                                if(!empty($user->last_deposit))
                                    return $user->last_deposit->banco;
                                else
                                    return 'N/A';
                            })
                            ->editColumn('last_deposit', function($user){
                                if(!empty($user->last_deposit))
                                    return $user->last_deposit->last_deposit_date;
                                else
                                    return 'N/A';
                            })
                            ->editColumn('last_conc', function($user){
                                if(!empty($user->last_concilation))
                                    return $user->last_concilation->date_process;
                                else
                                    return 'N/A';
                            })
                            ->make(true);
    }

    public function getUserDebt(Request $request){
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $today = now()->subDay()->startOfDay()->toDateTimeString();        
        $status = 'A';

        if(!empty($request->status)){
            $status = ($request->status == 'A') ? ['A', 'D'] : $request->status;
        }

        $users = User::getAllDebt([
            'orgs' => !empty($request->filter)? [$request->filter] : $orgs->pluck('id'),
            'status' => $status
        ]);
        
        $deb_old = User::getTotalDebt([
            'orgs' => !empty($request->filter)? [$request->filter] : $orgs->pluck('id'),
            'status' => $status,
            'date_end' => $today
        ]);    

        $deb_today = User::getTotalDebt([
            'orgs' => !empty($request->filter)? [$request->filter] : $orgs->pluck('id'),
            'status' => $status,
            'date_begin' => $today
        ]);
  
        foreach ($users as $user){
            $user->deposits = BankDeposits::getTotalDebt($user->email);

            $user->last_deposit = BankDeposits::getLastDeposit($user->email);

            $user->last_concilation = AssignedSales::getLastConciliation($user->email);

            $user->debt_today = $deb_today->first(function ($item, $key) use ($user){
                                    return $item->email == $user->email;
                                });

            $user->debt_today = !empty($user->debt_today) ? $user->debt_today->debt : 0;

            $user->debt = $deb_old->first(function ($item, $key) use ($user){
                                    return $item->email == $user->email;
                                });

            $user->debt = !empty($user->debt) ? $user->debt->debt : 0;

            $user = AssignedSales::getDays_deb_old($user);

            $user->debt_sellers_old = Sale::getTotalDebtFromSellers([
                'user' => $user->email,
                'date_end' => $today
            ]);

            $user->debt_sellers_today = Sale::getTotalDebtFromSellers([
                'user' => $user->email,
                'date_begin' => $today
            ]);
        }

        return DataTables::of($users)
                            ->setRowId(function($user){
                                return str_replace('=', '', base64_encode($user->id));
                            })
                            ->editColumn('name', function($user){
                                // if(session('user')->email == 'admin@admin.com')
                                    return $user->name.' '.$user->last_name." (".$user->email.") ";
                                // else
                                //     return $user->name.' '.$user->last_name;
                            })
                            ->editColumn('residue_amount', function($user){
                                if($user->residue_amount > 0)
                                    return '$'.number_format($user->residue_amount,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt', function($user){
                                if($user->debt > 0)
                                    return '$'.number_format($user->debt,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt_today', function($user){
                                if($user->debt_today > 0)
                                    return '$'.number_format($user->debt_today,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt_sellers_old', function($user){
                                if($user->debt_sellers_old > 0)
                                    return '$'.number_format($user->debt_sellers_old,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debt_sellers_today', function($user){
                                if($user->debt_sellers_today > 0)
                                    return '$'.number_format($user->debt_sellers_today,2,'.',',');
                                else
                                    return '$0';
                            })
                            ->editColumn('debtIns', function($user){
                                $di = PayInstallment::getDebUser($user->email);
                                return '$'.number_format($di,2,'.',',');
                            })
                            ->editColumn('deposits', function($user){
                                if($user->deposits > 0)
                                    return '$'.number_format($user->deposits,2,'.',',');
                                else
                                    return 'N/A';
                            })
                            ->editColumn('cod', function($user){
                                return str_replace('=', '', base64_encode($user->id));
                            })
                            ->editColumn('bank', function($user){
                                if(!empty($user->last_deposit))
                                    return $user->last_deposit->banco;
                                else
                                    return 'N/A';
                            })
                            ->editColumn('last_deposit', function($user){
                                if(!empty($user->last_deposit))
                                    return $user->last_deposit->last_deposit_date;
                                else
                                    return 'N/A';
                            })
                            ->editColumn('last_conc', function($user){
                                if(!empty($user->last_concilation))
                                    return $user->last_concilation->date_process;
                                else
                                    return 'N/A';
                            })
                            ->make(true);
    }

    public function lockedUser(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                UserLocked::doLoked(session('user')->email, $request->email);
                User::setStatusLockUser($request->email, 'Y');
                
                return response()->json([
                    'success' => true, 
                    'msg' => 'Usuario bloqueado con exito.'
                ]);
            }

            return response()->json([
                'success' => false, 
                'msg' => 'No se pudo bloquear el usuario.'
            ]);
        }
    }

    public function unLockedUser(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                UserLocked::doUnLocked(session('user')->email, $request->email);
                User::setStatusLockUser($request->email, 'N');

                return response()->json([
                    'success' => true, 
                    'msg' => 'Usuario desbloqueado con exito.'
                ]);
            }

            return response()->json([
                                    'success' => false, 
                                    'msg' => 'No se desbloquear el usuario.'
                                ]);
        }
    }

    public function getLastsDeposits(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                $bankUser = UserDeposit::BankUser($request->email);
                if(!empty($bankUser)){
                    $deposists = BankDeposits::DepositsByUser($request->email,'A',5);
                    $html = view('pages.ajax.conciliation.lastDep', compact('deposists', 'bankUser'))->render();
                    return response()->json(array('success' => true, 'html' => $html));
                }else{
                    return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
                }
            }else{
                return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
            }
        }
        return response()->json(['success' => false, 'msg' => 'No se pudieron consultar los últimos depósitos.']);
    }

    public function getLastDepositNotConc(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                $bankUser = UserDeposit::BankUser($request->email);
                if(!empty($bankUser)){
                    $deposists = BankDeposits::DepositsByUser($request->email,'P',1);
                    if($deposists->count()){
                        $isDelete = true;
                        $html = view('pages.ajax.conciliation.lastDep', compact('deposists', 'bankUser', 'isDelete'))->render();
                        return response()->json(array('success' => true, 'html' => $html));
                    }else{
                        return response()->json(['success' => false, 'msg' => 'El usuario no tiene depósitos sin conciliar.']);
                    }
                }else{
                    return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
                }
            }else{
                return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
            }
        }
        return response()->json(['success' => false, 'msg' => 'No se obtner el último depósitos.']);
    }

    public function deleteLastDeposit(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                $dep = BankDeposits::getConnect('W')
                                    ->select(
                                        'islim_bank_deposits.id',
                                        'islim_bank_deposits.amount',
                                        'islim_bank_deposits.id_deposit',
                                        'islim_user_deposit_id.id as id_cod'
                                    )
                                    ->join(
                                        'islim_user_deposit_id',
                                        'islim_user_deposit_id.id_deposit',
                                        'islim_bank_deposits.id_deposit'
                                    )
                                    ->where([
                                        ['islim_bank_deposits.email', $request->email],
                                        ['islim_bank_deposits.status', 'P'],
                                        ['islim_user_deposit_id.status', 'A']
                                    ])
                                    ->orderBy('islim_bank_deposits.id', 'DESC')
                                    ->first();

                $dep->status = 'T';
                $dep->user_delete = session('user')->email;
                $dep->date_delete = date('Y-m-d H:i:s');
                $dep->save();

                Balance::getConnect('W')->where('id_deposit', $dep->id)->update(['status' => 'T']);

                return response()->json([
                    'success' => true, 
                    'amount' => $dep->amount,
                    'code' => str_replace('=', '', base64_encode($dep->id_cod))
                ]);
            }else{
                return response()->json(['success' => false, 'msg' => 'No se encontro el usuario.']);
            }
        }
        return response()->json(['success' => false, 'msg' => 'No se pudo eliminar el depósito.']);
    }

    public function detailDebtSellers(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email) && !empty($request->type)){
                $bankUser = UserDeposit::BankUser($request->email);
                $sellers = User::getConnect('R')
                                 ->select('dni', 'name', 'last_name', 'email')
                                 ->where([
                                    ['parent_email', $request->email]
                                 ])
                                 ->whereIn('islim_users.status', ['A','D'])
                                 ->get();

                //$today = now()->startOfDay()->toDateTimeString();
                $today = now()->subDay()->startOfDay()->toDateTimeString();

                foreach ($sellers as $seller){
                    $seller->sales = Sale::getConnect('R')
                                        ->select(
                                            'islim_sales.id',
                                            'islim_sales.unique_transaction',
                                            'islim_sales.msisdn',
                                            'islim_sales.sale_type',
                                            'islim_sales.amount',
                                            'islim_sales.date_reg',
                                            'islim_packs.title as pack',
                                            'islim_inv_articles.title as arti'
                                        )
                                        ->join(
                                            'islim_packs',
                                            'islim_packs.id',
                                            'islim_sales.packs_id'
                                        )
                                        ->join(
                                            'islim_inv_arti_details',
                                            'islim_inv_arti_details.id',
                                            'islim_sales.inv_arti_details_id'
                                        )
                                        ->join(
                                            'islim_inv_articles',
                                            'islim_inv_articles.id',
                                            'islim_inv_arti_details.inv_article_id'
                                        )
                                        ->where([
                                            ['islim_sales.status', 'E'],
                                            ['islim_sales.users_email', $seller->email],
                                            ['islim_sales.amount', '>', 0]
                                        ]);

                    if($request->type == 'today'){
                        $seller->sales->where('islim_sales.date_reg', '>=', $today);
                    }else{
                        $seller->sales->where('islim_sales.date_reg', '<', $today);
                    }

                    $seller->sales = $seller->sales->get();
                }

                $html = view('pages.ajax.conciliation.detailDebtSeller', compact('sellers', 'bankUser'))->render();

                return response()->json(array('success' => true, 'html' => $html));

            }else{
                return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de la deuda.'));
            }
        }else{
            return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de la deuda.'));
        }
    }

    public function detailDebt(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email) && !empty($request->type)){
                $bankUser = UserDeposit::BankUser($request->email);
                if(!empty($bankUser)){
                    //$today = now()->startOfDay()->toDateTimeString();
                    $today = now()->subDay()->startOfDay()->toDateTimeString();

                    $details = AssignedSales::getConnect('R')
                                        ->select(
                                            'islim_asigned_sales.id',
                                            'islim_users.email',
                                            'islim_asigned_sales.amount',
                                            'islim_asigned_sales.date_reg',
                                            'islim_users.name',
                                            'islim_users.last_name'
                                        )
                                        ->join(
                                            'islim_users',
                                            'islim_users.email',
                                            'islim_asigned_sales.users_email'
                                        )
                                        ->where([
                                            ['islim_asigned_sales.status', 'P'],
                                            ['islim_asigned_sales.parent_email', $request->email]
                                        ])
                                        ->orderBy('islim_asigned_sales.date_reg', 'ASC');

                    if($request->type == 'today'){
                        $details->where('islim_asigned_sales.date_reg', '>=', $today);
                    }else{
                        $details->where('islim_asigned_sales.date_reg', '<', $today);
                    }

                    $details = $details->get();

                    if($details->count()){
                        foreach ($details as $detail) {
                            $salesDetail = AssignedSaleDetails::getConnect('R')
                                                              ->select(
                                                                'islim_asigned_sale_details.unique_transaction',
                                                                'islim_sales.amount',
                                                                'islim_sales.date_reg',
                                                                'islim_sales.msisdn',
                                                                'islim_sales.sale_type',
                                                                'islim_packs.title as pack',
                                                                'islim_inv_articles.title as arti'
                                                              )
                                                              ->join(
                                                                'islim_sales',
                                                                'islim_sales.unique_transaction',
                                                                'islim_asigned_sale_details.unique_transaction'
                                                              )
                                                              ->join(
                                                                'islim_packs',
                                                                'islim_packs.id',
                                                                'islim_sales.packs_id'
                                                              )
                                                              ->join(
                                                                'islim_inv_arti_details',
                                                                'islim_inv_arti_details.id',
                                                                'islim_sales.inv_arti_details_id'
                                                              )
                                                              ->join(
                                                                'islim_inv_articles',
                                                                'islim_inv_articles.id',
                                                                'islim_inv_arti_details.inv_article_id'
                                                              )
                                                              ->where(
                                                                [
                                                                    ['islim_asigned_sale_details.asigned_sale_id', $detail->id],
                                                                    ['islim_sales.amount', '>', 0]
                                                                ]
                                                              )
                                                              ->get();

                            $detail->salesDetail = $salesDetail;
                        }

                        $html = view('pages.ajax.conciliation.detailDebt', compact('details', 'bankUser'))->render();

                        return response()->json(array('success' => true, 'html' => $html));
                    }else{
                        return response()->json(array('success' => false, 'msg' => 'El usuario no tiene deuda.'));
                    }
                }else{
                   return response()->json(array('success' => false, 'msg' => 'No se consiguio el usuario.'));
                }
            }else{
                return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de la deuda.'));
            }
        }
    }

    public function detailDebtInst(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->email)){
                $bankUser = UserDeposit::BankUser($request->email);
                if(!empty($bankUser)){
                    $details = PayInstallment::getGroupDetailDeb($request->email);

                    if($details->count()){
                        foreach ($details as $detail) {
                            $detail->salesDetail = PayInstallment::getDetailReport($detail->id_report);
                        }

                        $html = view('pages.ajax.conciliation.detailDebtInt', compact('details', 'bankUser'))->render();

                        return response()->json(array('success' => true, 'html' => $html));
                    }else{
                        return response()->json(array('success' => false, 'msg' => 'El usuario no tiene deuda.'));
                    }
                }else{
                   return response()->json(array('success' => false, 'msg' => 'No se consiguio el usuario.'));
                }
            }else{
                return response()->json(array('success' => false, 'msg' => 'No se pudo consultar el detalle de la deuda.'));
            }
        }
    }

    public function viewDeposit(Request $request) { // (por revisar perfiles)
        $users;
        if (session('user')->platform == 'admin') {
            if(session('user')->profile->type == "master")
                $users = User::select('email', 'name', 'last_name')->where('platform', 'coordinador')->get();
            else
                $users = User::select('email', 'name', 'last_name')->where([['platform', 'coordinador'],['id_org',session('user')->id_org]])->get();
        } else {
        	if (session('user')->platform == 'coordinador') {
            	$users = User::select('email', 'name', 'last_name')->where(['platform' => 'vendor', 'parent_email' => session('user')->email])->get();
        	}
        }
        $html = view('pages.ajax.seller_deposit', compact('users'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewDepositTable (Request $request, $email) {
        $object = AssignedSales::getAssignedSales($email, null, 'P');
        $banks = Bank::all();
        $sales = $object['sales'];
        $ids = json_encode($object['ids']);
        $html = view('pages.ajax.seller_reception.deposit', compact('amount', 'sales', 'ids', 'banks'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function viewDepositDetailTable ($sale) {
        $sales = AssignedSales::getAssignedSalesDetails($sale);
        $html = view('pages.ajax.seller_reception.depositDetail', compact('sales'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function aprove (Request $request, $ids, $received, $user) {
    	try {
    		$parent_email = $user;
    		if (User::where(['email' => $user, 'platform' => 'vendor'])->count() > 0) {
    			$parent_email = User::select('parent_email')->where(['email' => $user])->first()->parent_email;
    		}
    		if (!isset($parent_email) or empty($parent_email))
    			$parent_email = session('user')->email;
    		$ids = json_decode($ids);
    		$receiveds = json_decode($received);

    		$assignedSale = new AssignedSales();
			$assignedSale->parent_email = $parent_email;
			$assignedSale->users_email = $user;
			$assignedSale->date_reg = date ('Y-m-d H:i:s', time());
			$assignedSale->status = 'P';
			$assignedSale->amount = 0;
			$assignedSale->amount_text = 0;
    		$details = array();
    		foreach ($ids as $i => $id) {
    			$sale = Sale::where(['id' => $id, 'status' => 'E'])->first();
    			if (isset($sale)) {
	    			$sale->status = 'A';
	    			$sale->save();

					$assignedSale->amount += $sale->amount;
					$assignedSale->amount_text += $receiveds[$i];

	    			$detail = new AssignedSaleDetails();
	    			$detail->amount = $sale->amount;
	    			$detail->amount_text = $receiveds[$i];
	    			$detail->unique_transaction = $sale->unique_transaction;
	    			$details[] = $detail;
    			}
    		}

			$assignedSale->save();

			foreach ($details as $detail) {
				$detail->asigned_sale_id = $assignedSale->id;
				$detail->save();
			}

        	return response()->json(array('success' => true, 'msg'=>'Los registros han sido procesados', 'numError'=>0));
    	} catch (Exception $e) {
        	return response()->json(array('success' => true, 'msg'=>'Hubo un error actualizando, intente más tarde', 'numError'=>1, 'msgError'=>$e->getMessage()));
    	}
    }

    public function report(Request $request, $ids) {
    	try {
            if ($request->hasFile('image')){
                $image = $request->file('image');
                $file = \File::get($image);
                $name = $ids.'.'.$image->extension();
                $success = HelpersS3::insertImage($name, 'DepositSeller', $file);
                if(!$success){
                    return response()->json(array('success' => false, 'msg'=>'Error al guardar la imagen', 'numError'=>0));
                }
            }
			$assignedSale = AssignedSales::find($ids);
			$assignedSale->n_tranfer = $request->deposit;
            $assignedSale->user_process = session('user')->email;
            $assignedSale->date_process = date ('Y-m-d H:i:s', time());
			$assignedSale->bank_id = $request->bank;
			$assignedSale->status = 'A';
			$assignedSale->save();
        	return response()->json(array('success' => true, 'msg'=>'Se guardaron los datos correctamente', 'numError'=>0));
    	} catch (Exception $e) {
        	return response()->json(array('success' => true, 'msg'=>'Hubo un error actualizando, intente más tarde', 'numError'=>1, 'msgError'=>$e->getMessage()));
    	}
    }

    public function depositID($type='A'){ //si type = T es para vendedores eliminados
        $canEdit = User::hasPermission(session('user')->email, 'SEL-EDD');
        $html = view('pages.ajax.conciliation.depositId', compact('canEdit','type'))->render();
        return response()->json(array('success' => true, 'msg'=>$html, 'numError'=>0));
    }

    public function getUsers(Request $request){
        $users = UserDeposit::getReport($request->all());

        /*$users = UserDeposit::select('islim_user_deposit_id.*','islim_users.name','islim_users.last_name')
                        ->join('islim_users','islim_users.email','=','islim_user_deposit_id.email')
                        ->where('islim_user_deposit_id.status', 'A');
        if($request->type == 'A'){
            $users =$users->where('islim_users.platform','!=','vendor');
        }
        else{
            $users =$users->where('islim_users.platform','vendor')
                        ->where('islim_users.status','T');
        }
        $users =$users->get();*/

        return DataTables::of($users)
                            ->editColumn('date_reg', function($user){
                                return date("d-m-Y", strtotime($user->date_reg));
                            })
                            ->editColumn('name', function($user){
                                return $user->name.' '.$user->last_name;
                            })
                            ->make(true);
    }

    public function downloadCodDepUsers(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $users = UserDeposit::getReport($request->all());

            /*$users = UserDeposit::select('islim_user_deposit_id.*','islim_users.name','islim_users.last_name')
                        ->join('islim_users','islim_users.email','=','islim_user_deposit_id.email')
                        ->where('islim_user_deposit_id.status', 'A');
            if($request->type == 'A'){
                $users =$users->where('islim_users.platform','!=','vendor');
            }
            else{
                $users =$users->where('islim_users.platform','vendor')
                            ->where('islim_users.status','T');
            }
            $users =$users->get();*/

            $data []= ['Nombre', 'Email', 'Cod. Depósito', 'Banco', 'Fecha Registro'];

            foreach ($users as $user){
                $data []= [
                            $user->name.' '.$user->last_name,
                            $user->email,
                            $user->id_deposit,
                            $user->bank,
                            date("d-m-Y", strtotime($user->date_reg))
                          ];
            }

            $url = CommonHelpers::saveFile(
                                    '/public/reports',
                                    'reports_seller',
                                    $data,
                                    'Codigos_Depositos_'.date('d-m-Y H:i:s')
                                );

            return response()->json(array('url' => $url));
        }
    }

    /*Deprecate*/
    /*Guarda los reportes en el server y genera una url para descarga que es enviada al front*/
    private function saveReport($data = false, $filename = 'report'){
        $pathReport = '/public/reports';

        return CommonHelpers::saveFile($pathReport, 'reports_seller', $data, $filename);

        /*Deprecate*/

        //Cargando directorios dentro de public/reports
        $directory = Storage::disk('local')->directories($pathReport);

        $pathReportBI = $pathReport.'/'.'reports_bi';

        //Si no existe el directorio de reporte para las altas se crea
        if(!in_array($pathReportBI, $directory))
            Storage::disk('local')->makeDirectory($pathReportBI);

        \Excel::create($filename, function($excel) use ($data) {
            $excel->sheet('Reporte', function($sheet) use ($data) {
                $sheet->fromArray($data, null, 'A1', false, false);
            });
        })->store('xls',storage_path('/app'.$pathReportBI));

        return $pathReportBI.'/'.$filename.".xls";
    }

    /*DEPRECADO*/
    public function getUserByDeposit(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            $platform=['coordinador','admin'];
            $status = 'A';
            if(!empty($request->t)){
                if($request->t != 'A'){
                    $platform=['vendor'];
                    $status='T';
                }
            }
            if(!empty($request->q)){
                $users = User::getConnect('R')->select(
                                'islim_users.name',
                                'islim_users.last_name',
                                'islim_users.email',
                                DB::raw('CONCAT(islim_users.name, " ", islim_users.last_name) as username')
                            )
                            ->join('islim_profile_details',function($join) use ($status){
                                $join->on('islim_profile_details.user_email', '=', 'islim_users.email');
                                    if($status=='A')
                                        $join=$join->where('islim_profile_details.status', 'A');
                            })
                            ->join('islim_profiles',function($join) use ($platform){
                                $join->on('islim_profiles.id', '=', 'islim_profile_details.id_profile')
                                     ->whereIn('islim_profiles.platform',$platform)
                                     ->where('heredity','Y');
                            })
                            ->where(function($query) use($request){
                                $query->where('islim_users.name', 'like', '%'.$request->q.'%')
                                      ->orWhere('islim_users.last_name', 'like', '%'.$request->q.'%');
                            })
                            ->where('islim_users.status', $status);

                $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                $users = $users->whereIn('islim_users.id_org', $orgs->pluck('id'));
                $users = $users->limit(10);

                // $query = vsprintf(str_replace('?', '%s', $users->toSql()), collect($users->getBindings())->map(function ($binding) {
                //     return is_numeric($binding) ? $binding : "'{$binding}'";
                // })->toArray());

                // Log::info($query);

                $users = $users->get();
                return response()->json(array('success' => true, 'users' => $users));
            }
            return response()->json(array('success' => false));
        }
    }

    public function createIdDep(Request $request){
        if(!empty($request->userS) && !empty($request->cod)){

            $res = UserDeposit::CreatedOrUpdate($request, 'created');

            if($res['success'])
                Balance::createLine($request->userS, 0, 'I', null);

            return response()->json($res);
        }
    }

    public function deleteIdDep(Request $request){
        if(!empty($request->codigo)){
            UserDeposit::where('id', $request->codigo)->update(['status' => 'I']);
        }
        return response()->json(array('success' => true));
    }

    public function editIdDep(Request $request){
        if(!empty($request->cod) && !empty($request->userS)){

            $res = UserDeposit::CreatedOrUpdate($request, 'edit');

            return response()->json($res);
        }
    }

    /*
        Retorna vista principal de conciliación para ventas en abono
    */
    public static function loadDebtsInst(){
        $banks = Bank::all();
        $html = view('pages.ajax.conciliation.loadDebtsInst', compact('banks'))->render();
        return response()->json(array('success' => true, 'msg'=> $html, 'numError'=> 0));
    }

    /*
        Retorna usuarios que pueden tener deuda, actualmente se usa solo en conciliacion de ventas en abono
    */
    public static function getUsersDeb(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->q)){
                $search = $request->q;

                $users = User::select('islim_users.name', 'islim_users.last_name', 'islim_users.email')
                                        ->join('islim_profile_details',function($join){
                                            $join->on('islim_profile_details.user_email', '=', 'islim_users.email')
                                                 ->where('islim_profile_details.status', 'A');
                                        })
                                        ->join('islim_profiles',function($join){
                                            $join->on('islim_profiles.id', '=', 'islim_profile_details.id_profile')
                                                 ->whereIn('islim_profiles.platform',['coordinador','admin'])
                                                 ->where('heredity','Y');
                                        })
                                        ->where(function($query) use ($search){
                                            $query->where('islim_users.name','like',$search.'%')
                                                  ->orWhere('islim_users.last_name','like',$search.'%');
                                        })
                                        ->limit(10);

                $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                $users = $users->whereIn('islim_users.id_org', $orgs->pluck('id'));

                return response()->json(array('success' => true, 'clients' => $users->get()));
            }
        }

        return response()->json(array('success' => false));
    }

    public static function getDebtInstDT(Request $request){
        if($request->isMethod('post') && $request->ajax()){

            $debts = PayInstallment::getDetailDeb($request->user);

            return DataTables::of($debts)//eloquent
                            ->editColumn('client', function($debt){
                                return $debt->name.' '.$debt->last_name;
                            })
                            ->editColumn('amount', function($debt){
                                if($debt->amount > 0)
                                    return '$'.number_format($debt->amount,2,'.',',');
                                else
                                    return 'N/A';
                            })
                            ->addColumn('real_amount', function($debt){
                                return $debt->amount;
                            })
                            ->editColumn('date', function($debt){
                                return $debt->date;
                            })
                            ->addColumn('timestamp', function($debt){
                                return strtotime($debt->date);
                            })
                            ->editColumn('unique_transaction', function($debt){
                                return base64_encode($debt->unique_transaction.'_-_'.$debt->pay);
                            })
                            ->make(true);
        }
    }

    public static function getInfoUser(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->user)){
                $userData = User::select(
                                    'islim_users.residue_amount',
                                    'islim_users.email',
                                    'islim_users.name',
                                    'islim_users.last_name',
                                    'islim_user_deposit_id.id as cod'
                                  )
                                  ->join(
                                    'islim_user_deposit_id',
                                    'islim_user_deposit_id.email',
                                    'islim_users.email'
                                  )
                                  ->where('islim_users.email', $request->user);

                if(session('user.profile.id') != '1'){
                    if(!empty(session('user.id_org')))
                        $userData = $userData->where('id_org', session('user.id_org'));
                    else
                        return response()->json(['success' => false]);
                }

                $userData = $userData->first();

                if(!empty($userData)){
                    $amountdep = BankDeposits::select('amount')
                                           ->where([
                                            ['email', $request->user],
                                            ['status', 'P']
                                           ])
                                           ->sum('amount');

                    $totalDebtIns = PayInstallment::select('islim_pay_installments.amount')
                                              ->join(
                                                'islim_sales_installments_detail',
                                                'islim_sales_installments_detail.id',
                                                'islim_pay_installments.sale_installment_detail'
                                              )
                                              ->join(
                                                'islim_sales_installments',
                                                'islim_sales_installments.unique_transaction',
                                                'islim_sales_installments_detail.unique_transaction'
                                              )
                                              ->where([
                                                ['islim_pay_installments.status','C'],
                                                ['islim_sales_installments.coordinador', $request->user]
                                              ])
                                              ->sum('islim_pay_installments.amount');

                    $userData->amountT = '$'.number_format($totalDebtIns,2,'.',',');
                    $userData->amountR = number_format(($userData->residue_amount + $amountdep),2,'.',',');
                    $userData->amountRR = $userData->residue_amount + $amountdep;
                    $userData->cod = base64_encode($userData->cod);

                    if($amountdep > 0){
                        $lasat_dep = BankDeposits::select(
                                                    'islim_bank_deposits.amount',
                                                    'islim_bank_deposits.date_reg',
                                                    'islim_banks.name'
                                                   )
                                                   ->join(
                                                    'islim_banks',
                                                    'islim_banks.id',
                                                    'islim_bank_deposits.bank'
                                                   )
                                                   ->where([
                                                    ['islim_bank_deposits.email', $request->user],
                                                    ['islim_bank_deposits.status', 'P']
                                                   ])
                                                   ->orderBy('islim_bank_deposits.id', 'DESC')
                                                   ->first();

                        $userData->dep_bank = $lasat_dep->name;
                        $userData->dep_amount = '$'.number_format($lasat_dep->amount,2,'.',',');
                        $userData->dep_date = date('d-m-Y H:i:s', strtotime($lasat_dep->date_reg));
                        $userData->isdepnc = true;
                    }else{
                        $userData->isdepnc = false;
                    }

                    return response()->json(['success' => true, 'data' => $userData->toArray()]);
                }
            }

            return response()->json(['success' => false]);
        }
    }

    public static function bashConciliateIns(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->cons) && is_array($request->cons) && !empty($request->user)){
                $user = User::select('residue_amount')
                              ->where('email', $request->user)
                              ->first();

                $amountdep = BankDeposits::select('amount')
                                           ->where([
                                            ['email', $request->user],
                                            ['status', 'P']
                                           ])
                                           ->sum('amount');

                $totalRes = !empty($user) ? ($user->residue_amount + $amountdep) : 0;

                if(!empty($user) && $totalRes > 0){
                    $concDo = 0;
                    foreach($request->cons as $conciliation) {
                        $cond = explode('_-_', base64_decode($conciliation));
                        if(count($cond) == 2){
                            $up = PayInstallment::select(
                                                    'islim_pay_installments.id',
                                                    'islim_pay_installments.amount',
                                                    'islim_pay_installments.sale_installment_detail',
                                                    'islim_config_installments.quotes'
                                                  )
                                                  ->join(
                                                    'islim_sales_installments_detail',
                                                    'islim_sales_installments_detail.id',
                                                    'islim_pay_installments.sale_installment_detail'
                                                  )
                                                  ->join(
                                                    'islim_sales_installments',
                                                    'islim_sales_installments.unique_transaction',
                                                    'islim_sales_installments_detail.unique_transaction'
                                                  )
                                                  ->join(
                                                    'islim_config_installments',
                                                    'islim_config_installments.id',
                                                    'islim_sales_installments.config_id'
                                                  )
                                                  ->where([
                                                    ['islim_pay_installments.status','C'],
                                                    ['islim_sales_installments.coordinador', $request->user],
                                                    ['islim_sales_installments.unique_transaction', $cond[0]],
                                                    ['islim_pay_installments.id', $cond[1]]
                                                  ])
                                                  ->first();

                            if(!empty($up) && $totalRes >= $up->amount){
                                //Marcando como conciliada la cuota
                                $up->user_process = session('user')->email;
                                $up->date_update = date('Y-m-d H:i:s');
                                $up->status = 'P';
                                $up->save();

                                SaleInstallmentDetail::where('id', $up->sale_installment_detail)
                                                    ->update([
                                                        'conciliation_status' => 'P',
                                                        'date_update' => date('Y-m-d H:i:s')
                                                    ]);

                                //Descontando monto del saldo a favor
                                $totalRes -= $up->amount;

                                //Marcando la venta como conciliada si ya se pagaron todas las cuotas
                                $tc = SaleInstallmentDetail::where([
                                                        ['unique_transaction', $cond[0]],
                                                        ['conciliation_status', 'p']
                                                       ])
                                                       ->count();

                                if($tc == $up->quotes){
                                    Sale::where('unique_transaction', $cond[0])
                                          ->update(['conciliation' => 'Y']);
                                }

                                //Agregando linea a balance del usuario
                                Balance::createLine($request->user, $up->amount, 'E', $up->sale_installment_detail, 'I');

                                $concDo++;
                            }
                        }
                    }

                    //Procesando pago en caso de estar conciliando con deposito cargado y no solo saldo a favor
                    BankDeposits::where([
                                    ['email', $request->user],
                                    ['status', 'P']
                                 ])
                                  ->update([
                                    'status' => 'A',
                                    'user_process' => session('user')->email,
                                    'date_process' => date('Y-m-d H:i:s')
                                  ]);

                    //Actualizando saldo a favor del usuario al que se le esta haciendo la conciliación
                    User::where('email', $request->user)
                          ->update(['residue_amount' => $totalRes]);

                    return response()->json([
                                        'success' => true,
                                        'msg' => 'Se conciliaron '.$concDo.' Cuotas.'
                                    ]);
                }
            }

            return response()->json(['success' => false, 'No se pudo hacer la conciliación.']);
        }
    }

    /*
        Retorna html con ultimas conciliaciones hechas del vendedor dado
    */
    public static function getConcInst(Request $request){
        if($request->isMethod('post') && $request->ajax()){
            if(!empty($request->user)){
                $sales = PayInstallment::select(
                                            'islim_pay_installments.id_report',
                                            'islim_pay_installments.amount',
                                            'islim_pay_installments.date_acept',
                                            'islim_sales_installments.unique_transaction',
                                            'islim_sales_installments.msisdn',
                                            'islim_sales_installments.date_reg_alt',
                                            'islim_sales_installments_detail.n_quote',
                                            'islim_clients.name',
                                            'islim_clients.last_name',
                                            'islim_packs.title as pack',
                                            'islim_services.title as service',
                                            'islim_inv_articles.title as artic'
                                        )
                                        ->join(
                                            'islim_sales_installments_detail',
                                            'islim_sales_installments_detail.id',
                                            'islim_pay_installments.sale_installment_detail'
                                        )
                                        ->join(
                                            'islim_sales_installments',
                                            'islim_sales_installments.unique_transaction',
                                            'islim_sales_installments_detail.unique_transaction'
                                        )
                                        ->join(
                                            'islim_clients',
                                            'islim_clients.dni',
                                            'islim_sales_installments.client_dni'
                                        )
                                        ->join(
                                            'islim_services',
                                            'islim_services.id',
                                            'islim_sales_installments.service_id'
                                        )
                                        ->join(
                                            'islim_packs',
                                            'islim_packs.id',
                                            'islim_sales_installments.pack_id'
                                        )
                                        ->join(
                                            'islim_inv_arti_details',
                                            'islim_inv_arti_details.msisdn',
                                            'islim_sales_installments.msisdn'
                                        )
                                        ->join(
                                            'islim_inv_articles',
                                            'islim_inv_articles.id',
                                            'islim_inv_arti_details.inv_article_id'
                                        )
                                        ->where([
                                            ['islim_pay_installments.status', 'P'],
                                            ['islim_sales_installments.coordinador', $request->user]
                                        ])
                                        ->orderBy('islim_pay_installments.date_update', 'DESC')
                                        ->limit(10)
                                        ->get();

                if(count($sales)){
                    $html = view('pages.ajax.conciliation.lastConciliation', compact('sales'))->render();

                    return response()->json([
                                'success' => true,
                                'html' => $html,
                                'No se pudo hacer la conciliación.'
                            ]);
                }
            }

            return response()->json([
                        'success' => false,
                        'No se pudieron cargar las últimas conciliaciones.'
                    ]);
        }
    }
}