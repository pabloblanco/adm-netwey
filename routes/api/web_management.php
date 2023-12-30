<?php

use Illuminate\Support\Facades\Route;

Route::prefix('view/web-management')->group(function() {
    Route::get('/fees', 'WebManagementFeeController@index');
    Route::get('/financing_methods', 'FinancingController@methodsView');
});



Route::get('api/web-management/fees', 'WebManagementFeeController@getDataTable');
Route::post('api/web-management/fees', 'WebManagementFeeController@store');
Route::post('api/web-management/fees/{id}', 'WebManagementFeeController@update');
Route::get('api/web-management/fees/get-fees-select', 'WebManagementFeeController@getFeesSelect');
Route::get('api/web-management/fees/{id}', 'WebManagementFeeController@show');
Route::delete('api/web-management/fees/{id}', 'WebManagementFeeController@destroy');

//Metodos de Financiamineto (Descuentos).
Route::get('api/web-management/financing-methods', 'FinancingController@getMethodsDiscounts');
Route::post('api/web-management/financing-methods', 'FinancingController@updateMethodsDiscounts');