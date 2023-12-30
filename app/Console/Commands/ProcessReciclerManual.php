<?php
/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Enero 2022
 */
namespace App\Console\Commands;

use App\Inv_reciclers;
use Illuminate\Console\Command;

class ProcessReciclerManual extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:ProcessReciclajeManual';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Analiza las peticiones manuales de reciclaje del seller de DN para importar a Netwey a travez de nueva portabilidad';

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
    $InvCheckAltan = Inv_reciclers::getConnect('W')
      ->where('status', 'M')
      ->get();

    if (!empty($InvCheckAltan) && $InvCheckAltan->count() > 0) {
      foreach ($InvCheckAltan as $InvCheckItem) {

        $respPreview = Inv_reciclers::chekkingClient($InvCheckItem->msisdn);
        if ($respPreview['code'] == 'DIFF_OFFER') {

          $InvCheckItem->checkOffert = 'Y';
          $InvCheckItem->checkAltan  = 'N';
          $InvCheckItem->status      = 'C';

        } elseif ($respPreview['code'] == 'DN_FIBRA'
          || $respPreview['code'] == 'OTHER_STATUS') {

          $InvCheckItem->status       = 'E';
          $InvCheckItem->detail_error = $respPreview['msg'];
        } else {
          //Aca es permitido que tenga fallas altan ya que el DN viene a netwey y por tanto no existe
          $InvCheckItem->checkOffert  = 'N';
          $InvCheckItem->checkAltan   = 'N';
          $InvCheckItem->status       = 'C';
          $InvCheckItem->detail_error = null;
        }

        if (isset($respPreview['offert'])) {
          $InvCheckItem->codeOffert = $respPreview['offert'];
        }
        if (isset($respPreview['msg']) && $respPreview['code'] != 'FAIL_ALTAN') {
          $InvCheckItem->detail_error = $respPreview['msg'];
        }

        $InvCheckItem->date_update = date('Y-m-d H:i:s', time());
        $InvCheckItem->save();
        $this->info("**************************");
        $this->info("Se actualizo la solicitud manual de reciclaje id: " . $InvCheckItem->id);
        $this->info("**************************");
      }
    } else {
      $this->info("**************************");
      $this->info("No hay registros manuales nuevos para procesar reciclaje");
      $this->info("**************************");
    }
  }
}
