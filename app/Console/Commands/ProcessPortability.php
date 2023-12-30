<?php
/*
Autor: Ing. LuisJ
Agosto 2021
 */
namespace App\Console\Commands;

use App\Client;
use App\ClientNetwey;
use App\Portability;
use App\Portability_log;
use App\Sale;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPortability extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:updatePortability';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Procesa a nivel de BD las diversas tablas en las cuales esta presente del DN transitorio y se reemplaza por el DN a portar';

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
    //Buscando que no exista solicitudes en proceso de actualizacion
    $En_proceso = Portability::where('status', 'W')
      ->orderBy('date_reg', 'ASC')
      ->first();

    if (empty($En_proceso)) {
      //Busco los que estan es proceso de solicitud
      $En_solicitud = Portability::where('status', 'S')
        ->orderBy('date_reg', 'ASC')
        ->first();

      if (!empty($En_solicitud)) {

        try {

          //verifico que el Dn a portar no este en clientes
          $ban14_2 = ClientNetwey::existDN($En_solicitud->dn_portability);

          if (!empty($ban14_2)) {
            $dniCli = ClientNetwey::getDNIClient($En_solicitud->dn_portability);
            if (!empty($dniCli)) {
              $infoCli = Client::getInfoClient($dniCli->clients_dni);
              if (!empty($infoCli)) {
                $errors = 'El DN a portar: ' . $En_solicitud->dn_portability . ' esta registrado en Netwey con el siguiente Cliente. Dni: ' . $dniCli->clients_dni . ' - ' . $infoCli->name . ' ' . $infoCli->last_name . ' - ' . $infoCli->email;
              } else {
                $errors = 'El DN a portar: ' . $En_solicitud->dn_portability . ' esta registrado en BD como cliente Netwey.';
              }
            }

            Portability_log::setLogPotability($En_solicitud->id, 'islim_client_netweys', 'ERROR', $errors);

            $En_solicitud->status        = 'E';
            $En_solicitud->date_process  = date('Y-m-d H:i:s');
            $En_solicitud->details_error = $errors;
            $En_solicitud->save();

            return $this->output->writeln('Ocurrio un error al tratar de hacer el proceso de actualizacion en netwey de la portabilidad: ' . $En_solicitud->id . ' islim_client_netweys - ' . $errors, false);
          }

          //Si la orden fue creada manualmente verifico que la orden de venta corresponda a la portabilidad
          $ban13 = Sale::existDN($En_solicitud->dn_netwey);

          if (!empty($ban13->id)) {
            // Log::info("Ban13: " . $ban13->id . ' Sale: ' . $sale_id . 'DN' . $ban13->msisdn);
            if ($ban13->id != $En_solicitud->sale_id) {

              $errors = 'El DN transitorio: ' . $En_solicitud->dn_netwey . ' esta bajo la orden de venta: ' . $ban13->id . ' el cual no corresponde con la orden de venta registrada en la portabilidad: ' . $En_solicitud->sale_id;

              Portability_log::setLogPotability($En_solicitud->id, 'islim_sales', 'ERROR', $errors);

              $En_solicitud->status        = 'E';
              $En_solicitud->date_process  = date('Y-m-d H:i:s');
              $En_solicitud->details_error = $errors;
              $En_solicitud->save();

              return $this->output->writeln('Ocurrio un error al tratar de actualizar el registro de portabilidad: ' . $En_solicitud->id . ' islim_sales - ' . $errors, false);
            }
          }

          //Actualizo el stado a en proceso
          $En_solicitud->status       = 'W';
          $En_solicitud->date_process = date('Y-m-d H:i:s');
          $En_solicitud->save();

//Lista de tablas a consultar y que se deben actualizar
          $TablesUpdate = DB::table('islim_portability_tables')
            ->where('active', 'Y')
            ->whereIn('type', ['A', 'I'])
            ->get();

          foreach ($TablesUpdate as $item_Table) {

            if ($item_Table->name_table == 'islim_altan_portability_sftp') {
              $Bandera = DB::table($item_Table->name_table)
                ->where([
                  [$item_Table->name_dn, $En_solicitud->dn_portability],
                  ['view', 'N'],
                  ['request', 'Port-in']])
                ->get();
            } else {
              $Bandera = DB::table($item_Table->name_table)
                ->where($item_Table->name_dn, $En_solicitud->dn_netwey)
                ->get();
            }

            if (count($Bandera) > 0) {
              try {
                if ($item_Table->foreing_key == 'Y') {
                  DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                }

                if ($item_Table->name_table == 'islim_altan_portability_sftp') {

                  $updateItem = DB::table($item_Table->name_table)->where($item_Table->name_dn, $En_solicitud->dn_portability)
                    ->update([
                      'view'        => 'Y',
                      'date_update' => date('Y-m-d H:i:s'),
                    ]);
                } else {
                  $updateItem = DB::table($item_Table->name_table)->where($item_Table->name_dn, $En_solicitud->dn_netwey)
                    ->update([
                      $item_Table->name_dn => $En_solicitud->dn_portability,
                    ]);
                }

                /*No se necesita eliminar ya que solo sera cambiado el DN*/
                if ($item_Table->foreing_key == 'Y') {
                  DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                }
                $this->output->writeln("Actualizada " . $item_Table->name_table . "!");
                Portability_log::setLogPotability($En_solicitud->id, $item_Table->name_table);
              } catch (Exception $e) {
                Portability_log::setLogPotability($En_solicitud->id, $item_Table->name_table, 'ERROR', $e->getMessage());

                $En_solicitud->status        = 'E';
                $En_solicitud->date_process  = date('Y-m-d H:i:s');
                $text0                       = 'Hubo un error al procesar el id de portabilidad:' . $En_solicitud->id . ' en la tabla ' . $item_Table->name_table . ' - ' . $e->getMessage();
                $En_solicitud->details_error = $text0;
                $En_solicitud->save();

                return $this->output->writeln($text0, false);
              }
            }
          }
##################################
          /*

           */
################################################################3
          //Si llego aca significa que no hubo error y se establece como procesado.
          //
          DB::table('islim_portability')->where('id', $En_solicitud->id)->update(['date_process' => date('Y-m-d H:i:s'), 'status' => 'P']);

          //Portability_log::setLogPotability($En_solicitud->id, 'islim_portability');

          //Actualiza el registro del resultado soap de portabilidad
          try {
            DB::table('islim_soap_portability_result')->where('portID', $En_solicitud->portID)->update(['date_update' => date('Y-m-d H:i:s'), 'status' => 'PN']);
          } catch (Exception $e) {
            $this->output->writeln('Ocurrio un error en islim_soap_portability_result: ' . $e->getMessage(), false);
          }

        } catch (\Exception $e) {
          $En_solicitud->status        = 'E';
          $En_solicitud->date_process  = date('Y-m-d H:i:s');
          $En_solicitud->details_error = $e->getMessage();
          $En_solicitud->save();
          $text0 = 'Ocurrio un error al tratar de hacer la portabilidad: ' . $En_solicitud->id . ' - ' . $e->getMessage();
          $this->output->writeln($text0, false);
          Log::error($text0);
        }
      }
    }
  }
}
