<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SellerInventoryTemp extends Model
{
  protected $table = 'islim_inv_assignments_temp';

  protected $fillable = [
    'user_email',
    'inv_arti_details_id',
    'status',
    'assigned_by',
    'date_reg',
    'date_status',
    'notification_view',
    'reason_reject',
    'reject_notification_view'
  ];

  /*protected $primaryKey = [
  'users_email',
  'inv_arti_details_id'
  ];*/

  protected $primaryKey = 'id';

  public $incrementing = true;

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\SellerInventoryTemp
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SellerInventoryTemp;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

}
