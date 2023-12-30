<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Sale;
use App\SaleMetrics;
use App\Concentrator;
use App\ClientNetwey;
//use App\MetricsBi;
use App\MetricsBi2;
use App\MetricsDasboard;
use App\MetricsDasboardB;
use DateTime;

class DashboardController extends Controller
{

	public function dashboardGrapSales(Request $request)
	{
		if ($request->isMethod('post') && $request->ajax() && !empty($request->type) && !empty($request->typeS)) {
			$today = Carbon::now();

			$db = $today->copy()->startOfDay()->toDateTimeString();
			$de = $today->copy()->endOfDay()->toDateTimeString();

			$metToday = SaleMetrics::getTotalSales(
				$db,
				$de,
				$request->typeS == 'U' ? 'P' : $request->typeS,
				$request->type,
				'wo',
				false
			);

			$metmonth = MetricsDasboardB::getSumMetrics(
				$today->copy()->startOfMonth()->format('Y-m-d'),
				$today->format('Y-m-d'),
				$request->typeS,
				$request->type
			);

			$metTri = MetricsDasboardB::getSumMetrics(
				$today->copy()
					->subMonths(3)
					->startOfDay()
					->format('Y-m-d'),
				$today->format('Y-m-d'),
				$request->typeS,
				$request->type
			);

			//Grafica de altas trimestral
			$todayG = Carbon::now()->startOfDay();
			$endd = $todayG->copy()->subMonths(3);
			$cicles = $endd->diffInDays($todayG);
			$dataMet = [];

			for ($i = 0; $i < $cicles; $i++) {
				$ut = MetricsDasboardB::getSumByDate(
					$endd->format('Y-m-d'),
					$request->typeS, //== 'U' ? 'P' : $request->typeS, 
					$request->type
				);

				$dataMet[] = ['count' => $ut, 'date' => $endd->format('m-d')];

				$endd->addDay();
			}

			return response()->json([
				'success' => true,
				'today' => $metToday->total_u,
				'month' => $metmonth,
				'tri' => $metTri,
				'graf' => $dataMet
			]);
		}
	}

	public static function dashboardClient(Request $request)
	{
		if ($request->isMethod('post') && $request->ajax() && !empty($request->type)) {
			$A90 = 0;
			$metric = MetricsBi2::getLastMetric(
				$request->type == 'H' ? 'HBB' : $request->type,
				'R'
			);
			if (!empty($metric)) {
				$A90 += $metric->A90;
			}

			$today = Carbon::now();

			$db = $today->startOfDay()->toDateTimeString();
			$de = $today->endOfDay()->toDateTimeString();

			$uptoday = SaleMetrics::getTotalSales($db, $de, 'P', $request->type, 'wo', false);

			$upmonth = MetricsDasboardB::getSumMetrics(
				$today->copy()->startOfMonth()->format('Y-m-d'),
				$today->format('Y-m-d'),
				'U',
				$request->type
			);

			$active = $A90 + (!empty($upmonth) ? $upmonth : 0) + (!empty($uptoday->total_u) ? $uptoday->total_u : 0);

			//Clientes totales
			$uTotal = SaleMetrics::getTotalSales(false, false, 'P', $request->type, 'wo', true);

			$inac = $uTotal->total_u - $active;

			//Recargas totales
			$rTotal = SaleMetrics::getTotalRecharge($request->type);

			return response()->json([
				'success' => true,
				'active' => $active,
				'inactive' => $inac,
				'total_up' => $uTotal->total_u,
				'total_re' => $rTotal->total_u
			]);
		}
	}

	public static function dashboardConcentrator(Request $request)
	{
		if ($request->isMethod('post') && $request->ajax()) {
			//Saldo de concentradores
			$concentrator = Concentrator::getConcentrators();
			$clist = [];
			foreach ($concentrator as $con) {
				$clist[] = [
					'business_name' => $con->business_name,
					'balance' => number_format($con->balance, 2, '.', ',')
				];
			}

			return response()->json([
				'success' => true,
				'concentrator' => $clist
			]);
		}
	}

