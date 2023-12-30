<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ParentDeleteUser extends Model
{
    protected $table = 'islim_parent_delete_users';

	protected $fillable = [
        'email', 'parent_email', 'status', 'date_nodified'
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
            $obj = new ParentDeleteUser;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}