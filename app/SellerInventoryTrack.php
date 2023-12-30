<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\UserRole;
use App\Organization;
use Carbon\Carbon;
use Log;

class SellerInventoryTrack extends Model {
	protected $table = 'islim_inv_assignments_tracks';

	protected $fillable = [
        'inv_arti_details_id',
        'origin_user',
        'origin_wh',
        'destination_user',
        'destination_wh',
        'assigned_by',
        'comment',
        'date_reg'
    ];
    /*protected $primaryKey = [
        'users_email',
        'inv_arti_details_id'
    ];*/

    protected $primaryKey = 'id';

    public $incrementing = true;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\SellerInventory
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new SellerInventoryTrack;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function setInventoryTrack($arti_detail_id,$origin_user,$origin_wh,$destination_user, $destination_wh, $assigned_by, $comment = null){

        $track= self::getConnect('W');
        $track->inv_arti_details_id = $arti_detail_id;
        $track->origin_user = $origin_user;
        $track->origin_wh = $origin_wh;
        $track->destination_user = $destination_user;
        $track->destination_wh = $destination_wh;
        $track->assigned_by = $assigned_by;
        $track->comment = $comment;
        $track->date_reg = date('Y-m-d H:i:s',time());
        $track->save();
    }

    public static function getInventoryTracks($filters = []){
        $data = self::getConnect('R')
          ->select(
            'islim_inv_assignments_tracks.inv_arti_details_id as id',
            'islim_inv_arti_details.msisdn',
            'islim_inv_articles.sku',
            'islim_inv_articles.title as article'
          )
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', 'islim_inv_assignments_tracks.inv_arti_details_id')
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id');


        if (is_array($filters)) {
            if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                $data->whereBetween('islim_inv_assignments_tracks.date_reg', [$filters['dateb'], $filters['datee']]);
            } elseif (!empty($filters['dateb'])) {
                $data->where('islim_inv_assignments_tracks.date_reg', '>=', $filters['dateb']);
            } elseif (!empty($filters['datee'])) {
                $data->where('islim_inv_assignments_tracks.date_reg', '<=', $filters['datee']);
            }

            if (!empty($filters['is_sell'])){
                if($filters['is_sell'] == 'N'){
                    $data->whereNotIn('islim_inv_arti_details.status',['V','T']);
                }
                else{
                    $data->where('islim_inv_arti_details.status','V');
                }
            }

            if(!empty($filters['msisdn_select'])){
                $data->whereIn('islim_inv_arti_details.msisdn',$filters['msisdn_select']);
            }
        }

