<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RetentionActivates extends Model
{
    protected $table    = 'islim_retention_activates';
    protected $fillable = [
        'id',
        'user_creator',
        'user_autorization',
        'services_id',
        'reason_id',
        'sales_id',
        'status',
        'date_reg',
        'msisdn',
        'is_view',
        'view_date'];
    protected $hidden = [
        'id', 'status', 'sales_id',
    ];
    protected $primaryKey = 'id';
    public $timestamps    = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
     */
    public static function getConnect($typeCon = false)
    {
        if ($typeCon) {
            $obj = new RetentionActivates;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    //cantidad de activaciones de los ultimos 30 dias por usuario, mode null=(todas), S=(propias, Sin Autorizacion), A=(autorizadas por supervisor), P=(todas las activaciones hechas o autorizadas por el usuario)
    public static function getCantMonth($email, $mode = null)
    {

        $desde = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . ' -30 days'));
        $query = self::where('status', 'A');
        if ($mode != 'P') {
            $query = $query->where('user_creator', $email);
            if ($mode == 'S') {
                $query = $query->whereNull('user_autorization');
            }
            if ($mode == 'A') {
                $query = $query->whereNotNull('user_autorization');
            }
        } else {
            $query = $query->where(function ($qry) use ($email) {
                $qry->where('user_creator', $email)
                    ->orWhere('user_autorization', $email);
            });
        }
        $query = $query->where('date_reg', '>=', $desde);
        return $query->count();
    }

    //cantidad de activaciones de los ultimos 30 dias por dn)
    public static function getCantMonthByDN($msisdn)
    {

        $desde = date('Y-m-d h:i:s', strtotime(date('Y-m-d h:i:s') . ' -30 days'));

        $query = self::where('status', 'A')
            ->where('msisdn', $msisdn);

        $query = $query->where('date_reg', '>=', $desde);

        return $query->count();

    }

    //Devuelve el tiempo en horas desde la ultima activacion que se realizo a un DN
    public static function getTimeLastActivateByDN($msisdn)
    {
        $query = self::selectRaw("TIMESTAMPDIFF(HOUR,date_reg,NOW()) as horas")
            ->where('status', 'A')
            ->where('msisdn', $msisdn)
            ->orderBy('date_reg', 'DESC')
            ->first();
        if (!empty($query)) {
            return $query->horas;
        }
        return -1;
    }

    public static function getServicesActivates($msisdn = false, $filters = [])
    {
        $retentions = self::select(
            'islim_services.title as service',
            DB::raw('CONCAT(uc.name, " ",uc.last_name) AS user_creator'),
            DB::raw('CONCAT(ua.name, " ",ua.last_name) AS user_autorization'),
            'islim_retention_reasons.reason as reason',
            'islim_retention_reasons.sub_reason as sub_reason',
            'islim_retention_activates.date_reg'

        )
            ->join('islim_retention_reasons', 'islim_retention_reasons.id', 'islim_retention_activates.reason_id')
            ->join('islim_services', 'islim_services.id', 'islim_retention_activates.services_id')
            ->join('islim_users as uc', 'uc.email', '=', 'islim_retention_activates.user_creator')
            ->leftJoin('islim_users as ua', 'ua.email', '=', 'islim_retention_activates.user_autorization')
            ->where([
                ['islim_retention_activates.msisdn', $msisdn],
            ])
            ->whereIn('islim_retention_activates.status', ['A']);

        if (is_array($filters) && count($filters)) {
            if (!empty($filters['dateB'])) {
                $retentions = $retentions->where('islim_retention_activates.date_reg', '>=', $filters['dateB']);
            }
        }
        return $retentions;
    }

    public static function getDTRetentionPeriodDataReport($filters = [])
    {

        $data = self::getConnect('R')
            ->select(
                'islim_retention_activates.msisdn',
                'islim_services.title as service',
                DB::raw('CONCAT(uc.name, " ",uc.last_name) AS user_creator'),
                DB::raw('CONCAT(ua.name, " ",ua.last_name) AS user_autorization'),
                'islim_retention_reasons.reason as reason',
                'islim_retention_reasons.sub_reason as sub_reason',
                'islim_retention_activates.date_reg'
            )
            ->join('islim_retention_reasons', 'islim_retention_reasons.id', '=', 'islim_retention_activates.reason_id')
            ->join('islim_services', 'islim_services.id', '=', 'islim_retention_activates.services_id')
            ->join('islim_users as uc', 'uc.email', '=', 'islim_retention_activates.user_creator')
            ->leftJoin('islim_users as ua', 'ua.email', '=', 'islim_retention_activates.user_autorization')
            ->whereIn('islim_retention_activates.status', ['A']);

        if (is_array($filters)) {
            if (!empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
                $data->whereBetween('islim_retention_activates.date_reg', [$filters['dateStar'], $filters['dateEnd']]);
            } elseif (!empty($filters['dateStar'])) {
                $data->where('islim_retention_activates.date_reg', '>=', $filters['dateStar']);
            } elseif (!empty($filters['dateEnd'])) {
                $data->where('islim_retention_activates.date_reg', '<=', $filters['dateEnd']);
            }
        }
        $data = $data->orderBy('islim_retention_activates.date_reg', 'DESC')->get();
        // print_r(vsprintf(str_replace(['?'], ['\'%s\''], $data->toSql()), $data->getBindings()));
        // exit;
        return $data;
    }
}
