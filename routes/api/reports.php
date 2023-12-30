<?php
Route::get('view/reports/seller_status', 'ReportsController@status_seller_view');
Route::get('view/reports/seller/status', 'ReportsController@seller_detail_view');

Route::get('view/reports/ur/{view}', 'ReportsController@viewUpsOrRecharges');
Route::post('view/reports/ur/users/filter', 'ReportsController@getFilterUsersUR');
Route::get('view/reports/ur/detail/{view}', 'ReportsController@viewUpsOrRechargesDetail');
Route::post('view/reports/ur/detail-dt/ups', 'ReportsController@detailDtUP');
Route::post('view/reports/ur/detail-dt/recharge', 'ReportsController@detailDtRecharge');
Route::post('view/reports/ur/download/detail', 'ReportsController@downloadCSVReportUR');

Route::get('view/reports/sales', 'ReportsController@viewSales');
Route::get('view/reports/sales/detail/', 'ReportsController@viewSalesDetail');
Route::post('view/reports/sales/dt', 'ReportsController@getSalesDT');
Route::post('view/reports/sales/download/detail', 'ReportsController@downloadXLSReportSales');

Route::get('view/reports/concentrators', 'ReportsController@viewConcentrators');
Route::get('view/reports/concentrators/detail/', 'ReportsController@viewConcentratorsDetail');
Route::post('view/reports/concentrators/detail/detail-dt', 'ReportsController@detailDtConc');
Route::post('view/reports/concentrators/detail/download', 'ReportsController@downloadXLSReportConc');

Route::get('view/reports/warehouses', 'ReportsController@viewWarehouses');
Route::get('view/reports/warehouses/detail/', 'ReportsController@viewWarehousesDetail');
Route::get('view/reports/get_warehouses/{warehouse}', 'ReportsController@getWarehouses');

Route::get('view/reports/seller/inventories', 'ReportsController@viewSellerInventory');
Route::get('view/reports/seller/inventories/detail/', 'ReportsController@viewSellerInventoryDetail');
Route::post('view/reports/filter/sell-coo', 'ReportsController@filterByTypeUser');
Route::post('view/reports/filter/user', 'ReportsController@filterUserByType');
Route::post('view/reports/seller/inventories/download-report', 'ReportsController@downloadInvSCReport');

Route::get('view/reports/clients', 'ReportsController@viewClients');
Route::post('view/reports/clients/detail', 'ReportsController@viewClientsDetail');
Route::post('view/reports/clients/get-dns', 'ReportsController@getDNForReport');
Route::post('view/reports/clients/dt', 'ReportsController@dtClient');
Route::post('view/reports/clients/download', 'ReportsController@downloadXLSClient');

Route::get('view/reports/prospects', 'ReportsController@viewProspects');
Route::get('view/reports/prospects/detail/', 'ReportsController@viewProspectsDetail');
Route::post('reports/prospects/get-sellers', 'ReportsController@getSellerProspect');
Route::post('reports/prospects/dt', 'ReportsController@dtProspect');
Route::post('reports/prospects/download', 'ReportsController@downloadXLSProspect');

Route::get('view/reports/users', 'ReportsController@viewUsers');
Route::post('view/reports/users/filter', 'ReportsController@getFilterUsers')->name('getUserByType');
Route::post('view/reports/users/detail', 'ReportsController@viewUsersDetail');
Route::get('view/reports/users/status/{email}', 'ReportsController@viewUsersDetailReport');

Route::get('view/reports/balance', 'ReportsController@viewBalance');
Route::get('view/reports/balance/detail/{type}', 'ReportsController@viewBalanceDetail');

Route::get('view/reports/selldeposit', 'ReportsController@viewSellDeposit');
Route::get('view/reports/selldeposit/detail', 'ReportsController@viewSellDepositDetail');

Route::get('api/datatable/seller_inventories/{user}/{product}/{org?}', 'ReportsController@sellerinvdt');

