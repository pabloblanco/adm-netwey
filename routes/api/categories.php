<?php

Route::get('api/categories','ProductsCategoryController@index');

Route::post('api/categories/store', 'ProductsCategoryController@store');

Route::get('api/categories/{dni}', 'ProductsCategoryController@show');

Route::put('api/categories/{dni}', 'ProductsCategoryController@update');

Route::delete('api/categories/{dni}', 'ProductsCategoryController@destroy');