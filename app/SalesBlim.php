<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use \Curl;

class SalesBlim extends Model{    
    protected $table = 'islim_sales_blim';
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';

    protected $fillable = [
        'msisdn',
        'sales_id',
        'pin',
        'status',
        'redeemed',
        'date_reg'
    ];

    public static function getBlimCodes($msisdn = false, $filters = []){
       
        $blimcodes = self::select(
            'id',
            'pin',
            'date_reg',
            'redeemed'
        )            
        ->where([
            ['msisdn', $msisdn]
        ]) 
        ->whereIn('status',['A','P']);
        

        if(is_array($filters) && count($filters)){
            if(!empty($filters['dateB'])){
                $blimcodes = $blimcodes->where('date_reg', '>=', $filters['dateB']);
            }
        }

        $blimCs= $blimcodes->get();
        foreach ($blimCs as $key => $blimC) {
            if($blimC->redeemed == 'N'){
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_URL => env('URL_API_BLIM'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => array('pin' => $blimC->pin),
                    CURLOPT_HTTPHEADER => array(
                    'Cookie: PHPSESSID=0uivtj0261kk8vbunehse38qog'
                    ),
                ));
                $response = curl_exec($curl);
                curl_close($curl);

                $res=json_decode($response);

                if($res->status == 'success'){
                    if($res->response == 'REDIMIDO'){
                        $bc=self::find($blimC->id);
                        $bc->redeemed = 'Y';
                        $bc->save(); 
                    }
                }
            }
        }
        //Log::info($blimcodes->toSql());
        //return $blimcodes->get();
        return $blimcodes;

    }

}