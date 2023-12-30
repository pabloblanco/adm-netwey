<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryDebtUps extends Model {
	protected $table = 'islim_history_debt_ups_details';

	protected $fillable = [
        'id_history_debt',
        'id_sales',
        'status'
    ];
    
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';


    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new self;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getHistoryDebtUpRegister($id_history_debt,$id_sales)
    {
        return self::getConnect('W')
            ->where('id_history_debt',$id_history_debt)
            ->where('id_sales',$id_sales)
            ->first();
    }
}