<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceChanel extends Model {
	protected $table = 'islim_service_channel';
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ServiceChanel;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getConcService($service = false){
    	if($service){
    		return self::getConnect('R')
    					->select(
    						'islim_concentrators.id',
    						'islim_concentrators.name'
    					)
    					->join(
                            'islim_concentrators',
                            'islim_concentrators.id',
                            'islim_service_channel.id_concentrator'
                        )
                        ->where([
                            ['islim_service_channel.status', 'A'],
                            ['islim_service_channel.id_service', $service],
                            ['islim_concentrators.status', 'A']
                        ])
                        ->get();
    	}

    	return [];
    }

    public static function getChService($service = false){
    	if($service){
    		return self::getConnect('R')
    					->select(
    						'islim_channels.id', 
    						'islim_channels.name'
    					)
    					->join(
                            'islim_channels',
                            'islim_channels.id',
                            'islim_service_channel.id_channel'
                        )
                        ->where([
                            ['islim_service_channel.status', 'A'],
                            ['islim_service_channel.id_service', $service],
                            ['islim_channels.status', 'A']
                        ])
                        ->get();
    	}

    	return [];
    }

    public static function getListService($service = false){
    	if($service){
    		return self::getConnect('R')
    					->select(
    						'islim_list_dns.id',
    						'islim_list_dns.name'
    					)
    					->join(
                            'islim_list_dns',
                            'islim_list_dns.id',
                            'islim_service_channel.id_list_dns'
                        )
                        ->where([
                            ['islim_service_channel.status', 'A'],
                            ['islim_service_channel.id_service', $service],
                            ['islim_list_dns.status', 'A']
                        ])
                        ->get();
    	}

    	return [];
    }
}