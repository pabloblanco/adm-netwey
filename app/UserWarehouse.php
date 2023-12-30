<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserWarehouse extends Model {

    protected $table = 'islim_wh_users';

    protected $fillable = [
        'users_email', 'warehouses_id', 'date_reg', 'status'
    ];

    protected $primaryKey = [
        'users_email',
        'warehouses_id'
    ];

    public static function uwhcount ($user_email){
        $whs= UserWarehouse::where(['users_email'=>$user_email,'status'=>'A'])->count();
        return $whs;
    }

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
            $obj = new UserWarehouse;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }
}