	public function dashboardGrap2(Request $request)
	{
		if ($request->isMethod('post') && $request->ajax()) {
			$today = Carbon::now();

			if ($request->interval == 'quarterly') {
				$db = $today->copy()->subMonths(3)->startOfDay();
				$cicles = $db->diffInDays($today);
			} elseif ($request->interval == 'monthly') {
				$db = $today->copy()->startOfMonth()->startOfDay();
				$cicles = $db->diffInDays($today);

				//Si es el primer dia del mes, muestra data el mes anterior
				if ($cicles == 0) {
					$db = $today->copy()->subMonths(1)->startOfDay()->format('Y-m-d H:i:s');
					$cicles = $db->diffInDays($today);
				}
			} elseif ($request->interval == 'weekly') {
				$db = $today->copy()->startOfWeek()->startOfDay();

				if ($db->format('Y-m-d') == $today->format('Y-m-d')) {
					$db->subDays(7);
				}

				$cicles = $db->diffInDays($today);
			} elseif ($request->interval == 'daily') {
				$db = $today->copy()->subDay()->startOfDay();
				$cicles = 24;
			}

			if (!empty($cicles)) {
				$dataUp = [];

				for ($i = 0; $i < $cicles; $i++) {
					if ($request->interval == 'daily') {
						$ut = SaleMetrics::getTotalSales($db->format('Y-m-d H:i:s'), $db->copy()->endOfHour()->format('Y-m-d H:i:s'), $request->type, $request->device, 'wo', false);

						$dataUp[] = ['count' => $ut->total_u, 'date' => $db->format('Y-m-d H:i')];

						$db->addHour();
					} else {
						$ut = MetricsDasboardB::getSumByDate(
							$db->format('Y-m-d'),
							$request->type == 'P' ? 'U' : $request->type,
							$request->device
						);

						$dataUp[] = ['count' => $ut, 'date' => $db->format('m-d')];

						$db->addDay();
					}
				}

				return response()->json([
					'success' => true,
					'data' => $dataUp
				]);
			}


			return response()->json([
				'success' => false
			]);
		}
	}

	/*public function dashboardGrapSalesH(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$today = Carbon::now();

			$db = $today->copy()->startOfDay()->toDateTimeString();
      		$de = $today->copy()->endOfDay()->toDateTimeString();

            $uptoday = SaleMetrics::getTotalSales($db, $de, 'P', 'H', 'wo');

            $upmonth = MetricsDasboardB::getSumMetrics(
            								$today->copy()->startOfMonth()->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'U',
            								'H'
            							);

            $uptri = MetricsDasboardB::getSumMetrics(
            								$today->copy()
            									  ->subMonths(3)
            									  ->startOfDay()
            									  ->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'U',
            								'H'
            							);

            //Grafica de altas trimestral
            $todayG = Carbon::now()->startOfDay();
			$endd = $todayG->copy()->subMonths(3);
			$cicles = $endd->diffInDays($todayG);
			$dataUp = [];

			for($i = 0; $i < $cicles; $i++){
            	$ut = MetricsDasboardB::getSumByDate($endd->format('Y-m-d'), 'U', 'H');

            	$dataUp []= ['count' => $ut, 'date' => $endd->format('m-d')];

            	$endd->addDay();
			}

			return response()->json([
                                        'success' => true,
                                        'up_today' => $uptoday->total_u,
                                        'up_month' => $upmonth,
                                        'up_tri' => $uptri,
                                        'up_graf' => $dataUp
                                    ]);
		}
	}

	public function dashboardGrapSalesM(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$today = Carbon::now();

			$db = $today->startOfDay()->toDateTimeString();
      		$de = $today->endOfDay()->toDateTimeString();

            $uptoday = SaleMetrics::getTotalSales($db, $de, 'P', 'T', 'wo');

            $upmonth = MetricsDasboardB::getSumMetrics(
            								$today->copy()->startOfMonth()->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'U',
            								'T'
            							);

            $uptri = MetricsDasboardB::getSumMetrics(
            								$today->copy()
            									  ->subMonths(3)
            									  ->startOfDay()
            									  ->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'U',
            								'T'
            							);

            //Grafica de altas trimestral
            $todayG = Carbon::now()->startOfDay();
			$endd = $todayG->copy()->subMonths(3);
			$cicles = $endd->diffInDays($todayG);
			$dataUp = [];

			for($i = 0; $i < $cicles; $i++){
            	$ut = MetricsDasboardB::getSumByDate($endd->format('Y-m-d'), 'U', 'T');

            	$dataUp []= ['count' => $ut, 'date' => $endd->format('m-d')];

            	$endd->addDay();
			}

			return response()->json([
                                        'success' => true,
                                        'up_today' => $uptoday->total_u,
                                        'up_month' => $upmonth,
                                        'up_tri' => $uptri,
                                        'up_graf' => $dataUp
                                    ]);
		}
	}*/

