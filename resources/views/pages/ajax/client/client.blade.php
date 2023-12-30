@php
$actRetPer = false;
$actRetExt = false;
$requestPort = false;
@endphp
@foreach (session('user')->policies as $policy)

@if($policy->code == 'CLA-ECO' && $policy->value > 0)
@php($canChcoor = true)
@endif

@if($policy->code == 'CLA-ASL' && $policy->value > 0)
@php($canSusp = true)
@endif

@if($policy->code == 'CLA-SPS' && $policy->value > 0)
@php($canSuspPar = true)
@endif

@if($policy->code == 'RET-LAS' && $policy->value > 0)
@php($actRetPer = true)
@endif

@if($policy->code == 'RET-ASE' && $policy->value > 0)
@php($actRetExt = true)
@endif

@if($policy->code == 'P0R-NEW' && $policy->value > 0)
@php($requestPort = true)
@endif

@endforeach
<style type="text/css">
  .text-mywarning {
    color: #F0AD4E !important;
  }

  .text-mydanger {
    color: #D9534F !important;
  }

  .title-details {
    font-size: 2rem;
    padding: 10px 15px 20px 5px;
    margin: auto;
    font-weight: bold;
  }
</style>
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Clientes
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="/islim/">
            Dashboard
          </a>
        </li>
        <li class="active">
          clientes
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="white-box">
  <div class="row">
    <div class="col-md-6">
      <div class="row">
        <div class="col-md-12">
          <div class="form-check">
            <label class="form-check-label bt-switch">
              <input class="form-check-input" id="client_manual_check" type="checkbox"/>
              Ingresar MSISDN manualmente
            </label>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="form-check">
            <label class="form-check-label bt-switch">
              <input class="form-check-input" id="client_name_manual_check" type="checkbox"/>
              Buscar por nombre
            </label>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-12">
          <div class="form-check">
            <label class="form-check-label bt-switch">
              <input class="form-check-input" id="client_file_check" type="checkbox"/>
              Buscar por archivo
            </label>
          </div>
        </div>
      </div>
      <div class="row" id="msisdn_file_container">
        <div class="col-md-12">
          <div class="form-group" id="file">
            <label class="control-label">
              Archivo con MSISDN
            </label>
            <input class="form-control-file" id="msisdn_file" name="msisdn_file" type="file"/>
            <label class="control-label" id="error_msisdn_file">
            </label>
          </div>
        </div>
      </div>
      <div class="row" id="msisdn_select_container">
        <div class="col-md-12">
          <div class="form-group" id="manual">
            <label class="control-label">
              MSISDN
            </label>
            <select class="form-control" id="msisdn_select" multiple="" name="msisdn_select">
              <option value="">
                Seleccione el(los) msisdn(s)
              </option>
            </select>
            <label class="control-label" id="error_msisdn_select">
            </label>
          </div>
        </div>
      </div>
      <div class="row" id="name_select_container">
        <div class="col-md-12">
          <div class="form-group" id="manual">
            <label class="control-label">
              Nombre
            </label>
            <select class="form-control" id="name_select" multiple="" name="name_select">
              <option value="">
                Seleccione el(los) cliente(s)
              </option>
            </select>
            <label class="control-label" id="error_name_select">
            </label>
          </div>
        </div>
      </div>
    </div>
    <div class="col-md-2">
      <div class="form-group">
        <label class="control-label">
        </label>
        <button class="btn btn-info btn-md button" onclick="getClientTable()" type="button">
          Buscar
        </button>
        {{--
        <button class="btn btn-primary btn-md button" onclick="getAllClientTable()" type="button">
          Buscar todos
        </button>
        --}}
      </div>
    </div>
    <div class="col-md-4">
      <label class="control-label" id="error_msisdn_file">
      </label>
    </div>
  </div>
</div>
<!-- table area -->
<div class="row white-box">
  <div class="col-md-12" id="client_table_area">
  </div>
