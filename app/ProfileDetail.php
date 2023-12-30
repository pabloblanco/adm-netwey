<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProfileDetail extends Model
{

  protected $table = 'islim_profile_details';

  protected $fillable = [
    'id',
    'id_profile',
    'user_email',
    'status'];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new ProfileDetail;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getUserCallCenter($IdPlatfrom, $email)
  {
    $users = self::getConnect('R')
      ->select(
        DB::raw('CONCAT(
        IFNULL(islim_users.name,"")," ",
        IFNULL(islim_users.last_name,"") ) AS UserFullName') /*,
    'islim_profile_details.user_email'*/
      )
      ->join('islim_profiles',
        'islim_profiles.id',
        'islim_profile_details.id_profile')
      ->join('islim_users',
        'islim_users.email',
        'islim_profile_details.user_email')
      ->where([
        ['islim_profile_details.id_profile', $IdPlatfrom],
        ['islim_profile_details.user_email', $email],
        ['islim_profile_details.status', 'A']]);

    if ($IdPlatfrom != '1') {
      $users = $users->where('islim_profiles.hierarchy_branch', 'C');
    }
    return $users->first();
  }

}
