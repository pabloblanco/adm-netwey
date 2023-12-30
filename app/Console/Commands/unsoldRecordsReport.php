<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Helpers\CommonHelpers;
use App\Client;

class unsoldRecordsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:unsoldRecordsReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía reporte de clientes registrados hace 3 dias y que no hicieron compras.';

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
    public function handle(){

         
       
        
        $clients = Client::getClientsUnSoldRecordsForReportCron();

         

        if(count($clients)){

            $data [] = ['Nombre','Email','Telefono','Direccion','Colonia','Ciudad','Estado','Cod Postal','Fecha Registro'];

            foreach($clients as $client){
                $data []= [
                    $client->Nombre.' '.$client->Apellido,
                    empty($client->Email) ? '' : $client->Email,
                    empty($client->Telefono) ? '' : $client->Telefono,
                    empty($client->Direccion) ? '' : $client->Direccion,
                    empty($client->Colonia) ? '' : $client->Colonia,
                    empty($client->Ciudad) ? '' : $client->Ciudad,
                    empty($client->Estado) ? '' : $client->Estado,
                    empty($client->Cod_Postal) ? '' : $client->Cod_Postal,
                    $client->Fecha_Registro
                ];
            }
            

            $url = CommonHelpers::saveFile('/public/reportsOS', 'unsoldRecords', $data, 'unsold_records_report_'.date('d-m-Y', strtotime("-3 days")));

            $emails = explode(',', env('EMAILS_UNSOLD_GDL'));
            
            Mail::send([], [], function ($message) use ($emails, $url){
                $message->to($emails)
                        ->subject('Reporte de ventas netwey')
                        ->setBody('<h2>Reportes Netwey - Registros Online sin ventas del '.date("d-m-Y", strtotime("-3 days")).'</h2><p>En el siguiente enlace puedes descargar el reporte: <a href="'.$url.'">click aqui para desacargar</a> o copia el enlace en el navegador '.$url.' </p>', 'text/html');
            });
        }
        else{

             $emails = explode(',', env('EMAILS_UNSOLD_GDL'));

             
             Mail::send([], [], function ($message) use ($emails){
                $message->to($emails)
                        ->subject('Reporte de ventas netwey')
                         ->setBody('<h2>Reportes Netwey - Registros Online sin ventas del '.date("d-m-Y", strtotime("-3 days")).'</h2><p>No hubo registros sin ventas el dia '.date("d-m-Y", strtotime("-3 days")).'</p>', 'text/html');
            });

            
           
        }


    }
}
