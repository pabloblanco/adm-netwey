<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
  /**
   * The Artisan commands provided by your application.
   *
   * @var array
   */
  protected $commands = [
    //
  ];

  /**
   * Define the application's command schedule.
   *
   * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
   * @return void
   */
  protected function schedule(Schedule $schedule)
  {
    //$schedule->command('command:kpi')->cron('0 1 1 * * *');
    //$schedule->command('command:kpiMBB')->cron('0 1 1 * * *');
    $schedule->command('command:kpisNew', ['T'])->cron('0 1 1 * * *');

    //$schedule->command('command:kpiHBB')->cron('0 3 1 * * *');
    $schedule->command('command:kpisNew', ['H'])->cron('0 3 1 * * *');

    //$schedule->command('command:kpiMIFI')->cron('0 3 1 * * *');
    $schedule->command('command:kpisNew', ['M'])->cron('0 4 1 * * *');

    //$schedule->command('command:kpiMIFIH')->cron('0 3 1 * * *');
    $schedule->command('command:kpisNew', ['MH'])->cron('0 4 1 * * *');

    $schedule->command('command:kpisNew', ['F'])->cron('0 5 1 * * *');

    $schedule->command('command:activeClients')->dailyAt('01:00');
    $schedule->command('command:suspendInactive')->dailyAt('02:00');
    $schedule->command('command:suspendExpired')->dailyAt('03:00');
    //$schedule->command('command:suspendExpired')->monthlyOn(date('t'), '1:00');
    $schedule->command('command:dashboard')->dailyAt('05:00');
    //$schedule->command('command:test')->everyMinute();
    $schedule->command('command:sendAlert')->hourly();
    $schedule->command('command:sendAlertInstallment')->hourly();
    //$schedule->command('command:sendReports')->everyMinute()->withoutOverlapping(15);
    $schedule->command('command:sendReportsV2')->everyMinute()->withoutOverlapping(5);
    $schedule->command('command:nomina')->dailyAt('01:00');
    $schedule->command('command:tokens')->dailyAt('03:00');
    $schedule->command('command:InstallmentExpired')->dailyAt('02:00');
    $schedule->command('command:checkInstallments')->everyMinute();
    //$schedule->command('command:convertiaReport')->dailyAt('09:00');
    $schedule->command('command:unsoldRecordsReport')->dailyAt('07:00');
    $schedule->command('command:leadsEnvioCeroReport')->dailyAt('07:00');

    $schedule->command('command:dashboard2', ['H'])->dailyAt('05:00');
    $schedule->command('command:dashboard2', ['T'])->dailyAt('06:00');
    $schedule->command('command:dashboard2', ['M'])->dailyAt('06:00');
    $schedule->command('command:dashboard2', ['F'])->dailyAt('06:00');
    $schedule->command('command:dashboard2', ['MH'])->dailyAt('06:00');
    $schedule->command('command:dashboard2', ['MH_M'])->dailyAt('06:00');

    //$schedule->command('command:offerMH')->dailyAt('01:00');
    $schedule->command('command:offerList')->dailyAt('01:00');
    //Commando para actualizar datos de portabilidad
    $schedule->command('command:updatePortability')->everyMinute()->withoutOverlapping(5);

    $schedule->command('command:setStatusInventory')->dailyAt('01:00');
    //$schedule->command('command:moveInventoryToMermaOldAutomatic')->monthlyOn(10, '01:00');
    //$schedule->command('command:getOrderProva')->everyThirtyMinutes();
    $schedule->command('command:deactiveInactive')->dailyAt('04:00');

    //calculos de KPI Bajas
    $schedule->command('command:KPIDismissal', [null, null])->monthlyOn(1, '03:00');

    //Revision de peticiones manuales de reciclaje, cada 5 minutos
    $schedule->command('command:ProcessReciclajeManual')
      ->everyFiveMinutes()
      ->withoutOverlapping(5);

    //proceso de cargar en inventario los reciclajes y el envio del reporte via email de los reciclajes del dia que acaba de finalizar
    $schedule->command('command:ProcessReciclaje')->dailyAt('01:00')->withoutOverlapping(5);

    $schedule->command('command:autoRejectPreAssigned')->dailyAt('00:30');

    //facturacion masiva
    $schedule->command('command:masiveBillingProcess')->weeklyOn(4, '12:00');
    $schedule->command('command:masiveBillingProcess')->weeklyOn(4, '17:00');
    $schedule->command('command:masiveBillingProcess')->weeklyOn(5, '12:00');
    $schedule->command('command:masiveBillingProcess')->weeklyOn(5, '17:00');

    //Envía sms a clientes que van a expiar de fibra
    $schedule->command('command:sendSMSExpired')->dailyAt('09:00');


    //calcula historico de deudas y conciliaciones de coordinadores y vendedores
    $schedule->command('command:historyDebtsCalc')->dailyAt('00:01');

    //Revisa si hay alguna peticion manual de Alta de fibra en la que fallo815, Cada 2min se toma uno
    $schedule->command('command:insertFiberAlta')->everyMinute()->withoutOverlapping(5);

    //Generar Reporte de Altas Totales (Base de Altas) y enviarlo a un correo Especifico.
    $schedule->command('command:totalUpsReport')->monthlyOn(1, '05.00');


    //Se encarga de mantener Suspendidos en 815 a los clientes suspendidos previamente por Netwey.
    $schedule->command('command:fiberSuspend')->hourly();
  }

  /**
   * Register the commands for the application.
   *
   * @return void
   */
  protected function commands()
  {
    $this->load(__DIR__ . '/Commands');

    require base_path('routes/console.php');
  }
}
