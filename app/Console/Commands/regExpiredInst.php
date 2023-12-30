<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\SaleInstallment;
use App\ExpiredInstallment;

class regExpiredInst extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InstallmentExpired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Guarda registro de cuotas expiradas, se debe ejecutar dia a dia, calcula cuotas expiradas el dia anterior al que se esta ejecutando.';

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
        $sales = SaleInstallment::getExpiredPayment(0);
        $today = date('Y-m-d H:i:s');

        foreach ($sales as $sale) {
            $dosave = ExpiredInstallment::select('id')
                                          ->where([
                                            ['id_sale_installment', $sale->id],
                                            ['quote', $sale->qp]
                                          ])
                                          ->first();

            if(empty($dosave)){
                $save = new ExpiredInstallment;
                $save->id_sale_installment = $sale->id;
                $save->quote = $sale->qp;
                $save->amount = ($sale->amount - $sale->first_pay) / ($sale->quotes - 1);
                $save->date_expired = $sale->date_expired;
                $save->date_reg = $today;
                $save->status = 'A';
                $save->save();
            }
        }
    }
}