        $data = $data->groupBy('islim_inv_arti_details.msisdn')->orderBy('islim_inv_arti_details.msisdn')->get();
        return $data;
    }

    public static function getInventoryTracksDetails($id){
        $data = self::getConnect('R')
              ->select(
                'islim_inv_assignments_tracks.date_reg',
                DB::raw('CONCAT(islim_users_origin.name," ",islim_users_origin.last_name) as origin_user'),
                DB::raw('CONCAT("BODEGA ",islim_warehouses_origin.name) as origin_wh'),
                DB::raw('CONCAT(islim_users_destination.name," ",islim_users_destination.last_name) as destination_user'),
                DB::raw('CONCAT("BODEGA ",islim_warehouses_destination.name) as destination_wh'),
                DB::raw('CONCAT(islim_assigned_by.name," ",islim_assigned_by.last_name) as assigned_by'),
                'islim_inv_assignments_tracks.comment'
              )
              ->leftJoin('islim_users as islim_users_origin','islim_users_origin.email','islim_inv_assignments_tracks.origin_user')
              ->leftJoin('islim_warehouses as islim_warehouses_origin','islim_warehouses_origin.id','islim_inv_assignments_tracks.origin_wh')
              ->leftJoin('islim_users as islim_users_destination','islim_users_destination.email','islim_inv_assignments_tracks.destination_user')
              ->leftJoin('islim_warehouses as islim_warehouses_destination','islim_warehouses_destination.id','islim_inv_assignments_tracks.destination_wh')
              ->leftJoin('islim_users as islim_assigned_by','islim_assigned_by.email','islim_inv_assignments_tracks.assigned_by')
              ->where('islim_inv_assignments_tracks.inv_arti_details_id',$id)
              ->orderBy('islim_inv_assignments_tracks.date_reg','ASC')
              ->get();

        return $data;
    }

    public static function getInventoryTracksReport($filters = []){
        $data = self::getConnect('R')
          ->select(
            'islim_inv_arti_details.msisdn',
            'islim_inv_articles.sku',
            'islim_inv_articles.title as article',
            'islim_inv_assignments_tracks.date_reg',
            DB::raw('CONCAT(islim_users_origin.name," ",islim_users_origin.last_name) as origin_user'),
            DB::raw('CONCAT("BODEGA ",islim_warehouses_origin.name) as origin_wh'),
            DB::raw('CONCAT(islim_users_destination.name," ",islim_users_destination.last_name) as destination_user'),
            DB::raw('CONCAT("BODEGA ",islim_warehouses_destination.name) as destination_wh'),
            DB::raw('CONCAT(islim_assigned_by.name," ",islim_assigned_by.last_name) as assigned_by'),
            'islim_inv_assignments_tracks.comment'
          )
          ->join('islim_inv_arti_details', 'islim_inv_arti_details.id', 'islim_inv_assignments_tracks.inv_arti_details_id')
          ->join('islim_inv_articles', 'islim_inv_articles.id', 'islim_inv_arti_details.inv_article_id')
          ->leftJoin('islim_users as islim_users_origin','islim_users_origin.email','islim_inv_assignments_tracks.origin_user')
              ->leftJoin('islim_warehouses as islim_warehouses_origin','islim_warehouses_origin.id','islim_inv_assignments_tracks.origin_wh')
              ->leftJoin('islim_users as islim_users_destination','islim_users_destination.email','islim_inv_assignments_tracks.destination_user')
              ->leftJoin('islim_warehouses as islim_warehouses_destination','islim_warehouses_destination.id','islim_inv_assignments_tracks.destination_wh')
              ->leftJoin('islim_users as islim_assigned_by','islim_assigned_by.email','islim_inv_assignments_tracks.assigned_by');


        if (is_array($filters)) {
            if (!empty($filters['dateb']) && !empty($filters['datee'])) {
                $data->whereBetween('islim_inv_assignments_tracks.date_reg', [$filters['dateb'], $filters['datee']]);
            } elseif (!empty($filters['dateb'])) {
                $data->where('islim_inv_assignments_tracks.date_reg', '>=', $filters['dateb']);
            } elseif (!empty($filters['datee'])) {
                $data->where('islim_inv_assignments_tracks.date_reg', '<=', $filters['datee']);
            }

            if (!empty($filters['is_sell'])){
                if($filters['is_sell'] == 'N'){
                    $data->whereNotIn('islim_inv_arti_details.status',['V','T']);
                }
                else{
                    $data->where('islim_inv_arti_details.status','V');
                }
            }

            if(!empty($filters['msisdn_select'])){
                $data->whereIn('islim_inv_arti_details.msisdn',$filters['msisdn_select']);
            }
        }

        $data = $data->orderBy('islim_inv_arti_details.msisdn','ASC')->orderBy('islim_inv_arti_details.date_reg','ASC')->get();
        return $data;
    }

    public static function getDetailTrack($id){
        return self::getConnect('R')
                    ->select(
                        'islim_inv_assignments_tracks.date_reg',
                        'assigned.name as assigned_name',
                        'assigned.last_name as assigned_last_name',
                        'origin.name as origin_name',
                        'origin.last_name as origin_last_name'
                    )
                    ->leftJoin(
                        'islim_users as assigned',
                        'assigned.email',
                        'islim_inv_assignments_tracks.assigned_by'
                    )
                    ->leftJoin(
                        'islim_users as origin',
                        'origin.email',
                        'islim_inv_assignments_tracks.origin_user'
                    )
                    ->where('islim_inv_assignments_tracks.id', $id)
                    ->first();
    }
}