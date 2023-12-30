@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  $importPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'ADP-UIW')
      $accessPermission = $policy->value;

    if ($policy->code == 'ADP-UIW')
      $importPermission = $policy->value;

    if ($policy->code == 'ADP-CDP')
      $addPermission = $policy->value;

    if ($policy->code == 'ADP-UDP')
      $editPermission = $policy->value;

    if ($policy->code == 'ADP-DDP')
      $delPermission = $policy->value;
  }
@endphp
@if ($accessPermission > 0)
<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button class="close" id="modal_close_x" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Crear inventario general
        </h4>
      </div>
      <div class="modal-body">
        <!--asda-->
        <form action="api/inventories/store" id="inventory_form" method="POST" data-id='null' data-type='null'>
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h3 class="box-title">
                        Información general
                      </h3>
                      <hr/>
                      @if (count($object['inventories']) < 0)
                      <div class="row" style="display: none;">
                        <div class="col-md-6">
                          <div class="form-check">
                            <label class="form-check-label bt-switch">
                              <input class="form-check-input" id="cb_parent_id_container" type="checkbox">
                                Es un subinventario?
                              </input>
                            </label>
                          </div>
                        </div>
                        <div class="col-md-6" id="parent_id_container">
                          <div class="form-group">
                            <label class="control-label">
                              Inventarios
                            </label>
                            <select class="form-control" id="parent_id" name="parent_id" placeholder="Seleccionar inventario...">
                              <option value="">
                                Seleccionar inventario...
                              </option>
                              @foreach ($object['inventories'] as $inventory)
                              <option value="{{ $inventory->id }}">
                                Inventario N° {{ $inventory->id }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      @endif
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Productos
                            </label>
                            <select class="form-control" id="inv_article_id" name="inv_article_id" placeholder="Seleccionar producto...">
                              <option value="">
                                Seleccionar producto...
                              </option>
                              @foreach ($object['products'] as $product)
                              <option data-type="{{ $product->artic_type }}" value="{{ $product->id }}">
                                {{ $product->title }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        @if(($importPermission > 0))
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Bodegas
                            </label>
                            <select class="form-control" id="warehouses_id" name="warehouses_id" placeholder="Seleccionar bodega...">
                              <option value="">
                                Seleccionar bodega...
                              </option>
                              @foreach ($object['warehouses'] as $warehouse)
                              <option value="{{ $warehouse->id }}">
                                {{ $warehouse->name }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        @endif
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">
                              Serial
                            </label>
                            <input class="form-control" id="serial" name="serial" placeholder="Ingresar n° de Serial" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">
                              MSISDN
                            </label>
                            <input class="form-control" id="msisdn" name="msisdn" placeholder="Ingresar MSISDN" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-4" id="iccid-container">
                          <div class="form-group">
                            <label class="control-label">
                              ICCID
                            </label>
                            <input class="form-control" id="iccid" name="iccid" placeholder="Ingresar ICCID" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-3" hidden="">
                          <div class="form-group">
                            <label class="control-label">
                              IMSI
                            </label>
                            <input class="form-control" id="imsi" name="imsi" placeholder="Ingresar IMSI" type="text" value="0">
                            </input>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label" id="imei-label">
                              IMEI
                            </label>
                            <input class="form-control" id="imei" name="imei" placeholder="Ingresar IMEI" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">
                              Fecha de recepción
                            </label>
                            <div class="input-group">
                              <input class="form-control" id="date_reception" name="date_reception" placeholder="Fecha de recepción" type="text">
                                <span class="input-group-addon">
                                  <i class="icon-calender">
                                  </i>
                                </span>
                              </input>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">
                              Fecha de envío
                            </label>
                            <div class="input-group">
                              <input class="form-control" id="date_sending" name="date_sending" placeholder="Fecha de envío" type="text">
                                <span class="input-group-addon">
                                  <i class="icon-calender">
                                  </i>
                                </span>
                              </input>
                            </div>
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">
                              Precio pagado
                            </label>
                            <input class="form-control" id="price_pay" name="price_pay" placeholder="Precio pagado" type="text">
                            </input>
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6" id="usr_col">
                          <div class="form-group">
                            <label class="control-label">
                              Usuario (email)
                            </label>
                            <input class="form-control" id="user_email" name="user_email" placeholder="ejemplo@email.com" type="email">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-6" id="obs_col">
                          <div class="form-group">
                            <label class="control-label">
                              Observaciones
                            </label>
                            <input class="form-control" id="obs" name="obs" placeholder="Observaciones" type="text">
                            </input>
                          </div>
                        </div>
                        {{--
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">
                              Status
                            </label>
                            <select class="form-control" id="status" name="status">
                              <option value="A">
                                Activo
                              </option>
                              <option value="I">
                                Inactivo
                              </option>
                              <option value="Trash">
                                Eliminado
                              </option>
                            </select>
                          </div>
                        </div>
                        --}}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
              <button class="btn btn-success" onclick="save();" type="submit">
                <i class="fa fa-check">
                </i>
                Guardar
              </button>
              <button class="btn btn-default" id="modal_close_btn" type="button">
                Close
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Detalles de Producto
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
          Inventario de productos
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <form id="file_form">
    @if(($importPermission > 0 && $addPermission > 0))
    <div class="row">
      <label class="control-label">
        Importacion por archivos CSV
      </label>
      <br/>
      <div class="col-md-4">
        <input id="csv" name="csv" type="file"/>
      </div>
      <div class="col-md-2">
        <button class="btn btn-success" id="fileup">
          <i class="fa fa-check">
          </i>
          Guardar
        </button>
      </div>
    </div>
    @endif
  </form>
  <hr/>
  <div class="row">
    <div class="col-md-12">
      @if($addPermission > 0)
      <label class="control-label">
        Agregar inventario de forma manual
      </label>
      <br/>
      <button class="btn btn-info btn-lg" id="open_modal_btn" type="button">
        Agregar
      </button>
      <hr/>
      @endif
      <div class="table-responsive mb-5">
        <table class="table table-striped" id="myTable">
          <thead>
            <tr>
              <th>
                Acciones
              </th>
              <th>
                Id
              </th>
              <th>
                Producto
              </th>
              <th>
                MSISDN
              </th>
              <th>
                IMEI / MAC
              </th>
              <th>
                Precio
              </th>
              <th>
                Estado
              </th>
            </tr>
          </thead>
          {{--
          <tbody>
            @foreach ($object['inventories'] as $inventory)
            <tr>
              <th>
                @if($editPermission > 0)
                <button class="btn btn-warning btn-md button" onclick="update('{{ $inventory }}')" type="button">
                  Editar
                </button>
                @endif
                    @if($delPermission > 0)
                <button class="btn btn-danger btn-md button" onclick="deleteData('{{ $inventory->id }}', '{{ $inventory->id }}')" type="button">
                  Eliminar
                </button>
                @endif
              </th>
              <th>
                {{ $inventory->id }}
              </th>
              <th>
                {{ $inventory->title }}
              </th>
              <th>
                {{ $inventory->msisdn }}
              </th>
              <th>
                {{ !empty($inventory->imei) ? $inventory->imei : 'N/A' }}
              </th>
              <th>
                {{ $inventory->price_pay }}
              </th>
              @if($inventory->status =='A')
              <th>
                Activo
              </th>
              @else
                    @if ($inventory->status =='I')
              <th>
                Inactivo
              </th>
              @else
              <th>
                Eliminado
              </th>
              @endif
                  @endif
            </tr>
            @endforeach
          </tbody>
          --}}
        </table>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  var editPermission  = "{{$editPermission }}";
  var delPermission  = "{{$delPermission }}";
</script>
<script src="js/inventories/main.js?v=2.1">
</script>
<script src="js/common-modals.js">
</script>
@else
<h3>
  Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo
</h3>
@endif
