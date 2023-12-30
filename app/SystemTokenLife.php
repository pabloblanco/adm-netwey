<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Helpers\Google;

class SystemTokenLife extends Model {

  use HasFactory;
  protected $table = 'islim_api_all_token_life';

  protected $fillable = [
    'id', // id autoincrementado int(11)
    'token', // Cadena con el token. Varchar(255)
    'date_start', // Inicio de vida. Datetime
    'date_end', // Fin de vida. DateTime
    'status', // Activo, Eliminado. Enum(A,T)
    'tokenType', // Tipo de token. Bearer u otros
    'expire_in', // tiempo en seg de vida del token
    'api' // Enum('telmovPay','99v3','altan')
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\models\SystemModel
   */
  public static function getConnect($typeCon = false) {

    if ($typeCon) {

      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'mysql::write' : 'mysql::read');

      return $obj;

    }

    return null;

  }

  public static function getToken ($request) {

    //Seteamos un espacio de tiempo de respaldo para evitar que un token se venza justo en el momento de la ejecución de una llamada al api final
    $timeSpan = 15;

    $token = self::getConnect('R')
      ->select('*')
      ->where([
        ['status', 'A'],
        ['api', env('APP_NAME')]
      ])
      ->first();

    if (!empty($token)) {

      $date1 = new \DateTime($token->date_end);
      $date2 = new \DateTime("now");
      $timeDiffSeg = $date1->getTimestamp() - $date2->getTimestamp();

      $token = $token->attributesToArray();

      if ($date2->getTimestamp() < ($date1->getTimestamp() - $timeSpan))
        return $token;
      else
        self::deleteToken($token['id']);

    }

    return self::newToken($request);

  }

  private static function newToken($request) {

    $newToken = Google::token(env('TELMOVPAY_REFRESH_KEY'), $request);

    if ($newToken['success']) {

      $token = self::getConnect('W');
      $token->token = $newToken['data']->access_token;
      $token->tokenType = ucwords($newToken['data']->token_type);

      $dateIni = date("Y-m-d H:i:s");
      //sumo los segundos de vida
      $dateEnd = date("Y-m-d H:i:s", strtotime($dateIni . "+ " . $newToken['data']->expires_in . " seconds"));

      $token->date_start = $dateIni;
      $token->date_end  = $dateEnd;
      $token->expire_in = $newToken['data']->expires_in;
      $token->api = env('APP_NAME');
      $token->save();

      $token = $token->attributesToArray();
      
      return $token;

    } else
      return null;

  }

  public static function deleteToken($id) {

    return self::getConnect('W')
      ->where('id', $id)
      ->update([ 'status' => 'T' ]);

  }

}
