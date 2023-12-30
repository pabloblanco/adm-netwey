<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Helpers\CommonHelpers;
use Illuminate\Support\Facades\DB;
use App\Client;

class leadsEnvioCeroReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:leadsEnvioCeroReport';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía reporte de leads que dejaron sus datos en popup promocional EnvioCero del dia anterior.';

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

         
       
        
        //$clients = Client::getClientsUnSoldRecordsForReportCron();

        $dini=date("Y-m-d", strtotime("-1 days"))."%2000:00:00";
        $dend=date("Y-m-d", strtotime("-1 days"))."%2023:59:59";
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => "https://mautic.netwey.com.mx/api/contacts?search=email:!%22%22%20and%20origin:%22EC%22%20and%20isregister:%22N%22&limit=0&where%5B0%5D%5Bexpr%5D=gte&where%5B0%5D%5Bcol%5D=dateAdded&where%5B0%5D%5Bval%5D=".$dini."&where%5B1%5D%5Bexpr%5D=lte&where%5B1%5D%5Bcol%5D=dateAdded&where%5B1%5D%5Bval%5D=".$dend."",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          CURLOPT_HTTPHEADER => array(
            "Authorization: Basic YWRtaW46TFlQQHZRSzs4RDc4QlVpOg==",
            "Cookie: 659488090dd5f2926bfca2796ea50e33=fdrkadr5lcg6iipbbfglvt79tq"
          ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);
        

        if (!$err) {
            $response = json_decode($response,true);
            if(count($response['contacts'])>0){
                $data [] = ['Nombre','Email','Telefono','Ciudad','Fecha Registro'];
                $i=0;  
                      foreach ($response['contacts'] as $key => $contact) {
                       
                        $i++;
                         

                         $user=DB::table('islim_clients')
                                ->where('email',$contact['fields']['all']['email'])
                                ->first();
                            if ($user==null or $user=="") {  

                                $data []= [
                                    $contact['fields']['all']['firstname'],
                                    $contact['fields']['all']['lastname'],
                                    empty($contact['fields']['all']['phone']) ? '' : $contact['fields']['all']['phone'],
                                    empty($contact['fields']['all']['email']) ? '' : $contact['fields']['all']['email'],
                                    empty($contact['fields']['all']['city']) ? '' : $contact['fields']['all']['city'],
                                    empty(date_format(date_create($contact['dateAdded']),'d/m/Y H:i:s')) ? '' : date_format(date_create($contact['dateAdded']),'d/m/Y H:i:s'),
                                    
                                ];
                               
                            }
                            else{
                              
                                
                                $curl2 = curl_init();

                                curl_setopt_array($curl2, array(
                                  CURLOPT_URL => "https://mautic.netwey.com.mx/api/contacts/".$contact['fields']['all']['id']."/edit",
                                  CURLOPT_RETURNTRANSFER => true,
                                  CURLOPT_ENCODING => "",
                                  CURLOPT_MAXREDIRS => 10,
                                  CURLOPT_TIMEOUT => 0,
                                  CURLOPT_FOLLOWLOCATION => true,
                                  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                                  CURLOPT_CUSTOMREQUEST => "PATCH",
                                  CURLOPT_POSTFIELDS =>"{\r\n  \"isregister\":\"Y\"\r\n}",
                                  CURLOPT_HTTPHEADER => array(
                                    "Content-Type: application/json",
                                    "Authorization: Basic YWRtaW46TFlQQHZRSzs4RDc4QlVpOg==",
                                    "Content-Type: application/json",
                                    "Cookie: 659488090dd5f2926bfca2796ea50e33=oq89ad9t2imrcf6jk9far0npan"
                                  ),
                                ));

                                $res = curl_exec($curl2);
                                curl_close($curl2);


                            }
                      }


                $url = CommonHelpers::saveFile('/public/reportsOS', 'leadsEnvioCero', $data, 'leadsEnvioCero_'.date('d-m-Y', strtotime("-1 days")));

                $emails = explode(',', env('EMAILS_UNSOLD_GDL'));
                // $emails = explode(',',"miguel.becerra02@gmail.com");
                
                Mail::send([], [], function ($message) use ($emails, $url){
                    $message->to($emails)
                            ->subject('Reporte de ventas netwey')
                            ->setBody('<h2>Reportes Netwey - Leads Envio Cero del '.date("d-m-Y", strtotime("-1 days")).'</h2><p>En el siguiente enlace puedes descargar el reporte: <a href="'.$url.'">click aqui para desacargar</a> o copia el enlace en el navegador '.$url.' </p>', 'text/html');
                });



            }
            else{
                 $emails = explode(',', env('EMAILS_UNSOLD_GDL'));
                 // $emails = explode(',',"miguel.becerra02@gmail.com");
            
                 Mail::send([], [], function ($message) use ($emails){
                    $message->to($emails)
                            ->subject('Reporte de ventas netwey')
                             ->setBody('<h2>Reportes Netwey - Leads Envio Cero del '.date("d-m-Y", strtotime("-1 days")).'</h2><p>No hubo captura de leads para el dia '.date("d-m-Y", strtotime("-1 days")).'</p>', 'text/html');
                });
            }
        }   
        else{
            $emails = explode(',', env('EMAILS_UNSOLD_GDL'));
            // $emails = explode(',',"miguel.becerra02@gmail.com");
            Mail::send([], [], function ($message) use ($emails){
                $message->to($emails)
                        ->subject('Reporte de ventas netwey')
                         ->setBody('<h2>Reportes Netwey - Leads Envio Cero del '.date("d-m-Y", strtotime("-1 days")).'</h2><p>No hubo captura de leads para el dia '.date("d-m-Y", strtotime("-1 days")).'</p>', 'text/html');
            });
        }

    }
}
