<?php
/*rutas CRUD usuario*/

Route::get('api/user', 'UserController@index');

Route::post('api/user/store', 'UserController@store');

Route::post('api/user/update-pass', 'UserController@updateUserPass')->name('updateUserPass');

Route::get('api/user/{user_email}', 'UserController@show');

Route::put('api/user/{user_email}', 'UserController@update');

Route::delete('api/user/{user_email}', 'UserController@destroy');

Route::get('api/user/getUserPolicy/{user_email}/{activos?}', 'UserController@getUser');

Route::put('api/user/chpass/{user_email}', 'UserController@chpass');

Route::post('api/user/get/datatable', 'UserController@getusersdt');

Route::post('api/user/get/csvUser', ['as' => 'csvUsers', 'uses' => 'UserController@downloadCSVUsers']);

Route::get('api/user/get/profile/{type}', 'UserController@profiles');

Route::post('api/user/get/filter', 'UserController@getFilterUsers');

Route::post('api/user/get/warehouses', 'UserController@warehouses');

Route::post('api/user/get/filter-profiles', 'UserController@getFilterProfiles');
Route::post('api/user/get/filter-supervisors', 'UserController@getFilterSupervisors');

Route::post('api/user/get/profiles-by-platform', 'UserController@getProfilesByPlatform');
Route::post('api/user/get/supervisors', 'UserController@getSupervisors');
Route::post('api/user/get/replacements', 'UserController@getReplacements');

Route::post('api/user/get/is-removable', 'UserController@isRemovable');

//Retorna códigos de deposito
Route::post('api/user/bank/cod-dep', 'UserController@getCodeDeposit');
//Válida si un código de deposito existe
Route::post('api/user/bank/check-code', 'UserController@checkCodeDeposit');
//Valida que el password corresponde al usaurio logeado
Route::post('api/user/chekingPass', 'UserController@checkPass');
//Valida si el usuario tiene inventario activo
Route::post('api/user/chekingInv', 'UserController@checkInv');
//Retorna la informacion de las divisiones
Route::post('api/user/getdivision', 'UserController@getdivision');
//Retorna la informacion de regiones
Route::post('api/user/getregions', 'UserController@getregions');
//Retorna la informacion de coordinaciones
Route::post('api/user/getcoordinacion', 'UserController@getcoordinacion');
//Elimina código de depósito
Route::post('api/user/delete-cod-dep', 'UserController@deleteCodDep');

//Esquema comercial netwey
Route::get('view/user/scheme', 'UserController@getListScheme');
Route::post('api/user/schemeDt', 'UserController@getListSchemeDT');
Route::post('api/user/get_filter_scheme', 'UserController@get_filter_scheme');
Route::post('api/user/scheme/edit', 'UserController@edit_scheme');
Route::post('api/user/scheme/delete', 'UserController@delete_scheme');
Route::post('api/user/scheme/formCreate', 'UserController@formCreate_scheme');
Route::post('api/user/scheme/create', 'UserController@create_scheme')->name('create_scheme');


//Distribuidores de Usuarios

Route::get('view/user/distributor', 'UserController@distributorsView');


Route::get('api/user/distributor/datatable', 'UserController@getDistributorDT');
Route::post('api/user/distributor/store', 'UserController@storeDistributor');
Route::post('api/user/distributor/update', 'UserController@updateDistributor');
Route::delete('api/user/distributor/delete/{id}', 'UserController@destroyDistributor');