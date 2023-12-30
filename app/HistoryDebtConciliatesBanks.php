<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryDebtConciliatesBanks extends Model {
	protected $table = 'islim_history_debt_conciliate_details';

	protected $fillable = [
        'id_history_debt',
        'id_bank_dep',
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

    public static function getHistoryDebtConcBankRegister($id_history_debt,$id_bank_dep)
    {
        return self::getConnect('W')
            ->where('id_history_debt',$id_history_debt)
            ->where('id_bank_dep',$id_bank_dep)
            ->first();
    }
}