<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Helpers\CommonHelpers;
use App\TempCar;

class convertiaReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:convertiaReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía reporte de ventas realizadas en el día a convertia.';

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
        $filters['key'] = env('TOKEN_CONVERTIA');
        $data = TempCar::getSalesReport($filters);

        if(count($data)){
            $reportxls []= [
                            'Transaccion',
                            'Nombre',
                            'Telefono',
                            'Correo',  
                            'Requiere Factura',  
                            'RFC/INE',
                            'DN',
                            'Pack',
                            'Fecha compra',
                            'Orden Netwey',
                            'Orden envio',
                            'Estatus envio',
                            'PDF 99min',
                            'Estatus DN',
                            'Monto envio',
                            'Monto pack'
                        ];

            foreach($data as $r){
                $reportxls []= [
                    $r->transaction,
                    $r->name.' '.$r->last_name,
                    !empty($r->phone_home) ? $r->phone_home : 'N/A',
                    !empty($r->email) ? $r->email : 'N/A',
                    $r->require_invoice == 'Y' ? 'Si' : 'No',
                    !empty($r->rfc) ? $r->rfc : $r->dni,
                    !empty($r->msisdn) ? $r->msisdn : 'N/A',
                    $r->title,
                    !empty($r->date) ? date("d-m-Y", strtotime($r->date)) : 'N/A',
                    $r->order,
                    !empty($r->order99) ? $r->order99 : 'N/A',
                    !empty($r->description) ? strtolower($r->description) : 'N/A',
                    !empty($r->url_pdf) ? $r->url_pdf : 'N/A',
                    !empty($r->status_dn) ? $r->status_dn : 'N/A',
                    '$'.number_format($r->amount_del,2,'.',','),
                    '$'.number_format($r->price_pack,2,'.',',')
                ];
            }

            $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, 'Convertia_'.time().'_'.date('d-m-Y'));

            $emails = explode(',', env('EMAILS_CONVERTIA'));

            Mail::send([], [], function ($message) use ($emails, $url){
                $message->to($emails)
                        ->subject('Reporte de ventas netwey')
                        ->setBody('<h1>Reporte de ventas netwey</h1> <p>En el siguiente enlace puedes descargar el reporte: <a href="'.$url.'">click aqui para desacargar</a> o copia el enlace en el navegador '.$url.' </p>', 'text/html');
            });
        }
    }
}