</div>
<!--modal de detalle-->
{{--
<button data-target="#detail" data-toggle="modal" hidden="" id="open_detail_btn" type="button">
</button>
--}}
<div class="modal" id="detail" role="dialog" style="overflow-y: scroll;">
  <div class="modal-dialog modal-details" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Información del cliente
        </h4>
      </div>
      <div class="modal-body">
        <ul class="nav nav-tabs" id="myTab" role="tablist">
          <li class="nav-item">
            <a aria-controls="detalles" aria-selected="true" class="nav-link active" data-toggle="tab" href="#detalles" id="detalles-tab" role="tab">
              Detalles
            </a>
          </li>
          <li class="nav-item">
            <a aria-controls="recargas" aria-selected="false" class="nav-link" data-toggle="tab" href="#recargas" id="recargas-tab" role="tab">
              Recargas
            </a>
          </li>
          <li class="nav-item" id="tabconsumos">
            <a aria-controls="consumos" aria-selected="false" class="nav-link" data-toggle="tab" href="#consumos" id="consumos-tab" role="tab">
              Consumos
            </a>
          </li>
          <li class="nav-item" id="tabrecdisp">
            <a aria-controls="recdisp" aria-selected="false" class="nav-link" data-toggle="tab" href="#recdisp" id="recdisp-tab" role="tab">
              Rec. Disponibles
            </a>
          </li>

          <li class="nav-item" id="tabblim" style="display: none;">
            <a aria-controls="blim" aria-selected="false" class="nav-link" data-toggle="tab" href="#blim" id="blim-tab" role="tab">
              Blim
            </a>
          </li>
          <li class="nav-item">
            <a aria-controls="coordenadas" aria-selected="false" class="nav-link" data-toggle="tab" href="#coordenadas" id="coordenadas-tab" role="tab">
              Coordenadas
            </a>
          </li>
          <li class="nav-item">
            <a aria-controls="compensaciones" aria-selected="false" class="nav-link" data-toggle="tab" href="#compensaciones" id="compensaciones-tab" role="tab">
              Compensaciones
            </a>
          </li>
          <li class="nav-item">
            <a aria-controls="salud" aria-selected="false" class="nav-link" data-toggle="tab" href="#salud" id="salud-tab" role="tab">
              Salud
            </a>
          </li>
          <li class="nav-item d-none" id="tabsuspension">
            <a aria-controls="suspension" aria-selected="false" class="nav-link" data-toggle="tab" href="#suspension" id="suspension-tab" role="tab">
              Suspensión
            </a>
          </li>
          @if($actRetPer || $actRetExt)
          <li class="nav-item" id="tabretention">
            <a aria-controls="retention" aria-selected="false" class="nav-link" data-toggle="tab" href="#retention" id="retention-tab" role="tab">
              Serv. de Retención
            </a>
          </li>
          @endif
          <li class="nav-item" id="tabpromocion">
            <a aria-controls="promocion" aria-selected="false" class="nav-link" data-toggle="tab" href="#promocion" id="promocion-tab" onclick="refreshPromocion()" role="tab">
              Serv. Promocionales
            </a>
          </li>
          <li class="nav-item" style="display: none;">
            <a aria-controls="buyback" aria-selected="false" class="nav-link" data-toggle="tab" href="#buyback" id="buyback-tab" role="tab">
              Recompra
            </a>
          </li>
          @if($requestPort)
          <li class="nav-item" id="tabportabilidad">
            <a aria-controls="portability" aria-selected="false" class="nav-link" data-toggle="tab" href="#portability" id="portability-tab" role="tab">
              Portabilidad
            </a>
          </li>
          @endif
        </ul>
        <div class="tab-content" id="myTabContent">
          <div aria-labelledby="detalles-tab" class="tab-pane fade show active" id="detalles" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body row">
                      <div class="col-md-6" id="col-1">
                        <h4 class="modal-title">
                          Cliente
                        </h4>
                        <hr/>
                        <div class="row">
                          <div class="col-md-12">
                            <!--datos del cliente-->
                            <div class="row">
                              <div class="col-md-12">
                                <label class="control-label">
                                  Nombre:
                                  <strong>
                                    <span id="modal_name">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Teléfono de contacto:
                                  <strong>
                                    <span id="modal_phone">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Teléfono aux:
                                  <strong>
                                    <span id="modal_phone_aux">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Email:
                                  <strong>
                                    <span id="modal_email">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-12">
                                <label class="control-label">
                                  Dirección:
                                  <strong>
                                    <span id="modal_address">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <hr/>
                        <div id="credit-content">
                          <h4 class="modal-title">
                            Crédito
                          </h4>
                          <hr/>
                          <div class="row">
                            <div class="col-md-12">
                              <!--datos del cliente-->
                              <div class="row">
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Financiamiento:
                                    <strong>
                                      <span id="modal_financing">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Monto Financiado:
                                    <strong>
                                      <span id="modal_financing_amount">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Cuota Plan Semanal:
                                    <strong>
                                      <span id="modal_week_quote">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Cuota Plan Quincenal:
                                    <strong>
                                      <span id="modal_quin_quote">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Cuota Plan mensual:
                                    <strong>
                                      <span id="modal_month_quote">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Cuotas pagadas:
                                    <strong>
                                      <span id="modal_quote_pay">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Deuda:
                                    <strong>
                                      <span id="modal_total_payment">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <h4 class="modal-title">
                          Vendedor
                        </h4>
                        <hr/>
                        <div class="row">
                          <div class="col-md-12">
                            <!--datos del vendedor-->
                            <div class="row">
                              <div class="col-md-6">
                                <label class="control-label">
                                  Nombre:
                                  <strong>
                                    <span id="modal_name_seller">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Organización:
                                  <strong>
                                    <span id="modal_org_seller">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Fecha de alta:
                                  <strong>
                                    <span id="modal_date_up">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Empresa de financiamiento:
                                  <strong>
                                    <span id="modal_bussines_financing">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <hr/>
                        <h4 class="modal-title">
                          Estatus de la linea
                        </h4>
                        <hr/>
                        <div class="row">
                          <div class="col-md-12">
                            <!--datos de netwey-->
                            <div class="row">
                              <div class="col-md-6" id="label_mifi">
                                <label class="control-label">
                                  MIFI:
                                  <strong>
                                    <span id="modal_mifi">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  DN netwey:
                                  <strong>
                                    <span id="modal_msisdn">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="label_migrations_content">
                                <label class="control-label">
                                  DN migrado:
                                  <strong>
                                    <span id="modal_hbb_migrations">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="label_incidences_content">
                                <label class="control-label">
                                  Status del DN:
                                  <strong>
                                    <span id="modal_hbb_incidences" style="color:red;">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="label_date_incidences_content">
                                <label class="control-label">
                                  Ultimo Status del DN:
                                  <strong>
                                    <span id="modal_hbb_date_incidences">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="label_time_recharge_content">
                                <label class="control-label">
                                  Ultima recarga:
                                  <strong>
                                    <span id="modal_time_recharge" style="color:red;">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Equipo:
                                  <strong>
                                    <span id="modal_equipo">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_broadband-content">
                                <label class="control-label">
                                  Servicialidad:
                                  <strong>
                                    <span id="modal_broadband">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label">
                                  Estatus de la linea:
                                  <strong>
                                    <span id="modal_status">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6 info-pho">
                                <label class="control-label" id="label_marca">
                                  Marca:
                                  <strong>
                                    <span id="modal_marca">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6 info-pho">
                                <label class="control-label" id="label_modelo">
                                  Modelo:
                                  <strong>
                                    <span id="modal_modelo">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label" id="label_imei">
                                  <span id="label_imei_title">
                                    Imei:
                                  </span>
                                  <strong>
                                    <span id="modal_imei">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_service_sell_container">
                                <label class="control-label" id="modal_service_sell_label">
                                  Plan de alta:
                                  <strong>
                                    <span id="modal_service_sell">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6">
                                <label class="control-label" id="modal_service_label">
                                  Plan activo:
                                  <strong>
                                    <span id="modal_service">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_imei_new">
                                <label class="control-label">
                                  Imei actual:
                                  <strong>
                                    <span style="color: red;">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_b28">
                                <label class="control-label">
                                  Es banda28:
                                  <strong>
                                    <span>
                                    </span>
                                    <a class="pl-2" data-target="#modal_updateb28" data-toggle="modal" href="#">
                                      Actualizar
                                    </a>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_lat-content">
                                <label class="control-label">
                                  Latitud:
                                  <strong>
                                    <span id="modal_lat">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_lng-content">
                                <label class="control-label">
                                  Longitud:
                                  <strong>
                                    <span id="modal_lng">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_gb-content">
                                <label class="control-label">
                                  GB restantes:
                                  <strong>
                                    <span id="modal_gb">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_coverage_zone-content">
                                <label class="control-label">
                                  Zona de Cobertura:
                                  <strong>
                                    <span id="modal_coverage_zone">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_total_suspends-content">
                                <label class="control-label">
                                  Cantidad de Suspensiones:
                                  <strong>
                                    <span id="modal_total_suspends">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_date_expire-content">
                                <label class="control-label">
                                  Fecha de expiración:
                                  <strong>
                                    <span id="modal_date_expire">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_n_coord-content">
                                <label class="control-label">
                                  Cambio de coordenadas:
                                  <strong>
                                    <span id="modal_n_coord">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_sim_swap">
                                <label class="control-label">
                                  SIM SWAP:
                                  <strong>
                                    <span id="modal_n_swap">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_is_reduce">
                                <label class="control-label">
                                  Reducción de Vel.:
                                  <strong>
                                    <span id="is-reduce">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_total_gb_ret">
                                <label class="control-label">
                                  Total GB de Retención:
                                  <strong>
                                    <span id="modal_t_gbret">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                              <div class="col-md-6" id="modal_rest_gb_ret">
                                <label class="control-label">
                                  GB de Retención Restantes:
                                  <strong>
                                    <span id="modal_r_gbret">
                                    </span>
                                  </strong>
                                </label>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div id="compensation-cont" style="display: none;">
                          <hr/>
                          <h4 class="modal-title">
                            Bono de compensación
                          </h4>
                          <hr/>
                          <div class="row">
                            <div class="col-md-12">
                              <!--datos de netwey-->
                              <div class="row">
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Descripción:
                                    <strong>
                                      <span id="modal_compensation_description">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    Expiración:
                                    <strong>
                                      <span id="modal_compensation_expiredate">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    MB Compensados:
                                    <strong>
                                      <span id="modal_compensation_totalmb">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                                <div class="col-md-6">
                                  <label class="control-label">
                                    MB Restantes:
                                    <strong>
                                      <span id="modal_compensation_remainingmb">
                                      </span>
                                    </strong>
                                  </label>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div id="service-t-cont">
                          <hr/>
                          <h4 class="modal-title">
                            Servicios activos
                          </h4>
                          <hr/>
                          <div class="row">
                            <div class="col-md-12">
                              <!--servicio de telefonia-->
                              <div class="row" id="services-list">
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row justify-content-center">
                          <div class="col-lg-4 col-md-6 col-12 p-t-10">
                            <button class="btn btn-info btn-md button" id="btn-refresh" onclick="refreshData(false)" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                          @if(!empty($canSusp) && $canSusp)
                          <div class="col-lg-4 col-md-6 col-12 p-t-10">
                            <button class="btn btn-danger btn-md button" id="modal_suspend_btn" onclick="suspend()" type="button">
                              <font size="1">
                                Suspender
                              </font>
                            </button>
                            <button class="btn btn-primary btn-md button" id="modal_activate_btn" onclick="activate()" type="button">
                              <font size="1">
                                Activar
                              </font>
                            </button>
                          </div>
                          <div class="col-lg-4 col-md-6 col-12 p-t-10">
                            <button class="btn btn-danger btn-md button" id="modal_suspend_theft_btn" onclick="suspendTheft()" type="button">
                              <font size="1">
                                Susp. por robo o extravío
                              </font>
                            </button>
                            {{--
                            <button class="btn btn-primary btn-md button" id="modal_activate_btn" onclick="activate()" type="button">
                              <font size="1">
                                Activar
                              </font>
                            </button>
                            --}}
                          </div>
                          @endif
                            @if(!empty($canSuspPar) && $canSuspPar)
                          <div class="col-lg-4 col-md-6 col-12 p-t-10" id="content-barring">
                            <button class="btn btn-danger btn-md button" id="modal_suspend_par_btn" onclick="barring()" type="button">
                              <font size="1">
                                Barring.
                              </font>
                            </button>
                            <button class="btn btn-primary btn-md button" id="modal_activate_par_btn" onclick="unbarring()" type="button">
                              <font size="1">
                                Unbarring.
                              </font>
                            </button>
                          </div>
                          @endif
                            @if(!empty($canChcoor) && $canChcoor)
                          <div class="col-lg-4 col-md-6 col-12 p-t-10" id="coord-content">
                            <button class="btn btn-primary btn-md button" data-target="#modal_changecoor" data-toggle="modal" id="btn-chcoor-modal" type="button">
                              <font size="1">
                                Coordenadas
                              </font>
                            </button>
                            <button class="btn btn-primary btn-md button" id="btn-active-chcoor" type="button">
                              <font size="1">
                                Habilitar plan coord
                              </font>
                            </button>
                          </div>
                          @endif
                          <div class="col-lg-4 col-md-6 col-12 p-t-10" id="btn-reduce-content">
                            <button class="btn btn-info btn-md button" onclick="reduceDeactivate()" type="button">
                              Des. Reducción
                            </button>
                          </div>
                        </div>
                      </div>
                      <!--map-->
                      <div class="col-md-6" id="col-2">
                        <h4 class="modal-title">
                          Ubicación
                        </h4>
                        <hr/>
                        <div id="maps" style="width:100%;height:calc(100% - 80px); position: absolute; filter: none ;">
                        </div>
                        <div id="noMap" style="width:100%;height:calc(100% - 80px); position: absolute; z-index: 9; display: none;">
                          <center><h2 style="margin-top: 28vh;"><b>No se posee Datos de Localización</b></h2></center>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div aria-labelledby="recargas-tab" class="tab-pane fade" id="recargas" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Registro de recargas
                      </h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshRecharges()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div aria-labelledby="consumos-tab" class="tab-pane fade" id="consumos" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Consumo de los últimos 30 días
                      </h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables-comp">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshConsumos()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div aria-labelledby="recdisp-tab" class="tab-pane fade" id="recdisp" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Servicios de recargas disponibles</span>
                      </h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables-recdisp">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            {{-- <button class="btn btn-info btn-md button" onclick="refreshRecharges()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button> --}}
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div aria-labelledby="blim-tab" class="tab-pane fade" id="blim" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Códigos Blim
                      </h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables-blim">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshBlim()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div aria-labelledby="coordenadas-tab" class="tab-pane fade" id="coordenadas" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Cambios de Coordenadas
                      </h4>
                      <hr/>
                      <div id="modal-coordinates-container">
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshCoordinate()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div aria-labelledby="compensaciones-tab" class="tab-pane fade" id="compensaciones" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Histórico de Compensaciones
                      </h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables-compensations">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshCompensationsHistory()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div aria-labelledby="salud-tab" class="tab-pane fade" id="salud" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Estado de salud de la red
                      </h4>
                      <hr/>
                      <div class="row justify-content-center">
                        <div class="col-12 col-md-6" id="modal_health_data">
                          <label class="control-label d-block">
                            <p>
                              Fecha:
                              <strong>
                                <span id="modal_health_date">
                                </span>
                              </strong>
                            </p>
                          </label>
                          <label class="control-label d-block">
                            <p>
                              Coordenada de activación (Latitud, Longitud):
                              <strong>
                                (
                                <span id="modal_coord_activate">
                                </span>
                                )
                              </strong>
                            </p>
                          </label>
                          <label class="control-label d-block">
                            <p>
                              Coordenada de trafico (Latitud, Longitud):
                              <strong>
                                (
                                <span id="modal_coord_traffic">
                                </span>
                                )
                              </strong>
                            </p>
                          </label>
                          <label class="control-label d-block">
                            <p>
                              Distancia nodo original al actual:
                              <strong>
                                <span id="modal_health_nodo">
                                </span>
                              </strong>
                            </p>
                          </label>
                        </div>
                        <div class="col-12 col-md-6" id="modal_health_result">
                          <div class="alert alert font-weight-normal" id="modal_health_service" role="alert">
                            <p id="modal_healt_titleppal">
                            </p>
                            <hr/>
                            <h4 class="alert-heading font-weight-bold" id="modal_health_service_title">
                            </h4>
                            <p id="modal_health_service_description">
                            </p>
                            <hr/>
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshHealt()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div aria-labelledby="suspension-tab" class="tab-pane fade" id="suspension" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Detalle de la Suspensión
                      </h4>
                      <hr/>
                      <div class="row justify-content-center">
                        <div class="col-12">
                          <label class="control-label d-block">
                            <p>
                              Estatus:
                              <strong>
                                <span id="modal_suspension_status">
                                </span>
                              </strong>
                            </p>
                          </label>
                          <label class="control-label d-block">
                            <p>
                              Fecha:
                              <strong>
                                <span id="modal_suspension_date">
                                </span>
                              </strong>
                            </p>
                          </label>
                          <label class="control-label d-block">
                            <p>
                              Distancia:
                              <strong>
                                <span id="modal_suspension_distance">
                                </span>
                              </strong>
                            </p>
                          </label>
                          <label class="control-label d-block">
                            <p>
                              Consumo:
                              <strong>
                                <span id="modal_suspension_consumption">
                                </span>
                              </strong>
                            </p>
                          </label>
                        </div>
                      </div>
                      {{--
                      <hr>
                        --}}

                      {{--
                        <div class="row justify-content-center justify-content-md-start">
                          <div class="col-12 col-md-6">
                            <div class="col-8 col-md-4">
                              <button class="btn btn-info btn-md button" onclick="alert('ok');" type="button">
                                <font size="1">
                                  Refrescar
                                </font>
                              </button>
                            </div>
                          </div>
                        </div>
                        --}}
                      </hr>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @if($actRetPer || $actRetExt)
          <div aria-labelledby="retention-tab" class="tab-pane fade" id="retention" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Activar Servicios de Retención
                      </h4>
                      <hr/>
                      <form id="form-actRetentionService" method="POST" name="form-actRetentionService">
                        <div class="row justify-content-center">
                          <div class="col-12 col-md-3">
                            <div class="form-group">
                              <label class="control-label">
                                Servicio
                              </label>
                              <select class="form-control" id="retservice" name="retservice">
                                <option value="">
                                  Seleccione un servicio
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-12 col-md-3">
                            <div class="form-group">
                              <label class="control-label">
                                Motivo
                              </label>
                              <select class="form-control" id="retreason" name="retreason">
                                <option value="">
                                  Seleccione un motivo
                                </option>
                                @foreach ($reasons as $reason)
                                <option class="reason" value="{{$reason->reason}}">
                                  {{$reason->reason}}
                                </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-12 col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                SubMotivo
                              </label>
                              <select class="form-control" id="retsubreason" name="retsubreason">
                                <option value="">
                                  Seleccione un submotivo
                                </option>
                                @foreach ($subreasons as $subreason)
                                <option class="subreason d-none" data-reason="{{$subreason->reason}}" value="{{$subreason->id}}">
                                  {{$subreason->sub_reason}}
                                </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-12 col-md-2">
                            <div class="form-group">
                              <label class="control-label">
                              </label>
                              <button class="btn btn-info btn-md button" onclick="actRetentionService()" type="button">
                                Activar
                              </button>
                            </div>
                          </div>
                        </div>
                      </form>
                      <hr/>
                      <h4 class="modal-title">
                        Histórico de Activaciones de Servicios de Retención
                      </h4>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables-retention-services">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshRetentions()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif
          <div aria-labelledby="promocion-tab" class="tab-pane fade" id="promocion" role="tabpanel">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h4 class="modal-title">
                        Servicios de Promoción
                      </h4>
                      <hr/>
                      <div class="row">
                        <div class="col-md-12">
                          <div id="modal-tables-promo">
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row justify-content-center justify-content-md-start">
                        <div class="col-12 col-md-6">
                          <div class="col-8 col-md-4">
                            <button class="btn btn-info btn-md button" onclick="refreshPromocion()" type="button">
                              <font size="1">
                                Refrescar
                              </font>
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div aria-labelledby="buyback-tab" class="tab-pane fade" id="buyback" role="tabpanel">
                      <div class="row">
                        <div class="col-md-12">
                          <div class="panel panel-info">
                            <div aria-expanded="true" class="panel-wrapper collapse in">
                              <div class="panel-body">
                                <div class="col-md-12" hidden="" id="last-call-content">
                                  <h4 class="modal-title">
                                    Última llamada:
                                  </h4>
                                  <hr/>
                                  <label class="control-label d-block">
                                    <p>
                                      Fecha:
                                      <strong>
                                        <span id="last-call-buyback">
                                        </span>
                                      </strong>
                                    </p>
                                  </label>
                                  <label class="control-label d-block">
                                    <p>
                                      Acepto compra:
                                      <strong>
                                        <span id="last-status-buyback">
                                        </span>
                                      </strong>
                                    </p>
                                  </label>
                                  <label class="control-label d-block">
                                    <p>
                                      Comentario:
                                      <strong>
                                        <span id="last-comment-buyback">
                                        </span>
                                      </strong>
                                    </p>
                                  </label>
                                </div>
                                <h4 class="modal-title">
                                  Guardar llamada
                                </h4>
                                <hr/>
                                <form id="form-buyback" method="POST" name="form-buyback">
                                  <div class="form-group">
                                    <label class="form-check-label bt-switch">
                                      <input class="form-check-input" id="answer-buyback" name="answer-buyback" type="checkbox" value="Y"/>
                                      Respondio la llamada
                                    </label>
                                  </div>
                                  <div class="form-group">
                                    <label class="form-check-label bt-switch">
                                      <input class="form-check-input" id="acept-buyback" name="acept-buyback" type="checkbox" value="Y"/>
                                      Acepto la compra
                                    </label>
                                  </div>
                                  <div class="form-group">
                                    <label class="form-check-label bt-switch">
                                      Comentario:
                                    </label>
                                    <div style="padding-left: 14px; padding-top: 5px;">
                                      <textarea class="form-control" id="comment-buyback" name="comment-buyback" rows="5">
                                      </textarea>
                                    </div>
                                  </div>
                                  <div class="form-group">
                                    <div class="col-md-3">
                                      <button class="btn btn-info btn-md button" onclick="saveCallBuyBack()" type="button">
                                        <font size="1">
                                          Guardar
                                        </font>
                                      </button>
                                    </div>
                                  </div>
                                </form>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          {{--Modal cambio de coordenadas--}}
          <div class="modal fade" id="modal_changecoor" role="dialog">
            <div class="modal-dialog" id="modal02">
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal" type="button">
                    ×
                  </button>
                  <h4 class="modal-title">
                    Cambiar coordenadas del cliente
                  </h4>
                </div>
                <div class="modal-body">
                  <form action="api/client/updatelatlng" id="change_coor_form" method="POST">
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Latitud
                            </label>
                            <input class="form-control" id="latitud" name="lat" placeholder="Ingrese la nueva latitud" type="text"/>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Longitud
                            </label>
                            <input class="form-control" id="longitud" name="lng" placeholder="Ingrese la nueva longitud" type="text"/>
                          </div>
                        </div>
                      </div>
                      <input class="form-control" hidden="" id="dn_netwey" name="msisdn" placeholder="Ingrese la nueva longitud" type="text"/>
                      <div class="form-actions modal-footer">
                        <button class="btn btn-success" onclick="changecoor();" type="submit">
                          <i class="fa fa-check">
                          </i>
                          Guardar
                        </button>
                        <button class="btn btn-default" data-dismiss="modal" id="modal_close_btn" type="button">
                          Cancelar
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          {{--Modal actualización de banda28--}}
          <div class="modal fade" id="modal_updateb28" role="dialog">
            <div class="modal-dialog" id="modal02">
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <button class="close" data-dismiss="modal" type="button">
                    ×
                  </button>
                  <h4 class="modal-title">
                    Actualizar DN a banda28
                  </h4>
                </div>
                <div class="modal-body">
                  <form action="api/client/updateB28" id="updateB28_form" method="POST">
                    <div class="modal-body">
                      <div class="row">
                        <div class="col-md-12">
                          <p>
                            Para realizar esta acción debes ingresar o verificar el IMEI del teléfono del cliente,
                            recuerda que para obtener el IMEI se debe marcar
                            <b>
                              *#06#
                            </b>
                            en el teléfono
                          </p>
                        </div>
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="control-label">
                              IMEI
                            </label>
                            <input class="form-control" id="imei_form" name="imei_form" placeholder="Ingrese el IMEI del teléfono" type="number"/>
                          </div>
                        </div>
                      </div>
                      <input class="form-control" hidden="true" id="msisdn_imei" name="msisdn_imei" type="text"/>
                      <div class="form-actions modal-footer">
                        <button class="btn btn-success" onclick="updateB28();" type="submit">
                          <i class="fa fa-check">
                          </i>
                          Actualizar
                        </button>
                        <button class="btn btn-default" data-dismiss="modal" type="button">
                          Cancelar
                        </button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
          @if($requestPort)
          {{--Tab de portabilidad--}}
          <div aria-labelledby="portability-tab" class="tab-pane fade" id="portability" role="tabpanel">
            <div class="row">
              <div class="col-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <div class="col-12">
                        <h4 class="modal-title">
                          Solicitud de portabilidad:
                        </h4>
                        <hr/>
                      </div>
                      <div class="col-12" id="blockPortability">
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</div>
@include('partials.widgets.clients_updateForm')
<script>
  refreshHealt = () => getHealthNetwork($('#dn_netwey').val());

  refreshCoordinate = () => getCoordinatesChanges($('#dn_netwey').val());

  refreshCompensations = () => getCompensationsEstatus($('#dn_netwey').val());

  refreshCompensationsHistory = () => getview('client/datatable-compensations/' + ($('#dn_netwey').val()), 'modal-tables-compensations');

  refreshRecharges = () => getview('client/datatable/' + ($('#dn_netwey').val()), 'modal-tables');

  refreshConsumos = () => getview('client/datatable-comp/' + ($('#dn_netwey').val()), 'modal-tables-comp');

  refreshBuyBack = () => getBuyBack($('#dn_netwey').val());

  refreshRecDisp = () => getview('client/datatable-recdisp/' + ($('#dn_netwey').val()), 'modal-tables-recdisp',false);

  refreshBlim = () => getview('client/datatable-blim/' + ($('#dn_netwey').val()), 'modal-tables-blim');

  refreshSuspension = () => getSuspensionDetails('52' + $('#dn_netwey').val());

  refreshRetentions = () => getview('client/datatable-retention-services/' + ($('#dn_netwey').val()), 'modal-tables-retention-services');

  refreshPromocion = () => getview('client/datatable-promociones/' + ($('#dn_netwey').val()), 'modal-tables-promo');

  $('#recargas-tab').on('click', () => {
    if ($('#modal-tables').html().trim() == "")
      refreshRecharges();
  })

  $('#consumos-tab').on('click', () => {
    if ($('#modal-tables-comp').html().trim() == "")
      refreshConsumos();
  })

  $('#recdisp-tab').on('click', () => {
    if ($('#modal-tables-recdisp').html().trim() == "")
      refreshRecDisp();
  })

  $('#blim-tab').on('click', () => {
    if ($('#modal-tables-blim').html().trim() == "")
      refreshBlim();
  })

  $('#salud-tab').on('click', () => {
    if ($('#modal_health_service_title').html().trim() == "")
      refreshHealt();
  })

  $('#coordenadas-tab').on('click', () => {
    if ($('#modal-coordinates-container').html().trim() == "")
      refreshCoordinate();
  })

  $('#compensaciones-tab').on('click', () => {
    if ($('#modal-tables-compensations').html().trim() == "")
      refreshCompensationsHistory();
  })

  $('#suspension-tab').on('click', () => {
    if ($('#modal_suspension_status').html().trim() == "")
      refreshSuspension();
  })

  $('#retention-tab').on('click', () => {
    refreshRetentions();
  })

  $('#buyback-tab').on('click', () => {
    refreshBuyBack();
  })

  $('#retreason').on('change', () => {
    reason = $('#retreason').val();
    $('option.subreason[data-reason!="' + reason + '"]').addClass('d-none');
    $('option.subreason[data-reason="' + reason + '"]').removeClass('d-none');
    $('#retsubreason').val("");

  });

  $('#detail').on('show.bs.modal', function(e) {

    $('#modal-coordinates-container').html("");
    $('#modal_health_service_title').html("");
    $('#modal-tables-compensations').html("");
    $('#modal-tables-comp').html("");
    $('#modal-tables').html("");
    $('#modal-tables-blim').html("");
    $('#modal-tables-recdisp').html("");

    $('#modal_suspension_status').html('');
    $('#modal_suspension_date').html('');
    $('#modal_suspension_distance').html('');
    $('#modal_suspension_consumption').html('');

    $('#detalles-tab').trigger('click');
    //$(".preloader").fadeOut();
    $('#last-call-content').attr('hidden', true);
    $('#answer-buyback').attr("checked", null);
    $('#acept-buyback').attr("checked", null);
    $('#comment-buyback').val('');

    $('#retservice').val("");
    $('#retreason').val("");
    $('option.subreason[data-reason!=""]').addClass('d-none');
    $('#retsubreason').val("");

    // $('#retservice').selectize()[0].selectize.clear();
    // $('#retreason').selectize()[0].selectize.clear();

    refreshCompensations();
  });
  @if($actRetPer || $actRetExt)
  $('#detail').on('hidden.bs.modal', function(e) {
    $("#retservice option[value!='']").remove();
  });
  @endif
</script>
<script src="js/client/main.js?v=3.0">
</script>
