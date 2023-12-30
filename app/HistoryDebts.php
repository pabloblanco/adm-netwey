<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HistoryDebts extends Model {
	protected $table = 'islim_history_debts';

	protected $fillable = [
        'user_email',
        'date',
        'init_debt',
        'init_debt_sellers',
        'ups_debt_day',
        'cash_received',
        'cash_delivered',
        'conciliate_banks_day',
        'conciliate_sales_day',
        'finish_debt',
        'finish_debt_sellers',
        'status',
        'date_reg',
        'date_modified'
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

    public static function getHistoryDebtsRegister($email,$date)
    {
        return self::getConnect('W')
            ->where('user_email',$email)
            ->where('date', $date)
            ->first();
    }
}