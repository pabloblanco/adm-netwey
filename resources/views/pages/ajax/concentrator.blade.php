@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  $serversPermission = 0;
  $maxbalance = 0;
  $maxcomissions = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'AMC-RCO')
      $accessPermission = $policy->value;
    if ($policy->code == 'AMC-CCO')
      $addPermission = $policy->value;
    if ($policy->code == 'AMC-UCO')
      $editPermission = $policy->value;
    if ($policy->code == 'AMC-DCO')
      $delPermission = $policy->value;
    if ($policy->code == 'ACS-RCS')
      $serversPermission = $policy->value;
    if ($policy->code == 'AMC-ABC')
      $maxbalance = $policy->value;
    if ($policy->code == 'AMC-ACC')
      $maxcomissions = $policy->value;
  }
@endphp
@if (($accessPermission > 0))
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Concentradores</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="/islim/">Dashboard</a></li>
          <li class="active">Concentradores</li>
        </ol>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-md-12">
        @if (($addPermission > 0))
          <button type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button>
        {{-- @else
          <button hidden type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button> --}}
        @endif
        <hr>
        @if (isset($concentrators))
          @foreach ($concentrators as $concentrator)
            <div class="row white-box">
              <div class="col-md-12">
                <div class="card card-outline-primary text-center text-dark">
                  <div class="card-block">
                    <header>Concentrador n° {{ $concentrator->id }}</header>
                    <hr>
                    <div class="row">
                      <div class="col-md-10">
                        <div class="row">
                          <div class="col-md-4">
                            <label class="control-label">Nombre: {{ $concentrator->name }}</label>
                          </div>
                          <div class="col-md-4">
                            <label class="control-label">R.F.C: {{ $concentrator->rfc }}</label>
                          </div>
                          <div class="col-md-4">
                            <label class="control-label">Email: {{ $concentrator->email }}</label>
                          </div>
                          <div class="col-md-3" style="display: none;">
                            <label class="control-label">I.N.E: {{ $concentrator->dni }}</label>
                          </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-md-3">
                            <label class="control-label">Razon social: {{ $concentrator->business_name }}</label>
                          </div>
                          <div class="col-md-3">
                            <label class="control-label">Telefono: {{ $concentrator->phone }}</label>
                          </div>
                          <div class="col-md-3">
                            <label class="control-label">Balance: {{ $concentrator->balance }}</label>
                          </div>
                          <div class="col-md-3">
                            <label class="control-label">Comision: {{ $concentrator->commissions }}</label>
                          </div>
                        </div>
                        <hr>
                        <div class="row">
                          <div class="col-md-12">
                            <label class="control-label">Dirección: {{ $concentrator->address }}</label>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-2">
                        @if (($serversPermission > 0))
                          <div class="row">
                            <div class="col-md-12">
                              <button type="button" class="btn btn-success btn-md button" onclick="getview('servers/{{$concentrator->id}}');">servidores</button>
                            </div>
                          </div>
                          <br>
                        @endif
                        @if (($editPermission > 0))
                          <div class="row">
                            <div class="col-md-12">
                              <button type="button" class="btn btn-warning btn-md button" onclick="update('{{ $concentrator }}')">Editar</button>
                            </div>
                          </div>
                          <br>
                        @endif
                        @if (($delPermission > 0))
                          <div class="row">
                            <div class="col-md-12">
                              <button type="button" class="btn btn-danger btn-md button" onclick="deleteData('{{ $concentrator->id }}', '{{ $concentrator->name }}')">Eliminar</button>
                            </div>
                          </div>
                        @endif
                      </div>
                    </div>
                    @if (isset($concentrator->apikeys))
                      <footer>
                        <hr>
                        <div class="row">
                          <div class="col-md-12">
                            <label class="control-label">Canal: {{!empty($concentrator->id_channel)? $concentrator->channel->name : 'Sin Canal'}}</label>
                          </div>
                        </div>
                        <hr>
                        <div class="row">
                            @if (count($concentrator->apikeys) > 1)
                              @php
                              $idprod = 0;
                              $iddev = 0;
                              if ($concentrator->apikeys[0]->type == 'prod') {
                                $idprod = 0;
                                $iddev = 1;
                              } else {
                                $idprod = 1;
                                $iddev = 0;
                              }
                              @endphp
                              <div class="col-md-12">
                                API Key de producción: <b>{{ $concentrator->apikeys[$idprod]->api_key }}</b>
                              </div>
                              <div class="col-md-12">
                                  API Key de desarrollo: <b>{{ $concentrator->apikeys[$iddev]->api_key }}</b>
                              </div>
                            @else
                              @if ($concentrator->apikeys[0]->type == 'prod')
                                <div class="col-md-12">
                                  API Key de producción: <b>{{ $concentrator->apikeys[0]->api_key }}</b>
                                </div>
                              @else
                                <div class="col-md-12">
                                  API Key de desarrollo: <b>{{ $concentrator->apikeys[0]->api_key }}</b>
                                </div>
                              @endif
                            @endif
                        </div>
                      </footer>
                    @endif
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        @endif
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
          <h4 class="modal-title">Crear Concentrador</h4>
        </div>
        <div class="modal-body">
          <form id="concentrator_form" action="api/concentrator/store" method="POST">
            <input type="hidden" name="second_pass" id="second_pass">
            <div class="form-body">
              <div class="row">
                <div class="col-md-12">
                  <div class="panel panel-info">
                    <div class="panel-wrapper collapse in" aria-expanded="true">
                      <div class="panel-body">
                        <h3 class="box-title">Informacion general</h3>
                        <hr>
                        <div class="row">
                          <div class="col-md-8">
                            <div class="form-group">
                              <label class="control-label">Nombre</label>
                              <input type="text" id="name" name="name" class="form-control" placeholder="Ingresar nombre">
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">R.F.C</label>
                              <input type="text" id="rfc" name="rfc" class="form-control" placeholder="Ingresar numero RFC">
                            </div>
                          </div>
                          <div class="col-md-4" style="display: none;">
                            <div class="form-group">
                              <label class="control-label">I.N.E</label>
                              <input type="text" id="dni" name="dni" class="form-control" placeholder="Ingresar numero INE">
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Razon social</label>
                              <input type="text" id="business_name" name="business_name" class="form-control" placeholder="Ingresar razon social">
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
                        @php
                          $postpago = session('user')->policies->where('code', 'ORG-ADD')->first();
                        @endphp
                        @if(!empty($postpago) && $postpago->value > 0)
                          <div class="row">
                            <div class="col-md-4">
                              <div class="form-group">
                                <label class="control-label">Post-pago</label>
                                <select id="postpaid" name="postpaid" class="form-control">
                                  <option value="N">No</option>
                                  <option value="Y">Si</option>
                                </select>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-group">
                                <label class="control-label">Monto alerta</label>
                                  <input type="text" id="amount_alert" name="amount_alert" class="form-control" placeholder="Ingresar Monto de alerta" disabled>
                              </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-group">
                                <label class="control-label">Monto a Asignar</label>
                                <input type="text" id="amount_allocate" name="amount_allocate" class="form-control" placeholder="Ingresar Monto a asignar" disabled>
                              </div>
                            </div>
                          </div>
                        @endif
                        <div class="row">
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
                                <option value="Trash">Eliminado</option>
                              </select>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Balance</label>
                                <script type="text/javascript">
                                  var maxbalance = {{$maxbalance}};
                                </script>
                                @if($maxbalance > 0)
                                  <input type="text" id="balance" name="balance" class="form-control" placeholder="Ingresar balance">
                                @else
                                  <input type="text" id="balance" name="balance" class="form-control" placeholder="Ingresar balance" disabled>
                                @endif
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Comisión</label>
                              <script type="text/javascript">
                                var maxcomissions = {{$maxcomissions}};
                              </script>
                              @if($maxcomissions > 0)
                                <input type="text" id="commissions" name="commissions" class="form-control" placeholder="Ingresar comisión">
                              @else
                                <input type="text" id="commissions" name="commissions" class="form-control" placeholder="Ingresar comisión" disabled>
                              @endif
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="form-group">
                              <label class="control-label">Canales</label>
                              <select id="id_channel" name="id_channel" class="form-control">
                                <option value="">Seleccione un canal</option>
                                @foreach($channels as $channel)
                                  <option value="{{$channel->id}}">{{$channel->name}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <div class="col-md-12 ">
                            <div class="form-group">
                              <label>Direccion completa</label>
                              <input type="text" id="address" name="address" class="form-control" placeholder="Ingresar Direccion completa">
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
  <script src="js/concentrators/main.js"></script>
  <script src="js/common-modals.js"></script>
@else
  <h3>Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo</h3>
@endif