<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Organization;

class CoordinateChanges extends Model
{
	protected $table = 'islim_coordinate_changes';

	protected $fillable = [
        'id',
        'user_email',
        'dn',
        'old_lat',
        'old_lng',
        'old_point',
        'new_lat',
        'new_lng',
        'new_point',
        'sale_id',
        'date_reg'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\CoordinateChanges
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new CoordinateChanges;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getReport($filters = []){
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));

        $data = self::getConnect('R')
                    ->select(
                        'islim_clients.name as client_name',
                        'islim_clients.last_name as client_last_name',
                        'islim_clients.phone_home',
                        'islim_users.name as user_name',
                        'islim_users.last_name as user_last_name',
                        'islim_coordinate_changes.user_email',
                        'islim_coordinate_changes.dn',
                        'islim_coordinate_changes.old_lat',
                        'islim_coordinate_changes.old_lng',
                        'islim_coordinate_changes.new_lat',
                        'islim_coordinate_changes.new_lng',
                        'islim_coordinate_changes.date_reg'
                    )
                    // ->join(
                    //     'islim_users',
                    //     'islim_users.email',
                    //     'islim_coordinate_changes.user_email'
                    // )
                    ->join('islim_users',
                        function($join) use ($orgs){
                            $join->on('islim_users.email', '=', 'islim_coordinate_changes.user_email')
                                 ->whereIn('islim_users.id_org', $orgs->pluck('id'));
                        }
                    )
                    ->join(
                        'islim_client_netweys',
                        'islim_client_netweys.msisdn',
                        'islim_coordinate_changes.dn'
                    )
                    ->join(
                        'islim_clients',
                        'islim_clients.dni',
                        'islim_client_netweys.clients_dni'
                    );

        if(count($filters)){
            if(!empty($filters['dateb']) && !empty($filters['datee'])){
                $data->where([
                    ['islim_coordinate_changes.date_reg', '>=', date('Y-m-d', strtotime($filters['dateb'])).' 00:00:00'],
                ['islim_coordinate_changes.date_reg', '<=', date('Y-m-d', strtotime($filters['datee'])).' 23:59:59']
                ]);
            }

            if(empty($filters['dateb']) && !empty($filters['datee'])){
                $data->where(
                                'islim_coordinate_changes.date_reg',
                                '<=',
                                date('Y-m-d', strtotime($filters['datee'])).' 23:59:59'
                            );
            }

            if(!empty($filters['dateb']) && empty($filters['datee'])){
                $data->where(
                                'islim_coordinate_changes.date_reg',
                                '>=',
                                date('Y-m-d', strtotime($filters['dateb'])).' 00:00:00'
                            );
            }

            if(!empty($filters['userf'])){
                $data->where('islim_coordinate_changes.user_email', $filters['userf']);
            }

            if(!empty($filters['msisdn'])){
                $data->where('islim_coordinate_changes.dn', $filters['msisdn']);
            }
        }

        return $data;
    }
}
