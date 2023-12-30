@php
  $addServPermission = 0;
  $addServPermission = 0;
  $delServPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'ACS-CCS')
      $addServPermission = $policy->value;
    if ($policy->code == 'ACS-UCS')
      $editServPermission = $policy->value;
    if ($policy->code == 'ACS-DCS')
      $delServPermission = $policy->value;
  }
@endphp

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Servidores</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a onclick="getview('concent');" href="#">Concentradores</a></li>
        <li class="active">Servidores</li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      {{-- @foreach (session('user')->policies as $policy) --}}
        {{-- @if (($policy->code == 'ACS-CCS') && ($policy->value > 0)) --}}
        @if($addServPermission > 0)
          <button type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button>
        {{-- @else
          <button hidden type="button" id="open_modal_btn" class="btn btn-info btn-lg">Agregar</button> --}}
        @endif
      {{-- @endforeach --}}
      <hr>
      @foreach ($concentrators as $concentrator)
        @php
          $hasServers1 = false;
          $hasServers2 = false;
          $cont = 1;
        @endphp
        @foreach ($concentrator->apikeys as $apikey)
          @php
            if ($cont == 1) {
              $hasServers1 = count($apikey->servers) > 0;
            }
            if ($cont == 2) {
              $hasServers2 = count($apikey->servers) > 0;
            }
            $cont++;
          @endphp
        @endforeach
          @if ($hasServers1 || $hasServers2)
            <div class="row white-box">
              <div class="col-md-12">
                <div class="card card-outline-primary text-center text-dark">
                  <div class="card-block">
                    <header>Concentrador n° {{ $concentrator->id }}</header>
                    <hr>
                    @foreach ($concentrator->apikeys as $apikey)
                      @php
                        $nrAPI = 0;
                      @endphp
                      @if (count($apikey->servers) > 0)
                        <div class="row">
                          <div class="col-md-12">
                            <div class="row">
                              <div class="col-md-2">
                                <label class="control-label"><u>
                                  @if ($apikey->type == 'prod')
                                    Producción
                                  @else
                                    Desarrollo
                                  @endif
                                </u></label>
                              </div>
                              <div class="col-md-10">
                                @php
                                  $nrIP = 0;
                                @endphp
                                @foreach ($apikey->servers as $server)
                                  <div class="row">
                                    <div class="col-md-12">
                                      <div class="row">
                                        <div class="col-md-8">
                                          <label class="control-label">Servidor: {{ $server->ip }}</label>
                                        </div>
                                        {{-- @foreach (session('user')->policies as $policy) --}}
                                          {{-- @if (($policy->code == 'ACS-UCS') && ($policy->value > 0)) --}}
                                          @if($editServPermission > 0)
                                            <div class="col-md-2">
                                              <button type="button" class="btn btn-warning btn-lg button" onclick="update('{{ $server }}')">Editar</button>
                                            </div>
                                          @endif
                                        {{-- @endforeach --}}
                                        {{-- @foreach (session('user')->policies as $policy) --}}
                                          {{-- @if (($policy->code == 'ACS-DCS') && ($policy->value > 0)) --}}
                                          @if($delServPermission > 0)
                                            <div class="col-md-2">
                                              <button type="button" class="btn btn-danger btn-lg button" onclick="deleteData('{{ $server->id }}', '{{ $server->ip }}', '{{ $concentrator->id }}')">Eliminar</button>
                                            </div>
                                          @endif
                                        {{-- @endforeach --}}
                                      </div>
                                    </div>
                                  </div>
                                  <br>
                                @endforeach
                              </div>
                            </div>
                            @php
                              $nrIP = $nrIP + 1;
                            @endphp
                            @if (count($apikey->servers) > 0 && $nrIP != count($apikey->servers))
                              <hr>
                            @endif
                          </div>
                        </div>
                        @php
                          $nrAPI = $nrAPI + 1;
                        @endphp
                        @if (count($concentrator->apikeys) > 0 && $nrAPI != count($concentrator->apikeys))
                          <hr>
                        @endif
                      @endif
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          @endif
      @endforeach
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
        <h4 class="modal-title">Crear Servidor</h4>
      </div>
      <div class="modal-body">
        <form id="server_form" action="api/servers/store" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3 class="box-title">Información general</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="control-label">Concentradores</label>
                            <select id="concentrator" name="concentrator" class="form-control">
                              @foreach ($concentrators as $concentrator)
                                <option value="{{ $concentrator->id }}">{{ $concentrator->name }}, DNI: {{ $concentrator->dni }}</option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">IP</label>
                            <input type="text" id="ip" name="ip" class="form-control" placeholder="Ingresar IP">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Tipo</label>
                            <select id="type" name="type" class="form-control">
                              <option value="test">Desarrollo</option>
                              <option value="prod">Producción</option>
                            </select>
                          </div>
                        </div>
                        <div class="col-md-4">
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
                @foreach ($concentrators as $concentrator)
                  <button type="submit" class="btn btn-success" onclick="save({{$concentrator->id}});"> <i class="fa fa-check"></i> Guardar</button>
                @endforeach
                <button type="button" id="modal_close_btn" class="btn btn-default">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="js/servers/main.js"></script>
<script src="js/common-modals.js"></script>