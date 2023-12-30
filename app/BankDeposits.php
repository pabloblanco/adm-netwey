<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankDeposits extends Model
{
    protected $table = 'islim_bank_deposits';

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new BankDeposits;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function DepositsByUser($email,$status = null,$cant = null){
        $deposits = self::getConnect('R')
                ->select(
                    'islim_bank_deposits.id',
                    'islim_bank_deposits.cod_auth',
                    'islim_bank_deposits.amount',
                    'islim_bank_deposits.date_dep',
                    'islim_bank_deposits.date_reg',
                    'islim_bank_deposits.status',
                    'islim_banks.name'
                )
                ->leftJoin(
                    'islim_banks',
                    'islim_banks.id',
                    'islim_bank_deposits.bank'
                )
                ->where([
                    ['islim_bank_deposits.email', $email]
                ]);

        if(!empty($status)){
            $deposits = $deposits->where([
                    ['islim_bank_deposits.status', $status]
                ]);
        }
        $deposits = $deposits->orderBy('islim_bank_deposits.id', 'DESC');

        if(!empty($cant)){
            $deposits = $deposits->limit($cant);
        }
        $deposits = $deposits->get();

        return $deposits;
    }

    public static function existDeposit($cod_auth, $idBank){
        return self::getConnect('R')
                    ->where([
                        ['cod_auth', $cod_auth], 
                        ['bank', $idBank]
                    ])
                    ->count();
    }

    public static function getDeposit($cod_auth, $idBank){
        return self::getConnect('R')
                    ->select(
                        'id',
                        'email',
                        'status',
                        'reason'
                    )
                    ->where([
                        ['cod_auth', $cod_auth], 
                        ['bank', $idBank]
                    ])
                    ->first();
    }

    public static function getNotAssignedDeposit(){
        return self::getConnect('R')
                    ->select(
                        'islim_bank_deposits.id',
                        'islim_bank_deposits.amount',
                        'islim_bank_deposits.cod_auth',
                        'islim_bank_deposits.concept',
                        'islim_bank_deposits.date_dep',
                        'islim_bank_deposits.reason',
                        'islim_bank_deposits.date_reg',
                        'islim_banks.name',
                        'islim_banks.numAcount'
                    )
                    ->join(
                        'islim_banks',
                        'islim_banks.id',
                        'islim_bank_deposits.bank'
                    )
                    ->where('islim_bank_deposits.status', 'PA')
                    ->orderBy('islim_bank_deposits.date_reg', 'DESC')
                    ->get();
    }

    public static function deleteDepNotAs($dep, $user){
        return self::getConnect('W')
                    ->where([['id', $dep],['status', '!=', 'T']])
                    ->update([
                        'status' => 'T',
                        'user_delete' => $user,
                        'date_delete' => date('Y-m-d H:i:s')
                    ]);
    }

    public static function getDepositNotAssignedById($id){
        return self::getConnect('R')
                    ->select(
                        'bank',
                        'amount'
                    )
                    ->where([
                        ['status', 'PA'],
                        ['id', $id]
                    ])
                    ->first();
    }

    public static function getTotalDebt($user){
        return self::getConnect('R')
                    ->select('amount')
                    ->where([
                        ['status', 'P'],
                        ['email', $user]
                    ])
                    ->sum('amount');
    }

    public static function getLastDeposit($user){
        return self::getConnect('R')
                    ->select(
                        'islim_bank_deposits.bank',
                        'islim_bank_deposits.date_dep as last_deposit_date',
                        'islim_banks.name as banco'
                    )
                    ->join(
                        'islim_banks',
                        'islim_banks.id',
                        'islim_bank_deposits.bank'
                    )
                    ->where([
                        ['islim_bank_deposits.email', $user],
                        ['islim_bank_deposits.status', 'P']
                    ])
                    ->orderBy('islim_bank_deposits.id', 'DESC')
                    ->first();
    }
}
