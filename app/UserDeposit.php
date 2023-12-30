<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeposit extends Model
{
    protected $table = 'islim_user_deposit_id';

    public $timestamps = false;

    protected $primaryKey = 'id';

    protected $fillable = [
        'email',
        'id_deposit',
        'date_reg',
        'status',
        'id_bank'
    ];

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\UserDeposit
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new UserDeposit;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function CreatedOrUpdate($data, $type) {
        $id = UserDeposit::select('id')
        				   ->where('id_deposit', $data->cod)
        				   ->first();

        if(empty($id)){
        	$dep = UserDeposit::where('email', $data->userS)
                                ->first();

            if(!empty($dep) && $dep->status == 'A' && $type == 'created'){
                return ['success' => false, 'msg' => 'El usuario ya tiene un código de depósito asignado.'];
            }else{
                if(empty($dep))
                    $dep = new UserDeposit;

                $dep->email = $data->userS;
                $dep->id_deposit = $data->cod;
                $dep->date_reg = date('Y-m-d H:i:s');
                $dep->status = 'A';
                $dep->save();

                return ['success' => true];
            }
        }else{
        	return ['success' => false, 'msg' => 'El código de depósito ya se encuentra asignado a un usuario.'];
        }

        return ['success' => false, 'msg' => 'No se pudo crear o editar el código de depósito.'];
    }


    public static function BankUser($email){
        return UserDeposit::getConnect('R')
                ->select(
                    'islim_user_deposit_id.id_deposit',
                    'islim_users.name',
                    'islim_users.last_name'
                )
                ->join(
                    'islim_users',
                    'islim_users.email',
                    'islim_user_deposit_id.email'
                )
                ->where([
                    ['islim_user_deposit_id.email', $email],
                    ['islim_user_deposit_id.status', 'A']
                ])
                ->first();
    }

    public static function getCodeByBank($id){
        return self::getConnect('R')
                    ->select('id_deposit')
                    ->where([
                        ['status', '!=', 'T'],
                        ['id_bank', $id]
                    ])
                    ->get();
    }

    public static function checkCode($code, $user = false){
        $data = self::getConnect('R')
                    ->select('id_deposit')
                    ->where([
                        ['status', '!=', 'T'],
                        ['id_deposit', $code]
                    ]);

        if($user){
            $data->where('email', '!=', $user);
        }

        return $data->count();
    }

    public static function setInactiveCodes($user, $codes = []){
        return self::getConnect('W')
                    ->where([
                        ['email', $user],
                        ['status', '!=', 'T']
                    ])
                    ->whereIn('id_bank', $codes)
                    ->update([
                        'status' => 'T'
                    ]);
    }

    public static function deleteCode($user, $code, $bank){
        return self::getConnect('W')
                    ->where([
                        ['status', '!=', 'T'],
                        ['id_bank', $bank],
                        ['id_deposit', $code]
                    ])
                    ->update(['status' => 'T']);
    }

    public static function createCode($user, $code, $bank){
        return self::getConnect('W')
                    ->insert([
                        'email' => $user,
                        'id_deposit' => $code,
                        'id_bank' => $bank,
                        'date_reg' => date('Y-m-d H:i:s'),
                        'status' => 'A'
                    ]);
    }

    public static function checkCodeAndBank($code, $bank){
        return self::getConnect('R')
                    ->select('id_deposit')
                    ->where([
                        ['status', '!=', 'T'],
                        ['id_deposit', $code],
                        ['id_bank', $bank]
                    ])
                    ->count();
    }

    public static function getUserCodes($email){
        return self::getConnect('R')
                    ->select(
                        'islim_user_deposit_id.id_deposit',
                        'islim_user_deposit_id.id_bank',
                        'islim_banks.name',
                        'islim_banks.group'
                    )
                    ->join(
                        'islim_banks',
                        'islim_banks.id',
                        'islim_user_deposit_id.id_bank'
                    )
                    ->where([
                        ['islim_user_deposit_id.status', 'A'],
                        ['islim_banks.status', 'A'],
                        ['islim_user_deposit_id.email', $email]
                    ])
                    ->get();
    }

    public static function getReport($filters = []){
        $data = self::getConnect('R')
                    ->select(
                        'islim_user_deposit_id.*',
                        'islim_users.name',
                        'islim_users.last_name',
                        'islim_banks.name as bank'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_user_deposit_id.email'
                    )
                    ->join(
                        'islim_banks',
                        'islim_banks.id',
                        'islim_user_deposit_id.id_bank'
                    )
                    ->where('islim_user_deposit_id.status', 'A');

        if($filters['type'] == 'A'){
            $data->whereIn('islim_users.status', ['A', 'S', 'I']);
        }else{
            $data->where('islim_users.status','T');
        }

        return $data->get();
    }

    public static function getUserByCode($code, $bank){
        return self::getConnect('R')
                    ->select(
                        'islim_user_deposit_id.id', 
                        'islim_user_deposit_id.email',
                        'islim_user_deposit_id.id_bank',
                        'islim_users.name',
                        'islim_users.last_name'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_user_deposit_id.email'
                    )
                    ->where([
                        ['islim_user_deposit_id.status', 'A'],
                        ['islim_user_deposit_id.id_deposit', $code],
                        ['islim_user_deposit_id.id_bank', $bank]
                    ])
                    ->first();
    }

    public static function getCodeByUser($user, $bank){
        return self::getConnect('R')
                    ->select(
                        'islim_user_deposit_id.id',
                        'islim_user_deposit_id.id_deposit',
                        'islim_users.name',
                        'islim_users.last_name'
                    )
                    ->join(
                        'islim_users',
                        'islim_users.email',
                        'islim_user_deposit_id.email'
                    )
                    ->where([
                        ['islim_user_deposit_id.status', 'A'],
                        ['islim_user_deposit_id.email', $user],
                        ['islim_user_deposit_id.id_bank', $bank]
                    ])
                    ->first();
    }
}
