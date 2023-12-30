<?php

Route::get('api/services','ServiceController@view');

Route::post('api/services/store', 'ServiceController@store');

Route::get('api/services/{id}', 'ServiceController@show');

Route::put('api/services/{id}', 'ServiceController@update');

Route::delete('api/services/{id}', 'ServiceController@destroy');


Route::post('api/services/getServRetByPeriod', 'ServiceController@getServRetByPeriod');

Route::post('api/services/get-fiber-services-list', 'ServiceController@getFiberServicesList');

Route::post('api/services/get-service-fiber-service', 'ServiceController@getServiceFiberService');