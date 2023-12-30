<?php

Route::get('api/servicesprom','ServicesPromController@view');

Route::post('api/servicesprom/store', 'ServicesPromController@store');

Route::get('api/servicesprom/{id}', 'ServicesPromController@show');

Route::put('api/servicesprom/{id}', 'ServicesPromController@update');

Route::delete('api/servicesprom/{id}', 'ServicesPromController@destroy');