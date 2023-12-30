<?php

namespace App\Console\Commands;

use App\User;
use App\AssignedSales;
use App\AssignedSaleDetails;
use App\Sale;
use App\HistoryDebts;
use App\HistoryDebtUps;
use App\HistoryDebtCashs;
use App\HistoryDebtConciliatesBanks;
use App\Balance;
use App\BankDeposits;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class historyDebtsCalc extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:historyDebtsCalc {user?}';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Calcula historico de deudas para coordinadores y vendedores';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public $date;
  public $cont;

  public function __construct()
  {
    parent::__construct();
  }


  //***********************//
  //Calculando deuda inicial del coordinador (Total historico de Efectivo recibido - Total historico de Efectivo Conciliado - saldo a favor)
  //***********************//
  public function CalculateCoordInitDebt(User $user,$antDateEnd){

    unset($filters);
    $filters['parent_email'] = $user->email;
    $filters['status'] = ['P','A'];
    $filters['date_accepted'] = $antDateEnd;

    //$this->output->writeln(AssignedSales::getTotalCashMove($filters,true));
    $historical_cash_received=AssignedSales::getTotalCashMove($filters);
    $total_rec = $historical_cash_received->sum('amount');

    unset($filters);
    $filters['parent_email'] = $user->email;
    $filters['status'] = ['A'];
    $filters['date_process'] = $antDateEnd;

    //$this->output->writeln(AssignedSales::getTotalCashMove($filters,true));
    $historical_conciliate = AssignedSales::getTotalCashMove($filters);
    $total_conc = $historical_conciliate->sum('amount');

    $balance = Balance::getBalanceToDate($user->email,$antDateEnd);

    $init_debt = ($total_rec-$total_conc-$balance);

    return $init_debt;
  }


  //***********************//
  //Calculando deuda inicial del vendedor (Ventas en espera de deposito sin asignar + Ventas en espera de deposito asignadas y con la entrega de efectivo rechazada o sin aceptar)
  //***********************//
  public function CalculateVendorInitDebt(User $user,$antDateEnd){

    unset($filters);
    $filters['users_email'] = $user->email;
    $filters['status'] = ['V','I'];
    $filters['date_reg'] = $antDateEnd;

    //$this->output->writeln(AssignedSales::getTotalCashMove($filters,true));
    $cash_no_received=AssignedSales::getTotalCashMove($filters);
    $no_received_amount = $cash_no_received->sum('amount');

    $no_received = $cash_no_received->get();

    if($no_received){
        $assignedDetails = AssignedSaleDetails::getConnect('R')
          ->whereIn('asigned_sale_id',$no_received->pluck('id'))
          ->get();
    }

    //Ventas no conciliadas a la fecha (En espera de deposito) y que no se ha entregado el efectivo
    $sales = Sale::getConnect('R')
              ->whereIn('type',['V','P'])
              ->where('amount','>',0)
              ->where('users_email',$user->email)
              ->where('date_reg','<=',$antDateEnd)
              ->where('status','=','E');

    if($assignedDetails){
      $sales = $sales->whereNotIn('unique_transaction',$assignedDetails->pluck('unique_transaction'));
    }

    $sales_amount = $sales->sum('amount');

    return $no_received_amount+$sales_amount;
  }

  //***********************//
  //Calculando deuda inicial de vendedores subordinados al coordinador (Total historico de efectivo entregado, aceptado y conciliado  - (total historico de efectivo sin aceptar + altas no reportadas aun en entregas de efectivo (que no estan en asigned sales con estatus V,P,A)))
  //***********************//
  public function CalculateCoordInitDebtSellers(User $user, $vendors,$antDateEnd){

    $usrsmails = array();
    if(!empty($vendors)){
      if(count($vendors)){
        $usrsmails = $vendors->pluck('email')->toArray();
      }
    }
    array_push($usrsmails,$user->email);

    //Ventas no conciliadas a la fecha (En espera de deposito)
    $sales = Sale::getConnect('R')
              ->whereIn('type',['V','P'])
              ->where('amount','>',0)
              ->whereIn('users_email',$usrsmails)
              ->where('date_reg','<=',$antDateEnd)
              ->where('status','=','E')
              ->sum('amount');

    return ($sales);

  }

  //***********************//
  //Calculando altas del dia, incluye subordinados en caso de coordinadores
  //***********************//
  public function CalculateUserUpsDebtDay(User $user,$dateIni,$dateEnd,$vendors=null){

    $usrsmails = array();
    if(!empty($vendors)){
      if(count($vendors)){
        $usrsmails = $vendors->pluck('email')->toArray();
      }
    }
    array_push($usrsmails,$user->email);

    $UpsDay = Sale::getConnect('R')
              ->whereIn('type',['V','P'])
              ->where('amount','>',0)
              ->whereIn('users_email',$usrsmails)
              ->where('date_reg','>=',$dateIni)
              ->where('date_reg','<=',$dateEnd)
              ->where('status','<>','T');

    return $UpsDay;
  }

  //***********************//
  //Calculando Efectivo recibido
  //***********************//
  public function CalculateUserCashReceived(User $user,$dateIni,$dateEnd,$vendors){

    $CashRec = AssignedSales::getConnect('R')
              ->where('amount','>',0)
              ->where('parent_email',$user->email)
              ->whereIn('users_email',$vendors->pluck('email'))
              ->where('date_accepted','>=',$dateIni)
              ->where('date_accepted','<=',$dateEnd)
              ->whereNotIn('status',['T','I']);

    return $CashRec;
  }

  //***********************//
  //Calculando Efectivo Entregado
  //***********************//
  public function CalculateUserCashDelivered(User $user,$dateIni,$dateEnd){

    $CashDel = AssignedSales::getConnect('R')
              ->where('amount','>',0)
              ->where('users_email',$user->email)
              ->where('date_accepted','>=',$dateIni)
              ->where('date_accepted','<=',$dateEnd)
              ->whereNotIn('status',['T','I']);

    return $CashDel;
  }

  //***********************//
  //Calculando conciliaciones del dia (Ventas)
  //***********************//
  public function CalculateCoordConciliateSales(User $user,$dateIni,$dateEnd){
     $conciliate = AssignedSales::getConnect('R')
              ->where('amount','>',0)
              ->where('parent_email',$user->email)
               ->whereNotNull('user_process')
              ->where('date_process','>=',$dateIni)
              ->where('date_process','<=',$dateEnd)
              ->where('status','A');

    return $conciliate;
  }

  //***********************//
  //Calculando conciliaciones del dia (Depositos)
  //***********************//
  public function CalculateCoordConciliate(User $user,$dateIni,$dateEnd){
    // $dateIni = date("Y-m-d 00:00:00",strtotime($this->date));
    // $dateEnd = date("Y-m-d 23:59:59",strtotime($this->date));

    $conciliate = BankDeposits::getConnect('R')
              ->where('amount','>',0)
              ->where('email',$user->email)
              ->whereNotNull('user_process')
              ->where('date_process','>=',$dateIni)
              ->where('date_process','<=',$dateEnd)
              ->where('status','A');

    return $conciliate;
  }

  public function CalculateCoord(User $user){

      $yesterday = date("Y-m-d",strtotime($this->date."- 1 days"));
      $yesterdayIni = date("Y-m-d 00:00:00",strtotime($this->date."- 1 days"));
      $yesterdayEnd = date("Y-m-d 23:59:59",strtotime($this->date."- 1 days"));

      $today = date("Y-m-d",strtotime($this->date));
      $todayIni = date("Y-m-d 00:00:00",strtotime($this->date));
      $todayEnd = date("Y-m-d 23:59:59",strtotime($this->date));

      $vendors = User::getParentUsers($user->email,['A','D']);


      //consulta registro del dia anterior
      $LastHistoryDebt = HistoryDebts::getHistoryDebtsRegister($user->email,$yesterday);

      //deuda inicial de hoy del coordinador  = a deuda con la que finalizo ayer
      $init_debt = round(($this->CalculateCoordInitDebt($user,$yesterdayEnd)),2);

      //deuda inicial de hoy de los vendedores del coordinador  = a deuda con la que finalizo ayer
      $init_debt_sellers = $this->CalculateCoordInitDebtSellers($user,$vendors,$yesterdayEnd);

      if(!empty($LastHistoryDebt)){ //existe un registro del dia de ayer
        //Efectivo que recibio Ayer
        $cash_received = $this->CalculateUserCashReceived($user,$yesterdayIni,$yesterdayEnd,$vendors);
        $cash_received_amount = $cash_received->sum('amount');

        //Efectivo que entrego ayer
        $cash_delivered = $this->CalculateUserCashDelivered($user,$yesterdayIni,$yesterdayEnd);
        $cash_delivered_amount = $cash_delivered->sum('amount');

        //Conciliado en depositos del dia de Ayer
        $conciliate_banks_day = $this->CalculateCoordConciliate($user,$yesterdayIni,$yesterdayEnd);
        $conciliate_banks_amount = $conciliate_banks_day->sum('amount');

        //Deuda final del dia del dia de Ayer
        $finish_debt = ($LastHistoryDebt->init_debt + $cash_received_amount) - $conciliate_banks_amount;

        //altas del dia de ayer
        $ups_debt_day = $this->CalculateUserUpsDebtDay($user,$yesterdayIni,$yesterdayEnd,$vendors);
        $ups_debt_day_amount = $ups_debt_day->sum('amount');

        //Ventas conciliadas de ayer
        $conciliate_sales_day = $this->CalculateCoordConciliateSales($user,$yesterdayIni,$yesterdayEnd);
        $conciliate_sales_amount = $conciliate_sales_day->sum('amount');

        //deuda final de ayer de los vendedores del coordinador
        $finish_debt_sellers = ($LastHistoryDebt->init_debt_sellers + $ups_debt_day_amount) - $cash_received_amount;

        //Guarda resultados finales del dia de ayer
        $LastHistoryDebt->ups_debt_day = $ups_debt_day_amount;
        $LastHistoryDebt->cash_received = $cash_received_amount;
        $LastHistoryDebt->cash_delivered = $cash_delivered_amount;
        $LastHistoryDebt->conciliate_banks_day = $conciliate_banks_amount;
        $LastHistoryDebt->conciliate_sales_day = $conciliate_sales_amount;
        $LastHistoryDebt->finish_debt = $finish_debt;
        $LastHistoryDebt->finish_debt_sellers = $finish_debt_sellers;
        $LastHistoryDebt->date_modified = date('Y-m-d H:i:s');
        $LastHistoryDebt->save();

        //Asocia altas de dia de ayer con el registro historico
        $ups_day = $ups_debt_day->get();
        foreach ($ups_day as $key => $up_day) {
          $detail = HistoryDebtUps::getHistoryDebtUpRegister($LastHistoryDebt->id,$up_day->id);
          if(empty($detail)){
            $detail = HistoryDebtUps::getConnect('W');
            $detail->id_history_debt = $LastHistoryDebt->id;
            $detail->id_sales = $up_day->id;
          }
          $detail->status = 'A';
          $detail->save();
        }

        //Asocia efectivo recibido de ayer con el registro historico
        $receiveds = $cash_received->get();
        foreach ($receiveds as $key => $received) {
          $detail = HistoryDebtCashs::getHistoryDebtCashRegister($LastHistoryDebt->id,$received->id,'R');
          if(empty($detail)){
            $detail = HistoryDebtCashs::getConnect('W');
            $detail->id_history_debt = $LastHistoryDebt->id;
            $detail->id_asigned_sales = $received->id;
            $detail->type = 'R';
          }
          $detail->status = 'A';
          $detail->save();
        }

        //Asocia efectivo entregado de ayer con el registro historico
        $delivereds = $cash_delivered->get();
        foreach ($delivereds as $key => $delivered) {
          $detail = HistoryDebtCashs::getHistoryDebtCashRegister($LastHistoryDebt->id,$delivered->id,'D');
          if(empty($detail)){
            $detail = HistoryDebtCashs::getConnect('W');
            $detail->id_history_debt = $LastHistoryDebt->id;
            $detail->id_asigned_sales = $delivered->id;
            $detail->type = 'D';
          }
          $detail->status = 'A';
          $detail->save();
        }

        // Asocia ventas conciliadas de ayer con el registro historico
        $conciliate_sales = $conciliate_sales_day->get();
        foreach ($conciliate_sales as $key => $conciliate_sale) {
          $detail = HistoryDebtCashs::getHistoryDebtCashRegister($LastHistoryDebt->id,$conciliate_sale->id,'C');
          if(empty($detail)){
            $detail = HistoryDebtCashs::getConnect('W');
            $detail->id_history_debt = $LastHistoryDebt->id;
            $detail->id_asigned_sales = $conciliate_sale->id;
            $detail->type = 'C';
          }
          $detail->status = 'A';
          $detail->save();
        }

        // Asocia depositos bancarios conciliadas de ayer con el registro historico
        $conciliate_banks = $conciliate_banks_day->get();
        foreach ($conciliate_banks as $key => $conciliate_bank) {
          $detail = HistoryDebtConciliatesBanks::getHistoryDebtConcBankRegister($LastHistoryDebt->id,$conciliate_bank->id);
          if(empty($detail)){
            $detail = HistoryDebtConciliatesBanks::getConnect('W');
            $detail->id_history_debt = $LastHistoryDebt->id;
            $detail->id_bank_dep = $conciliate_bank->id;
          }
          $detail->status = 'A';
          $detail->save();
        }
      }


      //crea o actualiza registro con datos iniciciales del dia de hoy
      $HistoryDebt = HistoryDebts::getHistoryDebtsRegister($user->email,$today);
      if(empty($HistoryDebt)){
         $HistoryDebt = HistoryDebts::getConnect('W');
         $HistoryDebt->user_email = $user->email;
         $HistoryDebt->date_reg = date('Y-m-d H:i:s');
      }
      $HistoryDebt->date = $today;
      $HistoryDebt->init_debt = $init_debt;
      $HistoryDebt->init_debt_sellers = $init_debt_sellers;
      $HistoryDebt->status = 'A';
      $HistoryDebt->date_modified = date('Y-m-d H:i:s');
      $HistoryDebt->save();

      $this->output->writeln("-------------------------------------------------------");

  }

  public function CalculateVendor(User $user){

    $yesterday = date("Y-m-d",strtotime($this->date."- 1 days"));
    $yesterdayIni = date("Y-m-d 00:00:00",strtotime($this->date."- 1 days"));
    $yesterdayEnd = date("Y-m-d 23:59:59",strtotime($this->date."- 1 days"));

    $today = date("Y-m-d",strtotime($this->date));
    $todayIni = date("Y-m-d 00:00:00",strtotime($this->date));
    $todayEnd = date("Y-m-d 23:59:59",strtotime($this->date));

    //consulta registro del dia anterior
    $LastHistoryDebt = HistoryDebts::getHistoryDebtsRegister($user->email,$yesterday);

    //deuda inicial de hoy del vendedor  = a deuda con la que finalizo ayer
    $init_debt = round(($this->CalculateVendorInitDebt($user,$yesterdayEnd)),2);

    $ups_debt_day_amount = 0;
    $cash_delivered_amount = 0;

    if(!empty($LastHistoryDebt)){ //existe un registro del dia de ayer
      //altas del dia de ayer
      $ups_debt_day = $this->CalculateUserUpsDebtDay($user,$yesterdayIni,$yesterdayEnd);
      $ups_debt_day_amount = $ups_debt_day->sum('amount');

      //Efectivo que entrego ayer
      $cash_delivered = $this->CalculateUserCashDelivered($user,$yesterdayIni,$yesterdayEnd);
      $cash_delivered_amount = $cash_delivered->sum('amount');

      //Deuda final del dia del dia de Ayer
      $finish_debt = ($LastHistoryDebt->init_debt + $ups_debt_day_amount) - $cash_delivered_amount;

      //Guarda resultados finales del dia de ayer
      $LastHistoryDebt->ups_debt_day = $ups_debt_day_amount;
      $LastHistoryDebt->cash_delivered = $cash_delivered_amount;
      $LastHistoryDebt->finish_debt = $finish_debt;
      $LastHistoryDebt->date_modified = date('Y-m-d H:i:s');
      $LastHistoryDebt->save();

      //Asocia altas de dia de ayer con el registro historico
      $ups_day = $ups_debt_day->get();
      foreach ($ups_day as $key => $up_day) {
        $detail = HistoryDebtUps::getHistoryDebtUpRegister($LastHistoryDebt->id,$up_day->id);
        if(empty($detail)){
          $detail = HistoryDebtUps::getConnect('W');
          $detail->id_history_debt = $LastHistoryDebt->id;
          $detail->id_sales = $up_day->id;
        }
        $detail->status = 'A';
        $detail->save();
      }

      //Asocia efectivo entregado de ayer con el registro historico
      $delivereds = $cash_delivered->get();
      foreach ($delivereds as $key => $delivered) {
        $detail = HistoryDebtCashs::getHistoryDebtCashRegister($LastHistoryDebt->id,$delivered->id,'D');
        if(empty($detail)){
          $detail = HistoryDebtCashs::getConnect('W');
          $detail->id_history_debt = $LastHistoryDebt->id;
          $detail->id_asigned_sales = $delivered->id;
          $detail->type = 'D';
        }
        $detail->status = 'A';
        $detail->save();
      }
    }

    //crea o actualiza registro con datos iniciciales del dia de hoy
    $HistoryDebt = HistoryDebts::getHistoryDebtsRegister($user->email,$today);
    if(empty($HistoryDebt)){
       $HistoryDebt = HistoryDebts::getConnect('W');
       $HistoryDebt->user_email = $user->email;
       $HistoryDebt->date_reg = date('Y-m-d H:i:s');
    }
    $HistoryDebt->date = $today;
    $HistoryDebt->init_debt = $init_debt;
    $HistoryDebt->status = 'A';
    $HistoryDebt->date_modified = date('Y-m-d H:i:s');
    $HistoryDebt->save();

    $this->output->writeln("-------------------------------------------------------");

  }

  public function SelectUserDebts($type,$userEstudio = null){
    if($type == 'V'){
      $profiles = [11,19];
    }
    else{
      $profiles = [10,18];
    }

    $UsersDebts = User::getConnect('R')
      ->join('islim_profile_details', function ($join) use ($profiles) {
        $join->on('islim_users.email', '=', 'islim_profile_details.user_email')
          ->where('islim_profile_details.status', 'A')
          ->whereIn('islim_profile_details.id_profile', $profiles);
      })
      ->whereIn('islim_users.status',['A','D']);
    if(!empty($userEstudio)){
      $UsersDebts = $UsersDebts->where('islim_users.email',$userEstudio);
    }
    $UsersDebts = $UsersDebts->orderBy('islim_users.email','ASC');

    $UsersDebts=$UsersDebts->get();

    return $UsersDebts;

  }

  public function handle()
  {

    //se establece la fecha para el calculo
    $this->cont=0;
    $this->date = date('Y-m-d');

    if(!empty($this->argument('user'))){
      $userEstudio = $this->argument('user');
    }
    else{
      $userEstudio = null;
    }

    //selecciona todos los usuarios de tipo coordinador y subcoordinador (profiles 10 y 18)

    $UserCoord = self::SelectUserDebts('C',$userEstudio);
    if($UserCoord){
      foreach ($UserCoord as $key => $coord) {
        $this->output->writeln($coord->email);
        self::CalculateCoord($coord);
      }
    }
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $this->output->writeln("-------------------------------------------------------");
    $UserVendor = self::SelectUserDebts('V',$userEstudio);
    if($UserVendor){
      foreach ($UserVendor as $key => $vendor) {
        $this->output->writeln($vendor->email);
        self::CalculateVendor($vendor);
      }
    }

  }
}


/*
if($um==0){
      $query = vsprintf(str_replace('?', '%s', $UpsDay->toSql()), collect($UpsDay->getBindings())->map(function ($binding) {
                return is_numeric($binding) ? $binding : "'{$binding}'";
            })->toArray());

      $this->output->writeln($query);
    }
*/


