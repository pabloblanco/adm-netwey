@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A1S-RSV')
      $accessPermission = $policy->value;
    if ($policy->code == 'A1S-CSV')
      $addPermission = $policy->value;
    if ($policy->code == 'A1S-USV')
      $editPermission = $policy->value;
    if ($policy->code == 'A1S-DSV')
      $delPermission = $policy->value;
  }
@endphp

@if ($accessPermission > 0)
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Servicios</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li>
                    <a href="/islim/">
                        Dashboard
                    </a>
                </li>
                <li class="active">
                    Servicios
                </li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if($addPermission > 0)
            <button class="btn btn-info btn-lg" data-target="#myModal" data-toggle="modal" id="open_modal_btn" type="button">
                Agregar
            </button>
            @endif
            <hr>
                <div class="row white-box">
                    <div class="table-responsive">
                        <table class="table table-striped" id="myTable">
                            <thead>
                                <tr>
                                    @if ($editPermission || $delPermission)
                                    <th>Acciones</th>
                                    @endif
                                    <th>id</th>
                                    <th>Nombre</th>
                                    <th>Periodicidad</th>
                                    <th>Cód.Altan</th>
                                    <th>Cód.Altan Suplementario</th>
                                    <th>Costo</th>
                                    <th>General</th>
                                    <th>Broadband</th>
                                    <th>Tipo</th>
                                    <th>Servicio</th>
                                    <th>Banda 28</th>
                                    <th>Blim</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($services as $service)
                                <tr>
                                    @if ($editPermission || $delPermission)
                                    <th class="row">
                                        @if($editPermission > 0)
                                        <button class="btn btn-warning btn-md button" onclick="update('{{ $service }}')" type="button">
                                            Editar
                                        </button>
                                        @endif
                                        @if($delPermission > 0)
                                        <button class="btn btn-danger btn-md button" onclick="deleteData('{{ $service->id }}', '{{ $service->title }}')" type="button">
                                            Eliminar
                                        </button>
                                        @endif
                                    </th>
                                    @endif
                                    <th>{{ $service->id }}</th>
                                    <th>{{ $service->title }}</th>
                                    <th>{{ $service->periodicity }}</th>
                                    <th>@if( $service->service_type != 'F' && ( ( $service->type == 'A' ) || ( $service->type == 'P' && ( $service->service_type == 'H' || ( $service->service_type == 'T' && !empty( $service->sup ) ) ) ) ) )
                                              {{ $service->codeAltan }}
                                        @else
                                          {{ 'N/A '}}
                                        @endif
                                    </th>
                                    <th>@if( ( $service->type == 'P'  &&  $service->service_type == 'H' )  ||  ( $service->type == 'P'  &&  $service->service_type == 'T'  && !empty( $service->sup ) ) )
                                            {{ $service->sup }}
                                          @else
                                            @if ( ( $service->type == 'R' && $service->service_type == 'H' )  || ( $service->type == 'P' && ( $service->service_type == 'T' || $service->service_type == 'M' || $service->service_type == 'MH' ) ) )
                                              {{ $service->codeAltan }}
                                            @else
                                              {{ 'N/A' }}
                                            @endif
                                        @endif
                                    </th>
                                    <th>{{ number_format($service->price_pay,2,'.',',') }}</th>
                                    <th>{{ !empty($service->plan_type) ? $service->plan_type : 'N/A' }}</th>
                                    <th>{{ !empty($service->num_broad)? $service->num_broad : 'N/A' }}</th>
                                    <th>
                                        @php
                                          if ($service->type == 'A') { echo('Alta');}
                                          if ($service->type == 'P') { echo('Planes');}
                                          if ($service->type == 'H') { echo('Oculto');}
                                          if ($service->type == 'R') { echo('Retencion');}
                                        @endphp
                                    </th>
                                    <th>
                                        @switch($service->service_type)
                                            @case('H') Internet Hogar @break
                                            @case('M') MIFI Huella Nacional @break
                                            @case('MH') MIFI Huella Altan @break
                                            @case('T') Telefonía @break
                                            @case('F') Fibra @break
                                            @default Internet Hogar
                                        @endswitch


                                        {{-- {{ $service->service_type == 'H' ? 'Internet Hogar' : ($service->service_type == 'M' ? 'MIFI Huella Nacional' : ($service->service_type == 'MH' ? 'MIFI Huella Altan' : 'Telefonía')) }} --}}
                                    </th>
                                    <th>{{ !empty($service->is_band_twenty_eight)? ($service->is_band_twenty_eight == 'Y' ? 'Si' : 'No') : 'N/A' }}</th>
                                    <th>{{ !empty($service->blim_service_name)? $service->blim_service_name : 'N/A' }}</th>
                                    <th>{{ $service->status }}</th>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </hr>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
    <div class="modal-dialog" id="modal01">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" id="modal_close_x" type="button">
                    ×
                </button>
                <h4 class="modal-title">Crear Servicio</h4>
                <input type="hidden" id="servi_id" value="0">
            </div>
            <div class="modal-body">
                <form action="api/services/store" id="service_form" method="POST">
                    <input class="form-control" id="id" name="id" type="hidden">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div aria-expanded="true" class="panel-wrapper collapse in">
                                        <div class="panel-body">
                                            <h3 class="box-title">Informacion general</h3>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Título
                                                        </label>
                                                        <input class="form-control" id="title" name="title" placeholder="Título" type="text">

                                                    </div>
                                                </div>
                                                <div class="col-md-8">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Descripción
                                                        </label>
                                                        <input class="form-control" id="description" name="description" placeholder="Breve descripción del servicio" type="text">

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Servicio
                                                        </label>
                                                        <select class="form-control" id="service_type" name="service_type">
                                                            <option value=""> Seleccione... </option>
                                                            <option value="H"> Internet Hogar </option>
                                                            <option value="T"> Telefonía </option>
                                                            <option value="M"> MIFI Huella Nacional </option>
                                                            <option value="MH"> MIFI Huella Altan </option>
                                                            <option value="F"> Fibra </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Periodicidad
                                                        </label>
                                                        <select class="form-control" id="periodicity_id" name="periodicity_id">
                                                            <option value="">
                                                                Seleccione...
                                                            </option>
                                                            @foreach ($periodicities as $periodicity)
                                                            <option value="{{ $periodicity->id }}">
                                                                {{ $periodicity->periodicity }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="broadband-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Broadband
                                                        </label>
                                                        <select class="form-control" id="broadband" name="broadband">
                                                            <option value="">
                                                                Seleccione...
                                                            </option>
                                                            @foreach ($broadbands as $broadband)
                                                            <option value="{{ $broadband->broadband }}">
                                                                {{ $broadband->broadband }}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            {{-- </div>
                                            <div class="row"> --}}
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Tipo
                                                        </label>
                                                        <select class="form-control" id="type" name="type">
                                                            <option value=""> Seleccione... </option>
                                                            <option value="A"> Alta </option>
                                                            <option value="P"> Planes </option>
                                                            <option value="R"> Retencion </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                {{-- <div class="col-md-4" id="servEightFifteen-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Servicio 815
                                                        </label>
                                                        <select class="form-control" id="servEightFifteen" name="servEightFifteen">
                                                            <option value=""> Seleccione... </option>
                                                            @foreach ($servs_ef as $serv_ef)
                                                                <option value="{{$serv_ef['pk']}}">{{$serv_ef['model']." ".$serv_ef['value']}}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div> --}}

                                                <div class="col-md-4" id="codeAltan-content">
                                                    <div class="form-group">
                                                        <label class="control-label" id="codeAltan-title">
                                                            Código Altan
                                                        </label>
                                                        <input class="form-control" id="codeAltan" name="codeAltan" placeholder="Código Altan" type="text">

                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="codeAltanSuplementaryContainer">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Código Altan Suplementario
                                                        </label>
                                                        <input class="form-control" id="codeAltanSuplementary" name="codeAltanSuplementary" placeholder="Código Altan Suplementario" type="text">

                                                    </div>
                                                </div>
                                           {{--  </div>
                                            <div class="row"> --}}
                                                <div class="col-md-12 d-none mt-5" id="list_fiber_zones">
                                                  <label class="control-label">
                                                    Relacion de Servicios por Zona de Fibra
                                                  </label>
                                                  <hr class="mt-2 mb-3">
                                                  <div class="row">
                                                    <div class="col-md-11">
                                                      <div class="row">
                                                        <div class="col-md-6">
                                                          <div class="form-group">
                                                            <label class="control-label">
                                                              Zona de Fibra
                                                            </label>
                                                            <select class="form-control" id="fiber_zone" name="fiber_zone">
                                                              @if(!is_null($fiberzones))
                                                              <option value="">
                                                                Selecciona una zona de Fibra
                                                              </option>
                                                              @foreach ($fiberzones as $fiberzone)
                                                              <option value="{{ $fiberzone->id }}">
                                                                {{ trim($fiberzone->name) }}
                                                              </option>
                                                              @endforeach
                                                              @else
                                                              <option value="">
                                                                * Hubo un problema al listar las zonas de fibra, vuelve ingresar a servicios
                                                              </option>
                                                              @endif
                                                            </select>
                                                          </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                          <div class="form-group">
                                                            <label class="control-label">
                                                              Servicio de la Zona de Fibra
                                                            </label>
                                                            <select class="form-control" id="fiber_service" name="fiber_service">
                                                              <option value="">
                                                                Selecciona un servicio de la zona de fibra
                                                              </option>
                                                            </select>
                                                          </div>
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <div class="col-md-1">
                                                      <div class="form-group">
                                                        <label class="control-label d-none d-md-block">
                                                          &nbsp;
                                                        </label>
                                                        <button type="button" class="btn btn-success w-100" id="addServBtn" data-val="0">Agregar</button>
                                                      </div>
                                                    </div>
                                                  </div>

                                                  <div class="row" id="serv-fiber-zone-container">
                                                  </div>
                                                  <input type="hidden" id="serv-fiber-zone" name="serv_fiber_zone" value="">
                                                  <hr class="mt-0 mb-5">
                                                </div>
                                           {{--  </div>
                                            <div class="row"> --}}
                                                <div class="col-md-4" hidden="">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Indique si es suplementario
                                                        </label>
                                                        <select class="form-control" id="supplementary" name="supplementary">
                                                            <option value="N"> No </option>
                                                            <option value="Y"> Si </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Costo
                                                        </label>
                                                        <input class="form-control" id="price_pay" min="0" name="price_pay" placeholder="Costo" type="number">

                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="gb-container">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            GB
                                                        </label>
                                                        <input class="form-control" id="gb" min="0" name="gb" placeholder="Número de GB" type="number" value="0">

                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="sms-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            SMS
                                                        </label>
                                                        <input class="form-control" id="sms" min="0" name="sms" placeholder="Número de SMS" type="number" value="0">

                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="min-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Minutos
                                                        </label>
                                                        <input class="form-control" id="min" min="0" name="min" placeholder="Número de Minutos" type="number" value="0">

                                                    </div>
                                                </div>

                                                <div class="col-md-4" id="nbte-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Es banda 28
                                                        </label>
                                                        <select class="form-control" id="is_band_twenty_eight" name="is_band_twenty_eight">
                                                            <option class="d-none" selected="" disabled="true" value=""></option>
                                                            <option value="Y"> Si </option>
                                                            <option value="N"> No </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Status
                                                        </label>
                                                        <select class="form-control" id="status" name="status">
                                                            <option value="A"> Activo </option>
                                                            <option value="I"> Inactivo </option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-4" id="listsa-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Listas de DNs
                                                        </label>
                                                        <select class="form-control" id="listsA" name="listsA">
                                                            <option value="">Seleccione la(las) lista(s)</option>
                                                            @foreach($listsA as $list)
                                                            <option value="{{$list->id}}">
                                                                {{$list->name}}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row" id="content-especial-service">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Tipo de plan
                                                        </label>
                                                        <select class="form-control" id="plan_type" name="plan_type">
                                                            <option selected="" value="G"> General </option>
                                                            <option value="I"> Interno </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="service-list">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Listas de DNs
                                                        </label>
                                                        <select class="form-control" id="lists" multiple="" name="lists[]">
                                                            <option value="">Seleccione la(las) lista(s)</option>
                                                            @foreach($lists as $list)
                                                            <option value="{{$list->id}}">
                                                                {{$list->name}}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="channels-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Canal
                                                        </label>
                                                        <select class="form-control" id="chanels" multiple="" name="chanels[]" placeholder="Seleccione el(los) canal(es)">
                                                            @foreach($channels as $channel)
                                                            <option value="{{$channel->id}}">
                                                                {{$channel->name}}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4" id="conc-content">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Concentrador
                                                        </label>
                                                        <select class="form-control" id="conc" multiple="" name="conc[]" placeholder="Seleccione el(los) concentrador(es)">
                                                            @foreach($concentrators as $concentrator)
                                                            <option value="{{$concentrator->id}}">
                                                                {{$concentrator->name}}
                                                            </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row d-none" id="content-blim-service">
                                                <div class="col-md-4" id="blimservice-list">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Servicio Blim
                                                        </label>
                                                        <select class="form-control" id="blim_service" name="blim_service">
                                                            <option value="">
                                                                Sin Servicio Blim
                                                            </option>
                                                            @foreach($blimservices as $blimservice)
                                                            <option value="{{$blimservice->id}}">
                                                                {{$blimservice->name}}
                                                            </option>
                                                            @endforeach
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
                            <button class="btn btn-success" onclick="save();" type="submit"><i class="fa fa-check"></i>Guardar</button>
                            <button class="btn btn-default" id="modal_close_btn" type="button">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="js/services/main.js?v=2.2"></script>
<script src="js/common-modals.js"></script>
@else
<h3>
    Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo
</h3>
@endif