	/*public function dashboardGrapRechargesH(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$today = Carbon::now();

			$db = $today->startOfDay()->toDateTimeString();
      		$de = $today->endOfDay()->toDateTimeString();

			$rtoday = SaleMetrics::getTotalSales($db, $de, 'R', 'H', 'wo');

            $rmonth = MetricsDasboardB::getSumMetrics(
            								$today->copy()->startOfMonth()->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'R',
            								'H'
            							);

            $rtri = MetricsDasboardB::getSumMetrics(
            								$today->copy()
            									  ->subMonths(3)
            									  ->startOfDay()
            									  ->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'R',
            								'H'
            							);

			//Grafica de recargas trimestral
            $todayG = Carbon::now()->startOfDay();
			$endd = $todayG->copy()->subMonths(3);
			$cicles = $endd->diffInDays($todayG);
			$dataRe = [];

			for($i = 0; $i < $cicles; $i++){
            	$rt = MetricsDasboardB::getSumByDate($endd->format('Y-m-d'), 'R', 'H');

            	$dataRe []= ['count' => $rt, 'date' => $endd->format('m-d')];

            	$endd->addDay();
			}

			return response()->json([
                                        'success' => true,
                                        're_today' => $rtoday->total_u,
                                        're_month' => $rmonth,
                                        're_tri' => $rtri,
                                        're_graf' => $dataRe
                                    ]);
		}
	}

	public function dashboardGrapRechargesM(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$today = Carbon::now();

			$db = $today->copy()->startOfDay()->format('Y-m-d H:i:s');
      		$de = $today->copy()->endOfDay()->format('Y-m-d H:i:s');

			$rtoday = SaleMetrics::getTotalSales($db, $de, 'R', 'T', 'wo');

            $rmonth = MetricsDasboardB::getSumMetrics(
            								$today->copy()->startOfMonth()->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'R',
            								'T'
            							);

            $rtri = MetricsDasboardB::getSumMetrics(
            								$today->copy()
            									  ->subMonths(3)
            									  ->startOfDay()
            									  ->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'R',
            								'T'
            							);

			//Grafica de recargas trimestral
            $todayG = Carbon::now()->startOfDay();
			$endd = $todayG->copy()->subMonths(3);
			$cicles = $endd->diffInDays($todayG);
			$dataRe = [];

			for($i = 0; $i < $cicles; $i++){
            	$rt = MetricsDasboardB::getSumByDate($endd->format('Y-m-d'), 'R', 'T');

            	$dataRe []= ['count' => $rt, 'date' => $endd->format('m-d')];

            	$endd->addDay();
			}

			return response()->json([
                                        'success' => true,
                                        're_today' => $rtoday->total_u,
                                        're_month' => $rmonth,
                                        're_tri' => $rtri,
                                        're_graf' => $dataRe
                                    ]);
		}
	}*/

