<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Orders;

class SalesBrightstar extends Model
{
    protected $table = 'islim_tmp_sales_brightstar';

    /*protected $fillable = [
        'api_key', 'ip', 'date_reg', 'status',
    ];*/
    
    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\SalesBrightstar
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new SalesBrightstar;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getSales($date_ini,$date_end,$time_ini,$time_end,$cod_prom){
        $dini=substr($date_ini,6,4)."-".substr($date_ini,3,2)."-".substr($date_ini,0,2)." 00:00:00";
        $dend=substr($date_end,6,4)."-".substr($date_end,3,2)."-".substr($date_end,0,2)." 23:59:59";

        $tini=str_replace(":","",$time_ini)."00";
        $tend=str_replace(":","",$time_end)."59";

        $int_tini=intval($tini);
        $int_tend=intval($tend);


        $tini=$time_ini.":00";
        $tend=$time_end.":59";


        if($int_tini < $int_tend){
            $OP="AND";
        }
        else{
            $OP="OR";   
        }

        $sub = DB::raw('IF(islim_client_netweys.status="A","Activo",(Select islim_ordens_status.description FROM islim_ordens_status WHERE islim_ordens_status.id_ordens = islim_ordens.id ORDER BY islim_ordens_status.date DESC LIMIT 1)) as Estado');


        //DB::raw('IF(islim_client_netweys.status="A",DATEDIFF(islim_sales.date_reg, islim_webpay.date),"N/A") as Dias_en_Activar'),
                            //DB::raw('IF(islim_client_netweys.status="A",DATEDIFF(islim_sales.date_reg, islim_mercado_pago.date_reg),"N/A") as Dias_en_Activar_MP'),

        $sales = Orders::select(
                            'islim_clients.name as Nombre',
                            'islim_clients.last_name as Apellido',
                            'islim_clients.email as Email',
                            'islim_clients.phone_home as Telefono',
                            'islim_clients.city_store as Ciudad',
                            'islim_clients.cp_store as Codigo_Postal',
                            'islim_clients.person_type as Tipo_Persona',
                            'islim_clients.require_invoice as Requiere_Factura',
                            'islim_clients.rfc as RFC',
                            'islim_clients.date_reg as Fecha_Registro',
                            'islim_ordens.date as Fecha_Compra',                            
                            'islim_ordens.ordNbr as Nro_Orden',
                            'islim_ordens.id as Orden_id',
                            'islim_orders_details.msisdn as MSISDN',
                            'islim_inv_articles.title as Equipo_Comprado',
                            'islim_packs.title as Plan',
                            'islim_transaction.address as Direccion_Entrega',
                            'islim_transaction.city',
                            'islim_transaction.colonia',
                            'islim_transaction.codigozip',
                            'islim_transaction.state',
                            'islim_transaction.id_estafeta as Id_Estafeta',
                            'islim_ninety_nine_minutes.id as id_99',
                            'islim_ninety_nine_minutes.route',
                            'islim_ninety_nine_minutes.neighborhood',
                            'islim_ninety_nine_minutes.state as state_99',
                            'islim_ninety_nine_minutes.street_number',
                            'islim_cars.campaign as Campaña',
                            'islim_ordens.cod_prom',
                            'islim_mercado_pago.payment_method as Metodo_Pago',
                            DB::raw('IF(islim_client_netweys.status="A", DATE_FORMAT(islim_sales.date_reg, "%d/%m/%Y %H:%I:%S" ), "") as Fecha_Activacion'),
                            DB::raw('IF(islim_client_netweys.status="A",DATEDIFF(islim_sales.date_reg, islim_ordens.date),"N/A") as Dias_en_Activar'),
                            DB::raw('IF(islim_client_netweys.status="A",DATEDIFF(islim_sales.date_reg, islim_ordens.date),"N/A") as Dias_en_Activar_MP'),
                            $sub
                        )
                        ->join('islim_clients', 'islim_clients.dni', 'islim_ordens.client_id')
                        ->join('islim_orders_details','islim_orders_details.id_ordens','islim_ordens.id')
                        ->join('islim_inv_articles','islim_inv_articles.id','islim_orders_details.id_articles')
                        ->join('islim_pack_prices', 'islim_pack_prices.id', 'islim_orders_details.id_price')
                        ->join('islim_packs', 'islim_packs.id', 'islim_pack_prices.pack_id')

                        ->join('islim_cars_detail', 'islim_cars_detail.id', 'islim_orders_details.car_detail')
                        ->join('islim_cars', 'islim_cars.id', 'islim_cars_detail.car_id')


                        ->leftJoin('islim_transaction', 'islim_transaction.id_ordens', 'islim_ordens.id')
                        ->leftJoin('islim_webpay', 'islim_webpay.order_id', 'islim_orders_details.id_ordens')
                        ->leftJoin('islim_ninety_nine_minutes', function($join){
                            $join->on('islim_ninety_nine_minutes.order99', 'islim_ordens.ordNbr')
                                 ->whereIn('islim_ninety_nine_minutes.status', ['S', 'A']);
                        })
                        ->leftJoin('islim_mercado_pago', function($join){
                            $join->on('islim_mercado_pago.order_id', 'islim_ordens.id')
                                 ->where([
                                    ['islim_mercado_pago.type', 'S'],
                                    ['islim_mercado_pago.status', 'approved']
                                 ]);
                        })
                        ->leftJoin(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_orders_details.msisdn'
                        )
                        ->leftJoin('islim_sales',
                            function($join){
                                $join->on('islim_sales.msisdn', '=', 'islim_orders_details.msisdn')
                                     ->where([
                                        ['islim_sales.type', 'P'],
                                        ['islim_sales.status', 'A']
                                     ]);
                        })
                        ->where([
                            ['islim_ordens.date', '>=', $dini],
                            ['islim_ordens.date', '<=', $dend]
                        ])
                        ->where(function($query){
                            $query->where('islim_ordens.seller_email', env('SELLER_STORE_EMAIL'))
                                  ->orWhereNull('islim_ordens.seller_email');
                        })
                        ->whereNotNull('islim_ordens.ordNbr');

        if($cod_prom == "1"){
            $sales = $sales->where(function($query){
                                $query->whereRaw('LOWER(islim_ordens.cod_prom) = "enviocero"')
                                ->orWhereRaw('LOWER(islim_ordens.cod_prom) = "despacho0"');
                            });
        }
        if($cod_prom == "2"){
            $sales = $sales->where(function($query){
                            $query->whereRaw('LOWER(islim_ordens.cod_prom) <> "enviocero"')
                            ->WhereRaw('LOWER(islim_ordens.cod_prom) <> "despacho0"')
                            ->whereNotNull('islim_ordens.cod_prom');
                        });            
        }

        return $sales;
    }

