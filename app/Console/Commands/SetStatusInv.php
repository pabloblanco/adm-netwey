<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Carbon\Carbon;
Use App\SellerInventory;
Use App\SellerInventoryTrack;
Use App\HistoryInventoryStatus;
use Illuminate\Support\Facades\DB;

class SetStatusInv extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:setStatusInventory';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualiza estatus (Naraja y rojo) a las asignaciones de inventario.';

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
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $today = Carbon::now();

        //Busca ususarios que fueron aceptados para proceso de baja
        $usersLow = DB::table('islim_request_dismissal')
            ->select('user_dismissal')
            ->where("status", "P")->get();

        //Consultando asignaciones naranjas
        $dateOrange = $today->copy()->subDays(20)->format('Y-m-d H:i:s');

        $oranges = SellerInventory::getConnect('R')    
                    ->where([
                        ['status', 'A'],
                        ['date_reg', '<=', $dateOrange]
                    ])
                    ->whereNotIn('users_email', $usersLow->pluck('user_dismissal') ) //Omite inventario de usuarios en proceso de baja "aceptada"
                    ->whereNull('date_orange')
                    ->get();

        SellerInventory::getConnect('W')    
                        ->where([
                            ['status', 'A'],
                            ['date_reg', '<=', $dateOrange]
                        ])
                        ->whereNull('date_orange')
                        ->whereNotIn('users_email', $usersLow->pluck('user_dismissal') )
                        ->update([
                            'date_orange' => $today->format('Y-m-d H:i:s')
                        ]);

        //Registrando historico de cambio de estatus
        foreach($oranges as $orange){
            $history = new HistoryInventoryStatus;
            $history->users_email = $orange->users_email;
            $history->inv_arti_details_id = $orange->inv_arti_details_id;
            $history->status = 'P';
            $history->date_reg = $today->format('Y-m-d H:i:s');
            $history->color_destino = 'N';
            $history->save();
        }

        //Consultando asignaciones rojas
        $dateRed = $today->copy()->subDays(45)->format('Y-m-d H:i:s');

        $reds = SellerInventory::getConnect('R')
                                    ->select(
                                        'islim_inv_assignments.users_email',
                                        'islim_inv_assignments.inv_arti_details_id',
                                        'islim_users.parent_email',
                                        'islim_users.platform'
                                    )
                                    ->join(
                                        'islim_users',
                                        'islim_users.email',
                                        'islim_inv_assignments.users_email'
                                    )
                                    ->where([
                                        ['islim_users.status', 'A'],
                                        ['islim_inv_assignments.status', 'A'],
                                        ['islim_inv_assignments.date_reg', '<=', $dateRed]
                                    ])
                                    ->whereNull('islim_inv_assignments.date_red')
                                    ->whereNotNull('islim_inv_assignments.date_orange')
                                    ->whereNotIn('islim_inv_assignments.users_email', $usersLow->pluck('user_dismissal') ) //Omite inventario de usuarios en proceso de baja "aceptada"
                                    ->get();

        foreach($reds as $red){
            if($red->platform == 'vendor'){
                if(!empty($red->parent_email)){
                    SellerInventory::getConnect('W')
                                    ->where([
                                        ['users_email', $red->users_email],
                                        ['inv_arti_details_id', $red->inv_arti_details_id],
                                        ['status','<>','T']
                                    ])
                                    ->update([
                                        'date_red' => $today->format('Y-m-d H:i:s'),
                                        'status' => 'T',
                                        'obs' => 'Se retiro asignación por estatus rojo',
                                        'last_assigned_by' => null,
                                        'last_assignment' => date('Y-m-d H:i:s',time())
                                    ]);


                    $exist = SellerInventory::getConnect('R')
                                            ->where([
                                                ['users_email', $red->parent_email],
                                                ['inv_arti_details_id', $red->inv_arti_details_id]
                                            ])
                                            ->first();

                    if(!empty($exist)){
                        SellerInventory::getConnect('W')
                                            ->where([
                                                ['users_email', $red->parent_email],
                                                ['inv_arti_details_id', $red->inv_arti_details_id]
                                            ])
                                            ->update([
                                                'date_red' => $today->format('Y-m-d H:i:s'),
                                                'date_reg' => $today->format('Y-m-d H:i:s'),
                                                'user_red' => $red->users_email,
                                                'status' => 'A',
                                                'obs' => 'la asignación viene del usuario '.$red->users_email.' (estatus rojo)',
                                                'last_assigned_by' => null,
                                                'last_assignment' => date('Y-m-d H:i:s'),
                                                'red_notification_view' => 'N'
                                            ]);
                    }else{
                        SellerInventory::getConnect('W')
                                        ->insert([
                                            'users_email' => $red->parent_email,
                                            'inv_arti_details_id' => $red->inv_arti_details_id,
                                            'date_reg' => $today->format('Y-m-d H:i:s'),
                                            'obs' => 'la asignación viene del usuario '.$red->users_email.' (estatus rojo)',
                                            'date_red' => $today->format('Y-m-d H:i:s'),
                                            'user_red' => $red->users_email,
                                            'status' => 'A',
                                            'last_assigned_by' => null,
                                            'last_assignment' => date('Y-m-d H:i:s'),
                                            'red_notification_view' => 'N'
                                        ]);
                    }

                    $origin_user = $red->users_email;
                    $origin_wh = null;

                    $destination_user = $red->parent_email;
                    $destination_wh = null;

                     SellerInventoryTrack::setInventoryTrack(
                                    $red->inv_arti_details_id,
                                    $origin_user,
                                    $origin_wh,
                                    $destination_user,
                                    $destination_wh,
                                    null,
                                    'asignación automatica por estatus rojo'
                                );
                }                                    
            }else{
                SellerInventory::getConnect('W')
                                ->where([
                                    ['users_email', $red->users_email],
                                    ['inv_arti_details_id', $red->inv_arti_details_id]
                                ])
                                ->update([
                                    'date_red' => $today->format('Y-m-d H:i:s'),
                                    'user_red' => $red->users_email,
                                ]);
            }

            //Registrando historico de cambio de estatus
            $history = new HistoryInventoryStatus;
            $history->users_email = $red->users_email;
            $history->inv_arti_details_id = $red->inv_arti_details_id;
            $history->status = 'P';
            $history->date_reg = $today->format('Y-m-d H:i:s');
            $history->color_destino = 'R';
            $history->save();
        }
    }
}
