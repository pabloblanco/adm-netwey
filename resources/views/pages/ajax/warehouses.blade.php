@php
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  $assigmentPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A1W-CWH'){ $addPermission  = $policy->value; }
    if ($policy->code == 'A1W-UWH'){ $editPermission = $policy->value; }
    if ($policy->code == 'A1W-DWH'){ $delPermission  = $policy->value; }
    if ($policy->code == 'A1W-AWV'){ $assigmentPermission  = $policy->value; }
  }
@endphp

<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="modal_close_x">&times;</button>
        <h4 class="modal-title">Crear Bodega</h4>
      </div>
      <div class="modal-body">
        <!--asda-->
        <form id="warehouse_form" action="api/warehouses/store" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3 class="box-title">Información general</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Nombre</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Ingresar nombre">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Telefono</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="Ingresar teléfono">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Dirección</label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="Ingresar dirección">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Usuario(s) responsable(s)</label>
                            <input type="hidden" id="users_email_list" name="users_email_list">
                            {{-- @foreach (session('user')->policies as $policy) --}}
                              {{-- @if ($policy->code == 'A1W-AWV') --}}
                                {{-- @if($policy->value > 0) --}}
                                @if (($addPermission > 0))
                                  <select id="users_email" name="users_email" multiple class="form-control" placeholder="Seleccionar usuario(s)...">
                                    @foreach ($users as $user)
                                      <option value="{{ $user->email }}">{{ $user->name }} {{ $user->last_name }}</option>
                                    @endforeach
                                  </select>
                                @else
                                  <select id="users_email" name="users_email" multiple class="form-control" disabled placeholder="Seleccionar usuario(s)...">
                                    @foreach ($users as $user)
                                      <option value="{{ $user->email }}">{{ $user->name }} {{ $user->last_name }}</option>
                                    @endforeach
                                  </select>
                                @endif
                              {{-- @endif --}}
                            {{-- @endforeach --}}
                          </div>
                        </div>
                        <div class="col-md-3">
                          <label class="control-label">Organización</label>
                            <select name="org" id="org" class="form-control">
                              @if(session('user.profile.id') == (1 || 2))
                                <option value="">Maestra</option>
                              @endif
                              @foreach($orgs as $org)
                                <option value="{{$org->id}}">{{$org->business_name}}</option>
                              @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Status</label>
                            <select id="status" name="status" class="form-control">
                              <option value="A">Activo</option>
                              <option value="I">Inactivo</option>
                              <option value="Trash">Inhabilitado</option>
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Bodega para proveedor logístico</label>
                            <select id="group_log" name="group_log" class="form-control">
                              <option value="">No</option>
                              <option value="3">Voywey</option>
                              <option value="1">Prova</option>
                              <option value="2">99minutos</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Calle</label>
                            <input type="text" id="route" name="route" class="form-control" placeholder="Ingresar calle">
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Número de calle</label>
                            <input type="text" id="street_n" name="street_n" class="form-control" placeholder="Ingresar número de calle">
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Población</label>
                            <input type="text" id="neighb" name="neighb" class="form-control" placeholder="Ingresar población">
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Localidad</label>
                            <input type="text" id="locality" name="locality" class="form-control" placeholder="Ingresar localidad">
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Colonia</label>
                            <input type="text" id="sublocality" name="sublocality" class="form-control" placeholder="Ingresar colonia">
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Estado</label>
                            <input type="text" id="state" name="state" class="form-control" placeholder="Ingresar estado">
                          </div>
                        </div>

                        <div class="col-md-4 group-log" hidden="true">
                          <div class="form-group">
                            <label class="control-label">Código postal</label>
                            <input type="text" id="pc" name="pc" class="form-control" placeholder="Ingresar código postal">
                          </div>
                        </div>
                      </div>

                      <input type="hidden" id="lat" name="lat" class="form-control" placeholder="Latitud" value="0">
                      <input type="hidden" id="lng" name="lng" class="form-control" placeholder="Longitud" value="0">
                      <input type="hidden" id="position" name="position" class="form-control" placeholder="Posición" value="POINT(0,0)">
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
              <button type="submit" class="btn btn-success" onclick="save();"> <i class="fa fa-check"></i> Guardar</button>
              <button type="button" id="modal_close_btn" class="btn btn-default">Close</button>
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
      <h4 class="page-title">Bodegas</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Bodegas</li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      @php
      $countwh = App\UserWarehouse::uwhcount(session('user.email'));
      @endphp
      {{-- @foreach (session('user')->policies as $policy) --}}
        {{-- @if ($policy->code == 'A1W-CWH') --}}
          {{-- @if($policy->value > 0 && $countwh < $policy->value) --}}
          @if($addPermission > 0 && $countwh < $addPermission)
            <button type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button>
          @endif
        {{-- @endif --}}
      {{-- @endforeach --}}
      {{-- <button type="button" id="open_modal_udp" class="hidden" data-toggle="modal" data-target="#myModal"></button> --}}
      <hr>
      @foreach ($warehouses as $warehouse)
        <div class="row white-box">
          <div class="col-md-12">
            <div class="card card-outline-primary text-center text-dark">
              <div class="card-block">
                <header>Almacén {{ $warehouse->name }}</header>
                <header>ID de Almacen {{ $warehouse->id }}</header>
                <hr>
                <div class="row">
                  <div class="col-md-10">
                    <div class="row">
                      <div class="col-md-12">
                        <label class="control-label">Dirección: {{ $warehouse->address }}</label>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <label class="control-label">Telefono: {{ $warehouse->phone }}</label>
                      </div>
                      <div class="col-md-6">
                        <label class="control-label">Status: {{ $warehouse->status }}</label>
                      </div>
                    </div>
                    <hr>
                    <div class="row" hidden>
                      <div class="col-md-4">
                        <label class="control-label">Latitud: {{ $warehouse->lat }}</label>
                      </div>
                      <div class="col-md-4">
                        <label class="control-label">Longitud: {{ $warehouse->lng }}</label>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="row">
                      <div class="col-md-12">
                        {{-- @foreach (session('user')->policies as $policy) --}}
                          {{-- @if ($policy->code == 'A1W-UWH') --}}
                            {{-- @if($policy->value > 0) --}}
                            @if (($editPermission > 0))
                              <button type="button" class="btn btn-warning btn-md button" onclick="update('{{ $warehouse }}')">Editar</button>
                            @endif
                          {{-- @endif --}}
                        {{-- @endforeach --}}
                      </div>
                    </div>
                    <br>
                    <div class="row">
                      <div class="col-md-12">
                        {{-- @foreach (session('user')->policies as $policy) --}}
                          {{-- @if ($policy->code == 'A1W-DWH') --}}
                            {{-- @if($policy->value > 0) --}}
                            @if (($delPermission > 0))
                              <button type="button" class="btn btn-danger btn-md button" onclick="deleteData('{{ $warehouse->id }}', '{{ $warehouse->name }}')">Eliminar</button>
                            @endif
                          {{-- @endif --}}
                        {{-- @endforeach --}}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
<script src="js/warehouses/main.js?v=1.2"></script>
<script src="js/common-modals.js"></script>