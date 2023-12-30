<?php

Route::get('api/blimservices','BlimServiceController@view');

Route::post('api/blimservices/store', 'BlimServiceController@store');

Route::get('api/blimservices/{id}', 'BlimServiceController@show');

Route::put('api/blimservices/{id}', 'BlimServiceController@update');

Route::delete('api/blimservices/{id}', 'BlimServiceController@destroy');