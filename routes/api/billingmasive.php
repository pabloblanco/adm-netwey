<?php

Route::post('api/billingmasive/file-details/store-csv', 'BillingMasiveController@import_store_csv');

Route::post('api/billingmasive/file-details/list-dt', 'BillingMasiveController@listFileDetailsDT');

Route::post('api/billingmasive/file-details/process', 'BillingMasiveController@process_file');

/*reporte de facturas masivas*/
Route::post('view/reports/billing_masive_detail_report', 'BillingMasiveController@billing_masive_detail_report')
  ->name('billing_masive_detail_report');