//Reporte Estructura Organizativa
Route::get('view/organization_users', 'ReportsController@viewOrgEstruct');
Route::post('view/organization_users/detail', 'ReportsController@viewOrganizationEstructDetail');
Route::post('view/organization_users/download', 'ReportsController@organizationEstructDownload');

//Buscar bodega del DN
Route::get('view/reports/wh_dn', 'ReportsController@warehouseDn');
Route::get('view/reports/wh_dn/find/{search?}', 'ReportsController@warehouseDnSearch');
Route::get('view/reports/wh_dn_detail/{dn}', 'ReportsController@warehouseDnSearchDetail');

//Reporte de clientes suspendidos por movilidad
Route::get('view/reports/mobility', 'ReportsController@mobilityReport');
Route::post('view/reports/mobility/dt', 'ReportsController@mobilityReportDT');
Route::post('view/reports/mobility/dt_download', 'ReportsController@mobilityReportDTDownload');

//Url para descargar reportes almacenados en el directorio /public/reports
Route::get('view/reports/downloads/{delete}/{id?}', 'ReportsController@downloadReports');
Route::get('view/reports/downloads-file/{delete}/{id}', 'ReportsController@downloadReportsEmail')->name('downloadReport');

//Reporte de sim swap realizados
Route::get('view/reports/sim_swap', 'ReportsController@simswap');
Route::post('reports/sim_swap/get-swap', 'ReportsController@getSwapReport')->name('getSwap');
Route::post('reports/sim_swap/download-swap', 'ReportsController@downloadSwapReport')->name('downloadSwapReport');

//Reporte de inv. Brightstar
Route::get('view/reports/inv_brightstar', 'ReportsController@invBrightstar');

//Reporte de articulos vendidos pero no activos
Route::get('view/reports/sale_artic', 'ReportsController@saleArtic');
Route::post('view/reports/dt_sale_artic', 'ReportsController@saleArticDT')->name('saleArticDT');
Route::post('view/reports/dw_sale_artic', 'ReportsController@saleArticDW')->name('saleArticDW');
Route::post('view/reports/sale_artic/filters', 'ReportsController@saleArticF')->name('saleArticF');

//Reporte de articulos vendidos y activos
Route::get('view/reports/sale_artic_active', 'ReportsController@saleArticActive');
Route::post('view/reports/dt_sale_artic_active', 'ReportsController@saleArticActiveDT')->name('saleArticActiveDT');
Route::post('view/reports/dw_sale_artic_active', 'ReportsController@saleArticActiveDW')->name('saleArticActiveDW');
Route::post('view/reports/dw_sale_artic', 'ReportsController@saleArticDW')->name('saleArticDW');

//Reporte de clientes financiados
Route::get('view/reports/financing', 'ReportsController@financing');
Route::post('view/reports/dt_financing', 'ReportsController@financingDT')->name('financingDT');
Route::post('view/reports/dw_financing', 'ReportsController@financingDW')->name('financingDW');

//Reporte de recepcion de dinero
Route::get('view/reports/conciliations', 'ReportsController@conciliations');
Route::post('view/reports/get_report_conc', 'ReportsController@getReportConc')->name('getReportConc');
Route::post('view/reports/detail_deposits', 'ReportsController@getDetailDeposits')->name('getDetailDeposits');
Route::post('view/reports/detail_conc', 'ReportsController@getDetailConc')->name('getDetailConc');

//Reporte de conciliaciones
Route::get('view/reports/conciliations_rep', 'ReportsController@conciliationsRep');
Route::post('view/reports/get_ope_efec', 'ReportsController@getOpeEfec')->name('getOpeEfec');
Route::post('view/reports/get_rep_conc', 'ReportsController@getRepConc')->name('getRepConc');
Route::post('view/reports/download_rep_conc', 'ReportsController@downloadRepConc')->name('downloadRepConc');

