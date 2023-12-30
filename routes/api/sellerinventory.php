<?php
#Este archivo no se esta importando en el main de rutas, las rutas de este archivo se encuentran definidas en seller.php

Route::post('api/seller_inventories/associate/show', 'SellerInventoryController@associateSellerInventory');

Route::delete('api/seller_inventories/{user_email}/{type}/{article}', 'SellerInventoryController@delete');



Route::get('api/seller_inventories','SellerInventoryController@view');

Route::get('api/seller_inventories/sellerpacks/{user_email}','SellerInventoryController@sellerpacks');

Route::get('api/seller_inventories/otherpacks/{user_email}','SellerInventoryController@otherpacks');

Route::get('api/seller_inventories/otherproducts/{user_email}','SellerInventoryController@otherproducts');

Route::post('api/seller_inventories/store', 'SellerInventoryController@store');

Route::get('api/seller_inventories/{id}', 'SellerInventoryController@show');

Route::put('api/seller_inventories', 'SellerInventoryController@update');