	/*public static function dashboardClientH(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$A90 = 0;
			$metric = MetricsBi2::getLastMetric('HBB', 'R');

			if(!empty($metric)){
				$A90 += $metric->A90;
			}

			$today = Carbon::now();

			$db = $today->startOfDay()->toDateTimeString();
      		$de = $today->endOfDay()->toDateTimeString();

            $uptoday = SaleMetrics::getTotalSales($db, $de, 'P', 'H', 'wo');

            $upmonth = MetricsDasboardB::getSumMetrics(
            								$today->copy()->startOfMonth()->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'U',
            								'H'
            							);

			$active = $A90 + (!empty($upmonth) ? $upmonth : 0) + (!empty($uptoday->total_u) ? $uptoday->total_u : 0);

			//Clientes totales
			$uTotal = SaleMetrics::getTotalSales(false, false, 'P', 'H', 'wo');

			$inac = $uTotal->total_u - $active;

			//Recargas totales
			$rTotal = SaleMetrics::getTotalSales(false, false, 'R', 'H', 'wo');

			return response()->json([
                                        'success' => true,
                                        'active' => $active,
                                        'inactive' => $inac,
                                        'total_up' => $uTotal->total_u,
                                        'total_re' => $rTotal->total_u
                                    ]);
		}
	}

	public static function dashboardClientM(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$A90 = 0;
			$metric = MetricsBi2::getLastMetric('T', 'R');

			if(!empty($metric)){
				$A90 += $metric->A90;
			}

			$today = Carbon::now();

			$db = $today->startOfDay()->toDateTimeString();
      		$de = $today->endOfDay()->toDateTimeString();

            $uptoday = SaleMetrics::getTotalSales($db, $de, 'P', 'T', 'wo');

            $upmonth = MetricsDasboardB::getSumMetrics(
            								$today->copy()->startOfMonth()->format('Y-m-d'), 
            								$today->format('Y-m-d'), 
            								'U',
            								'T'
            							);

			$active = $A90 + (!empty($upmonth) ? $upmonth : 0) + (!empty($uptoday->total_u) ? $uptoday->total_u : 0);

			//Clientes totales
			$uTotal = SaleMetrics::getTotalSales(false, false, 'P', 'T', 'wo');

			$inac = $uTotal->total_u - $active;

			//Recargas totales
			$rTotal = SaleMetrics::getTotalSales(false, false, 'R', 'T', 'wo');

			return response()->json([
                                        'success' => true,
                                        'active' => $active,
                                        'inactive' => $inac,
                                        'total_up' => $uTotal->total_u,
                                        'total_re' => $rTotal->total_u
                                    ]);
		}
	}*/


	/*Deprecated*/
	/*protected function getUpsRecharges($supervisor , $vendor){
		$months = -2;
		$today_date = date ('Y-m-d', time());
		$last_month = date ('Y-m', strtotime ($months . ' month',strtotime($today_date)));
		$this_month = date ('Y-m', strtotime (1 . ' month',strtotime($today_date)));
		//reporte del dia
		$todayR = Sale::getSaleReport ('R', $supervisor, $vendor, $today_date, $today_date, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		//reporte del mes
		$monthR = Sale::getSaleReport ('R', $supervisor, $vendor, $last_month, $this_month, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();

		$totalR = Sale::getSaleReport ('R', $supervisor, $vendor,null,null, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();


		//reporte del dia
		$todayP = Sale::getSaleReport ('P', $supervisor, $vendor, $today_date, $today_date, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		//reporte del mes
		$monthP = Sale::getSaleReport ('P', $supervisor, $vendor, $last_month, $this_month, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();

		$totalP = Sale::getSaleReport ('P', $supervisor, $vendor,null,null, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		//grafica alta
		$graphic = array();
		for ($i = 0; $i > -3; $i--) {
			$date_ini = date('Y-m', strtotime ($i.' month',strtotime($today_date)));
			$date_end = date('Y-m', strtotime (($i+1).' month',strtotime($today_date)));
			$R = Sale::getSaleReport ('R', $supervisor, $vendor, $date_ini, $date_end, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$P = Sale::getSaleReport ('P', $supervisor, $vendor, $date_ini, $date_end, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$graphic[] = array('ups' => $P,'recharges' => $R, 'date'=>$date_ini);

		}
		usort($graphic, function($a, $b) {
    		return $a['date'] <=> $b['date'];
		});
		
		return array('todayR'=>number_format($todayR,0,'.',','),'monthR'=>number_format($monthR,0,'.',','),'todayP'=>number_format($todayP,0,'.',','),'monthP'=>number_format($monthP,0,'.',','),'graphic'=>$graphic,'totalR'=>number_format($totalR,0,'.',','),'totalP'=>number_format($totalP,0,'.',','));
	}*/

