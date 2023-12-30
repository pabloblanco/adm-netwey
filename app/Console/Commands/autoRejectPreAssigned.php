<?php

namespace App\Console\Commands;

use App\SellerInventoryTemp;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class autoRejectPreAssigned extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:autoRejectPreAssigned';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Rechaza automaticamente Pre-Assignaciones con mas de 24h sin aceptar o rechazar';

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
   *
   * @return mixed
   */
  public function handle()
  {
    //$date = date('Y-m-d');
    $date = Carbon::now()->subDay();

    SellerInventoryTemp::getConnect('W')
                    ->where('status','P')
                    ->where('date_reg','<=',$date)
                    ->update([
                        'status' => 'R',
                        'reason_reject' => 'Rechazado automaticamente por inacción',
                        'reject_notification_view' => 'N',
                        'date_status' => date('Y-m-d H:i:s')
                    ]);

  }
}
