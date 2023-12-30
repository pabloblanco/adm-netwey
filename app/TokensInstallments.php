<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TokensInstallments extends Model
{
    protected $table = 'islim_tokens_installments';

	protected $fillable = [
		'tokens_cron', 
		'tokens_assigned',
		'tokens_available',
		'assigned_user',
		'process_user',
		'config_id',
		'date_reg',
		'date_update',
		'status'
    ];
    
    public $timestamps = false;
}