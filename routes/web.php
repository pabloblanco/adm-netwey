<?php

/* RUTAS DE PRUEBAS */
Route::get('testuser', 'UserController@assign');
Route::get('test-billing', 'TestController@testbilling');
/* FIN DE RUTAS DE PRUEBAS */

Route::get('/', 'Auth\LoginController@islog')->name('root');

Route::get('login', 'Auth\LoginController@login')->name('login');

Route::post('login', 'Auth\LoginController@authenticate')->name('login');

Route::get('logout', 'Auth\LoginController@logout')->name('logout');

Route::post('report-noti', 'ReportsController@notificationReport')->name('reportNoti');

Route::post('report-check', 'ReportsController@checkReport')->name('checkReport');

/*rutas get para obtencion de vistas*/

Route::get('view/users', 'UserController@view');

Route::get('view/concent', 'ConcentratorController@view');

Route::get('view/servers/{concentrator}', 'ServersController@view');

Route::get('view/provider', 'ProductsProviderController@view');

Route::get('view/categories', 'ProductsCategoryController@view');

Route::get('view/warehouses', 'WarehouseController@view');

Route::get('view/products', 'ProductController@view');

Route::get('view/inventories', 'InventoryController@view');

Route::get('view/services', 'ServiceController@view');

Route::get('view/promo_list', 'PromoListController@view');

Route::get('view/pack', 'PackController@view');

Route::get('view/seller_inventories', 'SellerInventoryController@view');

Route::get('view/seller_inventories/{user_email}', 'SellerInventoryController@viewinv');

Route::get('view/blim/services', 'BlimServiceController@view');

Route::get('view/services_prom', 'ServicesPromController@view');

Route::get('view/status_inv', 'InventoryController@status_view');
Route::get('view/val_status_inv', 'InventoryController@val_status_view');

Route::get('view/pendding_orders', 'InventoryController@pendding_orders_view');

Route::get('view/merma_old_equipment', 'InventoryController@merma_old_equipment_view');

//Vendedores
Route::get('view/seller_reception', 'SellerReceptionController@view');

//Deprecate
Route::get('view/seller_deposits_old', 'SellerReceptionController@viewDeposit');

//conciliacion depositos ventas
Route::get('view/seller_deposits/{status?}', 'SellerReceptionController@loadDeposit');

//conciliacion depositos ventas en abono
Route::get('view/seller_deposits_inst', 'SellerReceptionController@loadDebtsInst');

//Listas de DNs
Route::get('view/seller_service', 'ChannelsController@viewSellerService');
Route::post('list_dn_service/get_lists', 'ChannelsController@listsDT')->name('get_lists');
Route::post('list_dn_service/create', 'ChannelsController@createList')->name('createList');
Route::post('list_dn_service/get_data_list', 'ChannelsController@getDatalist')->name('getDatalist');
Route::post('list_dn_service/get_dn', 'ChannelsController@getDNs')->name('getDNs');
Route::post('list_dn_service/save_edit_list', 'ChannelsController@saveEdit')->name('saveEdit');
Route::post('list_dn_service/delete_dn_list', 'ChannelsController@deleteDn')->name('deleteDn');
Route::post('list_dn_service/delete_list', 'ChannelsController@deleteList')->name('deleteList');

//Canales
Route::get('view/channels', 'ChannelsController@viewChannels');
Route::post('channels/get_channles', 'ChannelsController@channelsDT')->name('get_channles');
Route::post('channels/delete_channel', 'ChannelsController@deleteChannel')->name('deleteChannel');
Route::post('channels/create', 'ChannelsController@createChannel')->name('createChannel');
Route::post('channels/edit', 'ChannelsController@editChannel')->name('editChannel');

Route::get('view/seller_comission', 'SellerComissionController@view');

Route::get('view/movewh', 'InventoryController@movewhview');
#subview
Route::get('view/movewh/{whid}', 'InventoryController@viewdetail');
#endsubview
Route::get('view/client', 'ClientController@view');

#dasboard
/*Route::post('dashboard_grap_sales_h', 'DashboardController@dashboardGrapSalesH')
->name('dashboardGrapSalesH');
Route::post('dashboard_grap_sales_m', 'DashboardController@dashboardGrapSalesM')
->name('dashboardGrapSalesM');*/

Route::post('dashboard_grap_sales', 'DashboardController@dashboardGrapSales')
  ->name('dashboardGrapSales');
Route::post('dashboard_client', 'DashboardController@dashboardClient')
  ->name('dashboardClient');

