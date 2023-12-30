<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetricsBi extends Model {
	protected $table = 'islim_metrics_bi';

	protected $fillable = [
        'id', 'A60', 'A90', 'C30', 'C60', 'C90', 'D60', 'D90', 'BOP', 'EOP', 'AOP', 'date_reg', 'status'
    ];
    
    public $timestamps = false;
}