    public static function getSalesOnline($db = false, $de = false, $cod_prom = false){
        $sub = '(select islim_ordens_status.date from netwey.islim_ordens_status where ((islim_ordens_status.status = 5 AND islim_ordens_status.reference like "NET%") OR (islim_ordens_status.status in (6,12))) AND islim_ordens_status.reference = islim_ordens.ordNbr limit 1)';
        $sales = Orders::getConnect('R')
                        ->select(
                            'islim_clients.name as Nombre',
                            'islim_clients.last_name as Apellido',
                            'islim_clients.email as Email',
                            'islim_clients.phone_home as Telefono',
                            'islim_clients.city_store as Ciudad',
                            'islim_clients.cp_store as Codigo_Postal',
                            'islim_clients.person_type as Tipo_Persona',
                            'islim_clients.require_invoice as Requiere_Factura',
                            'islim_clients.rfc as RFC',
                            'islim_clients.date_reg as Fecha_Registro',
                            'islim_inv_articles.title as Equipo_Comprado',
                            'islim_packs.title as Plan',
                            'islim_ordens.date as Fecha_Compra',
                            DB::raw($sub.' as Fecha_Entrega'),
                            DB::raw('DATEDIFF(IFNULL('.$sub.',NOW()), islim_ordens.date) as Dias_en_Entregar'),
                            'islim_ordens.ordNbr as Nro_Orden',
                            'islim_ordens.id as Orden_id',
                            'islim_orders_details.msisdn as MSISDN',
                            'islim_client_netweys.status as Estado',
                            'islim_cars.campaign as Campaña',
                            'islim_ordens.cod_prom',
                            DB::raw('DATEDIFF(islim_sales.date_reg, islim_ordens.date) as Dias_en_Activar'),
                            'islim_sales.date_reg as Fecha_Activacion',
                            'islim_prova_delivery.courier_g',
                            'islim_prova_delivery.price as price_prova'
                            //DB::raw('DATEDIFF(islim_sales.date_reg, islim_ordens.date) as Dias_en_Activar_MP')
                        )
                        ->join(
                            'islim_clients', 
                            'islim_clients.dni', 
                            'islim_ordens.client_id'
                        )
                        ->join(
                            'islim_orders_details',
                            'islim_orders_details.id_ordens',
                            'islim_ordens.id'
                        )
                        ->join(
                            'islim_inv_articles',
                            'islim_inv_articles.id',
                            'islim_orders_details.id_articles'
                        )
                        ->join(
                            'islim_pack_prices', 
                            'islim_pack_prices.id', 
                            'islim_orders_details.id_price'
                        )
                        ->join(
                            'islim_packs', 
                            'islim_packs.id', 
                            'islim_pack_prices.pack_id'
                        )
                        ->join(
                            'islim_cars_detail',
                            'islim_cars_detail.id',
                            'islim_orders_details.car_detail'
                        )
                        ->join(
                            'islim_cars', 
                            'islim_cars.id', 
                            'islim_cars_detail.car_id'
                        )
                        ->leftJoin(
                            'islim_client_netweys',
                            'islim_client_netweys.msisdn',
                            'islim_orders_details.msisdn'
                        )
                        ->leftJoin(
                            'islim_prova_delivery',
                            'islim_prova_delivery.folio',
                            'islim_ordens.ordNbr'
                        )
                        ->leftJoin('islim_sales',
                            function($join){
                                $join->on('islim_sales.msisdn', 'islim_orders_details.msisdn')
                                     ->where([
                                        ['islim_sales.type', 'P'],
                                        ['islim_sales.status', 'A']
                                     ]);
                        })
                        ->where(function($query){
                            $query->where('islim_ordens.seller_email', env('SELLER_STORE_EMAIL'))
                                  ->orWhereNull('islim_ordens.seller_email');
                        })
                        ->whereNotNull('islim_ordens.ordNbr');

        if($db){
            $sales->where('islim_ordens.date', '>=', $db);
        }

        if($de){
            $sales->where('islim_ordens.date', '<=', $de);
        }

        if($cod_prom == "1"){
            $sales = $sales->where(function($query){
                                $query->whereRaw('LOWER(islim_ordens.cod_prom) = "enviocero"')
                                ->orWhereRaw('LOWER(islim_ordens.cod_prom) = "despacho0"');
                            });
        }

        if($cod_prom == "2"){
            $sales = $sales->where(function($query){
                            $query->whereRaw('LOWER(islim_ordens.cod_prom) <> "enviocero"')
                            ->WhereRaw('LOWER(islim_ordens.cod_prom) <> "despacho0"')
                            ->whereNotNull('islim_ordens.cod_prom');
                        });
        }

        return $sales;
    }
}