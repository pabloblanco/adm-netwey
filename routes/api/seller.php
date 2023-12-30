<?php
/*rutas CRUD para inventario de vendedores*/
Route::post('api/seller_inventories/associate/show', 'SellerInventoryController@associateSellerInventory');

Route::post('api/seller_inventories/delete_batch', 'SellerInventoryController@deleteBatch');

Route::delete('api/seller_inventories/{user_email}/{type}/{article}', 'SellerInventoryController@delete');

Route::get('api/seller_inventories','SellerInventoryController@view');

Route::get('api/seller_inventories/sellerpacks/{user_email}','SellerInventoryController@sellerpacks');

Route::get('api/seller_inventories/otherpacks/{user_email}','SellerInventoryController@otherpacks');

Route::get('api/seller_inventories/otherproducts/{user_email}','SellerInventoryController@otherproducts');

Route::post('api/seller_inventories/store', 'SellerInventoryController@store');

Route::get('api/seller_inventories/{id}', 'SellerInventoryController@show');

Route::put('api/seller_inventories', 'SellerInventoryController@update');

Route::post('api/seller_inventories/get_users_inv', 'SellerInventoryController@getUsersInv');

Route::post('api/seller_inventories/get_users', 'SellerInventoryController@getUsersSelect');

Route::post('api/seller_inventories/get_dns_available', 'SellerInventoryController@getDNsAvailable');

/*rutas CRUD recepción de dinero por vendedor*/

Route::get('view/seller_reception/sales/{email}','SellerReceptionController@viewSalesTable');
Route::put('api/seller_reception/sales/aprove/{ids}/{received}/{user}','SellerReceptionController@aprove');

Route::get('view/seller_reception/deposit/{email}','SellerReceptionController@viewDepositTable');

Route::post('api/seller_reception/deposit/report/{ids}','SellerReceptionController@report');

Route::get('view/seller_reception/deposit/detail/{sale}','SellerReceptionController@viewDepositDetailTable');

/*rutas CRUD consolidación de ventas*/
Route::get('view/seller/comission/{email}','SellerComissionController@viewSalesTable');
Route::put('api/seller/comission/consolidate/{ids}/{user}','SellerComissionController@consolidate');

/*rutas CRUD asignación de saldo*/
Route::get('view/seller/balance','SellerBalanceController@view');
Route::get('api/seller/balance/datatable','SellerBalanceController@datatable');
Route::post('api/seller/balance/assign/{user}','SellerBalanceController@assignBalance');

/*Id para depositos*/
Route::get('view/seller/deposit_id','SellerReceptionController@depositID');
Route::get('view/seller/deposit_id/{type?}','SellerReceptionController@depositID');
Route::post('api/seller/deposit_id/get_users', 'SellerReceptionController@getUsers')->name('get_users');
Route::post('api/seller/get_user_by_deposit', 'SellerReceptionController@getUserByDeposit')->name('get_user_by_deposit');
Route::post('api/seller/create_id_dep', 'SellerReceptionController@createIdDep')->name('create_id_dep');
Route::post('api/seller/delete_id_dep', 'SellerReceptionController@deleteIdDep')->name('delete_id_dep');
Route::post('api/seller/edit_id_dep', 'SellerReceptionController@editIdDep')->name('edit_id_dep');
Route::post('api/seller/download_cod_dep_users', 'SellerReceptionController@downloadCodDepUsers')->name('download_cod_dep_users');

//Carga depositos
//Carga depósitos desde csv
Route::post('api/seller/load_deposit_file', 'SellerReceptionController@loadDepositCSV')->name('load_deposit_file');
//Depósitos no asignados
Route::post('api/seller/load_deposit_na', 'SellerReceptionController@loadDepositNA')->name('load_deposit_na');
//Elimina depósito no asignado
Route::post('api/seller/delete_deposit_na', 'SellerReceptionController@deleteDepositNotAssigned')->name('delete_deposit_na');
//asocia a usuario depósito no asignado
Route::post('api/seller/asociate_deposit_na', 'SellerReceptionController@associateDeposit')->name('asociate_deposit_na');
//Deuda de los usuaios
Route::post('api/seller/get_user_debt', 'SellerReceptionController@getUserDebt')->name('get_user_debt');
//Carga depósito manual
Route::post('api/seller/load_manual_deposit', 'SellerReceptionController@loadManualDeposit')->name('load_manual_deposit');

//Bloque y desbloqueo de usuarios
Route::post('api/seller/locked_user', 'SellerReceptionController@lockedUser');
Route::post('api/seller/un_locked_user', 'SellerReceptionController@unLockedUser');

//Detalle de deuda y conciliacion
Route::post('api/seller/detail_debt', 'SellerReceptionController@detailDebt')
	   ->name('detail_debt');
Route::post('api/seller/detail_debt_sellers', 'SellerReceptionController@detailDebtSellers')
	   ->name('detail_debt_sellers');
Route::post('api/seller/detail_debt_inst', 'SellerReceptionController@detailDebtInst')
	   ->name('detailDebtInst');
Route::post('api/seller/bash_conciliate', 'SellerReceptionController@bashConciliate')->name('bash_conciliate');
Route::post('api/seller/get_lasts_deposits', 'SellerReceptionController@getLastsDeposits')->name('get_lasts_deposits');
Route::post('api/seller/delete_last_deposit', 'SellerReceptionController@deleteLastDeposit')->name('delete_last_deposit');
Route::post('api/seller/get_last_deposit_not_conc', 'SellerReceptionController@getLastDepositNotConc')->name('get_last_deposit_not_conc');

//Conciliación de ventas en abono
Route::post('api/seller/get_users_deb', 'SellerReceptionController@getUsersDeb')
	   ->name('get_users_deb');

Route::post('api/seller/get_debt_inst_dt', 'SellerReceptionController@getDebtInstDT')->name('get_debt_inst_dt');

Route::post('api/seller/get_info_user', 'SellerReceptionController@getInfoUser')->name('get_info_user');

Route::post('api/seller/bash_conciliate_ins', 'SellerReceptionController@bashConciliateIns')->name('bash_conciliate_ins');

Route::post('api/seller/get_conc_inst', 'SellerReceptionController@getConcInst')->name('get_conc_inst');

//Deuda de los coordinadores
Route::get('view/seller/coord_debt', 'SellerReceptionController@coordDebt')->name('coord_debt');
Route::post('api/seller/get_coord_debt', 'SellerReceptionController@getCoordDebt')->name('get_coord_debt');

//Pedido sugerido
Route::get('view/seller/suggested_order', 'SellerInventoryController@suggestedOrder');
Route::post('api/seller/suggested_order_save', 'SellerInventoryController@suggestedOrderSave')->name('suggested_order_save');

//Mostrar listado de usuarios del usuario autenticado para su Solicitud de baja
Route::post('api/seller/get-user-request-leave', 'SellerInventoryController@getUserRequestLeave')->name('getUserRequestLeave');

//Mostrar listado de usuarios del usuario autenticado para su Solicitud de baja
Route::post('api/seller/request-leave', 'SellerInventoryController@requestLeave')->name('requestLeave');

//Obtener el listado de ventas de las ultimas 2 semanas del usuario a solicitar la baja
Route::post('api/seller/get-user-detail', 'SellerInventoryController@getUserDetail')->name('getUserDetail');

//Mostrar listado de solicitudes de bajas del usuario autenticado
Route::post('api/seller/list-request-leave', 'SellerInventoryController@listRequestLeave')->name('listRequestLeave');
