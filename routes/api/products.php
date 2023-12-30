<?php

Route::get('api/products','ProductController@index');

Route::post('api/products/store', 'ProductController@store');

Route::get('api/products/{id}', 'ProductController@show');

Route::put('api/products/{id}', 'ProductController@update');

Route::delete('api/products/{id}', 'ProductController@destroy');

Route::post('api/products/get-fiber-products-list', 'ProductController@getFiberProductsList');

Route::post('api/products/get-article-fiber-product', 'ProductController@getArticleFiberProduct');

Route::post('api/products/is-unique-sku', 'ProductController@isUniqueSku');