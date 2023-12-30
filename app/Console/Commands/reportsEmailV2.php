<?php

namespace App\Console\Commands;

use App\AssignedSales;
use App\BillingMasive;
use App\Client;
use App\ClientNetwey;
use App\ConsumoAcumuladoDetails;
use App\CoordinateChanges;
use App\Coppel;
use App\DeferredPayment;
use App\FiberInstallation;
use App\Helpers\APIvoyWey;
use App\Helpers\CommonHelpers;
use App\Helpers\ReportsHelpers;
use App\HistoryDC2;
use App\Inventory;
use App\Inv_reciclers;
use App\KPISDismissal;
use App\LowRequest;
use App\Migrations;
use App\Paguitos;
use App\Payjoy;
use App\Portability;
use App\Portability_exportacion;
use App\Reports;
use App\Sale;
use App\SaleInstallment;
use App\SellerInventory;
use App\SellerInventoryTrack;
use App\StockProvaDetail;
use App\SuspendedByAdmin;
use App\TempCar;
use App\UserLocked;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class reportsEmailV2 extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'command:sendReportsV2';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Envía reportes solicitados al correo.';

  /**
   * Create a new command instance.
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
  }

  /**
   * Execute the console command.
   *
   * @return mixed
   */
  public function handle()
  {
    //Buscando los reportes pendientes por generar
    $report = Reports::where('status', 'C')
      ->orderBy('date_reg', 'ASC')
      ->first();

    if (!empty($report)) {
      try {
        $report->status = 'P';
        $report->save();

        //Cargando filtros
        $filters = json_decode($report->filters, true);
        $filters['user'] = !empty($report->user) ? $report->user : null;
        $filters['user_profile'] = !empty($report->user_profile) ? $report->user_profile : null;

        //NOTA: Si se modifican los limites se debe revisar que no hayan reportes generandoce
        //Limite de páginas por archivo
        $limitP = 5;
        //limite de filas por consulta a la bd y lineas por pagina en archivo
        $limitQ = 30000;
        //Array que va a almacenar datos del reporte
        $reportxls = [];

        //Bandera para saber si se debe crear mas partes del reporte
        $explode = false;
        $currentPage = 0;
        $isPaginte = false;
        $API = false;

        //Reporte de ventas
        if ($report->name_report == 'reporte_ventas') {
          $reportxls[] = [
            'Transacción única',
            'Fecha de la Transacción',
            'Concentrador',
            'Vendedor',
            'Instalador',
            'Tipo de venta',
            'Plan',
            'Producto',
            'Servicio',
            'Zona de Cobertura',
            'Nro orden altan',
            'Código altan',
            'Monto pagado',
            'Cliente',
            'Telf Netwey',
            'Tipo linea',
            'Telf de contacto',
            'Red Social',
            'Origen',
            'Referido de Telefonia',
            'Referido por'];

          //Opteniendo query
          $query = Sale::getSaleReportAll($filters)->orderBy('date_reg', 'DESC');
          //Función que arma el array del reportes
          $function = 'getArraySalesReport';
          //Directorio de S3
          $directory = 'sales';
        }

        //Reporte de recargas
        if ($report->name_report == 'reporte_recargas') {
          //Header
          $reportxls[] = [
            'Transacción única',
            'Folio OXXO',
            'Fecha de la Transacción',
            'Concentrador',
            'Vendedor',
            'Producto',
            'Telf Netwey',
            'DN Migrado',
            'Tipo linea',
            'IMEI',
            'ICCID',
            'Servicio',
            'Cliente',
            'Telf de contacto',
            'Telf de contacto 2',
            'Zona de Cobertura',
            'Monto pagado',
            'Conciliado',
            'Latitud',
            'Longitud',
            'Factura',
            'Instalador',
            'Email Instalador'];

          //Opteniendo query
          $query = Sale::getSaleReportRecharge($filters)->orderBy('date_reg', 'DESC');
          //Función que arma el array del reportes
          $function = 'getArrayRechargeReport';
          //Directorio de S3
          $directory = 'sales';
        }

        //Reporte de altas
        if ($report->name_report == 'reporte_altas') {
          //Header
          $reportxls[] = [
            'Transacción unica',
            'Fecha de la Transaccion',
            'Organizacion',
            'Vendedor',
            'Coordinador',
            'Plan',
            'Producto',
            'Telf Netwey',
            'DN Migrado',
            'Tipo linea',
            'IMEI',
            'ICCID',
            'Servicio',
            'Cliente',
            'Telf de contacto',
            'Telf de contacto 2',
            'Zona de Cobertura',
            'Monto pagado',
            'Tipo',
            'Conciliado',
            'Latitud',
            'Longitud',
            'Factura',
            'Red Social',
            'Origen',
            'Email Vendedor',
            'Email Coordinador',
            'Coordinador Bloqueado',
            'Instalador',
            'Email Instalador',
            'Financiamiento'];

          //Opteniendo query
          $query = Sale::getSaleReportUps($filters)->orderBy('date_reg', 'DESC');
          //Función que arma el array del reportes
          $function = 'getArrayResgisterReport';
          $directory = 'sales';
        }

        //Reporte de concentradores
        if ($report->name_report == "reporte_concentradores") {
          //Header
          $reportxls[] = [
            'Tipo de venta',
            'Plan',
            'Producto',
            'Servicio',
            'Transacción unica',
            'Concentrador',
            'Fecha',
            'MSISDN',
            'Tipo linea',
            'Monto pagado',
            'Conciliada'];

          //Opteniendo query
          $query = Sale::getSaleReportConcentrator($filters);
          //Función que arma el array del reportes
          $function = 'getArrayConcentratorReport';
          $directory = 'sales';
        }

        //Reporte de clientes
        if ($report->name_report == "reporte_clientes") {
          //Header
          $reportxls[] = [
            'Fecha de registro (Como cliente)',
            'Fecha de registro (Como prospecto)',
            'Nombre',
            'Email',
            'DN Netwey',
            'Tipo linea',
            'Teléfono',
            'Dirección',
            'Servicio adquirido',
            'Velocidad actual',
            'Financiamiento'];

          //Opteniendo query
          $msisdns = !empty($filters['msisdn_select']) ? explode(",", $filters['msisdn_select']) : null;
          $query = ClientNetwey::getReport(
            !empty($filters['service']) ? [$filters['service']] : [],
            ['A', 'S'],
            !empty($filters['date_ini']) ? $filters['date_ini'] : null,
            !empty($filters['date_end']) ? $filters['date_end'] : null,
            $msisdns,
            !empty($filters['type_line']) ? $filters['type_line'] : null
          );

          //Función que arma el array del reportes
          $function = 'getArrayClientsReport';
          $directory = 'sales';
        }

        //Reporte de prospectos
        if ($report->name_report == "reporte_prospectos") {
          //Header
          $reportxls[] = [
            'Fecha de registro',
            'Nombre',
            'Email',
            'Teléfono',
            'Dirección',
            'Nota',
            'Próximo contacto',
            'Persona que le registro',
            'Coordinador',
            'Organización',
            'Campaña'];

          //Opteniendo query
          $query = Client::getReport(
            !empty($filters['seller']) ? $filters['seller'] : [],
            $filters['date_ini'],
            $filters['date_end'],
            $filters['org'],
            true
          );

          //Función que arma el array del reportes
          $function = 'getArrayProspectReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_articulos_no_activos') {
          //Header
          $reportxls[] = [
            'ID',
            'Producto',
            'MSISDN',
            'IMEI',
            'Vendedor',
            'Fecha Venta'];

          //Opteniendo query
          $query = Sale::getSalesNotActive();

          //Función que arma el array del reportes
          $function = 'getArrayArticNotActiveReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_articulos_vendidos_no_activos') {
          //Header
          $reportxls[] = [
            'Transacción unica',
            'MSISDN',
            'Artículo',
            'Servicio',
            'Vendedor',
            'Coordinador',
            'Organización',
            'Fecha de venta'];

          //Opteniendo query
          $query = Sale::getSalesNotActiveReport($filters);

          //Función que arma el array del reportes
          $function = 'getArraySaleArticNotActiveReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_articulos_activos') {
          //Header
          $reportxls[] = [
            'Transacción unica',
            'Cliente',
            'Teléfono contacto',
            'Teléfono contacto 2',
            'Email',
            'MSISDN',
            'Artículo',
            'Servicio',
            'Vendedor',
            'Coordinador',
            'Organización',
            'Fecha de venta',
            'Fecha de activación'];

          //Opteniendo query
          $query = Sale::getSalesActiveReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayArticActiveReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_clientes_financiados') {
          //Header
          $reportxls[] = [
            'msisdn',
            'Fecha Alta',
            'Monto financiado',
            'Monto total deuda',
            '# Recargas',
            'Pago a la fecha',
            'Deuda remanente'];

          //Opteniendo query
          $query = ClientNetwey::getFinancingReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayFinancingClientReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_conciliaciones') {
          //Header
          $reportxls[] = [
            'Id depósito',
            'Monto',
            'banco',
            'Ope. Efectivo',
            'Coordinador',
            'Supervisor',
            'Cod. Depósito',
            'Fecha',
            'Motivo'];

          //Opteniendo query
          $query = AssignedSales::getReportConciliations($filters);

          //Función que arma el array del reportes
          $function = 'getArrayConcilationsReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_ventas_convertia' || $report->name_report == 'reporte_ventas_inconcert') {
          //Header
          $reportxls[] = [
            'Transaccion',
            'Nombre',
            'Telefono',
            'Correo',
            'Requiere Factura',
            'RFC/INE',
            'DN',
            'Pack',
            'Fecha compra',
            'Orden Netwey',
            'Orden envio',
            'Estatus envio',
            'PDF 99min',
            'Estatus DN',
            'Monto envio',
            'Monto pack'];

          //Opteniendo query
          $query = TempCar::getSalesReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayOnlineAPIReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_consumo') {
          //Header
          $reportxls[] = [
            'msisdn',
            'Consumo (GB)',
            'Servicio',
            'ID oferta',
            'Nombre oferta',
            'Fecha inicio oferta',
            'Fecha fin oferta',
            'Dias de consumo',
            'Tipo servicio'];

          //Opteniendo query
          $query = Sale::getConsuption($filters);

          //Función que arma el array del reportes
          $function = 'getArrayConsumoReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_venta_abono') {
          //Header
          $reportxls[] = [
            'Transacción única',
            'Organización',
            'Vendedor',
            'Coordinador',
            'Pack',
            'Producto',
            'DN Netwey',
            'Tipo linea',
            'Imei',
            'Plan',
            'Cliente',
            'Telf contacto',
            'Fecha de venta',
            'Fecha vencimiento prox. cuota',
            'Estatus',
            'Cutoa',
            'Monto'];

          //Opteniendo query
          $query = SaleInstallment::getSalesReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayInstSalesReport';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_consumo_cdr') {
          //Header
          $reportxls[] = [
            'msisdn',
            'Consumo (GB)',
            'Throttling (GB)',
            'Servicio',
            'ID oferta',
            'Fecha inicio oferta',
            'Fecha fin oferta',
            'Dias de consumo',
            'Tipo servicio'];

          //Opteniendo total de filas
          if (empty($filters['current_pg'])) {
            //$totalRecrods = CDRDataConsDetails::getTotalconsuption($filters);
            $totalRecrods = ConsumoAcumuladoDetails::getTotalconsuption($filters);

            if ($totalRecrods > ($limitP * $limitQ)) {
              $filters['length'] = ($limitP * $limitQ);
              $filters['start'] = 0;
            }
          } else {
            $filters['length'] = ($limitP * $limitQ);
            $filters['start'] = $filters['current_pg'] * $filters['length'];
            $totalRecrods = $filters['total_lines'];
          }

          //Opteniendo query
          //$query = CDRDataConsDetails::getConsuptionV2($filters);
          $query = ConsumoAcumuladoDetails::getConsuptionV2($filters);

          //Función que arma el array del reportes
          $function = 'getArrayConsumoCDRReport';
          $directory = 'sales';
          $typePag = 'in-model';
        }

        if ($report->name_report == 'recharge_base') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'Nombre',
            'Teléfono',
            'Teléfono Oficina',
            'Email',
            'I.N.E',
            'Fecha Registro'];

          //Opteniendo query
          $query = Sale::reportRechargeBase($filters['month'] . '/' . $filters['year'], $filters['typeC']);

          //Función que arma el array del reportes
          $function = 'getArrayRechargeBase';
          $directory = 'reportBI';
        }

        if ($report->name_report == 'active_base') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'Nombre',
            'Teléfono',
            'Teléfono Oficina',
            'Email',
            'I.N.E',
            'Fecha del evento',
            'Contactado',
            'Acepto recompra',
            'Comentario',
            'Fecha llamada'];

          //Opteniendo query
          $query = HistoryDC2::getClientsByTag(['A90', 'REC'], $filters['typeC']);

          //Función que arma el array del reportes
          $function = 'getArrayActiveBase';
          $directory = 'reportBI';
        }

        if ($report->name_report == 'churn_90') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'Nombre',
            'Teléfono',
            'Teléfono Oficina',
            'Email',
            'I.N.E',
            'Fecha del evento',
            'Contactado',
            'Acepto recompra',
            'Comentario',
            'Fecha llamada'];

          //Opteniendo query
          $query = HistoryDC2::getClientsByTag(['C90'], $filters['typeC']);

          //Función que arma el array del reportes
          $function = 'getArrayChurn90';
          $directory = 'reportBI';
        }

        if ($report->name_report == 'decay_90') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'Nombre',
            'Teléfono',
            'Teléfono Oficina',
            'Email',
            'I.N.E',
            'Fecha del evento',
            'Contactado',
            'Acepto recompra',
            'Comentario',
            'Fecha llamada'];

          //Obteniendo query
          $query = HistoryDC2::getClientsByTag(['D90'], $filters['typeC']);

          //Función que arma el array del reportes
          $function = 'getArrayDecay90';
          $directory = 'reportBI';
        }

        if ($report->name_report == 'reporte_financiamiento_payjoy') {
          //Header
          $reportxls[] = [
            'DN Netwey',
            'Coordinador',
            'Vendedor',
            'Cliente',
            'Monto inicial',
            'Monto financiado',
            'Monto total',
            'Fecha financiamiento',
            'Fecha asociación',
            'Estatus'];

          //Obteniendo query
          $query = Payjoy::getReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayPayjoy';
          $directory = 'reportBI';
        }

        if ($report->name_report == 'reporte_coordenadas') {
          //Header
          $reportxls[] = [
            'DN Netwey',
            'Cliente',
            'Teléfono',
            'Usuario',
            'Email',
            'Latitud inicial',
            'Longitud inicial',
            'Latitud nueva',
            'Longitud nueva',
            'Fecha'];

          //Obteniendo query
          $query = CoordinateChanges::getReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayCoordinates';
          $directory = 'reportBI';
        }

        $API = false;

        if ($report->name_report == 'reporte_nomina_voywey') {
          //Header
          $reportxls[] = [
            'Folio',
            'Nombre del vendedor',
            'Apellido del vendedor',
            'Email del vendedor',
            'Nombre del repartidor',
            'Apellido del repartidor',
            'Email del repartidor',
            'Telefono del repartidor',
            'DNI del repartidor',
            'Direccion de entrega',
            'Direccion de activacion',
            'Precio',
            'Forma de pago',
            'Numero de transacion',
            'Nombre del cliente',
            'Apellido del cliente',
            'Email del cliente',
            'Telefono del cliente',
            'Fecha de registro',
            'Fecha de entrega',
            'MSISDN activado'];

          //Obteniendo query
          $current_page = 1;
          $dataInfo = APIvoyWey::nomina($filters, $current_page);

          $arrayData = array();
          $arrayData = $dataInfo['data']->data->data;

          while ($dataInfo['data']->data->next_page_url != null) {
            $current_page++;

            $dataInfo = APIvoyWey::nomina($filters, $current_page);

            for ($i = 0; $i < count($dataInfo['data']->data->data); $i++) {
              array_push($arrayData, $dataInfo['data']->data->data[$i]);
            }
          }

          $query = $arrayData;
          $API = true;
          //Función que arma el array del reportes
          $function = 'getArrayNominaVoywey';
          $directory = 'reportVoywey';
        }

        if ($report->name_report == 'reporte_conciliacion_voywey') {
          //Header
          $reportxls[] = [
            'Folio',
            'Nombre del vendedor',
            'Apellido del vendedor',
            'Email del vendedor',
            'Nombre del repartidor',
            'Apellido del repartidor',
            'Email del repartidor',
            'Telefono del repartidor',
            'DNI del repartidor',
            'Direccion de entrega',
            'Direccion de activacion',
            'Deuda',
            'Forma de pago',
            'Dias de deuda',
            'Nombre del cliente',
            'Apellido del cliente',
            'Email del cliente',
            'Telefono del cliente',
            'Fecha de registro',
            'Fecha de entrega'];

          //Obteniendo query
          $current_page = 1;
          $dataInfo = APIvoyWey::conciliacion($filters, $current_page);

          $arrayData = array();
          $arrayData = $dataInfo['data']->data->data;

          while ($dataInfo['data']->data->next_page_url != null) {
            $current_page++;

            $dataInfo = APIvoyWey::conciliacion($filters, $current_page);

            for ($i = 0; $i < count($dataInfo['data']->data->data); $i++) {
              array_push($arrayData, $dataInfo['data']->data->data[$i]);
            }
          }

          $query = $arrayData;
          $API = true;
          //Función que arma el array del reportes
          $function = 'getArrayConciliacionVoywey';
          $directory = 'reportVoywey';
        }

        if ($report->name_report == 'reporte_inventario_voywey') {
          //Header
          $reportxls[] = [
            'Id',
            'Bodega',
            'Disponibles para vender',
            'Asignado a repartidores',
            'Equipos en camino',
            'Nombre del repartidor',
            'Apellido del repartidor',
            'Email del repartidor',
            'SKU a entregar',
            'Modelo del equipo a entregar',
            'DN',
            'Status'];

          //Obteniendo query
          $query = APIvoyWey::makeReportInventory($filters);

          $API = true;
          //Función que arma el array del reportes
          $function = 'getArrayInventaryVoywey';
          $directory = 'reportVoywey';
        }

        if ($report->name_report == 'reporte_SaleJelou_voywey') {
          //Header
          $reportxls[] = [
            'Orden',
            'Orden Voywey',
            'Status',
            'Fecha de creacion',
            'Dias transcurridos en Activar',
            'Fecha de Activacion',
            'Monto pagado',
            'Codigo promocional',
            'Forma de Pago',
            'Email del vendedor',
            'Nombre del vendedor',
            'Apellido del vendedor',
            'Telefono del vendedor',
            'INE del repartidor',
            'Nombre del repartidor',
            'Apellido del repartidor',
            'Email del repartidor',
            'Telefono del repartidor',
            'DNI del cliente',
            'Nombre del cliente',
            'Apellido del cliente',
            'Email del cliente',
            'MSISDN',
            'Modelo de equipo',
            'Plan adquirido'];

          //Opteniendo query
          $query = DeferredPayment::getData_DeferredPayment($filters);

          //Log::info("query: " . $query);
          if (!empty($query)) {
            foreach ($query as $RegJeoVoy) {
              $RegJeoVoy = DeferredPayment::getDetail_repartidorByOrden($RegJeoVoy->OrderVoy, $RegJeoVoy);
            }
          }

          //Log::info("query2: " . $query);

          //Función que arma el array del reportes
          $function = 'getArraySalesJelouVoywey';
          $directory = 'reportVoywey';
        }

        if ($report->name_report == 'reporte_usuarios_bloqueados') {
          //Header
          $reportxls[] = [
            'Usuario bloqueado',
            'Usuario que lo bloqueo',
            'Usuario que lo desbloqueo',
            'Fecha del bloqueo',
            'Fecha del desbloqueo',
            'Días bloqueado'];

          //Opteniendo query
          $query = UserLocked::getReport($filters)->orderBy('date_locked', 'DESC');

          //Función que arma el array del reportes
          $function = 'getArrayUserLockedReport';
          $directory = 'users';
        }

        if ($report->name_report == 'reporte_SuperSim') {
          //Header
          $reportxls[] = [
            'DN',
            'Cliente',
            'Email del cliente',
            'Vendedor',
            'Email del vendedor',
            'Monto de la recarga',
            'Servicio recargado',
            'Numero de recarga',
            'Fecha de recarga',
            'Venta'];

          //Obteniendo query
          $query = Sale::getSuper_Sim($filters);

          //Función que arma el array del reportes
          $function = 'getArraySuperSim';
          $directory = 'reportSuperSim';
          $API = true;
        }
        if ($report->name_report == 'report_portability_import') {
          //Header
          $reportxls[] = [
            'ID venta',
            'DN a portar',
            'DN transitorio',
            'NIP',
            'Fecha de creacion',
            'Fecha de actualizacion',
            'Status',
            'Observacion',
            'details_error',
            'portID'];

          //Obteniendo query
          $query = Portability::getDTPotabilityPeriod($filters);

          //Función que arma el array del reportes
          $function = 'getArrayPortImportPeriodo';
          $directory = 'reportPortability';
        }

        if ($report->name_report == 'report_portability_export') {
          //Header
          $reportxls[] = [
            'msisdn',
            'Id de venta',
            'Fecha de alta',
            'Fecha de exportacion',
            'PortID',
            'DNI Cliente',
            'Nombre del cliente',
            'Status',
            'Tipo'];

          //Obteniendo query
          $query = Portability_exportacion::getDTPotabilityPeriod($filters);

          //Función que arma el array del reportes
          $function = 'getArrayPortExportPeriodo';
          $directory = 'reportPortability';
        }

        if ($report->name_report == 'reporte_migracion') {
          //Header
          $reportxls[] = [
            'Cliente',
            'DN Origen',
            'Alta DN Origen',
            'Vendedor DN Origen',
            'Ultima Recarga DN Origen',
            'IMEI',
            'Tipo',
            'DN Nuevo',
            'Fecha de Migracion',
            'Vendedor Migracion',
            'Paquete Migracion'];

          //Opteniendo query
          $query = Migrations::getDTMigrationsDataReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayMigrationsReport';
          $directory = 'migrations';
        }

        if ($report->name_report == 'reporte_altas_con_consumos') {
          //Header
          $reportxls[] = [
            'msisdn única',
            'Fecha_Alta',
            'Fecha_Consumo',
            'Consumo (MB)'];

          //Opteniendo total de filas
          if (empty($filters['current_pg'])) {
            $totalRecrods = Sale::getTotalUpsWithConsumptions($filters);

            if ($totalRecrods > ($limitP * $limitQ)) {
              $filters['length'] = ($limitP * $limitQ);
              $filters['start'] = 0;
            }
          } else {
            $filters['length'] = ($limitP * $limitQ);
            $filters['start'] = $filters['current_pg'] * $filters['length'];
            $totalRecrods = $filters['total_lines'];
          }

          //Opteniendo query
          $query = Sale::getDTUpsWithConsumptionsDataReport($filters);

          //Función que arma el array del reportes
          $function = 'getArrayUpsWithConsumptionsReport';
          $directory = 'sales';
        }

        //Reporte de ventas por API
        if ($report->name_report == 'reporte_ventas_api') {
          //Header
          $reportxls[] = [
            'Transacción',
            'Organización',
            'Vendedor',
            'Producto',
            'Tipo de linea',
            'Plan',
            'Servicio',
            'MSISDN',
            'Cliente',
            'Teléfono',
            'Email',
            'Monto producto',
            'Proveedor',
            'Último estatus de la entrega',
            'Fecha de último estatus de la entrega',
            'Monto delivery',
            'Orden delivery',
            'Código postal',
            'Estado',
            'Ciudad',
            'Cupón',
            'Descuento',
            'Fecha de venta',
            'Fecha de entrega',
            'Días en activar',
            'Estatus de la compra'];

          //Opteniendo query
          $query = TempCar::getReportAPISales($filters)
            ->orderBy('islim_ordens.date', 'DESC')
            ->get();

          if (!empty($filters['status_sale'])) {
            if ($filters['status_sale'] == 'G') {
              $query = $query->filter(function ($value, $key) {
                return empty($value->status);
              });
            }

            if ($filters['status_sale'] == 'E') {
              $query = $query->filter(function ($value, $key) {
                return $value->status == 'I';
              });
            }
            if ($filters['status_sale'] == 'F') {
              $query = $query->filter(function ($value, $key) {
                return $value->status == 'A';
              });
            }
          }

          //Función que arma el array del reportes
          $function = 'getArraySalesAPIReport';
          //Directorio de S3
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_historico_suspensiones') {
          //Header
          $reportxls[] = [
            'msisdn',
            'Cliente',
            'Tipo Suspensión',
            'Fecha Suspensión'];

          //Opteniendo query
          $query = SuspendedByAdmin::getSuspendedHistory($filters);

          $function = 'getArraySuspendedHistory';
          $directory = 'reportClients';
        }

        if ($report->name_report == 'reporte_ventas_coppel') {
          //Header
          $reportxls[] = [
            'msisdn',
            'Vendedor',
            'Cliente',
            'Telefono',
            'Monto',
            'Fecha',
            'Paquete',
            'Articulo',
            'Estatus'];

          //Opteniendo query
          $query = Coppel::getCoppelSales($filters);

          $function = 'getArrayCoppelSales';
          $directory = 'sales';
        }

        if ($report->name_report == 'reporte_JelouSales') {
          //Header
          $reportxls[] = [
            'Folio',
            'Courier',
            'Nombre del Cliente',
            'Telefono del Cliente',
            'DNI del cliente',
            'Status',
            'Días transcurridos',
            'msisdn',
            'Status del DN',
            'Tipo de DN',
            'SKU',
            'Operador logistico',
            'Fecha de creacion',
            'Estado de la entrega',
            'Direccion de entrega',
            'Fecha de entrega',
            'Monto pagado',
            'Forma de pago',
            '¿Dinero en netwey?',
            'Fecha del deposito',
            'Fecha del alta'];

          //Opteniendo query
          $query = TempCar::getSalesJelou($filters);

          $function = 'getArrayJelouSales';
          $directory = 'sales';
        }
        if ($report->name_report == 'reporte_pedido_solicitado') {
          //Header
          $reportxls[] = [
            'Archivo',
            'Caja',
            'msisdn',
            'SKU',
            'iccid',
            'imei',
            'Branch',
            'Folio',
            'Usuario',
            'Estatus',
            'Es reciclado',
            'Ultima accion por',
            'Accion del Regional',
            'Accion del Coordinador',
            'Comentario',
            'Fecha'];

          //Opteniendo query
          $query = StockProvaDetail::getOrderRequest($filters);

          $function = 'getArrayOrderRequest';
          $directory = 'inventory';
        }

        if ($report->name_report == 'reporte_movimiento_inventario') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'SKU',
            'description',
            'fecha',
            'origen',
            'destino',
            'ejecutado por',
            'Observación'];

          //Opteniendo query
          $query = SellerInventoryTrack::getInventoryTracksReport($filters);

          $function = 'getArrayInventoryTracks';
          $directory = 'inventory';
        }

        if ($report->name_report == 'reporte_inventario_merma') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'Artículo',
            'Usuario que tenia el artículo',
            'Usuario que movio el artículo',
            'Bodega',
            'Fecha'];

          //Opteniendo query
          $query = Inventory::getReportMerma($filters);

          $function = 'getArrayInventoryMerma';
          $directory = 'inventory';
        }

        if ($report->name_report == 'reporte_instalaciones_de_fibra') {
          //Header
          $reportxls[] = [
            'Id Proceso',
            'MSISDN',
            'Cliente',
            'Email Cliente',
            'Telefono Cliente',
            'Dirección de Instalación',
            'Venderdor',
            'Instalador',
            'Teléfono del Instalador',
            'Zona de Cobertura',
            'Fecha de PreVenta',
            'Fecha de Instalación',
            'Cobrado',
            'Estado',
            'Fecha de Reprogramacion',
            'Nro Reprogramacion',
          ];

          //Opteniendo query
          $query = FiberInstallation::getFiberInstallationsReport($filters);

          $function = 'getArrayFiberInstallations';
          $directory = 'reportClients';
        }

        //Reporte status de inventario - logica ivan
        if ($report->name_report == 'reporte_estatus_inventario') {

          $reportxls[] = [
            'Asignado A',
            'Nombre y apellido',
            'Coordinacion',
            'MSISDN',
            'Producto',
            'Tipo de Producto',
            'Evidencia',
            'Estatus de Color',
            'Fecha de Estatus de Color',
          ];

          //Validando que vengan los dos rangos de fechas y formateando fecha
          if (empty($filters['dateb']) && empty($filters['datee'])) {
            $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
            $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
          } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
            $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
            $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
          } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
            $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
            $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
          } else {
            $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
            $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
          }

          //Opteniendo query
          $query = SellerInventory::getStatusInv($filters);
          //Función que arma el array del reportes
          $function = 'getArrayInventoryStatus';
          //Directorio de S3
          $directory = 'inventories';
        }

        if ($report->name_report == 'reporte_bodega_merma_equipos_viejos') {
          $reportxls[] = [
            'MSISDN',
            'Articulo',
            'Nombre y Apellido Supervisor',
            'Nombre y Apellido Vendedor',
            'Fecha Asignacion',
            'Fecha Color Rojo'];

          //Validando que vengan los dos rangos de fechas y formateando fecha
          if (empty($filters['dateb']) && empty($filters['datee'])) {
            $filters['dateb'] = Carbon::now()->format('Y-m-d H:i:s');
            $filters['datee'] = Carbon::now()->addMonth()->format('Y-m-d H:i:s');
          } elseif (empty($filters['dateb']) && !empty($filters['datee'])) {
            $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
            $filters['dateb'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['datee'])->subMonth()->startOfDay()->toDateTimeString();
          } elseif (empty($filters['datee']) && !empty($filters['dateb'])) {
            $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
            $filters['datee'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateb'])->endOfDay()->addMonth()->toDateTimeString();
          } else {
            $filters['dateb'] = Carbon::createFromFormat('d-m-Y', $filters['dateb'])->startOfDay()->toDateTimeString();
            $filters['datee'] = Carbon::createFromFormat('d-m-Y', $filters['datee'])->endOfDay()->toDateTimeString();
          }

          //Opteniendo query
          $query = Inventory::getMermaWarehouseOldEquipment($filters);
          //Función que arma el array del reportes
          $function = 'getArrayMermaWarehouseOldEquipment';
          //Directorio de S3
          $directory = 'inventories';
        }

        if ($report->name_report == 'user_low_request') {
          //Header
          $reportxls[] = [
            'Solicitante',
            'Nombre del solicitante',
            'Usuario a dar de baja',
            'Nombre del usuario a dar de baja',
            'Motivo de la baja',
            'Fecha de la solicitud',
            'Deuda en efectivo',
            'Dias de deuda con efectivo',
            'Deuda en inventario',
            'Deuda en abonos',
            'Dias en deuda con abono',
            'Deuda total'];

          //Obteniendo query
          $query = LowRequest::GetListRequest($filters);
          //Funcion del helper
          $function = 'getArrayLowRequest';
          //Directorio s3
          $directory = 'low';
        }

        if ($report->name_report == 'user_low_report') {
          //Header
          $reportxls[] = [
            'Usuario a dar de baja',
            'Nombre del usuario a dar de baja',
            'Distribuidor',
            'Fecha de solicitud',
            'Deuda en inventario',
            'Deuda en efectivo',
            'Deuda en abonos',
            'Deuda acumulada',
            'Saldo a favor',
            'Total a descontar',
            'Fecha de Aprobacion/Rechazo de solicitud',
            'Fecha de finalizacion',
            'Status',
            'Motivo del rechazo',
            'Monto descontado',
            'Monto del finiquito',
            'Fecha del finiquito'];

          //Obteniendo query
          $query = LowRequest::GetListProcess($filters);
          //Funcion del helper
          $function = 'getArrayLowReport';
          //Directorio s3
          $directory = 'low';
        }

        if ($report->name_report == 'user_low_process') {
          //Header
          $reportxls[] = [
            'Usuario a dar de baja',
            'Nombre del usuario a dar de baja',
            'Distribuidor',
            'Fecha de solicitud',
            'Deuda acumulada',
            'Saldo a favor',
            'Total a descontar',
            'Fecha de aprobacion de solicitud'];

          //Obteniendo query
          $query = LowRequest::GetListProcess($filters);
          //Funcion del helper
          $function = 'getArrayLowProcess';
          //Directorio s3
          $directory = 'low';
        }

        if ($report->name_report == 'reporte_kpi_articulos_perdidos') {
          //Header
          $reportxls[] = [
            'Periodo',
            'Regional',
            'Coordinador',
            'Equipos Viejos',
            'Merma',
            'Asignados',
            'KPI',
            'Costo de Equipos Perdidos',
            'Descuento(%)',
            'Descuento($)',
            'Desc. Regional(%)',
            'Desc. Regional($)',
            'Desc. Coordinador(%)',
            'Desc. Coordinador($)',
          ];

          //Opteniendo query
          $query = KPISDismissal::getDTKPI($filters);

          $function = 'getArrayKPIDismissal';
          $directory = 'inventory';
        }

        if ($report->name_report == 'report_inv_recicler') {
          //Header
          $reportxls[] = [
            'Status',
            'MSISDN',
            'Origen de la solicitud',
            'Responsable de la solicitud',
            'Fecha de la solicitud',
            'Codigo de oferta Altan',
            'Observacion',
            'Detalles',
            'Cliente en Netwey',
            'Dias sin recargar'];

          //Opteniendo query
          $query = Inv_reciclers::searchReportRecicler($filters);
          //metodo
          $function = 'getArrayInvRecicler';
          //directorio
          $directory = 'inventories';
        }

        if ($report->name_report == 'report_financiamiento_paguitos') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'Coordinador',
            'Vendedor',
            'Cliente',
            'Monto inicial',
            'Monto financiado',
            'Monto total',
            'Fecha financiamiento',
            'Fecha asociación',
            'Estatus'];

          //Opteniendo query
          $query = Paguitos::getReport($filters);
          //metodo
          $function = 'getArrayPaguitos';
          //directorio
          $directory = 'reportBI';
        }

        if ($report->name_report == 'reporte_facturas_masiva') {
          //Header
          $reportxls[] = [
            'place',
            'date_expired',
            'term',
            'oxxo_folio_date',
            'oxxo_folio_id',
            'oxxo_folio_nro',
            'date_pay',
            'doc_pay',
            'status_pay',
            'sub_total',
            'tax',
            'total',
            'pay_type',
            'mk_serie',
            'mk_folio'];

          //Opteniendo query
          $query = BillingMasive::getReportBillingsMasive($filters);
          //metodo
          $function = 'getArrayBillingsMasive';
          //directorio
          $directory = 'reportBI';
        }

        if ($report->name_report == 'reporte_historico_de_estatus_de_inventarios') {
          //Header
          $reportxls[] = [
            'Asignado a',
            'Coordinación',
            'Coordinador',
            'Region',
            'Regional',
            'MSISDN',
            'Producto',
            'Tipo de Producto',
            'Estatus de Color Actual',
            'Fecha de Estatus de Color Actual',
            'Ultima Fecha que cambio a Naranja',
            'Veces que cambio a Naranja'];

          //Opteniendo query
          $query = SellerInventory::getHistoryStatusInv($filters);
          //metodo
          $function = 'getArrayHistoryStatusInv';
          //directorio
          $directory = 'inventories';
        }

        if ($report->name_report == 'reporte_instalacion_fibra_estatus') {
          //Header
          $reportxls[] = [
            'MSISDN',
            'MAC',
            'Cliente',
            'Venderdor',
            'Colonia',
            'Zona de Cobertura',
            'Estatus',
            'Reprogramaciones',
            'Fecha de Venta',
            'Fecha de Instalación',
            'Antiguedad de venta',
          ];

          //Obteniendo query
          $query = FiberInstallation::getFiberInstallationsReportByStatus($filters);

          $function = 'getArrayFiberInstallationsByStatus';
          $directory = 'reportClients';
        }

        //Agregar los nuevos reportes de aqui para abajo

        //Generando excel
        if (!empty($query)) {
          //Lineas de la consulta
          if (!empty($typePag) && $typePag == 'in-model') {
            $lines = $totalRecrods;
          } else {
            if (is_array($query)) {
              $lines = count($query);
            } else {
              $lines = $query->count();
            }
          }

          $memory = CommonHelpers::getMemoryUse();
          $this->output->writeln('Data consultada: ' . $lines . ' Fecha' . date('H:i:s') . ' Memoria: ' . $memory, false);

          if ($lines > ($limitP * $limitQ)) {
            if (empty($filters['total_pg'])) {
              $filters['total_pg'] = ceil($lines / ($limitP * $limitQ));
              $filters['total_lines'] = $lines;
              $filters['current_pg'] = 1;
              $filters['father_report'] = $report->id;

              $lines = $limitP * $limitQ;
            } else {
              if (empty($typePag) || $typePag != 'in-model') {
                $currentPage = $filters['current_pg'] * $limitP;
              }

              $lines = $filters['total_lines'] - (($limitP * $limitQ) * $filters['current_pg']);
              if ($lines > ($limitP * $limitQ)) {
                $lines = $limitP * $limitQ;
              }

              $filters['current_pg'] += 1;
            }

            if ($filters['current_pg'] < $filters['total_pg']) {
              $explode = true;
            }

            $isPaginte = true;
          }

          //$memory = CommonHelpers::getMemoryUse();
          //$this->output->writeln('lineas: '.$lines.' Fecha: '.date('H:i:s').' Memoria: '.$memory, false);

          if (($lines > $limitQ) || $isPaginte) {
            $pg = ceil($lines / $limitQ);

            for ($i = 0; $i < $pg; $i++) {
              $skip = ($currentPage + $i) * $limitQ;

              if ($lines < $limitQ) {
                $limitQ = $lines;
              }

              if (get_parent_class($query) == 'Illuminate\Support\Collection') {
                $data = $query->slice($skip, $limitQ);
              } else {
                if (!$API) {
                  $data = $query->skip($skip)->take($limitQ)->get();
                } else {
                  $data = $query;
                }
              }

              $reportxls = ReportsHelpers::callGetArray(
                $function,
                $data,
                $reportxls
              );
            }
          } else {
            if (get_parent_class($query) == 'Illuminate\Support\Collection') {
              $data = $query;
            } else {
              if (!$API) {
                $data = $query->get();
              } else {
                $data = $query;
              }
            }

            $reportxls = ReportsHelpers::callGetArray(
              $function,
              $data,
              $reportxls
            );
          }

          //$memory = CommonHelpers::getMemoryUse();
          //$this->output->writeln('array armado: '.count($reportxls).' Fecha: '.date('H:i:s').' Memoria: '.$memory, false);
        }

        if (count($reportxls)) {
          $url = CommonHelpers::saveFile(
            '/public/reports',
            $directory,
            $reportxls,
            $report->name_report . '_' . time(),
            ($limitQ + 1), //Se suma uno por el header de cada hoja
            'csv'
          );

          //$memory = CommonHelpers::getMemoryUse();
          //$this->output->writeln('url generada: '.(String)$url.' Fecha. '.date('H:i:s').' Memoria: '.$memory, false);

          $report->status = 'G';
          $report->download_url = $url;
          $report->save();

          if ($explode) {
            unset($filters['user']);
            unset($filters['user_profile']);
            $nextReport = new Reports;
            $nextReport->name_report = $report->name_report;
            $nextReport->filters = (String) json_encode($filters);
            $nextReport->user_profile = $report->user_profile;
            $nextReport->user = $report->user;
            $nextReport->status = 'C';
            $nextReport->date_reg = date('Y-m-d H:i:s');
            $nextReport->save();
          }

          if ($isPaginte) {
            $report->name_report = $report->name_report . '_parte_' . $filters['current_pg'] . '-' . $filters['total_pg'];
            $report->save();
          }
        }
      } catch (\Exception $e) {
        $report->status = 'E';
        $report->save();

        $this->output->writeln('Ocurrio un error generando el reporte: ' . $report->id . ' - ' . $e->getMessage(), false);

        Log::error('Ocurrio un error generando el reporte: ' . $report->id . ' - ' . $e->getMessage());
      }
    }
  }
}
