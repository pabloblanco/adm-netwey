<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PolicyProfile extends Model {
    protected $table = 'islim_policy_profile';

    protected $fillable = [
        'id','profile_id','policy_id'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new PolicyProfile;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}