//Reporte RRE
Route::get('view/reports/rre', 'ReportsController@rre');
Route::post('view/reports/rep_rre', 'ReportsController@repRre')->name('repRre');
Route::post('view/reports/get_sales_detail', 'ReportsController@getSalesDetail')
  ->name('getSalesDetail');

//Reporte de ventas en abono
Route::get('view/reports/installment_sales', 'ReportsController@installmentSales');
Route::post('view/reports/get_filter_users_sellers', 'ReportsController@getFilterUsersSellers')
  ->name('getFilterUsersSellers');
Route::post('view/reports/get_sales_inst_dt', 'ReportsController@getSalesInstDT')
  ->name('getSalesInstDT');
Route::post('view/reports/download_rep_sales_inst', 'ReportsController@downloadRepSalesInst')->name('downloadRepSalesInst');
Route::post('view/reports/get_quote_detail', 'ReportsController@getQuoteDetail')->name('getQuoteDetail');

//Reporte RRE abono
Route::get('view/reports/installment_rre', 'ReportsController@installmentRRE');
Route::post('view/reports/get_RRE_inst_DT', 'ReportsController@getRREInstDT')
  ->name('getRREInstDT');
Route::post('view/reports/download_rep_RRE_inst', 'ReportsController@downloadRepRREInst')
  ->name('downloadRepRREInst');

//Reporte modems en abono
Route::get('view/reports/modems-installments', 'ReportsController@modemsInstallments');
Route::post('view/reports/download-modems-installments', 'ReportsController@downloadModInsReport')
  ->name('downloadModInsReport');

Route::post('view/reports/get_sales_detail', 'ReportsController@getSalesDetail')->name('getSalesDetail');

//Reporte de consumo
Route::get('view/reports/consumption', 'ReportsTwoController@consumption');

Route::post('reports/get_dt_consumption', 'ReportsTwoController@getDTConsumption')
  ->name('getDTConsumption');

Route::post('reports/download_dt_consumption', 'ReportsTwoController@downloadDTConsumption')
  ->name('downloadDTConsumption');

//Reporte de consumo CDR
Route::get('view/reports/consumption_cdr', 'ReportsTwoController@consumptionCDR');

Route::post('reports/get_dt_consumption_cdr', 'ReportsTwoController@getDTConsumptionCDR')
  ->name('getDTConsumptionCDR');

Route::post('reports/download_dt_consumption_cdr', 'ReportsTwoController@downloadDTConsumptionCDR')
  ->name('downloadDTConsumptionCDR');

//Reporte de Periodo de gracia
Route::get('view/reports/grace_period', 'ReportsTwoController@gracePeriod');

Route::post('reports/get_dt_grace_period', 'ReportsTwoController@getDTGracePeriod')
  ->name('getDTGracePeriod');

Route::post('reports/download_dt_grace_period', 'ReportsTwoController@downloadDTGracePeriod')
  ->name('downloadDTGracePeriod');

// Reporte de Servicios de Retencion
Route::get('view/reports/services_retention', 'ReportsTwoController@servicesRetention');

Route::post('reports/get_dt_services_retention', 'ReportsTwoController@getDTRetentionPeriod')
  ->name('getDTRetentionPeriod');

Route::post('reports/download_dt_services_retention', 'ReportsTwoController@downloadDTServicesRetention')
  ->name('downloadDTServicesRetention');

//Reporte payjoy
Route::post('reports/payjoy/dt', 'ReportsTwoController@getPayjoyDt')
  ->name('getPayjoyDt');
Route::post('reports/payjoy/download-report', 'ReportsTwoController@downloadPayjoyReport')
  ->name('downloadPayjoyReport');

//Reporte cambio de coordenadas
Route::post('reports/coordinates/dt', 'ReportsTwoController@getCoordinatesDt')
  ->name('getCoordinatesDt');
Route::post('reports/coordinates/download-report', 'ReportsTwoController@downloadCoordinatesReport')
  ->name('downloadCoordinatesReport');

