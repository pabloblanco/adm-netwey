<?php

Route::get('api/warehouses','WarehouseController@index');

Route::post('api/warehouses/store', 'WarehouseController@store');

Route::get('api/warehouses/{id}', 'WarehouseController@show');

Route::put('api/warehouses/{id}', 'WarehouseController@update');

Route::delete('api/warehouses/{id}', 'WarehouseController@destroy');