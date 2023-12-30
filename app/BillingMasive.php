<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Log;
use Carbon\Carbon;

class BillingMasive extends Model {
	protected $table = 'islim_billing_masive';

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
        'billing_nro',
        'xml_id',
        'date_reg',
        'date_gen',
        'status_gen',
        'id_gen'
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
            $obj = new BillingMasive;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }


    public static function getLastRegister($type_connect,$oxxo_folio_id,$oxxo_folio_nro,$doc_pay = null,$total = null) {

        if($type_connect != 'W')
            $type_connect = 'R';

        $lastResgister = self::getConnect($type_connect)
                        ->where([
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

    public static function getReportBillingsMasive($filters)
    {
        $billings = self::getConnect('R')->select(
                    'place', 'date_expired', 'term', 'oxxo_folio_date', 'oxxo_folio_id', 'oxxo_folio_nro', 'date_pay', 'doc_pay', 
                    'status_pay', 'sub_total', 'tax', 'total', 'pay_type', 'mk_serie', 'mk_folio'
        );

        if (!empty($filters['place'])) {
            $billings = $billings->where('place', '=', $filters['place']);
        }

        if (!empty($filters['status_pay'])) {

            if ( $filters['status_pay'] == 'Y' ) {
            
                //Validando que vengan los dos rangos de fechas y formateando fecha
                if (empty($filters['dateb']) && empty($filters['datee'])) {
                    $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
                    $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
                } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
                    $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
                    $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
                } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
                    $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
                    $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
                } else {
                    $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
                    $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
                }

                $billings = $billings
                                ->where('date_pay', '>=', $filters['dateb'])
                                ->where('date_pay', '<=', $filters['datee']);
            }
            else {
                $billings = $billings->where('status_pay', '=', $filters['status_pay']);
            }
        }

        return $billings->get();
    }
}