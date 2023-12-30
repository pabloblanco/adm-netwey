<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Periodicities extends Model {
	protected $table = 'islim_periodicities';

	protected $fillable = [
		'id', 'periodicity', 'price_fee', 'status'
    ];
    
    public $timestamps = false;
}