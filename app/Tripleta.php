<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Tripleta extends Model {
	protected $table = 'islim_tripletas';

	/*protected $fillable = [
		'id', 'periodicity', 'price_fee', 'status'
    ];*/
    
    public $timestamps = false;
}