<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PreDesactivate extends Model {
	protected $table = 'islim_pre_desactivate_dns';
    
    public $timestamps = false;

    public static function saveStatus($msisdn = false, $status = 'P', $orderId = false){
    	if($msisdn && $orderId){
    		$pre = new PreDesactivate;

			$pre->msisdn = $msisdn;
			$pre->status = $status;
			$pre->orderId = $orderId;
			$pre->date_reg = date('Y-m-d H:i:s');
			$pre->save();

    		return true;
    	}
    	return false;
    }
}