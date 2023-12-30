<?php
/*OJO Configurar constante ID_LIST_MH en .env cuando esto se pase a producción*/
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\ClientNetwey;

class listMHOffer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:offerMH';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agrega o saca DNs (MH) de la lista de servicio con oferta';

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
        $list = env('ID_LIST_MH', 18);
        $raw3m = DB::raw("(TIMESTAMPDIFF(MONTH, date_reg, '".date('Y-m-d H:i:s')."'))");

        //Agregando a la lista de ofertas, solo se agregan los dns que no esten en la 
        //lista de oferta y que su alta no tenga mas de 3 meses
        $dns = ClientNetwey::getConnect('R')
                            ->select(
                                'msisdn'
                            )
                            ->where([
                                ['dn_type', 'MH'],
                                ['status', '!=', 'T'],
                                [$raw3m, '<', 3]
                            ])
                            ->where(function($q) use ($list){
                                $q->where('id_list_dns', '!=', $list)
                                  ->orWhereNull('id_list_dns');
                            })
                            ->get();

        if($dns->count()){
            ClientNetwey::getConnect('W')
                        ->whereIn('msisdn', $dns->pluck('msisdn')->toArray())
                        ->update(['id_list_dns' => $list]);
        }

        //Sacando dns con mas de 3 meses desde que se dieron de alta de la lista de ofertas
        $dns = ClientNetwey::getConnect('R')
                            ->select(
                                'msisdn'
                            )
                            ->where([
                                ['dn_type', 'MH'],
                                ['status', '!=', 'T'],
                                [$raw3m, '>=', 3],
                                ['id_list_dns', $list]
                            ])
                            ->get();

        if($dns->count()){
            ClientNetwey::getConnect('W')
                        ->whereIn('msisdn', $dns->pluck('msisdn')->toArray())
                        ->update(['id_list_dns' => null]);
        }
    }
}
