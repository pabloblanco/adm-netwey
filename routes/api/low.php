<?php
Route::prefix('view/low/')->group(function () {

//Solicitud de bajas
  Route::get('list_request', 'LowController@ViewListRequest');
  Route::post('viewListRequest', 'LowController@getDTListRequestLow')->name('getDTListRequestLow');
  Route::post('viewEvidenceRequest', 'LowController@getEvidenceRequestLow')->name('getEvidenceRequestLow');
  Route::post('setRejectionLow', 'LowController@setRejectionLow')->name('setRejectionLow');
  Route::post('setAceptLow', 'LowController@setAceptLow')->name('setAceptLow');
  Route::post('getRequestDownload', 'LowController@getRequestDownload')->name('getRequestDownload');
  Route::post('get_filter_users_lows', 'LowController@getUserFilterLow')->name('getUserFilterLow');

//Reporte de bajas
  Route::get('request_process', 'LowController@ViewListReport');
  Route::post('viewListLowReport', 'LowController@getDTListLowReport')->name('getDTListLowReport');
  Route::post('getReportLowDownload', 'LowController@getDTListReportDownload')->name('getDTListReportDownload');

//Carga de archivo de finiquito de bajas
  Route::get('request_finish', 'LowController@ViewListLowFinish');
  Route::post('setUploadFiniquite', 'LowController@setUpFiniquite')->name('setUpFiniquite');
  Route::post('viewListLowFiniquite', 'LowController@getDTListLowFiniquite')->name('getDTListLowFiniquite');
  Route::post('getLowFiniquiteDownload', 'LowController@getDTListFiniquiteDownload')->name('getDTListFiniquiteDownload');

//KPI Descuentos por equipos perdidos
  Route::get('kpi_dismissal', 'LowController@ViewListKPIDismissal');
  Route::post('getMonthsAvailables', 'LowController@getMonthsAvailables')->name('getMonthsAvailables');

});
