<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Paysheet extends Model
{
    protected $table = 'islim_paysheet';

	protected $fillable = [
		'id',
		'rfc',
		'cert_number',
		'serie',
		'folio',
		'name_file',
		'url_download',
		'date_nom',
		'date_reg',
		'type',
		'rel_type',
		'status'
    ];
    
    public $timestamps = false;
}
