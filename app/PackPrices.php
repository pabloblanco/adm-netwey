<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ArticlePack;

class PackPrices extends Model
{
    protected $table = 'islim_pack_prices';

	protected $fillable = [
        'id', 
        'pack_id', 
        'service_id', 
        'type', 
        'price_pack', 
        'price_serv', 
        'total_price', 
        'status', 
        'id_financing'
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
            $obj = new PackPrices;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getPacksIdByService($service = false){
        if($service){
            return self::getConnect('R')
                        ->select('pack_id')
                        ->where([
                            ['status', 'A'],
                            ['service_id', $service]
                        ])
                        ->get();
        }

        return [];
    }

    public static function getServicesByPack ($id, $servicesStatus, $productsStatus) {
        $servicesId = PackPrices::select(
                                    'islim_pack_prices.service_id', 
                                    'islim_pack_prices.price_pack', 
                                    'islim_pack_prices.price_serv', 
                                    'islim_pack_prices.total_price', 
                                    'islim_pack_prices.id_financing', 
                                    'islim_pack_prices.type',
                                    'islim_financing.name'
                                )
                                ->leftJoin('islim_financing', function($join){
                                    $join->on('islim_financing.id', '=', 'islim_pack_prices.id_financing')
                                         ->where('islim_financing.status', 'A');
                                })
                                ->where(
                                    [
                                        'islim_pack_prices.pack_id' => $id, 
                                        'islim_pack_prices.status' => $productsStatus
                                    ]
                                )
                                ->get();

        $services = array();

        foreach ($servicesId as $ids) {
            $temp = Service::where(['id' => $ids->service_id, 'status' => $servicesStatus])->get();
            foreach ($temp as $service) {
                $service->price_pack = $ids->price_pack;
                $service->price_serv = $ids->price_serv;
                $service->total_price = $ids->total_price;
                $service->id_financing = $ids->id_financing;
                $service->name = $ids->name;
                $service->type = $ids->type;
                $services[] = $service;
            }
        }
        return $services;
    }

}