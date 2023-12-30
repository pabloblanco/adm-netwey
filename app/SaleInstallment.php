<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Organization;

class SaleInstallment extends Model
{
    protected $table = 'islim_sales_installments';

	protected $fillable = [
		'seller',
		'coordinador',
		'quotes',
		'config_id',
		'unique_transaction',
		'lat',
		'lng',
		'pack_id',
		'type_pack',
		'service_id',
		'client_dni',
		'msisdn',
		'first_pay',
		'amount',
		'view_seller',
		'date_reg_alt',
		'date_reg',
		'date_update',
		'alert_exp',
		'status'
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
            $obj = new SaleInstallment;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    /*
		Reporte de ventas en abono
    */
    public static function getSalesReport($filters = []){
    	$sales = self::getConnect('R')
                        ->select(
    					'islim_sales_installments.unique_transaction',
    					'islim_sales_installments.date_reg_alt',
    					'islim_sales_installments.msisdn',
    					'islim_sales_installments.quotes',
    					'islim_sales_installments.amount',
    					'islim_config_installments.quotes as total_quotes',
    					'islim_config_installments.days_quote',
    					'seller.name as name_seller',
    					'seller.last_name as last_name_seller',
    					'coord.name as name_coord',
    					'coord.last_name as last_name_coord',
    					'islim_dts_organizations.business_name',
    					'islim_packs.title as pack',
    					'islim_services.title as service',
    					'islim_inv_arti_details.imei',
    					'islim_inv_articles.title as product',
                        'islim_inv_articles.artic_type',
    					'islim_clients.name as name_client',
    					'islim_clients.last_name as last_name_client',
    					'islim_clients.phone_home'
    				   )
    				   ->join(
    				   	'islim_config_installments',
    				   	'islim_config_installments.id',
    				   	'islim_sales_installments.config_id'
    				   )
    				   ->join(
    				   	'islim_users as seller',
    				   	'seller.email',
    				   	'islim_sales_installments.seller'
    				   )
    				   ->join(
    				   	'islim_users as coord',
    				   	'coord.email',
    				   	'islim_sales_installments.coordinador'
    				   )
    				   ->join(
    				   	'islim_dts_organizations',
    				   	'islim_dts_organizations.id',
    				   	'seller.id_org'
    				   )
    				   ->join(
    				   	'islim_packs',
    				   	'islim_packs.id',
    				   	'islim_sales_installments.pack_id'
    				   )
    				   ->join(
    				   	'islim_services',
    				   	'islim_services.id',
    				   	'islim_sales_installments.service_id'
    				   )
    				   ->join(
    				   	'islim_inv_arti_details',
    				   	'islim_inv_arti_details.msisdn',
    				   	'islim_sales_installments.msisdn'
    				   )
    				   ->join(
    				   	'islim_inv_articles',
    				   	'islim_inv_articles.id',
    				   	'islim_inv_arti_details.inv_article_id'
    				   )
    				   ->join(
    				   	'islim_client_netweys',
    				   	'islim_client_netweys.msisdn',
    				   	'islim_sales_installments.msisdn'
    				   )
    				   ->join(
    				   	'islim_clients',
    				   	'islim_clients.dni',
    				   	'islim_client_netweys.clients_dni'
    				   );

    	//Verificando si vienen filtros
    	$notFS = true;//Bandera para condicinar agregar o no filtro de estatus
    	if(is_array($filters) && count($filters)){
    		//fechas
    		if(!empty($filters['dateb']) && !empty($filters['datee'])){
                $sales = $sales->whereBetween(
                					'islim_sales_installments.date_reg_alt',
                					[
                						date('Y-m-d', strtotime($filters['dateb'])).' 00:00:00',
                						date('Y-m-d', strtotime($filters['datee'])).' 23:59:59'
                					]);
            }

            if(empty($filters['dateb']) && !empty($filters['datee'])){
                $sales = $sales->where(
                					'islim_sales_installments.date_reg_alt',
                					'<=',
                					date('Y-m-d', strtotime($filters['datee'])).' 23:59:59'
                				);
            }

            if(!empty($filters['dateb']) && empty($filters['datee'])){
                $sales = $sales->where(
                					'islim_sales_installments.date_reg_alt',
                					'>=',
                					date('Y-m-d', strtotime($filters['dateb'])).' 00:00:00'
                				);
            }

            //Organizacion
            if(!empty($filters['org'])){
            	$sales = $sales->where('islim_dts_organizations.id', $filters['org']);
            }
            else{
                $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                $sales = $sales->whereIn('islim_dts_organizations.id', $orgs->pluck('id'));
            }

            //Coordinador
            if(!empty($filters['coord'])){
            	$sales = $sales->where('coord.email', $filters['coord']);
            }

            //Vendedor
            if(!empty($filters['seller'])){
            	$sales = $sales->where('seller.email', $filters['seller']);
            }

            //Servicio
            if(!empty($filters['service'])){
            	$sales = $sales->where('islim_services.id', $filters['service']);
            }

            //Equipo
            if(!empty($filters['product'])){
            	$sales = $sales->where('islim_inv_articles.id', $filters['product']);
            }

            //Estatus
            if(!empty($filters['status'])){
            	//Optimiza la consulta
            	if($filters['status'] == 'EXP'){
            		$notFS = false;
            		$sales = $sales->where('islim_sales_installments.status', 'P');
            	}
            }
    	}

    	if($notFS)
    		$sales = $sales->whereIn('islim_sales_installments.status', ['P', 'F']);

    	$sales = $sales->get();

    	$today = time();
    	foreach($sales as $sale){
    		$sale->expired = false;
    		$sale->date_expired = 'N/A';

    		if($sale->quotes < $sale->total_quotes){
    			//Verificando si la fecha limite para pago de la proxima cuota expiro
				$dateSale = strtotime('+ '.($sale->days_quote * $sale->quotes).' days',
											strtotime($sale->date_reg_alt)
										);
				$sale->date_expired = date('d-m-Y', $dateSale);

				if($today > $dateSale)
    				$sale->expired = true;
    		}
    	}

    	if(!empty($filters['status']) && $filters['status'] == 'EXP')
    		$sales = $sales->filter(function ($value, $key) {
									    return $value->expired;
									});

    	if(!empty($filters['status']) && $filters['status'] == 'OK')
    		$sales = $sales->filter(function ($value, $key) {
									    return !$value->expired;
									});

    	return $sales;
    }

    public static function getExpiredPayment($offset = 0){
    	$sales = self::select(
						'islim_sales_installments.unique_transaction',
						'islim_sales_installments.date_reg_alt',
						'islim_sales_installments.alert_exp',
						'islim_sales_installments.quotes as qp',
						'islim_sales_installments.id',
                        'islim_sales_installments.amount',
                        'islim_sales_installments.first_pay',
						'islim_clients.name as name_c',
						'islim_clients.last_name as last_name_c',
						'seller.name as name_seller',
						'seller.last_name as last_name_seller',
						'coord.name as name_coord',
						'coord.last_name as last_name_coord',
						'islim_config_installments.quotes',
						'islim_config_installments.days_quote'
					)
					->join(
						'islim_config_installments',
						'islim_config_installments.id',
						'islim_sales_installments.config_id'
					)
					->join(
						'islim_clients',
						'islim_clients.dni',
						'islim_sales_installments.client_dni'
					)
					->join(
						'islim_users as seller',
						'seller.email',
						'islim_sales_installments.seller'
					)
					->join(
						'islim_users as coord',
						'coord.email',
						'islim_sales_installments.coordinador'
					)
					->where('islim_sales_installments.status', 'P')
					->get();

		//Calculando data restante (cuotas, fecha de vencimiento,...)
        $today = time();
        foreach ($sales as $key => $sale){
            //Verificando si la fecha limite para pago de la proxima cuota expiro
            $dateSale = strtotime('+ '.($sale->days_quote * $sale->qp).' days',
                                        strtotime($sale->date_reg_alt)
                                    );

            $sale->date_expired = date('d-m-Y', $dateSale);

            //Separando resultados por vencidos y no vencidos.
            if($today <= strtotime('+ '.$offset.' days', $dateSale))
                $sales->pull($key);
        }

        return $sales;
    }
}
