<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Helpers\APIvoyWey;
use Illuminate\Support\Facades\Log;

class DeferredPayment extends Model
{
    protected $table = 'islim_deferred_payment';

    protected $fillable = [
        'id', 'folio', 'transaction', 'type_payment', 'amount', 'order_id', 'date_reg', 'date_update', 'status'];

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
     */
    public static function getConnect($typeCon = false)
    {
        if ($typeCon) {
            $obj = new DeferredPayment;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getStatus()
    {
        $table = 'islim_deferred_payment';
        $field = 'status';

        $test = DB::select(DB::raw("show columns from {$table} where field = '{$field}'"));

        preg_match('/^enum\((.*)\)$/', $test[0]->Type, $matches);
        foreach (explode(',', $matches[1]) as $value) {
            $enum[] = trim($value, "'");
        }
        asort($enum);
        return $enum;
    }

    public static function getData_DeferredPayment($filters)
    {
       // Log::info("filters_star: " . $filters['dateStar']);
       // Log::info("filters_end: " . $filters['dateEnd']);
        $data = self::getConnect('R')
            ->select(
                'islim_deferred_payment.order_id as Orden',
                'islim_ordens.ordNbr as OrderVoy',
                'islim_deferred_payment.status as status',
                'islim_deferred_payment.date_reg as Fecha',
                DB::raw('DATEDIFF(islim_sales.date_reg, islim_ordens.date) as Dias_en_Activar'),
                'islim_sales.date_reg as Fecha_Activacion',
                'islim_deferred_payment.amount as Monto',
                'islim_ordens.cod_prom as Codigo',
                'islim_deferred_payment.type_payment as FormaPago',
                'islim_ordens.seller_email as UserMail',
                'islim_users.name as UserName',
                'islim_users.last_name as Userlastname',
                'islim_users.phone as Userphone',
                'islim_ordens.client_id as DNI',
                'islim_clients.name as ClientName',
                'islim_clients.last_name as ClientLastName',
                'islim_clients.email as ClienteMail',
                'islim_client_netweys.status as status_client',
                'islim_orders_details.msisdn as MSISDN',
                'islim_inv_articles.title as Modelo',
                DB::raw("(CONCAT(islim_services.title, CONCAT(' - ',islim_services.description))) as Full_plan")
            )
            ->join(
                'islim_ordens',
                'islim_ordens.id',
                '=',
                'islim_deferred_payment.order_id')
            ->leftJoin(
                'islim_users',
                'islim_users.email',
                '=',
                'islim_ordens.seller_email')
            ->leftJoin(
                'islim_clients',
                'islim_clients.dni',
                '=',
                'islim_ordens.client_id')
            ->leftJoin(
                'islim_orders_details',
                'islim_orders_details.id_ordens',
                '=',
                'islim_deferred_payment.order_id')
            ->leftJoin(
                'islim_client_netweys',
                'islim_client_netweys.msisdn',
                'islim_orders_details.msisdn')
            ->leftJoin(
                'islim_inv_articles',
                'islim_inv_articles.id',
                '=',
                'islim_orders_details.id_articles')
            ->leftJoin(
                'islim_services',
                'islim_services.id',
                '=',
                'islim_orders_details.id_details')
            ->leftJoin('islim_sales',
                function ($join) {
                    $join->on('islim_sales.msisdn', 'islim_orders_details.msisdn')
                        ->where([
                            ['islim_sales.type', 'P'],
                            ['islim_sales.status', 'A']]
                        );
                });
          
            if(!empty($filters['status'])){  //listo todos los reportes en la fecha seleccionada  
                $data=$data->where('islim_deferred_payment.status', $filters['status']);    
            }
            $data=$data->where('islim_users.id_org', '=', '1')
            ->where('islim_users.concentrator_id', '=', '1')
            ->whereBetween('islim_deferred_payment.date_reg', [$filters['dateStar'], $filters['dateEnd']])
            ->orderBy('islim_deferred_payment.date_reg', 'DESC')->get();

        //  print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
        // exit;
        //Log::info("data: " . $data);
        return $data;
         /* ->leftJoin('islim_users',
                function ($join) {
                    $join->on('islim_users.email', 'islim_ordens.seller_email')
                        ->where('islim_users.id_org', '=', '1')
                        ->where('islim_users.concentrator_id', '=', '1');
                })*/
    }
    public static function getDetail_repartidorByOrden($folioVoywey,$RegJeoVoy){
        $inputapi = [
            'folio' => $folioVoywey];

        $dataInfo = APIvoyWey::get_repartidor($inputapi);
  
        if($dataInfo->success && !is_null($dataInfo->data)){
             $RegJeoVoy->Repartidor_name= $dataInfo->data->name;
             $RegJeoVoy->Repartidor_lastname= $dataInfo->data->last_name;
             $RegJeoVoy->Repartidor_mail= $dataInfo->data->email;
             $RegJeoVoy->Repartidor_phone= $dataInfo->data->phone;
             $RegJeoVoy->Repartidorine= $dataInfo->data->ine;
        }else{
            
            $RegJeoVoy->Repartidor_name= 'S/N';
             $RegJeoVoy->Repartidor_lastname= 'S/N';
             $RegJeoVoy->Repartidor_mail= 'S/N';
             $RegJeoVoy->Repartidor_phone= 'S/N';
             $RegJeoVoy->Repartidorine= 'S/N';
        }
      
        return $RegJeoVoy; 
    }
}
