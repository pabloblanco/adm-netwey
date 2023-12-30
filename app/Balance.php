<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'islim_balances';

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Balance;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function createLine($email = false, $amount = false, $type = false, $id = false, $typeE = 'N'){
    	if($amount !== false && $type && $email && $id !== false){
    		$lastBalance = Balance::getConnect('R')
                                    ->select('balance')
	    						    ->where([
	    						    	['status', 'A'],
	    						    	['user', $email]
	    						    ])
	    						    ->orderBy('id', 'DESC')
	    						    ->first();

	    	if(empty($lastBalance)) $lastBalance = 0;
	    	else $lastBalance = $lastBalance->balance;

	    	if($type == 'I')
	    		$lastBalance += $amount;
	    	else
	    		$lastBalance -= $amount;

	    	$balance = Balance::getConnect('W');
	    	$balance->user = $email;
	    	$balance->amount = $amount;
	    	$balance->type = $type;
	    	$balance->balance = $lastBalance;
	    	if($type == 'I')
	    		$balance->id_deposit = $id;
	    	else{
	    		$balance->id_asigned_sale = $id;
	    		$balance->type_e = $typeE;
	    	}
	    	$balance->date_reg = date('Y-m-d H:i:s');
	    	$balance->status = 'A';
	    	$balance->save();
	    	return true;
    	}
    	return false;
    }

    public static function getBalanceToDate($email,$date){
    	$ing = Balance::getConnect('R')
			    ->where([
			    	['status', 'A'],
			    	['user', $email],
			    	['date_reg','<=',$date],
			    	['type','I']
			    ])
			    ->sum('amount');

		$egr = Balance::getConnect('R')
			    ->where([
			    	['status', 'A'],
			    	['user', $email],
			    	['date_reg','<=',$date],
			    	['type','E']
			    ])
			    ->sum('amount');

		return ($ing - $egr);

    }
}
