<?php

Route::get('view/financing/list', 'FinancingController@view');

Route::post('api/financing/list-dt', 'FinancingController@listDT')->name('financing.listDT');
Route::post('api/financing/created', 'FinancingController@create')->name('financing.create');
Route::post('api/financing/edit', 'FinancingController@edit')->name('financing.edit');
Route::post('api/financing/delete', 'FinancingController@delete')->name('financing.delete');