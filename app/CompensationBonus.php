<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompensationBonus extends Model {
	protected $table = 'islim_compensation_bonus';
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';

    public static function getCompensationHistory($msisdn){

        $compensations = CompensationBonus::select(                               
                                    'date_bonus',
                                    'name_offer',
                                    'result',
                                    'ajuste_mb',
                                    'date_expire',
                                    'incident_id',
                                    'incident_date',
                                    'inc_hours'
                                )
                         ->where([
                            ['msisdn', $msisdn]                            
                         ])                                                 
                         ->orderBy('date_bonus','DESC');

        return $compensations;
    }
}