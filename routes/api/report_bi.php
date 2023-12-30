<?php
//Base total
Route::get('view/reports_bi/total_base', 'ReportBIController@totalBase');
Route::post('view/reports_bi/get-kpis', 'ReportBIController@getKPIs')->name('getKPIs');

//Base recargadora
Route::get('view/reports_bi/recharge_base', 'ReportBIController@rechargeBase');
Route::post('reports_bi/recharge_base/get-clients', 'ReportBIController@getClientsRechargeBase')
	   ->name('getClientsRechargeBase');
Route::post('reports_bi/recharge_base/download-clients', 'ReportBIController@downloadClientsRechargeBase')
	   ->name('downloadClientsRechargeBase');

//Base activa
Route::get('view/reports_bi/active_base', 'ReportBIController@activeBase');
Route::post('reports_bi/active_base/get-clients', 'ReportBIController@getClientsActiveBase')
	   ->name('getClientsActiveBase');
Route::post('reports_bi/active_base/get-metric', 'ReportBIController@getTotalActiveBase')
	   ->name('getMetricActiveBase');
Route::post('reports_bi/active_base/download-clients', 'ReportBIController@downloadClientsActiveBase')
	   ->name('downloadClientsActiveBase');

//Churn
Route::get('view/reports_bi/churn', 'ReportBIController@churn');
Route::post('reports_bi/churn/get-clients', 'ReportBIController@getClientsChurn')
	   ->name('getClientsChurn');
Route::post('reports_bi/churn/get-metric', 'ReportBIController@getTotalChurn')
	   ->name('getMetricChurn');
Route::post('reports_bi/churn/download-clients', 'ReportBIController@downloadChurn')
	   ->name('downloadClientsChurn');

//Churn30
Route::get('view/reports_bi/churn_th', 'ReportBIController@churnTh');
Route::post('reports_bi/churn_th/get-clients', 'ReportBIController@getClientsChurnTh')
	   ->name('getClientsChurnTh');
Route::post('reports_bi/churn_th/get-metric', 'ReportBIController@getTotalChurnTh')
	   ->name('getMetricChurnTh');
Route::post('reports_bi/churn_th/download-clients', 'ReportBIController@downloadChurnTh')
	   ->name('downloadChurnTh');

//Decay
Route::get('view/reports_bi/decay', 'ReportBIController@decay');
Route::post('reports_bi/decay/get-clients', 'ReportBIController@getClientsDecay')
	   ->name('getClientsDecay');
Route::post('reports_bi/decay/get-metric', 'ReportBIController@getTotalDecay')
	   ->name('getMetricDecay');
Route::post('reports_bi/decay/download-clients', 'ReportBIController@downloadDecay')
	   ->name('downloadClientsDecay');

//ARPU Altas
Route::get('view/reports_bi/arpu_up', 'ReportBIController@arpuUp');
Route::post('reports_bi/arpu_up/get-metric', 'ReportBIController@getArpuUp')
	   ->name('getArpuUp');

//ARPU base
Route::get('view/reports_bi/arpu_base', 'ReportBIController@arpuBase');
Route::post('reports_bi/arpu_base/get-metric', 'ReportBIController@getArpuBase')
	   ->name('getArpuBase');

//Mix de parque recargado
Route::get('view/reports_bi/mix_recharge', 'ReportBIController@mixRecharge');
Route::post('reports_bi/mix_recharge/get-mix', 'ReportBIController@getMix')
	   ->name('getMix');

//Calidad de altas
Route::get('view/reports_bi/quality_up', 'ReportBIController@qualityUp');
Route::post('reports_bi/quality_up/get-report', 'ReportBIController@getQualityUp')->name('getQualityUp');

//Url para descargar reportes almacenados en el directorio /public/reports
Route::get('view/reports_bi/downloads/{delete}', 'ReportBIController@downloadReports')
	   ->name('downloadFile');