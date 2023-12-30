<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model {
    protected $table = 'islim_banks';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name', 
        'address', 
        'numAcount', 
        'typeAcount', 
        'DNI', 
        'Date_reg', 
        'group', 
        'status',
        'column',
        'column_analyze'
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
            $obj = new Bank;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }


    public static function getBankByGruop($group){
        return self::getConnect('R')
                    ->select('id', 'name', 'group', 'numAcount')
                    ->where([
                        ['status', 'A'],
                        ['group', $group]
                    ])
                    ->get();
    }

    public static function getBankById($id){
        return self::getConnect('R')
                    ->select(
                        'name', 
                        'numAcount', 
                        'group',
                        'column',
                        'column_analyze'
                    )
                    ->where('id', $id)
                    ->first();
    }
}