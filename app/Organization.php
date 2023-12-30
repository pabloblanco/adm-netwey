<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model {
	protected $table = 'islim_dts_organizations';

	protected $fillable = [
        'id',
        'rfc',
        'business_name',
        'address',
        'contact_name',
        'contact_email',
        'contact_address',
        'contact_phone',
        'type',
        'status',
        'is_root'
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
            $obj = new Organization;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getOrgs($id = false){
        $data = self::getConnect('R')
                      ->select('id', 'business_name')
                      ->where('status','A');

        if(!empty($id)){
            $data->where('id', $id);
        }

        return $data->get();
    }

    //retorna las organizaciones permitidas para un usuario segun la organizacion a la que pertenece
    public static function getOrgsPermitByOrgs($org_id){
        if(!empty($org_id)){
            $orgs = self::getConnect('R')
                    ->where(
                        ['id' => $org_id]
                    );
            $test=$orgs->first();
            if($test){
                if($test->is_root=='N')
                    return $orgs->get();
                if($test->is_root=='Y')
                    return self::getConnect('R')->all();
            }
            else
                return null;
        }
        else{
           return self::getConnect('R')->all();
        }
    }
}