<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Financing extends Model
{
    protected $table = 'islim_financing';

    protected $fillable = [
        'name','amount_financing','total_amount','SEMANAL','MENSUAL','QUINCENAL','date_reg','status'
    ];

    public $timestamps = false;
}
