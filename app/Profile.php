<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
use App\ProfileDetail;

class Profile extends Model {

	protected $table = 'islim_profiles';

    protected $fillable = [
        'id', 'name', 'hierarchy_branch', 'hierarchy', 'heredity', 'has_supervisor', 'description', 'status', 'type', 'platform'
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
            $obj = new Profile;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    //retorna perfiles de los usuarios existentes para una o varias organizaciones
    public static function getProfileByOrgs($orgs){ //$orgs es un array
        if(!empty($orgs)){
            $prof_id = session('user.profile.id');
            $users = User::getConnect('R')
                        ->where('status','A')
                        ->where(function($query) use($orgs,$prof_id) {
                            $query->whereIn('id_org',$orgs);
                            if(count($orgs) > 1 && ($prof_id == '1' || $prof_id == '2')){
                                $query = $query->orWhereNull('id_org');
                            }
                        });
            $users = $users->get();

            if($users){
                $profileDetails = ProfileDetail::getConnect('R')
                                ->whereIn('user_email',$users->pluck('email'))
                                ->where('status','A')
                                ->get();

                if($profileDetails){
                    $profiles = self::getConnect('R')
                                ->whereIn('id',$profileDetails->pluck('id_profile'))
                                ->where('status','A');

                    if(count($orgs) > 1 && $prof_id == '2'){
                        $profiles = $profiles->where('id','<>','1');
                    }

                    $profiles = $profiles->get();
                    return $profiles;
                }
            }
        }
        return null;
    }


     //retorna perfiles segun platform
    public static function getProfilesByPlatform($platform){
        $profiles = self::getConnect('R')
                                ->where('status','A');
        if(!empty($platform)){
           $profiles = $profiles->where('platform',$platform);
        }
        $profiles = $profiles->get();

        return $profiles;
    }
}