<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLocked extends Model
{
    protected $table = 'islim_locked_users';

    public $timestamps = false;

    protected $fillable = [
        'user',
        'user_do_locked',
        'user_do_unlocked',
        'date_locked',
        'date_unlocked',
        'status'
    ];

    /**
     * Método para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\UserLocked
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new UserLocked;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /**
     * Método para bloquear a un usuario, marca primero como eliminados los registros
     * de bloqueo registrados anteriormente que no hayan sido marcados como desbloqueados
     * @param String $email, usuario que bloqueo
     * @param String $user, usuario al que bloquearon
     *
     * @return App\UserLocked
    */
    public static function doLoked($email, $user){
    	$update = self::getConnect('W')
		    		 ->where([['user', $user], ['status', 'A']])
		    		 ->whereNull('user_do_unlocked')
		    		 ->update(['status' => 'T']);

    	return self::getConnect('W')
    		  		->insert([
		    		  	'user' => $user,
		    		  	'user_do_locked' => $email,
		    		  	'date_locked' => date('Y-m-d H:i:s'),
		    		  	'status' => 'A'
	    		    ]);
    }

    /**
     * Método para desbloquear a un usuario, marca como desbloqueados 
     * todos los registros que existan con estatus de bloqueo (Solo debe existir uno)
     * @param String $email, usuario que bloqueo
     * @param String $user, usuario al que bloquearon
     *
     * @return App\UserLocked
    */
    public static function doUnLocked($email, $user){
    	return self::getConnect('W')
    				->where([
    					['user', $user],
    					['status', 'A']
    				])
    				->whereNull('user_do_unlocked')
    				->update([
    					'user_do_unlocked' => $email,
    					'date_unlocked' => date('Y-m-d H:i:s')
    				]);
    }

    /**
     * Método para consultar reporte de usuarios bloqueados
     * @param Array $filters
     *
     * @return App\UserLocked
    */
    public static function getReport($filters = []){
        $data = self::getConnect('R')
                    ->select(
                        'islim_locked_users.user',
                        'islim_locked_users.user_do_locked',
                        'islim_locked_users.user_do_unlocked',
                        'islim_locked_users.date_locked',
                        'islim_locked_users.date_unlocked',
                        'islim_locked_users.status',
                        'islim_users.name as name_user',
                        'islim_users.last_name as last_name_user',
                        'users_dolock.name as name_dolockuser',
                        'users_dolock.last_name as last_name_dolockuser',
                        'users_dounlock.name as name_dounlockuser',
                        'users_dounlock.last_name as last_name_dounlockuser',
                        'islim_users.is_locked'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_locked_users.user'
                    )
                    ->join(
                        'islim_users as users_dolock',
                        'users_dolock.email',
                        'islim_locked_users.user_do_locked'
                    )
                    ->leftJoin(
                        'islim_users as users_dounlock',
                        'users_dounlock.email',
                        'islim_locked_users.user_do_unlocked'
                    )
                    ->where('islim_locked_users.status', 'A');

        if(count($filters)){
            if(!empty($filters['userLocked'])){
                $data->where('islim_locked_users.user', $filters['userLocked']);
            }

            if(!empty($filters['statusLock']) && $filters['statusLock'] == 'locked'){
                $data->where('islim_users.is_locked', 'Y')
                     ->whereNull('islim_locked_users.user_do_unlocked');
            }

            if(!empty($filters['dateb']) && !empty($filters['datee'])){
                $data->where(function($query) use ($filters){
                    $query->where([
                        ['islim_locked_users.date_locked', '>=', date('Y-m-d', strtotime($filters['dateb'])).' 00:00:00'],
                        ['islim_locked_users.date_locked', '<=', date('Y-m-d', strtotime($filters['datee'])).' 23:59:59']
                    ])
                    ->orWhere([
                        ['islim_locked_users.date_unlocked', '>=', date('Y-m-d', strtotime($filters['dateb'])).' 00:00:00'],
                        ['islim_locked_users.date_unlocked', '<=', date('Y-m-d', strtotime($filters['datee'])).' 23:59:59']
                    ]);
                });
            }

            if(empty($filters['dateb']) && !empty($filters['datee'])){
                $data->where(function($query) use ($filters){
                    $query->where(
                        'islim_locked_users.date_locked', '<=', date('Y-m-d', strtotime($filters['datee'])).' 23:59:59'
                    )
                    ->orWhere(
                        'islim_locked_users.date_unlocked', '<=', date('Y-m-d', strtotime($filters['datee'])).' 23:59:59'
                    );
                });
            }

            if(!empty($filters['dateb']) && empty($filters['datee'])){
                $data->where(function($query) use ($filters){
                    $query->where(
                        'islim_locked_users.date_locked', '>=', date('Y-m-d', strtotime($filters['dateb'])).' 23:59:59'
                    )
                    ->orWhere(
                        'islim_locked_users.date_unlocked', '>=', date('Y-m-d', strtotime($filters['dateb'])).' 23:59:59'
                    );
                });
            }
        }

        return $data;
    }
}
