<?php

Route::get('api/provider','ProductsProviderController@index');

Route::post('api/provider/store', 'ProductsProviderController@store');

Route::get('api/provider/{dni}', 'ProductsProviderController@show');

Route::put('api/provider/{dni}', 'ProductsProviderController@update');

Route::delete('api/provider/{dni}', 'ProductsProviderController@destroy');