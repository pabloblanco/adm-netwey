<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;

class BillingMasiveFileDetail extends Model {
	protected $table = 'islim_billing_masive_file_details';

	protected $fillable = [
       'id',
       'file_id',
       'place',
       'date_expired',
       'term',
       'oxxo_folio_date',
       'oxxo_folio_id',
       'oxxo_folio_nro',
       'date_pay',
       'doc_pay',
       'status_pay',
       'sub_total',
       'tax',
       'total',
       'pay_type',
       'billable',
       'mk_serie',
       'mk_folio',
       'action'
    ];
    
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';


    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new BillingMasiveFileDetail;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }


    public static function getLastRegister($type_connect,$file_id,$oxxo_folio_id,$oxxo_folio_nro,$doc_pay = null,$total = null) {

        if($type_connect != 'W')
            $type_connect = 'R';

        $lastResgister = self::getConnect($type_connect)
                        ->where([
                            ['file_id', $file_id],
                            ['oxxo_folio_id', $oxxo_folio_id],
                            ['oxxo_folio_nro',$oxxo_folio_nro]
                        ]);
        if(!empty($doc_pay)){
            $lastResgister= $lastResgister->where('doc_pay',$doc_pay);
        }

        if(!empty($total)){
            $lastResgister= $lastResgister->where('total',$total);
        }




        // $query = vsprintf(str_replace('?', '%s', $lastResgister->toSql()), collect($lastResgister->getBindings())->map(function ($binding) {
        //         return is_numeric($binding) ? $binding : "'{$binding}'";
        //     })->toArray());

        // Log::info($query);

        $lastResgister= $lastResgister->orderBy('id','DESC')->first();

        return $lastResgister;

    }
}