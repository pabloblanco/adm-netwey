<?php
Route::prefix('view/fiber/')->group(function () {
  Route::get('view_zone', 'FiberController@ViewListFiber');

  Route::post('list_zone', 'FiberController@ListZone')->name('ListZone');
  Route::post('chekingZone', 'FiberController@chekingZone')->name('chekingZone');
  Route::post('getDownloadZones', 'FiberController@getDownloadZones')->name('getDownloadZones');
  Route::post('getDetailZona', 'FiberController@getDetailZona')->name('getDetailZona');

  Route::post('createZone', 'FiberController@createZone')->name('createZone');
  Route::post('updateZone', 'FiberController@updateZone')->name('updateZone');
  Route::post('deleteZone', 'FiberController@deleteZone')->name('deleteZone');

/**********************************************/

  Route::get('signal', 'FiberController@ViewSignalFiber');
  Route::post('listViewMap', 'FiberController@listViewMap')->name('listViewMap');
  Route::post('viewMap', 'FiberController@viewMap')->name('viewMap');
  Route::post('loadMap', 'FiberController@loadMap')->name('loadMap');
  Route::post('updateItemMap', 'FiberController@updateItemMap')->name('updateItemMap');
  Route::post('updateListMap', 'FiberController@updateListMap')->name('updateListMap');

});