	/*Deprecated*/
	/*protected function getUpsOrRecharges($type, $supervisor , $vendor){
		$today_date = date ('Y-m-d', time());
		$last_month = date ('Y-m-d', strtotime ('-1 month',strtotime($today_date)));
		//reporte del dia
		$today = Sale::getSaleReport ($type, $supervisor, $vendor, $today_date, null, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		//reporte del mes
		$month = Sale::getSaleReport ($type, $supervisor, $vendor, $last_month, $today_date, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		//grafica alta
		$graphic = array();
		for ($i = 0; $i > -12; $i--) {
			$date_ini = date('Y-m', strtotime ($i.' month',strtotime($today_date)));
			$date_end = date('Y-m', strtotime (($i+1).' month',strtotime($today_date)));
			$temp = Sale::getSaleReport ($type, $supervisor, $vendor, $date_ini, $date_end, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$graphic[] = array('count' => $temp, 'date'=>$date_ini);

		}
		return array('today'=>$today,'month'=>$month,'graphic'=>$graphic);
	}*/

	/*Deprecated*/
	/*protected function getClients(){
		$today_date = date ('Y-m-d', time());
		$last_month = date ('Y-m-d', strtotime ('-1 month',strtotime($today_date)));
		$rechargesmonth = Sale::whereBetween('date_reg', [$last_month, $today_date])->where('type','R')->distinct('msisdn')->count('msisdn');
		$clientnet = ClientNetwey::where([['status','!=', 'T'], ['date_reg', '<', $last_month]])->count();
		return array('act'=>$rechargesmonth, 'nct' =>($clientnet-$rechargesmonth));
	}*/

	/*Deprecated*/
	/*protected function getConcent(){
		$concentrator = Concentrator::where('status','!=','T')->get();
		$return = array();
		foreach ($concentrator as $con) {
			$return[] = array('concentrator'=>$con->business_name, 'balance'=>number_format($con->balance,2,'.',',')); 
		}
		return $return;
	}*/

	/*Deprecated*/
	/*protected function daily($type, $supervisor, $vendor){
		$array = array();
		for ($i = 0; $i < 24; $i++){
			$mk1 = mktime($i, 0, 0, date("m",time()), date("d",time())-1, date("Y",time())); 
			$date_ini =date('Y-m-d H:i:s', $mk1);
			$mk2 = mktime($i+1, 0, 0, date("m",time()), date("d",time())-1, date("Y",time()));
			$date_end =date('Y-m-d H:i:s', $mk2);
			$count = Sale::getSaleReport ($type, $supervisor, $vendor, $date_ini, $date_end, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$array[] = array('count'=>$count, 'date'=>$date_ini);
		}
		return $array;
	}*/

	/*Deprecated*/
	/*protected function weekly($type, $supervisor, $vendor){
		$array = array();
		$first = strtotime('last Monday');
		$last = strtotime('today -1 day');
		for($i=$first; $i<=$last; $i+=86400){
			$date_ini = date("Y-m-d", $i);
			$date_end = date("Y-m-d", $i+86400);
			$count = Sale::getSaleReport ($type, $supervisor, $vendor, $date_ini, $date_ini, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$array[] = array('count'=>$count, 'date'=>$date_ini);
		}
		return $array;
	}*/

	/*Deprecated*/
	/*protected function monthly($type, $supervisor, $vendor){
		$array = array();
		$first = strtotime("first day of this month");
		$last = strtotime("today -1 day");
		for($i=$first; $i<=$last; $i+=86400){
			$date_ini = date("Y-m-d", $i);
			$date_end = date("Y-m-d", $i+86400);
			$count = Sale::getSaleReport ($type, $supervisor, $vendor, $date_ini, $date_ini, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$array[] = array('count'=>$count, 'date'=>date("m-d", $i));
		}
		return $array;
	}*/

	/*Deprecated*/
	/*protected function quarterly($type, $supervisor, $vendor){
		$array = array();
		$first = strtotime("-3 month today -1 day");
		$last = strtotime("today"); // -1 day
		for($i=$first; $i<=$last; $i+=86400){
			$date_ini = date("Y-m-d", $i);
			$date_end = date("Y-m-d", $i+86400);
			$count = Sale::getSaleReport ($type, $supervisor, $vendor, $date_ini, $date_ini, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
			$array[] = array('count'=>$count, 'date'=>date("m-d", $i));
		}
		return $array;
	}*/

