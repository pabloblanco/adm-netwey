<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FileBuyBack extends Model
{
    protected $table = 'islim_files_buy_back';

	protected $fillable = [
        'file',
        'user',
        'clients_ok',
        'clients_error',
        'date_reg'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\ClientBuyBack
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new FileBuyBack;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getListFromUser($user = false){
        if($user){
            return self::getConnect('R')
                        ->where('user', $user);
        }

        return null;
    }

    public static function createRegCallCenter($user){
        $exist = self::getConnect('W')
                       ->select('id')
                       ->where([
                        ['user', $user],
                        ['file', 'Call-Center'] //OJO No cambiar este nombre
                       ])
                       ->first();

        if(!empty($exist)){
            return $exist;
        }else{
            $reg = new FileBuyBack;
            $reg->file = 'Call-Center';
            $reg->user = $user;
            $reg->clients_ok = 0;
            $reg->clients_error = 0;
            $reg->date_reg = date('Y-m-d H:i:s');
            $reg->save();

            return $reg;
        }
    }
}