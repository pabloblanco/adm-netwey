<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Policy extends Model {

	protected $table = 'islim_policies';

    protected $fillable = [
        'id', 'roles_id', 'name', 'code', 'type', 'description'
    ];

    public $timestamps = false;

     public function role(){
        return $this->belongsToMany('\App\Role','islim_user_roles','policies_id','roles_id')
            ->withPivot('user_email');
    }

    public function user(){
        return $this->belongsToMany('\App\User','islim_user_roles','policies_id','user_email')
            ->withPivot('roles_id');
    }

     /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Policy;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

}
