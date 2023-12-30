<?php

Route::get('api/pack','PackController@index');

Route::post('api/pack/store', 'PackController@store');

Route::get('api/pack/{id}', 'PackController@show');

Route::put('api/pack/{id}', 'PackController@update');

Route::delete('api/pack/{id}', 'PackController@destroy');

Route::get('view/pack/detail/{id}', 'PackController@detailView');

Route::post('api/pack/product/{id}', 'PackController@updateProduct');

Route::post('api/pack/service/{id}', 'PackController@updateService');

Route::get('view/pack/detail/associated/{id}', 'PackController@detailView');

Route::delete('api/pack/product/associated/{id}/{product}', 'PackController@destroyProduct');

Route::delete('api/pack/service/associated/{id}/{service}', 'PackController@destroyService');

//ruta para consultar coordinaciones
Route::post('api/pack/get_coordinations', 'PackController@get_coordinations');

//ruta para consultar servicios a partir de zonafibra
Route::post('api/pack/get-services-fiber-zone-pack', 'PackController@getServicesFiberZonePack');
//ruta para consultar productos a partir zonafibra
Route::post('api/pack/get-products-fiber-zone-pack', 'PackController@getProductsFiberZonePack');