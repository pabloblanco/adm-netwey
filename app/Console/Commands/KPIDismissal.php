<?php

namespace App\Console\Commands;

use App\User;
use App\SellerInventory;
use App\SellerInventoryTrack;
use App\KPISDismissal;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KPIDismissal extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */

  protected $signature = 'command:KPIDismissal {date?}'; //date = YYYY-mm del mes ha calcular (por defecto el mes anterior al actual)

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Calcula KPI de descuentos a regionales y coordinaciones por merma de equipos del año y mes dado (por defecto si no se envia date toma el mes anterior al actual)';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   *
   * @return mixed
   */
  public function handle()
  {

    //obtengo año-mes de estudio para calcular mermas y año-mes anterior al de estudio para calcular asignaciones
    if(!empty($this->argument('date'))){
      $year = date('Y', strtotime($this->argument('date').'-01'));
      if(intval($year) < 2022){
        print_r('argumento invalido');
        print_r("\n");
        return;
      }
      $month = date('m', strtotime($this->argument('date').'-01'));
      $prevyear = date('Y', strtotime($this->argument('date').'-01'."- 1 month"));
      $prevmonth = date('m', strtotime($this->argument('date').'-01'."- 1 month"));
    }
    else{
      $year = date('Y', strtotime(date('Y-m-01')."- 1 month"));
      $month = date('m', strtotime(date('Y-m-01')."- 1 month"));
      $prevyear = date('Y', strtotime(date('Y-m-01')."- 2 month"));
      $prevmonth = date('m', strtotime(date('Y-m-01')."- 2 month"));
    }

    $desde = date('Y-m-d 00:00:00', strtotime($year."-".$month.'-01'));
    $hasta = date('Y-m-d 23:59:59', strtotime($year."-".$month.'-01'."- 1day +1 month"));
    $prevdesde = date('Y-m-d 00:00:00', strtotime($prevyear."-".$prevmonth.'-01'));
    $prevhasta = date('Y-m-d 23:59:59', strtotime($prevyear."-".$prevmonth.'-01'."- 1day +1 month"));


    //consulto cordinadores activos o en proceso de bajas
    $coordinators = User::getConnect('R')
                    ->select(
                      'islim_users.email',
                      'islim_users.parent_email',
                      'islim_users.status',
                      'islim_profiles.platform'
                    )
                    ->join('islim_profile_details','islim_profile_details.user_email','islim_users.email')
                    ->join('islim_profiles','islim_profiles.id','islim_profile_details.id_profile')
                    ->whereIn('islim_users.status',['A','D'])
                    ->where('islim_profile_details.status','A')
                    ->where('islim_profiles.status','A')
                    ->where('islim_profiles.platform','coordinador')
                    ->whereNotNull('islim_users.parent_email')
                    ->get();

    //los paso a un array e inicializo variables para el calculo de los KPI

    $arr_coordinators = [];
    foreach ($coordinators as $key => $coordinator) {
      // print_r($key.' user: '.$coordinator->email." status: ".$coordinator->status." platform:".$coordinator->platform);
      $arr_coord = [
        $coordinator->email => [
          'parent_email' => $coordinator->parent_email,
          'old_articles' => 0,
          'decrease_articles' => 0,
          'assigned_articles' => 0,
          'kpi_result' => 0,
          'lost_articles_cost' => 0,
          'total_perc_discount' => 0,
          'regional_perc_discount' => 0,
          'coordinator_perc_discount' => 0,
          'total_amount_discount' => 0,
          'regional_amount_discount' => 0,
          'coordinator_amount_discount' => 0
        ]
      ];
      $arr_coordinators = array_merge( $arr_coord, $arr_coordinators);
    }



    // ---------------- bodega de equipos viejos -----------------------//
    //consulto articulos que pasaron a bodega de equipos viejos en el periodo de estudio

    //1- selecciono el ultimo movimiento de inventarios todos aquellos que se hayan movido a la bodega de equipos viejos en el mes de estudio
    $old_last_moves = SellerInventoryTrack::getConnect('R')
                    ->select(
                      DB::raw('MAX(islim_inv_assignments_tracks.id) as id_move_wh'),
                      'islim_inv_assignments_tracks.inv_arti_details_id',
                      'islim_inv_arti_details.price_pay'
                    )
                    ->join('islim_inv_arti_details','islim_inv_arti_details.id','islim_inv_assignments_tracks.inv_arti_details_id')
                    ->whereIn('islim_inv_assignments_tracks.destination_wh',[env('WH_MERMA_OLD')])
                    ->where('islim_inv_assignments_tracks.date_reg','>=',$desde)
                    ->where('islim_inv_assignments_tracks.date_reg','<=',$hasta)
                    ->groupBy('islim_inv_assignments_tracks.inv_arti_details_id')
                    ->get();


    //2- consigo el ultimo registro con usuario que tuvo cada articulo antes de moverlo a la bodega de equipos viejos en el mes de estudio

    foreach ($old_last_moves as $key => $old_last_move) {

      $old_last_user_move = SellerInventoryTrack::getConnect('R')
                      ->select(
                        DB::raw('MAX(id) as id_move')
                      )
                      ->where(function($query) {
                          $query->whereNotNull('origin_user')
                                ->orWhereNotNull('destination_user');
                      })
                      ->where('date_reg','<=',$hasta)
                      ->where('inv_arti_details_id',$old_last_move->inv_arti_details_id)
                      ->where('id','<=',$old_last_move->id_move_wh)
                      ->first();

      if($old_last_user_move){
        //Consulto ese ultimo registro para extraer el usuario de ese movimiento

        $old_last_user = SellerInventoryTrack::getConnect('R')->find($old_last_user_move->id_move);

        if($old_last_user){
          if(!empty($old_last_user->destination_user)){
            $olmuser = User::getCoordinator($old_last_user->destination_user);
          }
          else{
            $olmuser = User::getCoordinator($old_last_user->origin_user);
          }

          if(!empty($olmuser))
            if(!empty($arr_coordinators[$olmuser])){
              ($arr_coordinators[$olmuser])['old_articles']=($arr_coordinators[$olmuser])['old_articles']+1;
              ($arr_coordinators[$olmuser])['lost_articles_cost']=($arr_coordinators[$olmuser])['lost_articles_cost']+$old_last_move->price_pay;
            }
        }
      }
    }

    // ---------------- FIN bodega de equipos viejos -----------------------//


    // ---------------- bodega de equipos merma -----------------------//
    //consulto articulos que pasaron a bodega de equipos merma en el periodo de estudio

    //1- selecciono el ultimo movimiento de inventarios todos aquellos que se hayan movido a la bodega de equipos merma en el mes de estudio
    $dec_last_moves = SellerInventoryTrack::getConnect('R')
                    ->select(
                      DB::raw('MAX(islim_inv_assignments_tracks.id) as id_move_wh'),
                      'islim_inv_assignments_tracks.inv_arti_details_id',
                      'islim_inv_arti_details.price_pay'
                    )
                    ->join('islim_inv_arti_details','islim_inv_arti_details.id','islim_inv_assignments_tracks.inv_arti_details_id')
                    ->whereIn('islim_inv_assignments_tracks.destination_wh',[env('WH_MERMA_LOW')])
                    ->where('islim_inv_assignments_tracks.date_reg','>=',$desde)
                    ->where('islim_inv_assignments_tracks.date_reg','<=',$hasta)
                    ->groupBy('islim_inv_assignments_tracks.inv_arti_details_id')
                    ->get();

    //2- consigo el ultimo registro con usuario que tuvo cada articulo antes de moverlo a la bodega de equipos merma en el mes de estudio

    foreach ($dec_last_moves as $key => $dec_last_move) {

      $dec_last_user_move = SellerInventoryTrack::getConnect('R')
                      ->select(
                        DB::raw('MAX(id) as id_move')
                      )
                      ->where(function($query) {
                          $query->whereNotNull('origin_user')
                                ->orWhereNotNull('destination_user');
                      })
                      ->where('date_reg','<=',$hasta)
                      ->where('inv_arti_details_id',$dec_last_move->inv_arti_details_id)
                      ->where('id','<=',$dec_last_move->id_move_wh)
                      ->first();

      if($dec_last_user_move){
        //Consulto ese ultimo registro para extraer el usuario de ese movimiento

        $dec_last_user = SellerInventoryTrack::getConnect('R')->find($dec_last_user_move->id_move);

        if($dec_last_user){
          if(!empty($dec_last_user->destination_user)){
            $dlmuser = User::getCoordinator($dec_last_user->destination_user);
          }
          else{
            $dlmuser = User::getCoordinator($dec_last_user->origin_user);
          }

          if(!empty($dlmuser))
            if(!empty($arr_coordinators[$dlmuser])){
              ($arr_coordinators[$dlmuser])['decrease_articles']=($arr_coordinators[$dlmuser])['decrease_articles']+1;
              ($arr_coordinators[$dlmuser])['lost_articles_cost']=($arr_coordinators[$dlmuser])['lost_articles_cost']+$dec_last_move->price_pay;
            }
        }
      }
    }

    // ---------------- FIN bodega de equipos merma -----------------------//


    // ---------------- asignaciones del mes anterior al mes estudio -----------------------//
    $assigments = SellerInventory::getConnect('R')
                  ->select(
                    'users_email',
                    DB::raw('count(*) as assigment')
                  )
                  ->whereIn('users_email',$coordinators->pluck('email'))
                  ->where('first_assignment','>=',$prevdesde)
                  ->where('first_assignment','<=',$prevhasta)
                  ->groupBy('users_email')
                  ->get();

    foreach ($assigments as $key => $assigment) {
      if(!empty($arr_coordinators[$assigment->users_email]))
        ($arr_coordinators[$assigment->users_email])['assigned_articles']=$assigment->assigment;
    }

    // ---------------- FIN asignaciones del mes anterior al mes estudio -----------------------//


    //recorro coordinadores para calcular KPI, descuentos y guardar en BD
    foreach ($arr_coordinators as $key => $coordinator) {

      //calculo kpi por coordinador
      $merma_cant_tot =  $coordinator['old_articles'] + $coordinator['decrease_articles'] ;
      $assigments = $coordinator['assigned_articles'];
      if($assigments == 0){ // no tiene assignaciones en el mes anterior al de estudio
        if($merma_cant_tot == 0){ // y no tiene equipos en merma
          $kpi = 0;
        }
        else{ //cuando si tiene equipos en merma y no tiene asignaciones
          $kpi = 3;
        }
      }
      else{
        $kpi = ($merma_cant_tot/$assigments)*100; //si tiene assignaciones en el mes anterior al de estudio se calcula kpi por formula
      }

      $coordinator['kpi_result'] = $kpi;

      if($kpi > 1 && $kpi <= 1.5){
        $coordinator['total_perc_discount'] = 20;
        $coordinator['regional_perc_discount'] = 20;
        $coordinator['coordinator_perc_discount'] = 80;
      }
      if($kpi > 1.5 && $kpi <=2.5 ){
        $coordinator['total_perc_discount'] = 50;
        $coordinator['regional_perc_discount'] = 20;
        $coordinator['coordinator_perc_discount'] = 80;
      }
      if($kpi > 2.5){
        $coordinator['total_perc_discount'] = 100;
        $coordinator['regional_perc_discount'] = 20;
        $coordinator['coordinator_perc_discount'] = 80;
      }

      $coordinator['total_amount_discount'] = $coordinator['lost_articles_cost']*($coordinator['total_perc_discount']/100);

      $coordinator['regional_amount_discount'] = $coordinator['total_amount_discount']*($coordinator['regional_perc_discount']/100);

      $coordinator['coordinator_amount_discount'] = $coordinator['total_amount_discount']*($coordinator['coordinator_perc_discount']/100);


      //si hay un registro en la tabla de kpi para el mismo periodo, mismo regional y mismo coordinador lo marco con estatus en T

      KPISDismissal::getConnect('W')
                    ->where([
                      ['regional_email',$coordinator['parent_email']],
                      ['coordinator_email',$key],
                      ['year',$year],
                      ['month',$month],
                      ['status','A']
                    ])
                    ->update([
                        'status' => 'T',
                    ]);

      //inserto nuevo registro kpi
      KPISDismissal::getConnect('W')
              ->insert([
                'regional_email' => $coordinator['parent_email'],
                'coordinator_email' => $key,
                'year' => $year,
                'month' => $month,
                'old_articles' => $coordinator['old_articles'],
                'decrease_articles' => $coordinator['decrease_articles'],
                'assigned_articles' => $coordinator['assigned_articles'],
                'kpi_result' => $coordinator['kpi_result'],
                'lost_articles_cost' => $coordinator['lost_articles_cost'],
                'total_perc_discount' => $coordinator['total_perc_discount'],
                'regional_perc_discount' => $coordinator['regional_amount_discount'],
                'coordinator_perc_discount' => $coordinator['coordinator_amount_discount'],
                'total_amount_discount' => $coordinator['total_amount_discount'],
                'regional_amount_discount' => $coordinator['regional_amount_discount'],
                'coordinator_amount_discount' => $coordinator['coordinator_amount_discount'],
              ]);

    }

  }
}