	/*Deprecated*/
	///mktime(0, 0, 0, date("m", $date1), date("d", $date1)+1, date("Y", $date1)); 
	/*public function graphic($type, $interval){
		$user_type = session('user')->platform;
		$user_email = session('user')->email;
		$supervisor = null;
		$vendor = null;
		switch ($user_type) {
			case 'admin':
			break;
			case 'coordinador':
			$supervisor = $user_email;
			break;
			case 'vendor':
			$vendor = $user_email;
			break;
		}
		$graphic = null;
		switch ($interval) {
		    case 'daily':
		        $graphic = $this->daily($type, $supervisor, $vendor);
		        break;
		    case 'weekly':
		        $graphic = $this->weekly($type, $supervisor, $vendor);
		        break;
		    case 'monthly':
		        $graphic = $this->monthly($type, $supervisor, $vendor);
		        break;
	        case 'quarterly':
		        $graphic = $this->quarterly($type, $supervisor, $vendor);
		        break;
		    default:
		    	$graphic = $this->daily($type, $supervisor, $vendor);
		    	break;
		}
		return $graphic;
	}*/

	/*DEPRECATED*/
	/*public function dw_report(){
		$user_type = session('user')->platform;
		$user_email = session('user')->email;
		$supervisor = null;
		$vendor = null;
		switch ($user_type) {
			case 'admin':
			break;
			case 'coordinador':
			$supervisor = $user_email;
			break;
			case 'vendor':
			$vendor = $user_email;
			break;
		}
		$date_ini = date("Y-m-d",strtotime("first day of this month"));
		$date_end = date("Y-m-d",strtotime("today -1 day"));
		$upsTotal =  Sale::getSaleReport ('P', $supervisor, $vendor, $date_ini, $date_end, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		$rechargersTotal =  Sale::getSaleReport ('R', $supervisor, $vendor, $date_ini, $date_end, ['E', 'A'], null, null, null, null,null,null,null)['sales']->count();
		$ups = $this->getUpsRecharges('P',$supervisor, $vendor);
		$recharger = $this->getUpsRecharges('R',$supervisor, $vendor);
		$client = $this->getClients();
		$concent = $this->getConcent();
		$all = $this->getUpsRecharges($supervisor, $vendor);
		return array('all'=>$all, 'ups'=>$ups, 'recharger'=>$recharger, 'client'=>$client, 'concentrator'=>$concent, 'upstotal'=>$upsTotal, 'rechargerstotal'=>$rechargersTotal);
	}*/