// Reporte de cambios de datos del cliente en call center
Route::get('view/reports/clients_update', 'ReportsTwoController@clientsupdate');

Route::post('reports/get_dt_clients_update', 'ReportsTwoController@getDTClientsUpdateCall')
  ->name('getDTClientsUpdateCall');

Route::post('reports/download_dt_clients_update', 'ReportsTwoController@downloadDTClientsUpdateCall')
  ->name('downloadDTClientsUpdateCall');

//Usuarios bloqueados
Route::get('view/reports/locked_users', 'ReportsThreeController@lockedUsers');
Route::post('reports/locked_users/dt', 'ReportsThreeController@getUsersLDt')
  ->name('getUsersLDt');
Route::post('reports/locked_users/download-report', 'ReportsThreeController@downloadgetUsersLDt')
  ->name('downloadgetUsersLDt');

//reporte de migracion
Route::get('view/reports/migration', 'ReportsTwoController@migration');
Route::post('reports/get_dt_migration', 'ReportsTwoController@getDTMigration')
  ->name('getDTMigration');
Route::post('reports/download_dt_migration', 'ReportsTwoController@downloadDTMigration')
  ->name('downloadDTMigration');

//reporte de super sim
Route::get('view/reports/super_sim', 'ReportsTwoController@super_sim');
Route::post('reports/get_dt_super_sim', 'ReportsTwoController@getDTsuper_sim')
  ->name('getDTsuper_sim');
Route::post('reports/download_dt_super_sim', 'ReportsTwoController@downloadDTsuper_sim')
  ->name('downloadDTsuper_sim');

//reporte de altas con consumos
Route::get('view/reports/ups_with_consumptions', 'ReportsTwoController@upsWithConsumptions');
Route::post('reports/get_dt_ups_with_consumptions', 'ReportsTwoController@getDTUpsWithConsumptions')
  ->name('getDTUpsWithConsumptions');
Route::post('reports/download_dt_ups_with_consumptions', 'ReportsTwoController@downloadDTUpsWithConsumptions')
  ->name('downloadDTUpsWithConsumptions');

//Reporte de historico suspensiones
Route::get('view/reports/suspended_history', 'ReportsTwoController@suspendedHistory');

Route::post('reports/get_dt_suspended_history', 'ReportsTwoController@getDTSuspendedHistory')
  ->name('getDTSuspendedHistory');

Route::post('reports/download_dt_suspended_history', 'ReportsTwoController@downloadDTSuspendedHistory')
  ->name('downloadDTSuspendedHistory');

//Reporte de ventas Coppel
Route::get('view/reports/coppel_sales', 'ReportsTwoController@coppelSales');

Route::post('reports/get_dt_coppel_sales', 'ReportsTwoController@getDTCoppelSales')
  ->name('getDTCoppelSales');

Route::post('reports/download_dt_coppel_sales', 'ReportsTwoController@downloadDTCoppelSales')
  ->name('downloadDTCoppelSales');

//Reporte de ventas realizadas por JELOU
Route::get('view/reports/jelou_sales', 'ReportsTwoController@getFromSalesJelou');

Route::post('reports/get_dt_sales_jelou', 'ReportsTwoController@getDTJelouSales')
  ->name('getDTJelouSales');

Route::post('reports/download_dt_sales_jelou', 'ReportsTwoController@downloadDTJelouSales')
  ->name('downloadDTJelouSales');
//

//Reporte de pedido solicitado
Route::get('view/reports/order_request', 'ReportsTwoController@getOrderRequest');

Route::post('reports/get_dt_order_request', 'ReportsTwoController@getDTOrderRequest')
  ->name('getDTOrderRequest');

Route::post('reports/download_dt_order_request', 'ReportsTwoController@downloadDTOrderRequest')
  ->name('downloadDTOrderRequest');
//

//Reporte de movimientos de inventarios
Route::get('view/reports/inventory_track', 'ReportsThreeController@getInventoryTracks');
Route::post('view/reports/inventory_track/get-dns', 'ReportsThreeController@getDNInInventory');

