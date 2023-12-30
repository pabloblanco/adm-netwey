@php
  $asignPermision = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A1V-G1V')
      $asignPermision = $policy->value;
  }
@endphp
@if ($asignPermision > 0)
  <div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Asignación de inventario a usuarios</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/islim/">Dashboard</a></li>
                <li class="active">Asignar inv. coord.</li>
            </ol>
        </div>
    </div>
    <div class="row">
      <div class="col-md-8">
        <div class="white-box">
          <div class="row">
            <div class="col-md-12">
              <div class="alert alert-success m-t-10" style="padding: 5px;">
                A los usuarios <b>vendedores</b> solo se les puede asignar inventario si se encuentran sin inventario activo.
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">Usuario</label>
                <select id="seller" name="seller" class="form-control">
                  <option value="">Seleccione un Usuario</option>
                </select>
                <label class="control-label" id="error_seller"></label>
              </div>
            </div>
            <div class="col-md-6 asign-inv-content">
              <div class="container-fluid">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-check">
                      <label class="form-check-label bt-switch">
                        <input type="checkbox" id="inventory_manual_assign_check" class="form-check-input"> Asignar manualmente
                      </label>
                    </div>
                  </div>
                  <hr>
                  <div class="col-md-12" id="inventory_file_container">
                    <div class="form-group">
                      <label class="control-label">Productos</label>
                      <input type="file" id="inventory_file" name="inventory_file" class="form-control-file">
                      <label class="control-label" id="error_inventory_file"></label>
                    </div>
                  </div>
                  <div class="col-md-12" id="inventory_select_container">
                    <div class="form-group">
                      <label class="control-label">Productos</label>
                      <select id="inventory_select" name="inventory_select" class="form-control" multiple>
                        <option value="">Seleccione el(los) producto(s)</option>
                        {{--@foreach ($inventories as $inventory)
                          <option value="{{$inventory->id}}">{{$inventory->title}}: {{$inventory->msisdn}}</option>
                        @endforeach--}}
                      </select>
                      <label class="control-label" id="error_inventory_select"></label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-2 asign-inv-content">
              <button class="btn btn-success" onclick="save();">Asignar</button>
            </div>

            <div class="col-md-12" id="alert-dis" hidden="true">
              <div class="alert alert-warning m-t-10" style="padding: 5px;">
                Usuario en proceso de baja.
              </div>
            </div>
          </div>
          <hr>
          <div class="row">
            <div class="col-md-12" id="inventory_detail_container">
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="white-box">
          <div class="row">
            <div class="col-md-12">
              <header>
                Reporte de asignación
              </header>
            </div>
          </div>
          <hr>
          <div id="notification_area">

          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="js/sellerinventories/main.js?v=1.6"></script>
@else
  <h3>Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo</h3>
@endif