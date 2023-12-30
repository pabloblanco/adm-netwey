<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ConfigIstallments extends Model
{
    protected $table = 'islim_config_installments';

	protected $fillable = [
		'percentage',
		'end_day',
		'week_sales',
		'days_quote',
		'quotes',
		'firts_pay',
		'user_reg',
		'm_permit_c',
		'm_permit_s',
		'date_reg',
		'status'
    ];
    
    public $timestamps = false;
}
