<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\PayInstallment;

class sendAlertInstallment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendAlertInstallment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía alertas para notificaciones de recivo efectivo para las ventas en cuotas';

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
        $data = PayInstallment::getAlertReport();

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

            foreach ($data as $co){
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
                                ->setBody('<h1>El RRE de ventas a cuotas ID: <b>'.$co->id.'</b> y codigo de transaccion: <b>'.$co->unique_transaction.'</b> cambio a alerta '.$alert.'.</h1> <p>El vendedor: <b>'.$co->name_seller.' '.$co->last_name_seller.'</b> notifico la entrega de efectivo por el monto <b>'.$co->amount.'</b> el día <b>'.$co->date_reg.'</b> al coordinador: <b>'.$co->name_coord.' '.$co->last_name_coord.'</b></p>', 'text/html');
                    });

                    if($alert == 'naranja')
                        PayInstallment::where('id', $co->id)->update(['alert_orange_send' => 'S']);

                    if($alert == 'roja')
                        PayInstallment::where('id', $co->id)->update(['alert_red_send' => 'S']);
                }
            }
        }
    }
}
