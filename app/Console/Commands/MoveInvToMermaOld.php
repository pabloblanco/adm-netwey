<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

Use App\SellerInventoryTrack;
Use App\SellerInventory;
Use App\Inventory;
Use App\HistoryInventoryStatus;

class MoveInvToMermaOld extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:moveInventoryToMermaOld';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mueve el inventario marcado con motivos no válidos a la bodega merma de equipos viejos, el id de la bodega se toma del .env (WH_MERMA_OLD)';

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
        $assignments = SellerInventory::getConnect('R')
                                    ->select(
                                        'users_email',
                                        'inv_arti_details_id'
                                    )
                                    ->where('status', 'ME')
                                    ->get();

        foreach($assignments as $assignment){
            Inventory::getConnect('W')
                      ->where('id', $assignment->inv_arti_details_id)
                      ->update([
                          'warehouses_id' => env('WH_MERMA_OLD')
                      ]);

            SellerInventory::getConnect('W')
                            ->where([
                                ['users_email', $assignment->users_email],
                                ['inv_arti_details_id', $assignment->inv_arti_details_id]
                            ])
                            ->update([
                                'status' => 'T'
                            ]);

            //Reiniciando los estaus rojo y naranja a todas las asignaciones del dn
            SellerInventory::getConnect('W')
                            ->where('inv_arti_details_id', $assignment->inv_arti_details_id)
                            ->update([
                                'date_red' => null,
                                'date_orange' => null,
                                'user_red' => null
                            ]);

            SellerInventoryTrack::setInventoryTrack(
                                    $assignment->inv_arti_details_id,
                                    $assignment->users_email,
                                    null,
                                    null,
                                    env('WH_MERMA_OLD'),
                                    null,
                                    'Movido a bodega de merma, fue marcado como no vendido con motivos no válidos'
                                );

            HistoryInventoryStatus::rejectChangeStatus($assignment->inv_arti_details_id);
        }
    }
}
