<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\SaleInstallment;

class sendAlertInstallmentPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:sendAlertInstallmentPay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía alertas de ventas a cuotas con pagos atrazados';

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
        $data = SaleInstallment::getExpiredPayment(env('OFFSET_PAY_EXPIRED', 2));

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
                if($co->alert_exp == 'P'){
                    Mail::send([], [], function ($message) use ($emails, $co){
                        $message->to($emails)
                                ->subject('Cuota vencida')
                                ->from(env('MAIL_FROM_ADDRESS2'), env('MAIL_FROM_NAME2'))
                                ->setBody('<h1>La venta codigo de transaccion: <b>'.$co->unique_transaction.'</b> tiene la cuota: <b>'.($co->qp + 1).'</b> Vencida el dia '.$co->date_expired.'.</h1> <p>Hecha por el vendedor: <b>'.$co->name_seller.' '.$co->last_name_seller.'</b> coordinador: <b>'.$co->name_coord.' '.$co->last_name_coord.'</b></p>', 'text/html');
                    });

                    SaleInstallment::where('id', $co->id)->update(['alert_exp' => 'S']);
                }
            }
        }
    }
}
