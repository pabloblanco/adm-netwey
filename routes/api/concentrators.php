<?php

Route::get('api/concentrator','ConcentratorController@index');
Route::post('api/concentrator/store', 'ConcentratorController@store');
Route::get('api/concentrator/{id}', 'ConcentratorController@show');
Route::put('api/concentrator/{id}', 'ConcentratorController@update');
Route::delete('api/concentrator/{id}', 'ConcentratorController@destroy');

Route::get('view/concentrator/balance', 'ConcentratorController@balanceView');
Route::get('api/concentrator/balance/datatable', 'ConcentratorController@datatable');
Route::post('api/concentrator/balance/{id}', 'ConcentratorController@balanceAssign');