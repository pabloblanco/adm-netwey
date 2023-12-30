<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackEsquema extends Model
{
    protected $table = 'islim_pack_esquema';

	protected $fillable = [
        'id_pack',
        'id_esquema',
        'date_reg',
        'status'
    ];
    
    public $timestamps = false;

    public static function getEsquemasByPack($pack){
        return self::select(
                        'islim_pack_esquema.id_esquema',
                        'islim_esquema_comercial.name'
                    )
                    ->join(
                        'islim_esquema_comercial',
                        'islim_esquema_comercial.id',
                        'islim_pack_esquema.id_esquema'
                    )
                    ->where([
                        ['islim_pack_esquema.id_pack', $pack],
                        ['islim_pack_esquema.status', 'A']
                    ])
                    ->get();
    }
}
