<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SaleReportView extends Model {
	protected $table = 'islim_client_views';

	protected $fillable = [
		'unique_transaction', 'order_altan', 'name', 'last_name', 'email', 'phone_home', 'vendor', 'name_vendor', 'last_name_vendor', 'plan', 'type_buy', 'type', 'product', 'phone_netwey', 'imei', 'iccid', 'cost', 'serviceability', 'lat', 'lng', 'date_reg', 'date_buy', 'conciliation', 'status'
    ];

    public $timestamps = false;

    public static function getSaleReport ($type = null, $supervisor = null, $seller = null, $date_ini = null, $date_end = null, $saleStatus = null, $concentrator = null, $unique_transaction = null, $msisdn = null) {

        $report = SaleReportView::select();

        if (isset($type) && !empty($type)) {
            if ($type == 'ups' || $type == 'P') {
                $report = $report->where('type', '=', 'P');
            }
            if ($type == 'recharges' || $type == 'R') {
                $report = $report->where('type', '=', 'R');
            }
        }

        /*
        PREPARAMOS LOS FILTROS DE USUARIOS
        **/
        $filtersUser = array();
        if ((isset($supervisor) && !empty($supervisor)) && (isset($seller) && !empty($seller))) {
            if ($seller == 'UN_VALOR_QUE_INDIQUE_QUE_TRAIGA_SOLO_REGISTROS_DEL_COORDINADOR') {
                //$filtersUser = ['supervisor' => $supervisor];
            } else {
                //$filtersUser = ['supervisor' => $supervisor, 'vendor' => $seller];
            }
        } else {
            if (isset($supervisor) && !empty($supervisor)) {
                //$filtersUser = ['supervisor' => $supervisor];
            } else {
                if (isset($seller) && !empty($seller)) {
                    $filtersUser = ['vendor' => $seller];
                }
            }
        }
        /*
        PREPARAMOS LOS FILTROS DE VENTAS
        **/

        if (count($filtersUser) > 0) {
            $report = $report->where($filtersUser);;
        }

        if (isset($date_ini) && !empty($date_ini)) {
            $date_ini = $date_ini.' 00:00:00';
        }

        if (isset($date_end) && !empty($date_end)) {
            $date_end = $date_end.' 23:59:59';
        }

        if (!empty($date_ini) && !empty($date_end)) {
            $report = $report->whereBetween('date_reg', [$date_ini, $date_end]);
        } else {
            if (!empty($date_ini)) {
                $report = $report->where('date_reg', '>=', $date_ini);
            } else {
                if (!empty($date_end)) {
                    $report = $report->where('date_reg', '<=', $date_end);
                }
            }
        }

        if (isset($concentrator) && (count($saleStatus) > 0)) {
            $report = $report->whereIn('status', $saleStatus);
        }

        if((isset($concentrator) && !empty($concentrator))) {
            //$report = $report->where('concentrators_id', $concentrator);
        }
        
        if (isset($unique_transaction) && !empty($unique_transaction)) {
            $report = $report->where('unique_transaction', '=', $unique_transaction);
        }

        if (isset($msisdn) && !empty($msisdn)) {
            $report = $report->where('phone_netwey', '=', $msisdn);
        }
        
        $amount = $report->sum('cost');
        //return $report->get();
        return ['sales' => $report->distinct()->get(), 'amount' => $amount->sum('amount')];
    }
}