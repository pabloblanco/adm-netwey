<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Sale;

class Migrations extends Model
{
    protected $table = 'islim_migrations';

    protected $fillable = [
        'msisdn_new',
        'status',
        'type'
    ];

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Migrations
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new Migrations;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function isMigrations($msisdn = false){
        if($msisdn){
            $mob = self::getConnect('R')
                         ->select('msisdn_new')
                         ->where([
                            ['msisdn_old', $msisdn],
                            ['type','R'],
                            ['status','P']
                        ])
                         ->first();
            if(!empty($mob)){
                return true;
            }
        }
        return false;
    }

    public static function getDTMigrationsDataReport($filters = [])
    {

        $data = self::getConnect('R')
            ->select(
                DB::raw('CONCAT(islim_clients.name, " ",islim_clients.last_name) AS client'),
                'islim_migrations.msisdn_old',
                'iso.date_reg as alta_old',
                DB::raw('CONCAT(iuo.name, " ",iuo.last_name) AS vendor_old'),
                DB::raw("(SELECT MAX(isl.date_reg) FROM islim_sales as isl where isl.type='R' and isl.status <> 'T' and isl.msisdn = islim_migrations.msisdn_old) as last_recharge"),
                'iado.imei as imei_code',
                'iao.artic_type',
                'islim_migrations.msisdn_new',
                'islim_migrations.date_reg as date_migration',
                DB::raw('CONCAT(iun.name, " ",iun.last_name) AS vendor_new'),
                'ip.title as pack'

            )
            ->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_migrations.msisdn_old')
            ->join('islim_clients', 'islim_clients.dni', '=', 'islim_client_netweys.clients_dni')
            ->join('islim_sales as iso',
                function($join){
                    $join->on('iso.msisdn', '=', 'islim_migrations.msisdn_old')
                            ->where([
                                ['iso.type','P'],
                                ['iso.status','<>','T']
                            ]);
                }
            )
            ->join('islim_users as iuo', 'iuo.email', '=', 'iso.users_email')
            ->join('islim_inv_arti_details as iado', 'iado.msisdn', '=', 'islim_migrations.msisdn_old')
            ->join('islim_inv_articles as iao', 'iao.id', '=', 'iado.inv_article_id')
            ->join('islim_sales as isn',
                function($join){
                    $join->on('isn.msisdn', '=', 'islim_migrations.msisdn_new')
                            ->where([
                                ['isn.type','P'],
                                ['isn.status','<>','T']
                            ]);
                }
            )
            ->join('islim_users as iun', 'iun.email', '=', 'isn.users_email')
            ->join('islim_packs as ip', 'ip.id', '=', 'isn.packs_id')
            ->where([
                ['islim_migrations.type','R']
            ])
            ->whereIn('islim_migrations.status',['P','E']);

        if (is_array($filters)) {
            if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
                $data->whereBetween('islim_migrations.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
            } elseif (!empty($filters['dateStar'])) {
                $data->where('islim_migrations.date_reg', '>=', $filters['dateStar']);
            } elseif (!empty($filters['dateEnd'])) {
                $data->where('islim_migrations.date_reg', '<=', $filters['dateEnd']);
            }
        }


         // $query = vsprintf(str_replace('?', '%s', $data->toSql()), collect($data->getBindings())->map(function ($binding) {
         //        return is_numeric($binding) ? $binding : "'{$binding}'";
         //    })->toArray());

         //    Log::info($query);



        $data = $data->orderBy('islim_migrations.date_reg', 'DESC')->get();

        // foreach ($data as $key => $elem) {
        //     $last_recharge = Sale::getConnect('R')
        //                     ->where([
        //                         ['type','R'],
        //                         ['status','<>','T'],
        //                         ['msisdn',$elem->msisdn_old]
        //                     ])
        //                     ->max('date_reg');
        //     Log::info($last_recharge);
        //     $elem->last_recharge = $last_recharge;
        // }

        return $data;

    }
}