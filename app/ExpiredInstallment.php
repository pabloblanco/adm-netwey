<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpiredInstallment extends Model
{
    protected $table = 'islim_expired_installments';

	protected $fillable = [
		'id_sale_installment',
		'quote',
		'amount',
		'date_expired',
		'date_reg',
		'status'
    ];

    public $timestamps = false;
}
