<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\ClientNetwey;
use App\ListDns;
use App\ServiceChanel;
use App\Sale;
use App\Service;


class listOffer extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:offerList';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Agrega saca DNs de la lista de servicio con oferta';

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
    public function handle() {


        //Buscamos las listas de promociones activas con valor positivo en -lifetime-
        $currentDate = date('Y-m-d 01:00:00');
        

        $lists = ListDns::getConnect('R')
            ->select('id', 'lifetime', 'status')
            ->whereNotNull('lifetime')
            ->where('lifetime', '>', 0)->get();

        //$this->output->writeln(date("Y-m-d H:i:s").' respuesta lista: '.(String)json_encode($lists));

        //Recorremos las listas de promociones
        foreach ($lists as $key => $list) {
            
            $lastDay = date('Y-m-d 01:00:00', strtotime($currentDate . '- ' . $list->lifetime . ' day'));

            //Buscamos los servicios de alta asociados a esa lista y los recorremos
            $channels = ServiceChanel::getConnect('R')
                ->select('id_list_dns', 'id_service')
                ->where('id_list_dns', $list->id)
                ->where('status', 'A')->get();

            //$this->output->writeln(date("Y-m-d H:i:s").' respuesta Canal: '.(String)json_encode($channels));
            
            foreach ($channels as $key => $channel) {

                $service = Service::getConnect('R')
                    ->where('id', $channel->id_service)
                    ->where('type', 'A')
                    ->where('status', 'A')->first();



                if ($service != null && $list->status == 'A') {

                    //$this->output->writeln(date("Y-m-d H:i:s").' respuesta Servicio: '.(String)json_encode($service));

                    //Buscamos las ventas realizadas con este servicio y actualizamos cada msisdn en ClientNetwey con el valor de la lista
                    $sales = Sale::getConnect('W')
                        ->whereIn('islim_sales.status', ['A','E'])
                        ->where('islim_sales.type', 'P')
                        ->where('islim_sales.services_id', $channel->id_service)
                        ->whereBetween('islim_sales.date_reg', [$lastDay, $currentDate])
                        ->join('islim_client_netweys', function ($join) {
                            $join->on('islim_sales.msisdn', '=', 'islim_client_netweys.msisdn')
                                 ->whereNull('islim_client_netweys.id_list_dns');
                        })
                        ->update(['islim_client_netweys.id_list_dns' => $list->id]);
                }

            }

            //Damos de baja los usuarios que superaron el tiempo de vida de la lista asignada
            $diffDates = DB::raw("(TIMESTAMPDIFF(DAY, DATE(date_reg), '".date('Y-m-d')."'))");

            $sales2 = ClientNetwey::getConnect('W')
                ->where($diffDates, '>=', $list->lifetime)
                ->where('id_list_dns', $list->id)
                ->update(['id_list_dns' => null]);

        }
    }
}