/*Route::post('dashboard_grap_recharges_m', 'DashboardController@dashboardGrapRechargesM')
->name('dashboardGrapRechargesM');
Route::post('dashboard_grap_recharges_h', 'DashboardController@dashboardGrapRechargesH')
->name('dashboardGrapRechargesH');*/
/*Route::post('dashboard_client_h', 'DashboardController@dashboardClientH')
->name('dashboardClientH');
Route::post('dashboard_client_m', 'DashboardController@dashboardClientM')
->name('dashboardClientM');*/

Route::post('dashboard_concentrator', 'DashboardController@dashboardConcentrator')
  ->name('dashboardConcentrator');
Route::post('dashboard_grap', 'DashboardController@dashboardGrap2');

/*Deprecated*/
/*Route::post('dashboard_info', 'DashboardController@dashboardInfo')
->name('dashboard_info');*/
//Deprecated
//Route::get('dashboard', 'DashboardController@dw_report');
//Deprecated
#graphics
Route::get('dashboard/{type}/{interval}', 'DashboardController@graphic');

Route::get('view/organization', 'OrganizationController@view');

//brightstar
Route::get('view/brightstar/register-dn', 'brightstarController@register');
Route::post('view/brightstar/get-orders', 'brightstarController@getOrders')->name('brightstar.getOrders');
Route::post('view/brightstar/process-orders', 'brightstarController@processOrders')->name('brightstar.processOrders');
Route::post('brightstar/get-inventary', 'brightstarController@getInventary');

//Sim Swap
Route::get('view/sim_swap', 'ClientController@simSwapInit');
Route::post('view/sim_swap/step1', 'ClientController@simSwapStep1')->name('ClientController.simSwapStep1');
Route::post('view/sim_swap/step2', 'ClientController@simSwapStep2')->name('ClientController.simSwapStep2');
Route::post('view/sim_swap/verify_swap', 'ClientController@verifySwap')->name('ClientController.verifySwap');

//online sales (ventas online)
Route::get('view/report_os/view_sales', 'ReportOSController@viewSales');
/*Route::post('report_os/get_sales_for_report_os', 'ReportOSController@getSalesForReportOS')->name('getSalesForReportOS');*/
Route::post('report_os/get_sales_for_report_os', 'ReportOSController@getSalesForReportOSV2')->name('getSalesForReportOS');
Route::post('report_os/download_sales_for_report_os', 'ReportOSController@downloadSalesForReportOSV2')
  ->name('downloadSalesForReportOS');
/*Route::post('report_os/download_sales_for_report_os', 'ReportOSController@downloadSalesForReportOS')
->name('downloadSalesForReportOS');*/

//online sales (registros sin compras)
Route::get('view/report_os/unsold_records', 'ReportOSController@unsoldRecords');
Route::post('report_os/get_clients_unsold_records_for_report_os', 'ReportOSController@getClientsUnSoldRecords')->name('getClientsUnSoldRecords');
Route::post('report_os/download_clients_unsold_records_for_report_os', 'ReportOSController@downloadClientsUnSoldRecordsForReportOS')
  ->name('downloadClientsUnSoldRecordsForReportOS');

//online sales (leads Popup Promo EnvioCero)
Route::get('view/report_os/leads_promo_ec', 'ReportOSController@leadsPromoEC');
Route::post('report_os/get_leads_promoec_for_report_os', 'ReportOSController@getLeadsPromoECForReportOS')->name('getLeadsPromoECForReportOS');
Route::post('report_os/download_leads_promoec_for_report_os', 'ReportOSController@downloadLeadsPromoECForReportOS')
  ->name('downloadLeadsPromoECForReportOS');

//online sales (Referencias Pendientes de Pago)
Route::get('view/report_os/pendding_payment_ref', 'ReportOSController@penddingPaymentRef');
Route::post('report_os/get_pendding_payment_ref_for_report_os', 'ReportOSController@getPenddingPaymentRefForReportOS')->name('getPenddingPaymentRefForReportOS');
Route::post('report_os/download_pendding_payment_ref_for_report_os', 'ReportOSController@downloadPenddingPaymentRefForReportOS')
  ->name('downloadPenddingPaymentRefForReportOS');

//Reporte de convertia
Route::get('view/report_os/convertia', 'ReportOSController@convertia');
Route::post('report_os/get_dt_convertia_sales', 'ReportOSController@getDTconvertiaSales')
  ->name('getDTconvertiaSales');
Route::post('report_os/download_dt_convertia', 'ReportOSController@downloadDTconvertiaSales')
  ->name('downloadDTconvertiaSales');

//Reporte de inconcer
Route::get('view/report_os/inconcert', 'ReportOSController@inconcert');
Route::post('report_os/get_dt_inconcert_sales', 'ReportOSController@getDTInconcertSales')
  ->name('getDTInconcertSales');
