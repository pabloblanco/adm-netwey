<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ListDns extends Model
{
    protected $table = 'islim_list_dns';

	protected $fillable = [
        'id', 'name', 'date_reg', 'status', 'lifetime'
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
            $obj = new ListDns;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getServices($idList){
    	$dataList = ListDns::select('name', 'id', 'lifetime')
                                ->where([['status', 'A'], ['id', $idList]])
                                ->first();

        if(!empty($dataList)){
            $datadn = ClientNetwey::select(
                                'islim_client_netweys.msisdn',
                                'islim_clients.name',
                                'islim_clients.last_name'
                            )
                            ->join(
                                'islim_clients',
                                'islim_clients.dni',
                                '=',
                                'islim_client_netweys.clients_dni'
                            )
                            ->where([
                                ['islim_client_netweys.id_list_dns', $idList]
                            ])
                            ->get();

            return ['lista' => $dataList, 'dns' => $datadn];
        }

        return false;
    }
}
