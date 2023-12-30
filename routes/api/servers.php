<?php

Route::get('api/servers','ServersController@index');

Route::post('api/servers/store', 'ServersController@store');

Route::get('api/servers/{id}', 'ServersController@show');

Route::put('api/servers/{id}', 'ServersController@update');

Route::delete('api/servers/{id}', 'ServersController@destroy');