	/*Deprecated*/
	/*public function dashboardInfo(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			//Altas
			$today = date('Y-m-d').' 00:00:00';
			$lastHour = (3600 * 23) + 3599;

			$cv = DB::raw('COUNT(id) as total_u');

			//Hoy
			$db = $today;
			$de = date('Y-m-d H:i:s', strtotime($today) + $lastHour);
			$uptoday = Sale::select($cv)
                            ->where([
                                ['date_reg', '>=', $db],
                                ['date_reg', '<=', $de],
                                ['type', 'P']
                            ])
                            ->whereIn('status', ['A', 'E'])
                            ->first();
            //Mes
            $db = date("Y-m-d",strtotime("first day of this month"));
            $de = date('Y-m-d', strtotime($today));
            $upmonth = MetricsDasboard::select('quantity')
	            						   ->where([
	            						   	['date', '>=', $db],
	            						   	['date', '<=', $de],
	            						   	['type', 'U']
	            						   ])
	            						   ->sum('quantity');

            //Tres meses
            $db = date("Y-m-d",strtotime('-3 month', strtotime($today)));
            $de = date('Y-m-d', strtotime($today));
            $uptri = MetricsDasboard::select('quantity')
	            						   ->where([
	            						   	['date', '>=', $db],
	            						   	['date', '<=', $de],
	            						   	['type', 'U']
	            						   ])
	            						   ->sum('quantity');

            //Recargas
            //Hoy
			$db = $today;
			$de = date('Y-m-d H:i:s', strtotime($today) + $lastHour);
			$rtoday = Sale::select($cv)
                            ->where([
                                ['islim_sales.date_reg', '>=', $db],
                                ['islim_sales.date_reg', '<=', $de],
                                ['islim_sales.type', 'R']
                            ])
                            ->whereIn('islim_sales.status', ['A', 'E'])
                            ->first();

            //mes
            $db = date("Y-m-d",strtotime("first day of this month"));
            $de = date('Y-m-d', strtotime($today));
            $rmonth = MetricsDasboard::select('quantity')
	            						   ->where([
	            						   	['date', '>=', $db],
	            						   	['date', '<=', $de],
	            						   	['type', 'R']
	            						   ])
	            						   ->sum('quantity');

	        //Tres meses
            $db = date("Y-m-d",strtotime('-3 month', strtotime($today)));
            $de = date('Y-m-d', strtotime($today));
            $rtri = MetricsDasboard::select('quantity')
	            						   ->where([
	            						   	['date', '>=', $db],
	            						   	['date', '<=', $de],
	            						   	['type', 'R']
	            						   ])
	            						   ->sum('quantity');

            //Saldo de concentradores
            $concentrator = Concentrator::select('business_name', 'balance')->where('status','!=','T')->get();
			$clist = [];
			foreach ($concentrator as $con) {
				$clist[] = [
							'business_name'=>$con->business_name,
							'balance'=>number_format($con->balance,2,'.',',')
						]; 
			}

			//Clientes activos

			//$datetest = Carbon::now();
			$A90 = 0;
			$meHbb = MetricsBi2::select('A90')
							->where([
								['status', 'A'],
								['type', 'HBB']
							])
							->orderBy('id', 'DESC')
							->first();

			if(!empty($meHbb)){
				$A90 += $meHbb->A90;
			}

			$meMbb = MetricsBi2::select('A90')
							->where([
								['status', 'A'],
								['type', 'T']
							])
							->orderBy('id', 'DESC')
							->first();

			if(!empty($meMbb)){
				$A90 += $meMbb->A90;
			}

			$active = $A90 + (!empty($upmonth) ? $upmonth : 0) + (!empty($uptoday->total_u) ? $uptoday->total_u : 0);

			//Clientes totales
			$uTotal = Sale::select('islim_sales.id')
							->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
							->join('islim_clients', 'islim_client_netweys.clients_dni', '=', 'islim_clients.dni')
							->join('islim_services', 'islim_services.id', '=', 'islim_sales.services_id')
							->where([
			                    ['islim_clients.name', '!=', 'TEMPORAL'],
			                    ['islim_sales.type', 'P']
			                ])
							->whereIn('islim_sales.status', ['A', 'E'])
							->whereIn('islim_client_netweys.status', ['A', 'S'])
							->count();

			$inac = $uTotal - $active;

			//Recargas totales
			$rTotal = Sale::where('islim_sales.type', 'R')
							->join('islim_client_netweys', 'islim_client_netweys.msisdn', '=', 'islim_sales.msisdn')
							->whereIn('islim_sales.status', ['A'])
							->whereIn('islim_client_netweys.status', ['A', 'S'])
							->count();

			//Grafica de altas trimestral
			$db = date("Y-m-d",strtotime('-3 month', strtotime($today))).' 00:00:00';
			$cicles = ((((strtotime($today) - strtotime($db)) / 60) / 60) / 24);
			$dataCurrent = strtotime($db);
			$dataUp = [];

			for($i = 0; $i < $cicles; $i++){
				$db = date('Y-m-d', $dataCurrent);

            	$ut = MetricsDasboard::select('quantity')
            						   ->where([
            						   	['date', $db],
            						   	['type', 'U']
            						   ])
            						   ->sum('quantity');

            	$dataUp []= ['count' => $ut, 'date' => date('m-d', $dataCurrent)];

            	$dataCurrent = strtotime('+1 day', $dataCurrent);
			}

			//Grafica de recargas trimestral
			$db = date("Y-m-d",strtotime('-3 month', strtotime($today))).' 00:00:00';
			$cicles = ((((strtotime($today) - strtotime($db)) / 60) / 60) / 24);
			$dataCurrent = strtotime($db);
			$dataRe = [];

			for($i = 0; $i < $cicles; $i++){
				$db = date('Y-m-d', $dataCurrent);

            	$rt = MetricsDasboard::select('quantity')
            						   ->where([
            						   	['date', $db],
            						   	['type', 'R']
            						   ])
            						   ->sum('quantity');

            	$dataRe []= ['count' => $rt, 'date' => date('m-d', $dataCurrent)];

            	$dataCurrent = strtotime('+1 day', $dataCurrent);
			}

			return response()->json([
                                        'success' => true,
                                        'up_today' => $uptoday->total_u,
                                        'up_month' => $upmonth,
                                        'up_tri' => $uptri,
                                        're_today' => $rtoday->total_u,
                                        're_month' => $rmonth,
                                        're_tri' => $rtri,
                                        'concentrator' => $clist,
                                        'active' => $active,
                                        'inactive' => $inac,
                                        'total_up' => $uTotal,
                                        'total_re' => $rTotal,
                                        'up_graf' => $dataUp,
                                        're_graf' => $dataRe
                                    ]);
		}
	}*/