Route::post('reports/get_dt_inventory_track', 'ReportsThreeController@getDTInventoryTracks')
  ->name('getDTInventoryTracks');
Route::post('reports/get_dt_inventory_track_details', 'ReportsThreeController@getDtInventoryTracksDetails')
  ->name('getDtInventoryTracksDetails');
Route::post('reports/download_dt_inventory_tracks', 'ReportsThreeController@downloadDTInventoryTracks')
  ->name('downloadDTInventoryTracks');
//

//Reporte de inventario en bodegas merma
Route::get('view/reports/merma_inventory', 'ReportsThreeController@mermaInventory');

Route::post('reports/get_dt_inventory_merma', 'ReportsThreeController@getInventoryMermaDt')
  ->name('getInventoryMermaDt');

Route::post('reports/download_dt_inventory_merma', 'ReportsThreeController@downloadgetInventoryMermaDt')
  ->name('downloadgetInventoryMermaDt');

//Reporte de instalaciones de fibra
Route::get('view/reports/fiber_installations', 'ReportsThreeController@getFiberInstallations');
Route::post('view/reports/fiber_installations/get-dns', 'ReportsThreeController@getDNFiberInstallations');

Route::post('reports/fiber_installations/get-dt', 'ReportsThreeController@getDTFiberInstallations')
  ->name('getDTFiberInstallations');
Route::post('reports/fiber_installations/get-dt-details', 'ReportsThreeController@getDTFiberInstallationsDetails')
  ->name('getDTFiberInstallationsDetails');
Route::post('reports/fiber_installations/download_dt', 'ReportsThreeController@downloadDTFiberInstallations')
  ->name('downloadDTFiberInstallations');

//Reporte de estatus de inventario
Route::post('view/reports/download-report-status-inv', 'ReportsController@downloadCSVReportStatusInv')
  ->name('downloadCSVReportStatusInv');
Route::post('view/reports/download-report-merma-old-equipment', 'ReportsController@downloadReportMermaOldEquipment')
  ->name('downloadReportMermaOldEquipment');

//KPI Equipos Perdidos

Route::post('reports/kpi-dismissal/get-dt', 'ReportsThreeController@getDTKPIDismissal')
  ->name('getDTKPIDismissal');
Route::post('reports/kpi-dismissal/download-dt', 'ReportsThreeController@downloadDTKPIDismissal')
  ->name('downloadDTKPIDismissal');

//Reporte de Paguitos
Route::get('view/reports/paguitos', 'ReportsThreeController@paguitos');
Route::post('reports/paguitos/paguitosDt', 'ReportsThreeController@getPaguitosDt')->name('getPaguitosDt');
Route::post('reports/paguitos/download-report', 'ReportsThreeController@downloadPaguitosReport')
  ->name('downloadPaguitosReport');
//End Reporte de Paguitos

/*Reporte facturas masivas */
Route::post('reports/downloadBillingsMasiveReport', 'ReportsController@downloadBillingsMasiveReport')
  ->name('downloadBillingsMasiveReport');


//Reporte de instalaciones, citas reagendamientos de fibra
Route::get('view/reports/fiber_by_status', 'ReportsThreeController@getFiberInstallationByStatus');
Route::post('api/reports/report_fiber_installations/get-by-status', 'ReportsThreeController@getInstallationReportByStatus');
Route::post('api/reports/report_fiber_installations/download', 'ReportsThreeController@downloadReportInstallationsByStatus');


//Reporte telmovPay
Route::get('view/reports/telmov-pay', 'ReportsThreeController@telmovPay');
Route::post('reports/telmov-pay/get-telmov-pay-dt', 'ReportsTwoController@getTelmovPayDt')->name('getTelmovPayDt');
Route::post('reports/telmov-pay/download-telmov-pay-report', 'ReportsTwoController@downloadTelmovPayReport')->name('downloadTelmovPayReport');
