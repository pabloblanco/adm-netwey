<?php

namespace App\Console\Commands;

use App\Sale;
use App\Client;
use App\Reports;
use App\TempCar;
use Carbon\Carbon;
use App\ClientNetwey;
use App\AssignedSales;
use App\SaleInstallment;
use App\Helpers\CommonHelpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class reportsEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendReports';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía reportes solicitados al correo.';

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
        //Buscando los reportes pendientes por generar
        $reportes = Reports::where('status', 'C')->get();

        foreach ($reportes as $report) {
            $reportxls = [];
            
            //Reporte de recargas
            if($report->name_report == 'reporte_recargas'){
                $filters = json_decode($report->filters, true);
                $filters['user'] = $report->user;
                $filters['user_profile'] = $report->user_profile;

                //Obteniendo data del reporte
                $data = Sale::getSaleReportRecharge($filters)->get();

                $reportxls []= [
                                'Transacción unica',
                                'Folio OXXO',
                                'Fecha de la Transaccion', 
                                'Concentrador', 
                                'Vendedor', 
                                'Producto', 
                                'Telf Netwey', 
                                'Tipo linea',
                                'IMEI', 
                                'ICCID', 
                                'Servicio', 
                                'Cliente', 
                                'Telf de contacto', 
                                'Telf de contacto 2', 
                                'Monto pagado', 
                                'Conciliado', 
                                'Latitud', 
                                'Longitud'
                            ];

                foreach($data as $r){
                    $seller_name = !empty($r->user_name)? ($r->user_name.' '.$r->user_last_name) : 'N/A';
                    $folio = !empty($r->folio) && (!empty($r->concentrator) && $r->concentrator == 'OXXO')? $r->folio : 'N/A';

                    switch ($r->sale_type) {
                        case 'T':  $type_sale = 'Telefonía'; break;
                        case 'F':  $type_sale = 'Fibra'; break;
                        case 'M':  $type_sale = 'MIFI'; break;
                        default: $type_sale = 'Internet Hogar'; break;
                    }

                    $phone = !empty($r->client_phone)? $r->client_phone : 'N/A';
                    $phone2 = !empty($r->client_phone2)? $r->client_phone2 : 'N/A';

                    $reportxls []= [
                                    $r->unique_transaction,
                                    $folio,
                                    $r->date_reg,
                                    !empty($r->concentrator) ? $r->concentrator : 'N/A',
                                    $seller_name,
                                    $r->article,
                                    $r->msisdn,
                                    $type_sale,
                                    $r->imei,
                                    $r->iccid,
                                    $r->service,
                                    $r->client_name.' '.$r->client_lname,
                                    $phone,
                                    $phone2,
                                    number_format($r->amount, 2, '.', ','),
                                    $r->conciliation == 'Y'? 'SI' : 'NO',
                                    !empty($r->lat)? $r->lat : 'N/A',
                                    !empty($r->lng)? $r->lng : 'N/A'
                                ];
                }

                $url = CommonHelpers::saveFile('/public/reports', $filters['view'], $reportxls, $report->name_report.'_'.time());

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            //Reporte de altas
            if($report->name_report == 'reporte_altas'){
                $filters = json_decode($report->filters, true);
                //Obteniendo data del reporte
                $filters['user'] = $report->user;
                $filters['user_profile'] = $report->user_profile;

                $data = Sale::getSaleReportUps($filters)->get();

                $reportxls []= [
                                'Transacción unica', 
                                'Fecha de la Transaccion', 
                                'Organizacion', 
                                'Vendedor', 
                                'Coordinador',
                                'Plan',
                                'Producto',
                                'Telf Netwey', 
                                'Tipo linea',
                                'IMEI', 
                                'ICCID', 
                                'Servicio', 
                                'Cliente', 
                                'Telf de contacto', 
                                'Telf de contacto 2', 
                                'Monto pagado',
                                'Tipo',
                                'Conciliado', 
                                'Latitud', 
                                'Longitud'
                            ];

                foreach($data as $r){
                    $seller_name = !empty($r->user_name)? ($r->user_name.' '.$r->user_last_name) : 'N/A';
                    $coord_name = !empty($r->coord_name)? ($r->coord_name.' '.$r->coord_last_name) : $seller_name;

                    switch ($r->sale_type) {
                        case 'T':  $type_sale = 'Telefonía'; break;
                        case 'F':  $type_sale = 'Fibra'; break;
                        case 'M':  $type_sale = 'MIFI'; break;
                        default: $type_sale = 'Internet Hogar'; break;
                    }

                    $phone = !empty($r->client_phone)? $r->client_phone : 'N/A';
                    $phone2 = !empty($r->client_phone2)? $r->client_phone2 : 'N/A';

                    $reportxls []= [
                                    $r->unique_transaction,
                                    $r->date_reg,
                                    !empty($r->business_name)? $r->business_name : 'N/A',
                                    $seller_name,
                                    $coord_name,
                                    $r->pack,
                                    $r->article,
                                    $r->msisdn,
                                    $type_sale,
                                    $r->imei,
                                    $r->iccid,
                                    $r->service,
                                    $r->client_name.' '.$r->client_lname,
                                    $phone,
                                    $phone2,
                                    number_format($r->amount, 2, '.', ','),
                                    $r->type_buy == 'CO'? 'Contado' : 'Credito',
                                    $r->conciliation == 'Y'? 'SI' : 'NO',
                                    !empty($r->lat)? $r->lat : 'N/A',
                                    !empty($r->lng)? $r->lng : 'N/A'
                                ];
                }

                $url = CommonHelpers::saveFile('/public/reports', $filters['view'], $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            //Reporte de ventas
            if($report->name_report == "reporte_ventas"){
                $filters = json_decode($report->filters, true);

                $filters['user'] = $report->user;
                $filters['user_profile'] = $report->user_profile;

                $data = Sale::getSaleReportAll($filters)->get();

                $reportxls []= [
                                'Transacción unica', 
                                'Fecha de la Transaccion', 
                                'Concentrador', 
                                'Vendedor', 
                                'Tipo de venta',
                                'Plan',
                                'Producto',
                                'Servicio', 
                                'Nro orden altan', 
                                'Código altan', 
                                'Monto pagado', 
                                'Cliente', 
                                'Telf Netwey', 
                                'Tipo linea',
                                'Telf de contacto'
                            ];

                foreach($data as $r){
                    $seller_name = !empty($r->user_name)? ($r->user_name.' '.$r->user_last_name) : 'N/A';
                    $client = $r->client_name.' '.$r->client_lname;
                    switch ($r->sale_type) {
                        case 'T':  $type_sale = 'Telefonía'; break;
                        case 'F':  $type_sale = 'Fibra'; break;
                        case 'M':  $type_sale = 'MIFI'; break;
                        default: $type_sale = 'Internet Hogar'; break;
                    }
                    $type = $r->type == 'P'? 'Alta': 'Recarga';
                    $pack = $r->type == 'P' ? $r->pack : 'N/A';
                    $artic = $r->type == 'P' ? $r->article : 'N/A';
                    $conc = !empty($r->concentrator)? $r->concentrator : 'N/A';
                    $phone = !empty($r->client_phone)? $r->client_phone : 'N/A';

                    $reportxls []= [
                        $r->unique_transaction,
                        $r->date_reg,
                        $conc,
                        $seller_name,
                        $type,
                        $pack,
                        $artic,
                        $r->service,
                        $r->order_altan,
                        $r->codeAltan,
                        number_format($r->amount,2,'.',','),
                        $client,
                        $r->msisdn,
                        $type_sale,
                        $phone
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            //Reporte de concentradores
            if($report->name_report == "reporte_concentradores"){
                $filters = json_decode($report->filters, true);

                $filters['user'] = $report->user;
                $filters['user_profile'] = $report->user_profile;

                $data = Sale::getSaleReportConcentrator($filters)->get();

                $reportxls []= [
                                'Tipo de venta',
                                'Plan',
                                'Producto',
                                'Servicio',
                                'Transacción unica', 
                                'Concentrador', 
                                'Fecha',
                                'MSISDN', 
                                'Tipo linea',
                                'Monto pagado',
                                'Conciliada',
                            ];

                foreach($data as $r){
                    switch ($r->sale_type) {
                        case 'T':  $type_sale = 'Telefonía'; break;
                        case 'F':  $type_sale = 'Fibra'; break;
                        case 'M':  $type_sale = 'MIFI'; break;
                        default: $type_sale = 'Internet Hogar'; break;
                    }
                    $type = $r->type == 'P'? 'Alta': 'Recarga';
                    $pack = $r->type == 'P' ? $r->pack : 'N/A';
                    $artic = $r->type == 'P' ? $r->article : 'N/A';
                    $conc = !empty($r->concentrator)? $r->concentrator : 'N/A';

                    $reportxls []= [
                        $type,
                        $pack,
                        $artic,
                        $r->service,
                        $r->unique_transaction,
                        $conc,
                        $r->date_reg,
                        $r->msisdn,
                        $type_sale,
                        number_format($r->amount,2,'.',','),
                        $r->conciliation
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            //Reporte de clientes
            if($report->name_report == "reporte_clientes"){
                $filters = json_decode($report->filters, true);

                $msisdns = !empty($filters['msisdn_select']) ? explode(",", $filters['msisdn_select']) : null;

                $data = ClientNetwey::getReport(
                                        !empty($filters['service'])? [$filters['service']] : [],
                                        ['A', 'S'],
                                        !empty($filters['date_ini'])? $filters['date_ini'] : null,
                                        !empty($filters['date_end'])? $filters['date_end'] : null,
                                        $msisdns,
                                        !empty($filters['type_line'])? $filters['type_line'] : null
                                    );

                $reportxls []= [
                                'Fecha de registro (Como cliente)',
                                'Fecha de registro (Como prospecto)',
                                'Nombre',
                                'Email',
                                'DN Netwey',
                                'Tipo linea',
                                'Teléfono', 
                                'Dirección',
                                'Servicio adquirido',
                                'Velocidad actual'
                            ];

                foreach($data as $r){
                    $client_name = $r->name.' '.$r->last_name;
                    switch ($r->dn_type) {
                        case 'T':  $type_line = 'Telefonía'; break;
                        case 'F':  $type_line = 'Fibra'; break;
                        case 'M':  $type_sale = 'MIFI'; break;
                        default: $type_line = 'Internet Hogar'; break;
                    }
                    $speed = !empty($r->speed) ? $r->speed : 'N/A';
                    $phone = !empty($r->phone_home)? $r->phone_home : 'N/A';

                    $reportxls []= [
                        $r->client_date,
                        $r->prospect_date,
                        $client_name,
                        $r->email,
                        $r->msisdn,
                        $type_line,
                        $phone,
                        $r->address,
                        $r->service,
                        $speed
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            //Reporte de prospectos
            if($report->name_report == "reporte_prospectos"){
                $filters = json_decode($report->filters, true);

                $data = Client::getReport(
                                    !empty($filters['seller']) ? $filters['seller'] : [],
                                    $filters['date_ini'],
                                    $filters['date_end'],
                                    $filters['org']
                                )->get();

                $reportxls []= [
                                'Fecha de registro',
                                'Nombre',
                                'Email',
                                'Teléfono',
                                'Dirección', 
                                'Nota', 
                                'Próximo contacto',
                                'Persona que le registro',
                                'Coordinador',
                                'Organización'
                            ];

                foreach($data as $r){
                    $seller_name = $r->name.' '.$r->last_name;
                    $coord_name = !empty($r->name_coord) ? $r->name_coord.' '.$r->last_name_coord : 'N/A';

                    $reportxls []= [
                        $r->date_reg,
                        $r->name.' '.$r->last_name,
                        !empty($r->email) ? $r->email : 'N/A',
                        !empty($r->phone_home)? $r->phone_home : 'N/A',
                        !empty($r->address)? $r->address : 'N/A',
                        !empty($r->note)? $r->note : 'N/A',
                        !empty($r->contact_date) ? $r->contact_date : 'N/A',
                        $seller_name,
                        $coord_name,
                        !empty($r->business_name) ? $r->business_name : 'N/A'
                    ];
                }
                
                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            if($report->name_report == 'reporte_articulos_no_activos'){
                $data = Sale::getSalesNotActive()->get();

                $reportxls []= [
                                'ID', 
                                'Producto', 
                                'MSISDN',  
                                'IMEI',
                                'Vendedor',
                                'Fecha Venta'
                            ];

                foreach($data as $r){
                    $seller_name = $r->name.' '.$r->last_name;
                    $reportxls []= [
                        $r->id,
                        $r->title,
                        $r->msisdn,
                        $r->imei,
                        $seller_name,
                        $r->date_reg
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            if($report->name_report == 'reporte_articulos_vendidos_no_activos'){
                $filters = json_decode($report->filters, true);

                $data = Sale::getSalesNotActiveReport($filters)->get();

                $reportxls []= [
                                'Transacción unica', 
                                'MSISDN', 
                                'Artículo', 
                                'Servicio', 
                                'Vendedor',
                                'Coordinador',
                                'Organización',
                                'Fecha de venta'
                            ];

                foreach($data as $r){
                    $seller_name = $r->name.' '.$r->last_name;
                    $coord_name = !empty($r->namecoo)? $r->namecoo.' '.$r->lastnamecoo : $seller_name;
                    
                    $reportxls []= [
                        $r->unique_transaction,
                        $r->msisdn,
                        $r->title,
                        $r->service,
                        $seller_name,
                        $coord_name,
                        $r->business_name,
                        $r->date_reg
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            if($report->name_report == 'reporte_articulos_activos'){
                $filters = json_decode($report->filters, true);

                $data = Sale::getSalesActiveReport($filters)->get();

                $reportxls []= [
                                'Transacción unica', 
                                'Cliente',
                                'Teléfono contacto',
                                'Teléfono contacto 2',
                                'Email',
                                'MSISDN', 
                                'Artículo', 
                                'Servicio', 
                                'Vendedor',
                                'Coordinador',
                                'Organización',
                                'Fecha de venta',
                                'Fecha de activación'
                            ];

                foreach($data as $r){
                    $seller_name = $r->name.' '.$r->last_name;
                    $coord_name = !empty($r->namecoo)? $r->namecoo.' '.$r->lastnamecoo : $seller_name;

                    $reportxls []= [
                        $r->unique_transaction,
                        $r->cliname.' '.$r->clilastname,
                        !empty($r->phone_home)? $r->phone_home : 'N/A',
                        !empty($r->phone)? $r->phone : 'N/A',
                        !empty($r->email)? $r->email : 'N/A',
                        $r->msisdn,
                        $r->title,
                        $r->service,
                        $seller_name,
                        $coord_name,
                        $r->business_name,
                        $r->date_reg,
                        !empty($r->date_sale)? $r->date_sale : $r->date_reg
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            if($report->name_report == 'reporte_clientes_financiados'){
                $filters = json_decode($report->filters, true);

                $data = ClientNetwey::getFinancingReport($filters)->get();

                $reportxls []= [
                                'msisdn', 
                                'Fecha Alta',
                                'Monto financiado',
                                'Monto total deuda',
                                '# Recargas',
                                'Pago a la fecha',
                                'Deuda remanente'
                            ];

                foreach($data as $r){
                    $reportxls []= [
                        $r->msisdn,
                        date("d-m-Y H:i:s", strtotime($r->date_reg)),
                        number_format($r->amount_financing,2,'.',','),
                        number_format($r->total_amount,2,'.',','),
                        $r->num_dues == 0 ? '0' : $r->num_dues,
                        number_format($r->pay,2,'.',','),
                        number_format($r->price_remaining,2,'.',',')
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            if($report->name_report == 'reporte_conciliaciones'){
                $filters = json_decode($report->filters, true);
                
                $data = AssignedSales::getReportConciliations($filters);

                $reportxls []= [
                                'Id depósito',
                                'Monto',
                                'banco',
                                'Ope. Efectivo',  
                                'Coordinador',  
                                'Cod. Depósito',
                                'Fecha'
                            ];

                foreach($data as $r){
                    $reportxls []= [
                        $r->cod_auth,
                        $r->amount,
                        $r->bank,
                        $r->ope_name.' '.$r->ope_last_name,
                        $r->name.' '.$r->last_name,
                        $r->id_deposit,
                        $r->date_process
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                /*$urlEmail = route(
                                    'downloadReport',
                                    [
                                        'delete' => '0', 
                                        'id' => base64_encode($report->id)
                                    ]
                                );*/

                $report->save();

                //$emails = explode(',', str_replace(' ', '', $report->email));
            }

            if($report->name_report == 'reporte_ventas_convertia' || $report->name_report == 'reporte_ventas_inconcert'){
                $filters = json_decode($report->filters, true);
                
                $data = TempCar::getSalesReport($filters);

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

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            if($report->name_report == 'reporte_consumo'){
                $filters = json_decode($report->filters, true);
                
                $data = Sale::getConsuption($filters)->get();

                $reportxls []= [
                                'msisdn',
                                'Consumo (GB)',
                                'Servicio',
                                'ID oferta',  
                                'Nombre oferta',  
                                'Fecha inicio oferta',
                                'Fecha fin oferta',
                                'Dias de consumo',
                                'Tipo servicio'
                            ];

                foreach($data as $r){
                    $cons = 'S/I';
                    $nof = 'S/I';
                    $de = 'S/I';
                    $days = 'S/I';
                    $type = 'Recarga';

                    if(!empty($r->consuption)){
                        $cons = (String)round(((($r->consuption/1024)/1024)/1024),2);
                    }

                    if(!empty($r->offer_name)){
                        $nof = $r->offer_name;
                    }

                    if(!empty($r->date_sup_en)){
                        $de = Carbon::createFromFormat(
                                                     'Y-m-d', 
                                                     $r->date_sup_en
                                                    )
                                                    ->format('Y-m-d');
                    }

                    if(!empty($r->days)){
                        $days = (String)$r->days;
                    }

                    if($r->type == 'P'){
                        $type = 'Alta';
                    }

                    $db = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_reg)
                                  ->format('Y-m-d');

                    $reportxls []= [
                        $r->msisdn,
                        $cons,
                        $r->title,
                        $r->codeAltan,
                        $nof,
                        $db,
                        $de,
                        $days,
                        $type
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            if($report->name_report == 'reporte_venta_abono'){
                $filters = json_decode($report->filters, true);
                
                $data = SaleInstallment::getSalesReport($filters);

                $reportxls []= [
                                'Transacción única',
                                'Organización',
                                'Vendedor',
                                'Coordinador',  
                                'Pack',  
                                'Producto',
                                'DN Netwey',
                                'Tipo linea',
                                'Imei',
                                'Plan',
                                'Cliente',
                                'Telf contacto',
                                'Fecha de venta',
                                'Fecha vencimiento prox. cuota',
                                'Estatus',
                                'Cutoa',
                                'Monto'
                            ];

                foreach($data as $r){
                    $seller = $r->name_seller.' '.$r->last_name_seller;
                    $coord = $r->name_coord.' '.$r->last_name_coord;
                    $imei = !empty($r->imei) ? $r->imei : 'S/I';
                    $client = $r->name_client.' '.$r->last_name_client;
                    $phone = !empty($r->phone_home) ? $r->phone_home : 'N/A';

                    $dsale = 'N/A';
                    if(!empty($r->date_reg_alt)){
                        $dsale = Carbon::createFromFormat('Y-m-d H:i:s', $r->date_reg_alt)
                                  ->format('d-m-Y H:i:s');
                    }

                    $dnq = $r->date_expired;
                    if($dnq != 'N/A'){
                        $dnq = Carbon::createFromFormat('d-m-Y', $dnq)
                                  ->format('d-m-Y');
                    }

                    $status = $r->expired ? 'Vencida' : 'Al día';
                    $quote = $r->quotes.'/'.$r->total_quotes;
                    $amount = '$'.number_format($r->amount,2,'.',',');

                    switch ($r->artic_type) {
                        case 'T':  $tl = 'Telefonía'; break;
                        case 'F':  $tl = 'Fibra'; break;
                        case 'M':  $type_sale = 'MIFI'; break;
                        default: $tl = 'Internet Hogar'; break;
                    }

                    $reportxls []= [
                        $r->unique_transaction,
                        $r->business_name,
                        $seller,
                        $coord,
                        $r->pack,
                        $r->product,
                        $r->msisdn,
                        $tl,
                        $imei,
                        $r->service,
                        $client,
                        $phone,
                        $dsale,
                        $dnq,
                        $status,
                        $quote,
                        $amount
                    ];
                }

                $url = CommonHelpers::saveFile('/public/reports', 'sales', $reportxls, $report->name_report.'_'.time().'_'.date('d-m-Y'));

                $report->status = 'G';
                $report->download_url = $url;

                $report->save();
            }

            //Es necesario que luego de ejecutar cada reporte existas las siguientes variables
            //$emails -> array que guarda las direcciones destino
            //$report->name_report -> nombre del reporte, viene de la bd
            //$urlEmail-> url para que el usuario pueda descargar el reporte
            /*if(!empty($emails)){
                Mail::send([], [], function ($message) use ($emails, $report, $urlEmail){
                    $message->to($emails)
                            ->subject('Reporte '.$report->name_report)
                            ->setBody('<h1>Reporte '.$report->name_report.'</h1> <p>En el siguiente enlace puedes descargar el reporte: <a href="'.$report->download_url.'">click aqui para desacargar</a> o copia el enlace en el navegador '.$report->download_url.' </p>', 'text/html');
                });
            }*/
        }
    }
}