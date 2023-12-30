<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WebPay extends Model {
	protected $table = 'islim_webpay';

	/*protected $fillable = [
		'id', 'periodicity', 'price_fee', 'status'
    ];*/
    
    public $timestamps = false;
}