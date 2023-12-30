<?php

Route::get('api/inventories', 'InventoryController@index');

Route::post('api/inventories/store', 'InventoryController@store');

Route::get('api/inventories/{id}', 'InventoryController@show');

Route::put('api/inventories/{id}', 'InventoryController@update');

Route::delete('api/inventories/{id}', 'InventoryController@destroy');

Route::post('api/inventories/get-dns', 'InventoryController@getDNForFilter');

Route::post('api/inventories/get-available-dn-autogen', 'InventoryController@getAvailableDnAutogen')->name('getAvailableDnAutogen');

#mover inventario entre bodegas
Route::post('api/movewh/update', 'InventoryController@mpwhs');

Route::post('api/movewh/move-csv', 'InventoryController@mpwhs');

Route::post('api/inventories/store-csv', 'InventoryController@import_store_csv');

#estatus de inventario
Route::post('api/inventories/status/get-dt-status-inv', 'InventoryController@getDtStatusInv')
  ->name('getDtStatusInv');

Route::post('api/inventories/status/set-valid-motive', 'InventoryController@setValidMotive')
  ->name('setValidMotive');

Route::post('api/inventories/status/set-invalid-motive', 'InventoryController@setInvalidMotive')
  ->name('setInvalidMotive');

Route::post('api/inventories/status/set-theft-motive', 'InventoryController@setTheftMotive')
  ->name('setTheftMotive');

Route::post('api/inventories/status/load-file', 'InventoryController@loadStatusMasive_csv')
  ->name('loadStatusMasive_csv');

Route::post('api/inventories/status/move-to-merma', 'InventoryController@moveToMerma')
  ->name('moveToMerma');

#guias pendientes
Route::post('api/inventories/orders/get-dt-pendding-orders', 'InventoryController@getDtPenddingOrders')
  ->name('getDtPenddingOrders');
Route::post('api/inventories/orders/get-dt-pendding-order-detail', 'InventoryController@getDtPenddingOrderDetails')
  ->name('getDtPenddingOrderDetails');
Route::post('api/inventories/orders/action-pendding-orders', 'InventoryController@actionPenddingOrders')
  ->name('actionPenddingOrders');

#reporte bodega merma equipos viejos
Route::post('api/inventories/merma-old-equipment', 'InventoryController@getMermaOldEquipment')
  ->name('getMermaOldEquipment');

#Reporte de reciclaje de msisdn
Route::get('view/inv_recicler', 'InventoryController@inventoryRecicler');
Route::post('api/get_inv_recicler', 'InventoryController@getDtInventoryRecicler')->name('getDtInventoryRecicler');
Route::post('api/invReciclerDownload', 'InventoryController@InventoryReciclerDownload')->name('InventoryReciclerDownload');
Route::post('api/setReprocessRecicler', 'InventoryController@setProcessRecicler')->name('setProcessRecicler');

#detalles de invetario
Route::post('api/inventories/get_dt_inventory_details', 'InventoryController@getDTInventoryDetails')
  ->name('getDTInventoryDetails');


Route::post('api/inventories/update_ids_products', 'InventoryController@updateIdsProductsAction')
  ->name('updateIdsProductsAction');


//Reporte historico de estatus de inventarios
Route::get('view/status_history_inv', 'InventoryController@status_history_view');
Route::post('api/inventories/status/get-dt-status-history-inv', 'InventoryController@getDTStatusHistoryInv')
  ->name('getDTStatusHistoryInv');
Route::post('api/inventories/status/download-dt-status-history-inv', 'InventoryController@downloadDTStatusHistoryInv')
  ->name('downloadDTStatusHistoryInv');
//End Reporte historico de estatus de inventarios



