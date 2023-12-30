<?php
/*LJPD*/
/* VIEW/PORT */
Route::prefix('view/port')->group(function () {
//Importaciones
  Route::get('/importaciones', 'PortabilityController@portability_importacion');
  Route::post('/PortImportPeriod', 'PortabilityController@getDTPortImportPeriod')->name('getDTPortImportPeriod');
  Route::post('/PortImportPeriodFile', 'PortabilityController@getDTPortImportDownload')->name('getDTPortImportDownload');
  Route::post('/PortImportItem', 'PortabilityController@PortImportUpdateItem')->name('PortImportUpdateItem');
  Route::post('/PortImportObservation', 'PortabilityController@PortImportSetObservation')->name('PortImportSetObservation');

//Este metodo sirve para importacion y exportacion
  Route::post('/getDetailsSoap', 'PortabilityController@getDetailsSoap')->name('getDetailsSoap');

  Route::post('/PortImportSetCancelSoapItem', 'PortabilityController@PortImportSetCancelSoapItem')->name('PortImportSetCancelSoapItem');
  Route::post('/PortImportSetNewNIP', 'PortabilityController@PortImportSetNewNIP')->name('PortImportSetNewNIP');
  Route::post('/getStatusResult', 'PortabilityController@getStatusResult')->name('getStatusResult');
  Route::post('/PortImportSetReprocessInADB', 'PortabilityController@PortImportSetReprocessInADB')->name('PortImportSetReprocessInADB');

//Exportaciones
  Route::get('/exportaciones', 'PortabilityController@portability_exportacion');
  Route::post('/PortExportPeriod', 'PortabilityController@getDTPortExportPeriod')->name('getDTPortExportPeriod');
  Route::post('/PortExportPeriodFile', 'PortabilityController@getDTPortExportDownload')->name('getDTPortExportDownload');

//Call center
  Route::post('portFromNew', 'PortabilityController@viewFromNewPortability')->name('viewFromNewPortability');
  Route::post('portChekingNew', 'PortabilityController@portChekingDNPort')->name('portChekingDNPort');
  Route::post('portSuccessNew', 'PortabilityController@portSuccessNew')->name('portSuccessNew');
  Route::post('portSendNew', 'PortabilityController@sendFromNewPortability')->name('sendFromNewPortability');
  Route::post('chekingSupervisor', 'PortabilityController@chekingSupervisor')->name('chekingSupervisor');

});
