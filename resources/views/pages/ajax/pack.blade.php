@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  $asignPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A3P-RPQ')
      $accessPermission = $policy->value;
    if ($policy->code == 'A3P-CPQ')
      $addPermission = $policy->value;
    if ($policy->code == 'A3P-UPQ')
      $editPermission = $policy->value;
    if ($policy->code == 'A3P-DPQ')
      $delPermission = $policy->value;
    if ($policy->code == 'A3P-GPS')
      $asignPermission = $policy->value;
  }
@endphp
@if (($accessPermission > 0))
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Paquetes</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="/islim/">
            Dashboard
          </a>
        </li>
        <li class="active">
          Paquetes
        </li>
      </ol>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
        @if(($addPermission > 0))
        <button class="btn btn-info btn-lg" data-target="#myModal" data-toggle="modal" id="open_modal_btn" type="button">
          Agregar
        </button>
        @else
        <button class="btn btn-info btn-lg" data-target="#myModal" data-toggle="modal" hidden="" id="open_modal_btn" type="button">
          Agregar
        </button>
        @endif
        <hr>
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="myTable">
              <thead>
                <tr>
                  <th>
                    Acciones
                  </th>
                  <th>
                    Id
                  </th>
                  <th>
                    Titulo
                  </th>
                  <th>
                    Servicio
                  </th>
                  <th>
                    Producto
                  </th>
                  <th>
                    Tipo
                  </th>
                  <th>
                    Es banda 28
                  </th>
                  <th>
                    Abono
                  </th>
                  <th>
                    Portabilidad
                  </th>
                  <th>
                    WEB
                  </th>
                  <th>
                    Cupon MP
                  </th>
                  <th>
                    Precio
                  </th>
                  <th>
                    PayJoy
                  </th>
                  <th>
                    Servicio Promocional
                  </th>
                  <th>
                    Coordinación
                  </th>
                  <th>
                    Estatus
                  </th>
                </tr>
              </thead>
              <tbody>
                @foreach($packs as $pack)
                <tr>
                  <th class="row">
                    @if(($asignPermission > 0))
                    <button class="btn btn-info btn-md button d-block" onclick="getview('pack/detail/{{ $pack->id }}')" type="button">
                      Asociar
                    </button>
                    @endif
                      @if(($editPermission > 0))
                    <button class="btn btn-warning btn-md button d-block" onclick="update('{{ $pack }}')" type="button">
                      Editar
                    </button>
                    @endif
                      @if(($delPermission > 0))
                    <button class="btn btn-danger btn-md button d-block" onclick="deleteData('{{ $pack->id }}', '{{ $pack->title }}')" type="button">
                      Eliminar
                    </button>
                    @endif
                  </th>
                  <th>
                    {{ $pack->id }}
                  </th>
                  <th>
                    {{ $pack->title }}
                  </th>
                  <th>
                    {{ !empty($pack->service) ? $pack->service : 'N/A' }}
                  </th>
                  <th>
                    {{ !empty($pack->product) ? $pack->product : 'N/A' }}
                  </th>
                  <th>
                    @switch($pack->pack_type)
                        @case('H') Internet hogar @break
                        @case('M') MIFI Huella Nacional @break
                        @case('MH') MIFI Huella Altan @break
                        @case('T') Telefonía @break
                        @case('F') Fibra @break
                        @default Internet hogar
                      @endswitch
                  </th>
                  <th>
                    {{ !empty($pack->is_band_twenty_eight)? ($pack->is_band_twenty_eight == 'Y' ? 'Si' : 'No') : 'N/A' }}
                  </th>
                  <th>
                    {{ $pack->sale_type == 'Q' ? 'Si' : 'No' }}
                  </th>
                  <th>
                    {{ $pack->is_portability == 'Y' ? 'Si' : 'No' }}
                  </th>
                  <th>
                    {{ $pack->view_web == 'Y' ? 'Si' : 'No' }}
                  </th>
                  <th>
                    {{ $pack->acept_coupon == 'Y' ? 'Si' : 'No' }}
                  </th>
                  <th>
                    {{ !empty($pack->total_price) ? '$'.$pack->total_price : 'N/A' }}
                  </th>
                  <th>
                    {{ $pack->is_visible_payjoy == 'Y' ? 'Si' : 'No' }}
                  </th>
                  <th>
                    {{ !empty($pack->service_prom_name) ? $pack->service_prom_name : 'N/A' }}
                  </th>
                  <th>
                    {{ count($pack->esquemas) ? 'Si' : 'No' }}
                  </th>
                  <th>
                    {{ $pack->status == 'A' ? 'Activo' : 'Inactivo' }}
                  </th>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </hr>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" data-dismiss="modal" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Crear Paquete
        </h4>
      </div>
      <div class="modal-body">
        <form action="api/pack/store" id="pack_form" method="POST">
          <input class="form-control" id="id" name="id" type="hidden"/>
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h3 class="box-title">
                        Informacion general
                      </h3>
                      <hr>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Titulo
                              </label>
                              <input class="form-control" id="title" name="title" placeholder="Título..." type="text"/>
                            </div>
                          </div>
                          <div class="col-md-8">
                            <div class="form-group">
                              <label class="control-label">
                                Descripción
                              </label>
                              <input class="form-control" id="description" name="description" placeholder="Descripción..." type="text"/>
                            </div>
                          </div>
                          <input id="price_arti" name="price_arti" type="hidden" value="0"/>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Fecha de Inicio
                              </label>
                              <div class="input-group">
                                <input autocomplete="off" class="form-control" id="date_ini" name="date_ini" placeholder="yyyy-mm-dd..." type="text"/>
                                <span class="input-group-addon">
                                  <i class="icon-calender">
                                  </i>
                                </span>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Fecha de Finalización
                              </label>
                              <div class="input-group">
                                <input autocomplete="off" class="form-control" id="date_end" name="date_end" placeholder="yyyy-mm-dd..." type="text"/>
                                <span class="input-group-addon">
                                  <i class="icon-calender">
                                  </i>
                                </span>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Tipo
                              </label>
                              <select class="form-control" id="pack_type" name="pack_type">
                                <option value="H">
                                  Internet Hogar
                                </option>
                                <option value="T">
                                  Telefonía
                                </option>
                                <option value="M">
                                  MIFI Huella Nacional
                                </option>
                                <option value="MH">
                                  MIFI Huella Altan
                                </option>
                                <option value="F">
                                  Fibra
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4" id="port-content">
                            <div class="form-group">
                              <label class="control-label">
                                Portabilidad
                              </label>
                              <select class="form-control" id="is_portability" name="is_portability">
                                <option value="N">
                                  No
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4" id="nbte-content">
                            <div class="form-group">
                              <label class="control-label">
                                Es banda 28
                              </label>
                              <select class="form-control" id="is_band_twenty_eight" name="is_band_twenty_eight">
                                <option class="d-none" disabled="true" selected="" value="">
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                                <option value="N">
                                  No
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Visible en Tienda
                              </label>
                              <select class="form-control" id="view_web" name="view_web">
                                <option value="N">
                                  No
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4" id="type-pay-content">
                            <div class="form-group">
                              <label class="control-label">
                                Tipo de venta
                              </label>
                              <select class="form-control" id="sale_type" name="sale_type">
                                <option selected="" value="N">
                                  Normal
                                </option>
                                <option value="Q">
                                  A cuotas
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-8" id="desc-store-contet">
                            <div class="form-group">
                              <label class="control-label">
                                Descripción WEB
                              </label>
                              <input class="form-control" id="desc_web" name="desc_web" placeholder="Descripción para la pagina de ventas." type="text"/>
                            </div>
                          </div>
                          <div class="col-md-4" id="content-acept-coupon">
                            <div class="form-group">
                              <label class="control-label">
                                Aceptar cupon MP
                              </label>
                              <select class="form-control" id="acept_coupon" name="acept_coupon">
                                <option value="N">
                                  No
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Servicio Promocional
                              </label>
                              <select class="form-control" id="service_prom_id" name="service_prom_id">
                                <option value="">
                                  Sin Servicio Promocional
                                </option>
                                @foreach ($services_proms as $sp)
                                <option value="{{$sp->id}}">
                                  {{$sp->name}}
                                </option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label" title="Opciones de pago por financiaciación">
                                Venta con financiación
                              </label>
                              <select class="form-control" id="is_visible_financiacion" name="is_visible_financiacion">
                                <option value="N">
                                  No
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-12 px-3" id="block_finaciado">
                            <div class="card mb-4">
                              <div class="card-header">
                                Opciones de venta con financiación
                              </div>
                              <div class="card-body mt-2">
                                <div class="row">
                                  <div class="col-md-3 col-sm-6 col-12 px-4">
                                    <div class="form-group">
                                      <label class="control-label">
                                        Visible por PayJoy
                                      </label>
                                      <select class="form-control" id="is_visible_payjoy" name="is_visible_payjoy">
                                        <option value="N">
                                          No
                                        </option>
                                        <option value="Y">
                                          Si
                                        </option>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-12 px-4">
                                    <div class="form-group">
                                      <label class="control-label">
                                        Visible por Coppel
                                      </label>
                                      <select class="form-control" id="is_visible_coppel" name="is_visible_coppel">
                                        <option value="N">
                                          No
                                        </option>
                                        <option value="Y">
                                          Si
                                        </option>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-12 px-4">
                                    <div class="form-group">
                                      <label class="control-label">
                                        Visible por Paguitos
                                      </label>
                                      <select class="form-control" id="is_visible_paguitos" name="is_visible_paguitos">
                                        <option value="N">
                                          No
                                        </option>
                                        <option value="Y">
                                          Si
                                        </option>
                                      </select>
                                    </div>
                                  </div>
                                  <div class="col-md-3 col-sm-6 col-12 px-4">
                                    <div class="form-group">
                                      <label class="control-label">
                                        Visible por TelmovPay
                                      </label>
                                      <select class="form-control" id="is_visible_telmovPay" name="is_visible_telmovPay">
                                        <option value="N">
                                          No
                                        </option>
                                        <option value="Y">
                                          Si
                                        </option>
                                      </select>
                                    </div>
                                  </div>                                  
                                </div>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4" id="content-is-migration">
                            <div class="form-group">
                              <label class="control-label" id="is-migration-label">
                                Es para migracion MIFI
                              </label>
                              <select class="form-control" id="is_migration" name="is_migration">
                                <option value="N">
                                  No
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4" id="id_esquema_content">
                            <div class="form-group">
                              <label class="control-label">
                                Coordinación
                              </label>
                              <select class="form-control" id="id_esquema" multiple="true" name="id_esquema[]">
                                <option value="">
                                  Seleccione una coordinación
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4" id="content-is-migration">
                            <div class="form-group">
                              <label class="control-label" id="is-migration-label">
                                Validar identidad (Truora)
                              </label>
                              <select class="form-control" id="valid_identity" name="valid_identity">
                                <option value="N">
                                  No
                                </option>
                                <option value="Y">
                                  Si
                                </option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">
                                Estatus
                              </label>
                              <select class="form-control" id="status" name="status">
                                <option value="A">
                                  Activo
                                </option>
                                <option value="I">
                                  Inactivo
                                </option>
                              </select>
                            </div>
                          </div>
                        </div>
                      </hr>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
              <button class="btn btn-success" onclick="save();" type="submit">
                Guardar
              </button>
              <button class="btn btn-default" data-dismiss="modal" id="modal_close_btn" type="button">
                Close
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="js/pack/main.js?v=2.41">
</script>
@else
<h3>
  Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo
</h3>
@endif
