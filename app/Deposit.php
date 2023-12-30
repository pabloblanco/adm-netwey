<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Organization;

class Deposit extends Model {
    protected $table = 'islim_deposits';

    protected $fillable = [
        'id','bank_id','concentrators_id','users_email','description','photo','amount','real_amout','date_deposit','date_asigned','date_reg','status','obs',
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
            $obj = new Deposit;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    private static function getReportSelect ($type = null) {

    	$queryObs = '(CASE islim_deposits.obs WHEN NULL THEN "" ELSE islim_deposits.obs END) AS obs';
        $queryConcentrator = '(CASE WHEN '.
            '(islim_deposits.concentrators_id IS NOT NULL) THEN '.
            '(SELECT '.
                'islim_concentrators.name FROM '.
                'islim_concentrators WHERE '.
                'islim_concentrators.id = islim_deposits.concentrators_id) '.
            'ELSE islim_deposits.concentrators_id END) AS concentrator';
        $queryUser = '(CASE WHEN '.
            '(islim_deposits.users_email IS NOT NULL) THEN '.
            '(SELECT '.
                'islim_users.name FROM '.
                'islim_users WHERE '.
                'islim_users.email = islim_deposits.users_email) '.
            'ELSE islim_deposits.users_email END) AS user_name';

        $report = Deposit::getConnect('R'); //DB::table('islim_deposits');

    	if ($type == 'concentrator') {
    		$report = $report->select(
                'islim_deposits.id',
                'islim_deposits.bank_id',
                'islim_banks.name',
                'islim_banks.numAcount',
                'islim_banks.typeAcount',
                'islim_deposits.concentrators_id',
                'islim_deposits.description',
                'islim_deposits.amount',
                'islim_deposits.real_amout',
                'islim_deposits.date_deposit',
                'islim_deposits.date_asigned',
                'islim_deposits.status AS deposit_status',
                DB::raw($queryObs),
                DB::raw($queryConcentrator))
                	->join('islim_concentrators', 'islim_concentrators.id', '=', 'islim_deposits.concentrators_id');

    	} else if ($type == 'vendor') {
    		$report = $report->select(
                'islim_deposits.id',
                'islim_deposits.bank_id',
                'islim_banks.name',
                'islim_banks.numAcount',
                'islim_banks.typeAcount',
                'islim_deposits.users_email',
                'islim_deposits.description',
                'islim_deposits.amount',
                'islim_deposits.real_amout',
                'islim_deposits.date_deposit',
                'islim_deposits.date_asigned',
                'islim_deposits.status AS deposit_status',
                DB::raw($queryObs),
                DB::raw($queryUser)
            )
            ->join('islim_users', function ($join) {
                $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
                $join->on('islim_users.email', '=', 'islim_deposits.users_email')
                     ->whereIn('islim_users.id_org',$orgs->pluck('id'));
            });

            //->join('islim_users', 'islim_users.email', '=', 'islim_deposits.users_email');
    	} else {
    		$report = $report->select(
                'islim_deposits.id',
                'islim_deposits.bank_id',
                'islim_banks.name',
                'islim_banks.numAcount',
                'islim_banks.typeAcount',
                'islim_deposits.concentrators_id',
                'islim_deposits.users_email',
                'islim_deposits.description',
                'islim_deposits.amount',
                'islim_deposits.real_amout',
                'islim_deposits.date_deposit',
                'islim_deposits.date_asigned',
                'islim_deposits.status AS deposit_status',
                DB::raw($queryObs),
                DB::raw($queryConcentrator),
                DB::raw($queryUser));
    	}
    	return $report->join('islim_banks', 'islim_banks.id', '=', 'islim_deposits.bank_id');
    }

    public static function getReportBalance ($type, $id = null, $banks = null, $date_ini = null, $date_end = null) {

    	$report = self::getReportSelect ($type);

    	if (!empty($id)) {
    		if($type == 'concentrator'){
    			$report = $report->where('islim_deposits.concentrators_id',$id);
    		}else{
    			$ids = User::getConnect('R')->where('email',$id)->orWhere('parent_email',$id)->get()->pluck('email');
    			$report = $report->whereIn('islim_deposits.users_email',$ids);
    		}
    	}
    	if (!empty($banks)) {
    		$report = $report->where('islim_deposits.bank_id', $banks);
    	}
    	if (!empty($date_ini) || !empty($date_end)){
    		if(empty($date_ini)){
    			$date_ini = '';
    		}elseif(empty($date_end)){
    			$date_end = '';
    		}
    		$report = $report->whereBetween('islim_deposits.date_deposit', [$date_ini, $date_end]);
    	}

        return $report->get();
    }
}