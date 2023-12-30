<?php
namespace App\Console\Commands;

use App\Mail\TotalUpsReport as ReportMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
USE Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;


class TotalUpsReport extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:totalUpsReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generar Reporte de Altas Totales y enviarlo a un correo Especifico.';

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

        $report = DB::connection('netwey-r')
        ->table('islim_sales')
        ->select(
            'islim_sales.unique_transaction as "Transacción única"',
            'islim_sales.date_reg as "Fecha de la Transacción"',
            'islim_sales.msisdn as "Telf Netwey"',
            'islim_sales.conciliation as "Conciliado"',
            'islim_sales.sale_type as "Tipo linea"',
            'islim_clients.name as "Cliente nombre"',
            'islim_clients.last_name as "Cliente apellido"',
            'islim_clients.phone_home as "Telf de contacto"',
            'islim_clients.phone as "Telf de contacto 2"',
            'islim_client_netweys.lat as "Latitud"',
            'islim_client_netweys.lng as "Longitud"',
            'islim_client_netweys.type_buy as "Tipo"',
            'islim_users.name as "Vendedor nombre"',
            'islim_users.last_name as "Vendedor apellido"',
            'coo.name as "Coordinador nombre"',
            'coo.last_name as "Coordinador apellido"',
            'islim_packs.title as "Plan"',
            'islim_inv_articles.title as "Producto"',
            'islim_inv_arti_details.iccid as "ICCID"',
            'islim_inv_arti_details.imei as "IMEI"',
            'islim_services.title as "Servicio"',
            'islim_dts_organizations.business_name as "Organización"',
            DB::raw('(CASE
                WHEN (islim_sales.amount = 0
                AND islim_sales.is_migration = "N") THEN (
                SELECT
                    b.amount
                FROM
                    islim_sales as b
                WHERE
                    b.unique_transaction = islim_sales.unique_transaction
                    AND b.type = "V" )
                ELSE islim_sales.amount
            END ) as "Monto pagado",
            CONCAT(islim_billings.serie, "-", islim_billings.id) as "Factura"'))
        ->join('islim_client_netweys', 'islim_client_netweys.msisdn', 'islim_sales.msisdn')
        ->join('islim_clients', 'islim_clients.dni', 'islim_client_netweys.clients_dni')
        ->join('islim_users', 'islim_users.email', 'islim_sales.users_email')
        ->leftJoin('islim_users as coo', 'coo.email', 'islim_users.parent_email')
        ->leftJoin('islim_dts_organizations', 'islim_dts_organizations.id', 'islim_users.id_org')
        ->leftJoin('islim_packs', 'islim_packs.id', 'islim_sales.packs_id')
        ->join('islim_inv_arti_details', 'islim_inv_arti_details.msisdn', 'islim_sales.msisdn')
        ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
        ->join('islim_services', 'islim_services.id', 'islim_sales.services_id')
        ->leftJoin('islim_billings', 'islim_billings.sales_unique_transaction', 'islim_sales.unique_transaction')
        ->where([
            ['islim_sales.type', '=', "P"],
            ['islim_clients.name', '!=', "TEMPORAL"]
        ])
        ->whereIn('islim_sales.status', ['A', 'E'])
        ->whereIn('islim_client_netweys.status', ['A', 'S'])->get();

        $report = json_decode(json_encode($report), true);

        $file = Excel::create('Base_De_Altas', function($excel) use ($report){


            $excel->sheet('Hoja 1', function($sheet) use($report) {

                $sheet->fromArray($report);

            })->store('csv', storage_path('app'));

        });

        $contents = Storage::disk('local')->get('Base_De_Altas.csv');


        $folder=env('APP_ENV')=='production'?'basealtas/':'basealtas/test/';

        $filename = $folder.'Base_De_Altas'.date('YmdHis').'.csv';
        Storage::disk('s3')->put($filename, $contents, 'public');

        Storage::disk('local')->delete('Base_De_Altas.csv');

        $url = Storage::disk('s3')->url($filename);




        $emailsNetwey = explode(',', env('TOTAL_UPS_REPORT_EMAIL'));

        try {
          Mail::to($emailsNetwey)->send(new ReportMail($url));
        } catch (\Exception $e) {
          $text0 = "No se pudo enviar el email de Base de Altas con el archivo: ".$url." ";
          Log::alert($text0 . " +Detalles: " . (String) json_encode($e->getMessage()));
        }



        //Mail::to(env('TOTAL_UPS_REPORT_EMAIL'))->send(new ReportMail($url));

        //Log::info($url);


    }
}