<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebManagementFee extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'islim_web_tarifas';

  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  // public $timestamps = false;
  const CREATED_AT = 'date_reg';
  const UPDATED_AT = 'date_update';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'parent_id',
    'description_web',
    'url_file',
    'position'];

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\Product
   */
  public static function getConnect($typeCon = false)
  {
    if (!$typeCon) {
      return null;
    }

    $obj = new WebManagementFee;
    $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
    return $obj;
  }

  /**
   * Obtiene el listado de registros
   *
   * @param array $filters
   */
  public static function getRowsDatatable($filters = [])
  {
    $query = WebManagementFee::getConnect('R')
      ->select('islim_web_tarifas.id', 'islim_web_tarifas.descripcion_web', 't2.descripcion_web AS belongs_to', 'islim_web_tarifas.date_reg')
      ->leftJoin('islim_web_tarifas AS t2', 't2.id', 'islim_web_tarifas.parent_id')
      ->where('islim_web_tarifas.status', 'A');

    if (!empty($filters['search'])) {
      $words = explode('*', $filters['search']);
      $words = implode('', $words);
      $query->where(function ($query) use ($words) {
        $query->whereRaw("CONCAT(islim_web_tarifas.descripcion_web, ' ', t2.descripcion_web) LIKE ?", ['%' . $words . '%']);
      });
    }

    $query->orderBy('id', 'desc')
      ->get();

    return $query;
  }

  /**
   * Obtiene el listado de tarifas en las que los hijos no contienen un archivo adjunto
   */
  public static function getFeeWithChilds()
  {
    return WebManagementFee::select('id', 'parent_id', 'descripcion_web')
      ->with(['childs' => function ($query) {
        $query->where('url_file', null);
        $query->where('status', 'A');
        $query->orderBy('position', 'ASC');
        $query->orderBy('descripcion_web', 'ASC');
      }])
      ->where([
        ['parent_id', null],
        ['url_file', null],
        ['status', 'A']])
      ->orderBy('parent_id', 'ASC')
      ->orderBy('position', 'ASC')
      ->orderBy('descripcion_web', 'ASC')
      ->get();
  }

  /**
   *
   */
  public function childs()
  {
    return $this->hasMany('App\WebManagementFee', 'parent_id', 'id');
  }
}
