<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Policy;

class Role extends Model {

	protected $table = 'islim_roles';

    protected $fillable = [
        'id', 'title', 'action', 'status'
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
            $obj = new Role;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }


    public static function getRoles() {
        $roles = Role::where('action', null)->get();
        foreach($roles as $role) {
            $subroles = Role::where('action', $role->id)->get();
            $role->subroles = $subroles;

            foreach ($role->subroles as $subrole) {
                $subpolicies = Policy::where('roles_id', $subrole->id)->get();
                $subrole->policies = $subpolicies;
            }

            $policies = Policy::where('roles_id', $role->id)->get();
            $role->policies = $policies;
        }
        return $roles;
    }

    public static function getRole($id) {
        $roles = Role::where('id', $id)->get();
        foreach($roles as $role) {
            $subroles = Role::where('action', $role->id)->get();
            $role->subroles = $subroles;

            foreach ($role->subroles as $subrole) {
                $subpolicies = Policy::where('roles_id', $subrole->id)->get();
                $subrole->policies = $subpolicies;
            }

            $policies = Policy::where('roles_id', $role->id)->get();
            $role->policies = $policies;
        }
        return $roles;
    }

    public function rolePolicies() {
        return $this->hasMany('App\Policy', 'roles_id');
    }

    public function user(){
        return $this->belongsToMany('\App\User','islim_user_roles','roles_id','user_email')
            ->withPivot('policies_id');
    }

    public function policy(){
        return $this->belongsToMany('\App\Policy','islim_user_roles','roles_id','policies_id')
            ->withPivot('user_email');
    }

}
