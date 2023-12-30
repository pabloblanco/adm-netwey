<?php
/*
Creado por: Luis
Enero 2021
 */
namespace App;

use App\Organization;
use Illuminate\Database\Eloquent\Model;

class ClientsUpdateCall extends Model
{
  protected $table    = 'islim_clients_report_update';
  protected $fillable = [
    'id',
    'clients_dni',
    'users_mail',
    'date_reg',
    'campo',
    'data_last',
    'data_new',
    'msisdn'];
  protected $hidden = [

  ];
  protected $primaryKey = 'id';
  public $timestamps    = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ClientsUpdateCall;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

/*
 * Retorna todos los reportes de actualizacion de clientes hechos por el call center filtando por rango de fecha
 */
  public static function getDTUpdatePeriodDataReport($filters = [])
  {
    $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
    $data = self::getConnect('R')
      ->select(
        'islim_clients_report_update.id',
        'islim_clients_report_update.msisdn',
        'islim_clients_report_update.users_mail',
        'islim_clients_report_update.date_reg',
        'islim_clients_report_update.campo',
        'islim_clients_report_update.data_last',
        'islim_clients_report_update.data_new'
      )
      ->join('islim_users',
        function ($join) use ($orgs) {
          $join->on('islim_users.email', '=', 'islim_clients_report_update.users_mail')
            ->whereIn('islim_users.id_org', $orgs->pluck('id'));
        }
      );

    if (is_array($filters)) {
      if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
        $data->whereBetween('islim_clients_report_update.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
      } elseif (!empty($filters['dateStar'])) {
        $data->where('islim_clients_report_update.date_reg', '>=', $filters['dateStar']);
      } elseif (!empty($filters['dateEnd'])) {
        $data->where('islim_clients_report_update.date_reg', '<=', $filters['dateEnd']);
      }
    }
    $data = $data->orderBy('islim_clients_report_update.date_reg', 'DESC')->get();
    // print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
    // exit;
    return $data;
  }
}