	/*DEPRECATED*/
	/*public function dashboardGrap(Request $request){
		if($request->isMethod('post') && $request->ajax()){
			$today = date('Y-m-d').'00:00:00';

			if($request->interval == 'quarterly'){
				$db = date("Y-m-d",strtotime('-3 month', strtotime($today))).' 00:00:00';
				$cicles = ((((strtotime($today) - strtotime($db)) / 60) / 60) / 24);
				$cicles = $cicles == 0 ? 1 : $cicles;

			}elseif($request->interval == 'monthly'){
				$db = date("Y-m-d",strtotime("first day of this month")).'00:00:00';
				$cicles = ((((strtotime($today) - strtotime($db)) / 60) / 60) / 24);

				//Si es el primer dia del mes, muestra data el mes anterior
				if($cicles == 0){
					$db = date("Y-m-d",strtotime("-1 month")).'00:00:00';
					$cicles = ((((strtotime($today) - strtotime($db)) / 60) / 60) / 24);
				}

			}elseif($request->interval == 'weekly'){
				$db = date("Y-m-d",strtotime('last Monday')).' 00:00:00';
				$cicles = ((((strtotime($today) - strtotime($db)) / 60) / 60) / 24);
				$cicles = $cicles == 0 ? 1 : $cicles;

			}elseif($request->interval == 'daily'){
				$db = date("Y-m-d",strtotime('-1 day', strtotime($today))).' 00:00:00';
				$cicles = 24;
			}

			if(!empty($cicles)){
				$dataCurrent = strtotime($db);
				$dataUp = [];

				for($i = 0; $i < $cicles; $i++){
	            	if($request->interval == 'daily'){
	            		$db = date('Y-m-d H:i:s', $dataCurrent);
	            		$de = date('Y-m-d H:i:s', $dataCurrent + 3599);

	            		$ut = Sale::where([
	                                ['islim_sales.date_reg', '>=', $db],
	                                ['islim_sales.date_reg', '<=', $de],
	                                ['islim_sales.type', 'P']
	                            ])
	                            ->whereIn('islim_sales.status', ['A', 'E'])
	                            ->count();

	            		$dataUp []= ['count' => $ut, 'date' => date('Y-m-d H:i', $dataCurrent)];
	            		$dataCurrent = strtotime('+1 hour', $dataCurrent);
	            	}
	            	else{
	            		$db = date('Y-m-d', $dataCurrent);
	            		$ut = MetricsDasboard::select('quantity')
		            						   ->where([
		            						   	['date', $db],
		            						   	['type', $request->type == 'P' ? 'U' : 'R']
		            						   ])
		            						   ->sum('quantity');

	            		$dataUp []= ['count' => $ut, 'date' => date('m-d', $dataCurrent)];
	            		$dataCurrent = strtotime('+1 day', $dataCurrent);
	            	}
				}

				return response()->json([
					'success' => true,
					'data' => $dataUp
				]);
			}


			return response()->json([
				'success' => false
			]);
		}
	}*/
}
