<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TokenApiSeller extends Model
{
    protected $table = 'islim_token_api_seller';

    protected $fillable = [
        'token',
        'public_token',
        'concentrator_id',
        'type',
        'ipn',
        'send_ipn',
        'use_pm_netwey',
        'status',
        'date_reg'
    ];

    public $timestamps = false;
}
