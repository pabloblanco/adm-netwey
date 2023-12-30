<?php
/* VIEW/CLIENTE */
Route::prefix('view/client')->group(function () {
  Route::post('/get', 'ClientController@viewClients');
  Route::get('/datatable/{msisdn}', 'ClientController@getviewtables');
  Route::get('/datatable-comp/{msisdn}', 'ClientController@getviewtablesComp');
  Route::get('/datatable-blim/{msisdn}', 'ClientController@getviewtablesblim');
  Route::get('/datatable-retention-services/{msisdn}', 'ClientController@getviewtablesretentions');
  Route::get('/datatable-recdisp/{msisdn}', 'ClientController@getviewtablesRecDisp');
  Route::post('/datatable-comp-details', 'ClientController@getviewCompDetails');
  Route::get('/datatable-compensations/{msisdn}', 'ClientController@getviewtablesCompensations');
  //Lista de productos vendidos pero no activos
  Route::get('/artic_buy', 'ClientController@articBuy');
  Route::post('/dt_artic_buy', 'ClientController@articBuyDT')->name('articBuyDT');
  Route::post('/dw_artic_buy', 'ClientController@articBuyDW')->name('articBuyDW');
  //Recompra
  Route::get('/buy_back', 'ClientControllerTwo@viewBuyBack');
  //Serviciabilidad para el call
  Route::get('/serviciability', 'ClientController@serviciability');
  Route::get('/datatable-promociones/{msisdn}', 'ClientController@servicespromociones');
  Route::post('/getdtpromociones', 'ClientController@getDTPromociones')->name('getDTPromociones');
  Route::post('/checkMail', 'ClientController@check_email')->name('check_email');
  Route::post('/datatable-plans-mp', 'ClientControllerTwo@getviewtablesPlansMercadoPago')->name('getDTPlansMP');

});
/* END VIEW/CLIENTE */

/* API/CLIENTE */
Route::prefix('api/client')->group(function () {
  Route::get('/datatable/{msisdn}', 'ClientController@rechargesdt');
  Route::get('/datatable-compensations/{msisdn}', 'ClientController@compensationsdt');
  Route::get('/datatable-blim/{msisdn}', 'ClientController@blimdt');
  Route::get('/datatable-retention-services/{msisdn}', 'ClientController@retentionsdt');
  Route::post('/update', 'ClientController@update');
  Route::post('/activate-retention-service', 'ClientController@activateRetentionService');
  Route::get('/getpoint/{lat}/{lng}', 'ClientController@getPointsRechargers');
  Route::post('/updatelatlng', 'ClientController@changelatlng');
  Route::post('/updateB28', 'ClientController@updateB28');
  Route::post('/canUpdatelatlng', 'ClientController@canChangelatlng');
  Route::post('/activate-change-coord', 'ClientController@activateChangeCoord');
  Route::post('/suspension-details', 'ClientController@getSuspensionDetails');
  Route::post('/set-suspended-history', 'ClientController@setSuspendedHistory');
  //Devuelve dns dada una pista
  Route::post('/get-clients-input', 'ClientController@getClientsForInput');
  Route::post('/get-clients-by-name', 'ClientController@getClientsByName');
  //end
  Route::post('/buy_back/process_file', 'ClientControllerTwo@processFile');
  Route::post('/buy_back/get_table', 'ClientControllerTwo@getTable');
  Route::post('/buy-back', 'ClientControllerTwo@getLastContact');
  Route::post('/save-call-buy-back', 'ClientControllerTwo@saveContact');
  //Serviciabilidad para el call
  Route::post('/get-serviciability', 'ClientController@getServiciability')->name('getServiciability');
  /* API/CLIENTE/ALTAN */
  Route::prefix('/altan')->group(function () {
    Route::post('/barring/{msisdn?}', 'ClientController@barring');
    Route::post('/unbarring/{msisdn?}', 'ClientController@unbarring');
    Route::post('/profile/{msisdn?}', 'ClientController@profile')->name('profile_altam');
    Route::post('/profile-new/{msisdn?}', 'ClientController@getClientProfile')->name('getClientProfile');
    Route::post('/reduce-deactivate/{msisdn?}', 'ClientController@reduceDeactivate')->name('reduceDeactivate');
    Route::post('/suspend/{msisdn}', 'ClientController@suspend');
    Route::post('/suspendtheft/{msisdn}', 'ClientController@suspendTheftorLost');
    Route::post('/activate/{msisdn}', 'ClientController@activate');
    Route::post('/pre-desactivate/{msisdn}', 'ClientController@preDesactivate');
    Route::post('/reactivate/{msisdn}', 'ClientController@reactivate');

    Route::post('/health-network', 'ClientController@getHealthNetwork');
    Route::post('/coordinates-changes', 'ClientController@getCoordinatesChanges');
    Route::post('/compensation-bonus', 'ClientController@getCompensationsStatus');
    Route::post('/compensation-history', 'ClientController@getCompensationsHistory');



  });
  /* END API/CLIENTE/ALTAN */
});
/* END API/CLIENTE */

/* API/CLIENTS/DATATABLE */
Route::prefix('api/clients/datatable')->group(function () {
  Route::get('/{msisdns}', 'ClientController@clientdt');
  Route::post('/get-dn', 'ClientController@getClientdt');
});
/* END API/CLIENTS/DATATABLE */
