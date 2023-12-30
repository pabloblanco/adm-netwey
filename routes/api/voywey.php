<?php
// Reporte de Voywey de Nomina
Route::get('view/voywey/voywey_nomina', 'ReportsTwoController@voyweynomina');

Route::post('voywey/dt_voywey_nomina', 'ReportsTwoController@getDTvoywey_nomina')
    ->name('getDTvoywey_nomina');

Route::post('voywey/download_dt_voywey_nomina', 'ReportsTwoController@downloadDTvoywey_nomina')
    ->name('downloadDTvoywey_nomina');
// END Reporte de Voywey de Nomina

// Reporte de Voywey de conciliacion
Route::get('view/voywey/voywey_conciliacion', 'ReportsTwoController@voyweyconciliacion');

Route::post('voywey/dt_voywey_conciliacion', 'ReportsTwoController@getDTvoywey_conciliacion')
    ->name('getDTvoywey_conciliacion');

Route::post('voywey/download_dt_voywey_conciliacion', 'ReportsTwoController@downloadDTvoywey_conciliacion')
    ->name('downloadDTvoywey_conciliacion');
// END Reporte de Voywey de conciliacion

// Reporte de Voywey de inventario
Route::get('view/voywey/voywey_inventory', 'ReportsTwoController@voyweyinventory');

Route::post('voywey/dt_voywey_inventory', 'ReportsTwoController@getDTvoywey_inventory')
    ->name('getDTvoywey_inventory');

Route::post('voywey/download_dt_voywey_inventory', 'ReportsTwoController@downloadDTvoywey_inventory')
    ->name('downloadDTvoywey_inventory');

Route::post('voywey/get_detail_inventory', 'ReportsTwoController@getDTvoywey_inventory_detail')
    ->name('getDTvoywey_inventory_detail');
// END Reporte de Voywey de inventario

// Report sales jelou
Route::get('view/voywey/sales_jelou', 'ReportsTwoController@voyweySalesjelou');

Route::post('voywey/dt_voywey_SalesJeluo', 'ReportsTwoController@getDTvoywey_salesjelou')
    ->name('getDTvoywey_salesjelou');

Route::post('voywey/download_dt_voywey_SalesJeluo', 'ReportsTwoController@downloadDTvoywey_salesjelou')
    ->name('downloadDTvoywey_salesjelou');
// End Report sales jelou