Route::post('report_os/download_dt_inconcert', 'ReportOSController@downloadDTInconcertSales')
  ->name('downloadDTInconcertSales');

//online sales (estadisticas de consultas a cobertura)
Route::get('view/report_os/coverage_stats', 'ReportOSController@coverageStats');
Route::post('report_os/get_coverage_stats', 'ReportOSController@getCoverageStats')->name('getCoverageStats');
Route::post('report_os/get_coverage_stats_charts', 'ReportOSController@getCoverageStatsCharts')->name('getCoverageStatsCharts');
Route::post('report_os/get_not_coverage_stats_charts', 'ReportOSController@getNotCoverageStatsCharts')->name('getNotCoverageStatsCharts');
Route::post('report_os/download_coverage_stats', 'ReportOSController@downloadCoverageStats')
  ->name('downloadCoverageStats');

//Reporte de ventas online por api
Route::get('view/report_os/sales_api', 'ReportOSTwoController@salesApi');
Route::post('report_os/get_sales_api', 'ReportOSTwoController@getSalesApi');
Route::post('report_os/download_sales_api', 'ReportOSTwoController@downloadSalesAPIReport');

//Reporte payjoy
Route::get('view/reports/payjoy', 'ReportsTwoController@payjoy');

//Reporte coordenadas
Route::get('view/reports/ch_coord', 'ReportsTwoController@coordinates');

//Facturacion Conceptos
Route::get('view/billing/concepts', 'BillingConceptController@view');
//Facturacion Masiva Oxxo
Route::get('view/billing/masive', 'BillingMasiveController@view');

//Coppel altas fallidas
Route::get('view/coppel/ups_fails', 'CoppelController@upsFailsView');

//Vista solicitd de bajas
Route::get('view/leave_request', 'SellerInventoryController@leave_request_view');

//Vista de listado solicitd de bajas en proceso
Route::get('view/leave_request_process', 'SellerInventoryController@listRequestLeaveView');

//Vista para cargar csv y actualizar id de productos de forma masiva
Route::get('view/update_ids_products', 'InventoryController@updateIdsProductsView');

//Vista para cargar la vista de reporte de facturacion masiva
Route::get('view/billing_masive', 'BillingMasiveController@report_masive_billing_view');

//Ruta para probar cron, borrarla
/*Route::get('/test-cron', function () {
Artisan::call('command:kpi');
});

Route::get('/test-client', function () {
Artisan::call('command:activeClients');
});*/

//Rutas API de los modulos
/*rutas CRUD usuario*/
require_once 'api/users.php';
/*rutas CRUD concentradores*/
require_once 'api/concentrators.php';
/*rutas CRUD IP de servidores*/
require_once 'api/servers.php';
/*rutas CRUD proveedores*/
require_once 'api/providers.php';
/*rutas CRUD categorías de productos*/
require_once 'api/categories.php';
/*rutas CRUD almacenes*/
require_once 'api/warehouses.php';
/*rutas CRUD productos*/
require_once 'api/products.php';
/*rutas CRUD inventario*/
require_once 'api/inventory.php';
/*rutas CRUD Paquetes*/
require_once 'api/pack.php';
/*rutas CRUD servicios*/
require_once 'api/services.php';
/*rutas para gestion de vendedores*/
require_once 'api/seller.php';
/*rutas Reportes*/
require_once 'api/reports.php';
/*rutas Exportación de reportes */
require_once 'api/exports.php';
/*rutas clientes */
require_once 'api/client.php';
/*rutas organizacion */
require_once 'api/organization.php';
/*Rutas de reportes BI*/
require_once 'api/report_bi.php';
/*Rutas de financiamiento*/
require_once 'api/financing.php';
/*Rutas de pago en abonos*/
require_once 'api/installments.php';
/*rutas CRUD servicios blim*/
require_once 'api/blimservices.php';
/*rutas CRUD servicios promocionales*/
require_once 'api/servicesprom.php';
/*rutas CRUD de Voywey*/
require_once 'api/voywey.php';
/*rutas CRUD conceptos de facturacion*/
require_once 'api/billingconcepts.php';
/*rutas Coppel*/
require_once 'api/coppel.php';
/*rutas Portabilidad*/
require_once 'api/portabilidad.php';
/*rutas Bajas de usuarios*/
require_once 'api/low.php';
/*rutas Facturacion Masiva*/
require_once 'api/billingmasive.php';
/*rutas Lista de Descuentos*/
require_once 'api/promo_list.php';
/*rutas Gestion Web*/
require_once 'api/web_management.php';
/*rutas Gestion de Fibra*/
require_once 'api/fiber.php';
/*Rutas politicas predeterminadas*/
require_once 'api/politics.php';
