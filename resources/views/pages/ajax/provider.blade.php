@php
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A1P-CPV'){ $addPermission  = $policy->value; }
    if ($policy->code == 'A1P-UPV'){ $editPermission = $policy->value; }
    if ($policy->code == 'A1P-DPV'){ $delPermission  = $policy->value; }
  }
@endphp

<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="modal_close_x">&times;</button>
        <h4 class="modal-title">Crear proveedor</h4>
      </div>
      <div class="modal-body">
        <!--asda-->
        <form id="provider_form" action="api/provider/store" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3 class="box-title">Información general</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Nombre</label>
                            <input type="text" id="name" name="name" class="form-control" placeholder="Ingresar Nombre">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Razon social</label>
                            <input type="text" id="business_name" name="business_name" class="form-control" placeholder="Ingresar Razon social">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">R.F.C</label>
                            <input type="text" id="rfc" name="rfc" class="form-control" placeholder="Ingresar numero RFC">
                          </div>
                        </div>
                        <div class="col-md-4" hidden>
                          <div class="form-group">
                            <label class="control-label">I.N.E</label>
                            <input type="text" id="dni" name="dni" class="form-control" placeholder="Ingresar numero INE">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Email</label>
                            <input type="text" id="email" name="email" class="form-control" placeholder="Ingresar Email">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Telefono</label>
                            <input type="text" id="phone" name="phone" class="form-control" placeholder="295-5636363">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Dirección</label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="Ingresar Dirección">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Responsable</label>
                            <input type="text" id="responsable" name="responsable" class="form-control" placeholder="Ingresar Nombre del responsable">
                          </div>
                        </div>
                        <div class="col-md-4"
                          @if (session('user')->platform != 'admin')
                            hidden
                          @endif
                        >
                          <div class="form-group">
                              <label class="control-label">Estado</label>
                              <select id="status" name="status" class="form-control">
                                  <option value="A">Activo</option>
                                  <option value="I">Inactivo</option>
                                  <option value="Trash">Inhabilitado</option>
                              </select>
                          </div>
                        </div>
                      </div>
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
      <h4 class="page-title">Proveedores</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Proveedores</li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      {{-- @foreach (session('user')->policies as $policy) --}}
        {{-- @if ($policy->code == 'A1P-CPV') --}}
          {{-- @if($policy->value > 0) --}}
          @if (($addPermission > 0))
            <button type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button>
          {{-- @else
            <button hidden type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button> --}}
          @endif
        {{-- @endif --}}
      {{-- @endforeach --}}
      <hr>
      @foreach ($providers as $provider)
        <div class="row white-box">
          <div class="col-md-12">
            <div class="card card-outline-primary text-center text-dark">
              <div class="card-block">
                <header>Proveedor R.F.C n°: {{ $provider->dni }}</header>
                <hr>
                <div class="row">
                  <div class="col-md-10">
                    <div class="row">
                      <div class="col-md-6">
                        <label class="control-label">Nombre: {{ $provider->name }}</label>
                      </div>
                      <div class="col-md-6">
                        <label class="control-label">Razon social: {{ $provider->business_name }}</label>
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-4">
                        <label class="control-label">R.F.C: {{ $provider->rfc }}</label>
                      </div>
                      <div hidden class="col-md-4">
                        <label class="control-label">I.N.E: {{ $provider->dni }}</label>
                      </div>
                      <div class="col-md-4">
                        <label class="control-label">Email: {{ $provider->email }}</label>
                      </div>
                      <div class="col-md-4">
                        <label class="control-label">Telefono: {{ $provider->phone }}</label>
                      </div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-6">
                        <label class="control-label">Dirección: {{ $provider->address }}</label>
                      </div>
                      <div class="col-md-6">
                        <label class="control-label">Responsable: {{ $provider->responsable }}</label>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-2">
                    <div class="row">
                      <div class="col-md-12">
                        {{-- @foreach (session('user')->policies as $policy) --}}
                          {{-- @if (($policy->code == 'A1P-UPV') && ($policy->value > 0)) --}}
                          @if (($editPermission > 0))
                            <button type="button" class="btn btn-warning btn-md button" onclick="update('{{ $provider }}')">Editar</button>
                          @endif
                        {{-- @endforeach --}}
                      </div>
                    </div>
                    <br>
                    <div class="row">
                      <div class="col-md-12">
                        {{-- @foreach (session('user')->policies as $policy) --}}
                          {{-- @if (($policy->code == 'A1P-DPV') && ($policy->value > 0)) --}}
                          @if (($delPermission > 0))
                            <button type="button" class="btn btn-danger btn-md button" onclick="deleteData('{{ $provider->dni }}', '{{ $provider->name }}')">Eliminar</button>
                          @endif
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
<script src="js/provider/main.js"></script>
<script src="js/common-modals.js"></script>