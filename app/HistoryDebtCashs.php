<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryDebtCashs extends Model {
	protected $table = 'islim_history_debt_cash_details';

	protected $fillable = [
        'id_history_debt',
        'id_asigned_sales',
        'type',
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

    public static function getHistoryDebtCashRegister($id_history_debt,$id_asigned_sales,$type)
    {
        return self::getConnect('W')
            ->where('id_history_debt',$id_history_debt)
            ->where('id_asigned_sales',$id_asigned_sales)
            ->where('type',$type)
            ->first();
    }
}