<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;
use App\User;


class StockProva extends Model
{
  protected $table = 'islim_stock_prova';

  protected $fillable = [
    'file_name',
    'status',
    'date_reg'];

  protected $primaryKey = 'id';

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *

   * @return App\StockProva
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

  public static function getPenddingOrders($parent)
  {
    $coord = User::getConnect('R')
      ->select('email')
      ->where('parent_email',$parent)
      ->where('status','A')
      ->get();

    $data = self::getConnect('R')
      ->select(
        'islim_stock_prova.file_name',
        'islim_stock_prova_detail.folio',
        'islim_stock_prova.date_reg'
      )
      ->join('islim_stock_prova_detail','islim_stock_prova.id','islim_stock_prova_detail.id_stock_prova')
      ->where('islim_stock_prova_detail.status', 'A')
      ->whereIn('islim_stock_prova_detail.users', $coord->pluck('email'));

    $data = $data->groupBy('islim_stock_prova_detail.folio')->get();
    return $data;
  }

  public static function existsFile($file, $status = 'A'){
    $data = self::getConnect('R')
                ->select('id')
                ->where('file_name', $file);

    if($status){
      $data->where('status', $status);
    }

    return $data->count();
  }
}
