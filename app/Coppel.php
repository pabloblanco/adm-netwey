<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Coppel extends Model
{
  protected $table = 'islim_coppel';

  protected $fillable = [
    'transaction_code',
    'request',
    'token',
    'ip',
    'auth_code',
    'auth',
    'signature',
    'msisdn',
    'amount',
    'clients_dni',
    'service_id',
    'pack_id',
    'articles_id',
    'user_email',
    'user_associated',
    'error_coppel',
    'error_altan',
    'status',
    'date_reg',
    'date_associated'];

  protected $primaryKey = 'id';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Coppel
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new self;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
      return $obj;
    }
    return null;
  }

  public static function getCoppelSales($filters = [])
  {
    $data = self::getConnect('R')
      ->select(
        'islim_coppel.msisdn',
        DB::raw('CONCAT(islim_users.name," ",islim_users.last_name) as seller_name'),
        DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client_name'),
        'islim_clients.phone_home as client_phone',
        'islim_coppel.amount',
        'islim_coppel.date_reg',
        'islim_packs.title as pack',
        'islim_inv_articles.title as article',
        'islim_coppel.status'
      )
      ->join('islim_users', 'islim_users.email', 'islim_coppel.user_email')
      ->join('islim_clients', 'islim_clients.dni', 'islim_coppel.clients_dni')
      ->join('islim_packs', 'islim_packs.id', 'islim_coppel.pack_id')
      ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_coppel.articles_id')
      ->where('islim_coppel.status', '<>', 'T');

    if (is_array($filters)) {
      if (!empty($filters['dateb']) && !empty($filters['datee'])) {
        $data->whereBetween('islim_coppel.date_reg', [$filters['dateb'], $filters['datee']]);
      } elseif (!empty($filters['dateb'])) {
        $data->where('islim_coppel.date_reg', '>=', $filters['dateb']);
      } elseif (!empty($filters['datee'])) {
        $data->where('islim_coppel.date_reg', '<=', $filters['datee']);
      }
    }

    $data = $data->get();
    return $data;
  }
}
