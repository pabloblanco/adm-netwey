<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StepRecord extends Model
{
    protected $table = 'islim_steps_records';
    public $timestamps = false;
    public $incrementing = true;
    protected $primaryKey  = 'id';

    /**
    * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
    * @param String $typeCon
    *
    * @return App\StepRecord
    **/
    public static function getConnect($typeCon = false)
    {
        if ($typeCon) {
          $obj = new StepRecord;
          $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

          return $obj;
        }
        return null;
    }

    public static function getClientsCoverageConsult($date_ini, $date_end, $client_type="", $result_type="")
    {
        $dini = substr($date_ini, 6, 4) . "-" . substr($date_ini, 3, 2) . "-" . substr($date_ini, 0, 2) . " 00:00:00";
        $dend = substr($date_end, 6, 4) . "-" . substr($date_end, 3, 2) . "-" . substr($date_end, 0, 2) . " 23:59:59";

        $clients  = self::getConnect('R')
                    ->select(
                        DB::raw('CONCAT(islim_clients.name," ",islim_clients.last_name) as Cliente'),
                        'islim_clients.email as Email',
                        'islim_clients.phone_home as Telefono',
                        'islim_steps_records.coverage_address as Direccion_Consultada',
                        'islim_steps_records.coverage_colony as Colonia_Consultada',
                        'islim_steps_records.coverage_city as Ciudad_Consultada',
                        'islim_steps_records.coverage_state as Estado_Consultado',
                        'islim_steps_records.coverage_zip as ZIP_Consultado',
                        'islim_steps_records.coverage_status as Resultado',
                        'islim_steps_records.coverage_date as Fecha_Consulta'
                    )
                    ->leftJoin('islim_clients','islim_steps_records.client_id','=','islim_clients.dni')
                    ->whereBetween('islim_steps_records.coverage_date', [$dini, $dend]);

        if($client_type == 'A'){
            $clients=$clients->whereNull('islim_clients.dni');  
        }
        if($client_type == 'R'){
            $clients=$clients->whereNotNull('islim_clients.dni');  
        }

        if($result_type != ""){
           $clients=$clients->where('islim_steps_records.coverage_status',$result_type); 
        }

        return $clients;
    }

    public static function getCoverageStatsCharts($date_ini, $date_end)
    {
        $dini = substr($date_ini, 6, 4) . "-" . substr($date_ini, 3, 2) . "-" . substr($date_ini, 0, 2) . " 00:00:00";
        $dend = substr($date_end, 6, 4) . "-" . substr($date_end, 3, 2) . "-" . substr($date_end, 0, 2) . " 23:59:59";

        $stats  = self::getConnect('R')
                    ->select(
                        'islim_steps_records.coverage_status as Resultado',
                        DB::raw('COUNT(*) as Cantidad')
                    )                
                    ->whereBetween('islim_steps_records.coverage_date', [$dini, $dend])
                    ->groupBy('islim_steps_records.coverage_status')
                    ->get();

        return $stats;
    }

    public static function getNotCoverageStatsCharts($date_ini, $date_end)
    {
        $dini = substr($date_ini, 6, 4) . "-" . substr($date_ini, 3, 2) . "-" . substr($date_ini, 0, 2) . " 00:00:00";
        $dend = substr($date_end, 6, 4) . "-" . substr($date_end, 3, 2) . "-" . substr($date_end, 0, 2) . " 23:59:59";

        $stats  = self::getConnect('R')
                    ->select(
                        DB::raw('CONCAT(islim_steps_records.coverage_city," - ",islim_steps_records.coverage_state) as Ciudad'),
                        DB::raw('COUNT(*) as Cantidad')
                    )                
                    ->whereBetween('islim_steps_records.coverage_date', [$dini, $dend])
                    ->whereNotNull('islim_steps_records.coverage_address')
                    ->whereNotNull('islim_steps_records.coverage_city')
                    ->whereNotNull('islim_steps_records.coverage_state')
                    ->where('islim_steps_records.coverage_status','SC')
                    ->groupBy('islim_steps_records.coverage_state','islim_steps_records.coverage_city')
                    ->orderBy('Cantidad','DESC')
                    ->limit(4);

        $cantp=0;
        foreach ($stats->get() as $key => $stat) {
            $cantp+=$stat->Cantidad;
        }

        $stats2  = self::getConnect('R')
                    ->select( 
                        DB::raw('COUNT(*) as Cantidad')
                    )                
                    ->whereBetween('islim_steps_records.coverage_date', [$dini, $dend])
                    ->whereNotNull('islim_steps_records.coverage_address')
                    ->where('islim_steps_records.coverage_status','SC')
                    ->first();

        $cantp = $stats2->Cantidad - $cantp;

        $stats2=self::getConnect('R')
                    ->select(
                        DB::raw('CONCAT("Otros") as Ciudad')
                    )
                    ->selectRaw('? as Cantidad',[$cantp]);  
        
        $stats = $stats->union($stats2)->get();
        //print_r(vsprintf(str_replace(['?'], ['\'%s\''], $stats->toSql()), $stats->getBindings()));
        //exit;
        return $stats;
    }
}

/*SELECT COUNT(*) as cant, concat(isr.coverage_city,', ',isr.coverage_state), isr.coverage_status 
FROM netwey_test.islim_steps_records isr
where isr.coverage_city is not null
and isr.coverage_status = 'SC'
group by isr.coverage_city 
ORDER BY cant desc*/
