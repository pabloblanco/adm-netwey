<?php
#descargar csv
Route::get('download/prospect', 'ReportsController@ProspectsDetailexport');
Route::get('download/client', 'ReportsController@viewClientsExport');