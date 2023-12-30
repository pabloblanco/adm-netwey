<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SaleInstallment;
use App\TokensInstallments;

class checkRequestInstallments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:checkInstallments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica solicitudes de venta en abono, para ver si estan vencidas y marcarlas como eliminadas';

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
        $requests = SaleInstallment::select('id', 'date_reg', 'date_update', 'status', 'coordinador')
                                    ->whereIn('status', ['R', 'A'])
                                    ->get();

        foreach ($requests as $request) {
            if(strtotime('+ '.env('TTL_INSTALLMENT').' minutes', strtotime($request->date_update)) <= time())
            {
                if($request->status == 'A'){
                    $tokens = TokensInstallments::where([
                                                    ['assigned_user', $request->coordinador],
                                                    ['status', 'A']
                                                ])
                                                ->select(
                                                    'id', 
                                                    'tokens_available', 
                                                    'tokens_assigned'
                                                )
                                                ->first();

                    if(!empty($tokens) 
                        && ($tokens->tokens_available + 1) <= $tokens->tokens_assigned
                    ){
                        $tokens->tokens_available += 1;
                        $tokens->save();
                    }
                }

                $request->date_update = date('Y-m-d H:i:s');
                $request->status = 'T';
                $request->save();
            }
        }
        
    }
}
