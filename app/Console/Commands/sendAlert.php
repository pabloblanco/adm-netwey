<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\AssignedSales;

class sendAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendAlert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía alertas (notificaciones de recivo efectivo)';

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
        $diff = DB::raw("TIME_FORMAT(TIME(TIMEDIFF(CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City'),islim_asigned_sales.date_reg)), '%H')");
        $consult = DB::raw("(TIME_FORMAT(TIME(TIMEDIFF(CONVERT_TZ(NOW(), 'UTC', 'America/Mexico_City'),islim_asigned_sales.date_reg)), '%H')) as diff");

        $data = AssignedSales::select(
                                'islim_asigned_sales.id',
                                'islim_asigned_sales.amount',
                                'islim_asigned_sales.date_reg',
                                'islim_asigned_sales.date_process',
                                'islim_users.name',
                                'islim_users.last_name',
                                'seller.name as seller_name',
                                'seller.last_name as seller_last_name',
                                'islim_asigned_sales.alert_orange_send',
                                'islim_asigned_sales.alert_red_send',
                                $consult
                               )
                               ->join('islim_users', 'islim_users.email', 'islim_asigned_sales.parent_email')
                               ->join('islim_users as seller', 'seller.email', 'islim_asigned_sales.users_email')
                               ->where([
                                ['islim_asigned_sales.status', 'V'],
                                [$diff, '>=', 6]
                               ])
                               ->get();

        if($data->count()){
            $swiftTransport = new \Swift_SmtpTransport(
                                                    env('MAIL_HOST2'),
                                                    env('MAIL_PORT2'),
                                                    env('MAIL_ENCRYPTION2')
                                                );

            $swiftTransport->setUsername(env('MAIL_USERNAME2'))
                           ->setPassword(env('MAIL_PASSWORD2'));

            $swiftMailer = new \Swift_Mailer($swiftTransport);

            Mail::setSwiftMailer($swiftMailer);

            $emails = str_replace(' ', '', env('ALERT_EMAIL'));
            $emails = explode(',', $emails);

            foreach ($data as $co) {
                $alert = false;

                if($co->diff >= 6 && $co->diff < 12 && $co->alert_orange_send == 'P')
                    $alert = 'naranja';

                if($co->diff >= 12 && $co->alert_red_send == 'P')
                    $alert = 'roja';

                if($alert){
                    Mail::send([], [], function ($message) use ($emails, $co, $alert){
                        $message->to($emails)
                                ->subject('RRE alerta '.$alert)
                                ->from(env('MAIL_FROM_ADDRESS2'), env('MAIL_FROM_NAME2'))
                                ->setBody('<h1>El RRE ID: '.$co->id.' Cambio a alerta '.$alert.'.</h1> <p>El vendedor: <b>'.$co->seller_name.' '.$co->seller_last_name.'</b> notifico la entrega de efectivo por el monto <b>'.$co->amount.'</b> el día <b>'.$co->date_reg.'</b> al coordinador: <b>'.$co->name.' '.$co->last_name.'</b></p>', 'text/html');
                    });

                    if($alert == 'naranja')
                        AssignedSales::where('id', $co->id)->update(['alert_orange_send' => 'S']);

                    if($alert == 'roja')
                        AssignedSales::where('id', $co->id)->update(['alert_red_send' => 'S']);
                }
            }

            //Mail::setSwiftMailer($backup);
        }
    }
}
