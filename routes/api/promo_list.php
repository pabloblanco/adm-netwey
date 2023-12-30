<?php

Route::get('api/promo_list','PromoListController@view');

Route::post('api/promo_list/store', 'PromoListController@store');

Route::put('api/promo_list/{id}', 'PromoListController@update');

