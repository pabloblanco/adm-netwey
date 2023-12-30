@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'BIL-RBC')
      $accessPermission = $policy->value;
    if ($policy->code == 'BIL-RBC')
      $addPermission = $policy->value;
    if ($policy->code == 'BIL-UBC')
      $editPermission = $policy->value;
  }
@endphp
@if ($accessPermission > 0)
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Conceptos de Facturación</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="/islim/">Dashboard</a></li>
          <li class="active">Conceptos de Facturación</li>
        </ol>
      </div>
    </div>  
  </div>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        @if($addPermission > 0)
          {{-- <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Agregar</button> --}}
          <button type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button>
        @endif
        <hr>
        <div class="row white-box">
          <div class="table-responsive">

            <table id="myTable" class="table table-striped">
                <thead>
                    <tr>
                        @if($editPermission > 0)
                        <th id="actionCol">Acción</th>
                        @endif
                        <th>Id</th>
                        <th>Id MisKuentas</th>
                        <th>Descripción</th>
                        <th>Unidad</th>
                        <th>Nombre Unidad</th>
                        <th>Id de Servicio</th>
                        <th>Id de Paquete</th>
                        <th>Clave de Producto</th>
                        <th>Con Envio</th>
                        <th>Es Financiado</th>
                    </tr>
                </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Modal -->
  <div class="modal modalAnimate" id="myModal" role="dialog">
    <div class="modal-dialog" id="modal01">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" id="modal_close_x">&times;</button>
          <h4 class="modal-title">Crear Concepto de Facturación</h4>
        </div>
        <div class="modal-body">
          <form id="billing_form" action="api/billingconcepts/store" method="POST">
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
                              <label class="control-label">Id MisKuentas*</label>
                              <input type="text" id="nro_identification" name="nro_identification" class="form-control" placeholder="Identificador en MisKuentas">
                            </div>
                          </div>                          
                       
                          <div class="col-md-8">
                            <div class="form-group">
                              <label class="control-label">Descripción*</label>
                              <input type="text" id="description" name="description" class="form-control" placeholder="Descripción del concepto">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Clave de Producto*</label>
                              <input type="text" id="product_key" name="product_key" class="form-control" placeholder="Clave de Producto">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Unidad*</label>
                              <input type="text" id="unit_key" name="unit_key" class="form-control" placeholder="Clave de Unidad">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Nombre de Unidad</label>
                              <input type="text" id="unit" name="unit" class="form-control" placeholder="Nombre de Unidad">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-3">
                            <div class="form-group">
                              <label class="control-label">Id Servicio*</label>
                              <select id="service_id" name="service_id" class="form-control">
                                <option value="" selected>Seleccione Id de Servicio</option>
                                @foreach ($services as $service)
                                  <option value="{{$service->id}}">{{$service->id}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label class="control-label">Id Paquete</label>
                              <select id="pack_id" name="pack_id" class="form-control">
                                <option value="" selected>Seleccione Id de Paquete</option>
                                @foreach ($packs as $pack)
                                  <option value="{{$pack->id}}">{{$pack->id}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label class="control-label">Con envío*</label>
                              <select id="shipping" name="shipping" class="form-control">
                                <option value="Y">Si</option>
                                <option value="N" selected>No</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <div class="form-group">
                              <label class="control-label">Es Financiado*</label>
                              <select id="is_financed" name="is_financed" class="form-control">
                                <option value="Y">Si</option>
                                <option value="N" selected>No</option>
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
  <script src="js/billingconcepts/main.js?v={{date('YmdHis')}}"></script>
  <script src="js/common-modals.js"></script>
@else
  <h3>Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo</h3>
@endif