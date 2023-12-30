<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\mobility;
use App\TheftOrLoss;
use App\ImeiLock;
use App\Suspend;

class SuspendedByAdmin extends Model
{
    protected $table = 'islim_suspended_by_admin';

    protected $fillable = [
        'msisdn',
        'user_email',
        'date_reg',
    ];

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\SuspendedByAdmin
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new self;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');
            return $obj;
        }
        return null;
    }

    public static function getSuspendedHistory($filters = [])
    {
        $sw=0;
        if(empty($filters['typesuspended']) || $filters['typesuspended']=='callcenter'){
            $qry = self::getConnect('R')
                ->select(
                    'islim_suspended_by_admin.msisdn',
                    DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                    DB::raw('CONCAT("Call Center") as typesuspended'),
                    'islim_suspended_by_admin.date_reg'
                )
                ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_suspended_by_admin.msisdn')
                ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni');
            if (is_array($filters)) {
                if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                    $qry->whereBetween('islim_suspended_by_admin.date_reg', [$filters['dateb'], $filters['datee']]);
                } elseif (!empty($filters['dateb'])) {
                    $qry->where('islim_suspended_by_admin.date_reg', '>=', $filters['dateb']);
                } elseif (!empty($filters['datee'])) {
                    $qry->where('islim_suspended_by_admin.date_reg', '<=', $filters['datee']);
                }
                if(!empty($filters['msisdn'])){
                    $qry->where('islim_suspended_by_admin.msisdn',$filters['msisdn']);
                }
            }
            $data=$qry;
            $sw=1;
        }
        if(empty($filters['typesuspended']) || $filters['typesuspended']=='mobility'){
             $qry = mobility::getConnect('R')
                ->select(
                    'islim_mobility.msisdn',
                    DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                    DB::raw('CONCAT("Movilidad") as typesuspended'),
                    'islim_mobility.date_reg'
                )
                ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_mobility.msisdn')
                ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni');
            if (is_array($filters)) {
                if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                    $qry->whereBetween('islim_mobility.date_reg', [$filters['dateb'], $filters['datee']]);
                } elseif (!empty($filters['dateb'])) {
                    $qry->where('islim_mobility.date_reg', '>=', $filters['dateb']);
                } elseif (!empty($filters['datee'])) {
                    $qry->where('islim_mobility.date_reg', '<=', $filters['datee']);
                }
                if(!empty($filters['msisdn'])){
                    $qry->where('islim_mobility.msisdn',$filters['msisdn']);
                }
            }
            if($sw == 1){
                $data=$data->union($qry);
            }
            else{
                $data=$qry;
                $sw=1;
            }
        }
        if(empty($filters['typesuspended']) || $filters['typesuspended']=='theftorloss'){
             $qry = TheftOrLoss::getConnect('R')
                ->select(
                    'islim_theft_Loss_msisdn.msisdn',
                    DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                    DB::raw('CONCAT("Robo o Extravío") as typesuspended'),
                    'islim_theft_Loss_msisdn.date_reg'
                )
                ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_theft_Loss_msisdn.msisdn')
                ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni');
            if (is_array($filters)) {
                if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                    $qry->whereBetween('islim_theft_Loss_msisdn.date_reg', [$filters['dateb'], $filters['datee']]);
                } elseif (!empty($filters['dateb'])) {
                    $qry->where('islim_theft_Loss_msisdn.date_reg', '>=', $filters['dateb']);
                } elseif (!empty($filters['datee'])) {
                    $qry->where('islim_theft_Loss_msisdn.date_reg', '<=', $filters['datee']);
                }
                if(!empty($filters['msisdn'])){
                    $qry->where('islim_theft_Loss_msisdn.msisdn',$filters['msisdn']);
                }
            }
            if($sw == 1){
                $data=$data->union($qry);
            }
            else{
                $data=$qry;
                $sw=1;
            }
        }

        if(empty($filters['typesuspended']) || $filters['typesuspended']=='inactive'){
             $qry = Suspend::getConnect('R')
                ->select(
                    'islim_suspends.msisdn',
                    DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                    DB::raw('CONCAT("Inactividad") as typesuspended'),
                    'islim_suspends.date_reg'
                )
                ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_suspends.msisdn')
                ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni');
            if (is_array($filters)) {
                if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                    $qry->whereBetween('islim_suspends.date_reg', [$filters['dateb'], $filters['datee']]);
                } elseif (!empty($filters['dateb'])) {
                    $qry->where('islim_suspends.date_reg', '>=', $filters['dateb']);
                } elseif (!empty($filters['datee'])) {
                    $qry->where('islim_suspends.date_reg', '<=', $filters['datee']);
                }
                if(!empty($filters['msisdn'])){
                    $qry->where('islim_suspends.msisdn',$filters['msisdn']);
                }
            }
            if($sw == 1){
                $data=$data->union($qry);
            }
            else{
                $data=$qry;
                $sw=1;
            }
        }

        if(empty($filters['typesuspended']) || $filters['typesuspended']=='imeilock'){
             $qry = ImeiLock::getConnect('R')
                ->select(
                    'islim_imei_locks.msisdn',
                    DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as client'),
                    DB::raw('CONCAT("Imei Bloqueado") as typesuspended'),
                    'islim_imei_locks.date_reg'
                )
                ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_imei_locks.msisdn')
                ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni');
            if (is_array($filters)) {
                if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                    $qry->whereBetween('islim_imei_locks.date_reg', [$filters['dateb'], $filters['datee']]);
                } elseif (!empty($filters['dateb'])) {
                    $qry->where('islim_imei_locks.date_reg', '>=', $filters['dateb']);
                } elseif (!empty($filters['datee'])) {
                    $qry->where('islim_imei_locks.date_reg', '<=', $filters['datee']);
                }
                if(!empty($filters['msisdn'])){
                    $qry->where('islim_imei_locks.msisdn',$filters['msisdn']);
                }
            }
            if($sw == 1){
                $data=$data->union($qry);
            }
            else{
                $data=$qry;
                $sw=1;
            }
        }

        $data = $data->orderBy('date_reg', 'DESC');

        // print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
        // exit;

        $data = $data->get();
        return $data;
    }
}
