@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A1B-RSB')
      $accessPermission = $policy->value;
    if ($policy->code == 'A1B-CSB')
      $addPermission = $policy->value;
    if ($policy->code == 'A1B-USB')
      $editPermission = $policy->value;
    if ($policy->code == 'A1B-DSB')
      $delPermission = $policy->value;
  }
@endphp
@if ($accessPermission > 0)
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Servicios Blim</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="/islim/">Dashboard</a></li>
          <li class="active">Servicios Blim</li>
        </ol>
      </div>
    </div>  
  </div>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        @if($addPermission > 0)
          <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Agregar</button>
        @endif
        <hr>
        <div class="row white-box">
          <div class="table-responsive">
            <table id="myTable" class="table table-striped">
              <thead>
                <tr>
                  @if ($editPermission || $delPermission)
                    <th>Acciones</th>
                  @endif
                  {{-- <th>id</th> --}}
                  <th>SKU</th>
                  <th>Nombre</th>
                  <th>Descripción</th>                
                  <th>Costo</th>                  
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($blimservices as $blimservice)
                  <tr>
                    @if ($editPermission || $delPermission)
                      <th class="row">
                        @if($editPermission > 0)
                          <button type="button" class="btn btn-warning btn-md button" onclick="update('{{ $blimservice }}')">Editar</button>
                        @endif
                        @if($delPermission > 0)
                          <button type="button" class="btn btn-danger btn-md button" onclick="deleteData('{{ $blimservice->id }}','{{ $blimservice->name }}')">Eliminar</button>
                        @endif
                      </th>
                    @endif
                    {{-- <th>{{ $blimservice->id }}</th> --}}
                    <th>{{ $blimservice->sku }}</th>
                    <th>{{ $blimservice->name }}</th>
                    <th>{{ $blimservice->description }}</th>                    
                    <th>
                      {{ empty($blimservice->price)? '0' : $blimservice->price }}
                    </th>                  
                    <th>
                      {{ $blimservice->status == 'A' ? 'Activo' : 'Inactivo' }}
                    </th>                    
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
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
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title">Crear Servicio Blim</h4>
        </div>
        <div class="modal-body">
          <form id="service_form" action="api/blimservices/store" method="POST">
            {{ csrf_field() }}
            <input type="hidden" id="id" name="id" class="form-control">
            <div class="form-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="panel panel-info">
                    <div class="panel-wrapper collapse in" aria-expanded="true">
                      <div class="panel-body">
                        <h3 class="box-title">Informacion general</h3>
                        <hr>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Nombre</label>
                              <input type="text" id="name" name="name" class="form-control" placeholder="Nombre">
                            </div>
                          </div>                          
                       
                          <div class="col-md-8">
                            <div class="form-group">
                              <label class="control-label">Descripción</label>
                              <input type="text" id="description" name="description" class="form-control" placeholder="Breve descripción del servicio">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">SKU</label>
                              <input type="text" id="sku" name="sku" class="form-control" placeholder="SKU">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Costo</label>
                              <input type="number" id="price" name="price" class="form-control" placeholder="Costo">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Estatus</label>
                              <select id="status" name="status" class="form-control">
                                <option value="A">Activo</option>
                                <option value="I">Inactivo</option>
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
                  <button type="button" id="modal_close_btn" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="js/blimservices/main.js?v=2.0"></script>
@else
  <h3>Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo</h3>
@endif