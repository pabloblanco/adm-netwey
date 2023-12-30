<?php

Route::get('view/seller_reception/sales/{email}','SellerReceptionController@viewSalesTable');
Route::put('api/seller_reception/sales/aprove/{ids}/{received}/{user}','SellerReceptionController@aprove');

Route::get('view/seller_reception/deposit/{email}','SellerReceptionController@viewDepositTable');

Route::post('api/seller_reception/deposit/report/{ids}','SellerReceptionController@report');

Route::get('view/seller_reception/deposit/detail/{sale}','SellerReceptionController@viewDepositDetailTable');