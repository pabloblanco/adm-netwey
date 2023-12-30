<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;

class UserRole extends Pivot
{

	protected $table = 'islim_user_roles';

    protected $fillable = [
        'user_email', 'policies_id', 'roles_id', 'value', 'date_reg', 'status'
    ];

    protected $primaryKey = 'user_email';
    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new UserRole;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}
