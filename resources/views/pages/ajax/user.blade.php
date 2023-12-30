<link href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.0/css/intlTelInput.css" rel="stylesheet"/>

<link as="style" rel="preload" async="async" href="{{ asset('css/select2.min.css')}}" onload="this.onload=null;this.rel='stylesheet'" />

@php
  $profileTabPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'AMU-EPU'){
      $profileTabPermission = $policy->value;
    }

    if($policy->code == 'SEL-EDD'){
      $codDepPermission = $policy->value;
    }
  }
@endphp


<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#myModal">&times;</button>
        <h4 id="modal-title" class="modal-title">Crear Usuario</h4>
      </div>
      <div class="modal-body">
        <!--asda-->
        <ul class="nav nav-tabs">
          <li class="nav-item">
              <a id="user-data" class="nav-link active" data-toggle="tab" href="#datos">Datos</a>
          </li>
          <li class="nav-item">
            <script>
              var user_log = {{$profileTabPermission}};
            </script>
            @if ($profileTabPermission > 0)
              <a id="perfiltab" class="nav-link" data-toggle="tab" href="#perfil">Políticas</a>
            @endif
          </li>

          @if($codDepPermission > 0)
            <li class="nav-item tab-cod" hidden="true">
              <a id="codDeptab" class="nav-link" data-toggle="tab" href="#codDepContent">Cod. Dep&oacute;sito</a>
            </li>
          @endif
        </ul>
        <form id="user_form" action="api/user/store" method="POST">
          <div class="form-body">
            <div class="tab-content">
              <div id="datos" class="tab-pane fade in active">
                  <div class="row">
                      <div class="col-md-12">
                          <div class="panel panel-info">
                              <div class="panel-wrapper collapse in" aria-expanded="true">
                                  <div class="panel-body">
                                    <h3 class="box-title">Información personal</h3>
                                    <hr>

                                    <div class="row">
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <label class="control-label">Tipo de usuario</label>
                                          <select id="platform" name="platform" class="form-control">
                                            <option value="">Seleccione un tipo de usuario</option>
                                            @if(session('user.platform')=='admin')
                                            <option value="admin">Admin</option>
                                            @endif
                                            @if(session('user.platform')=='admin'||session('user.platform')=='coordinador')
                                            <option value="coordinador">Coordinador</option>
                                            @endif
                                            <option value="vendor">Vendedor</option>
                                            <option value="call">Callcenter</option>
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-6">
                                        <div class="form-group">
                                          <label>Perfil de usuario</label>
                                          <select id="profile" name="profile" class="form-control">
                                            <option value="">seleccione un perfil</option>
                                             @foreach($profiles as $profile)
                                              <option data-platform="{{$profile->platform}}" data-type="{{$profile->type}}" data-hassup="{{$profile->has_supervisor}}" value="{{$profile->id}}" class="profile_{{$profile->type}}">{{$profile->name}}</option>
                                             @endforeach
                                          </select>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="row">
                                      <div class="col-md-6" id="division_content">
                                        <div class="form-group">
                                          <label>Division</label>
                                          <select id="division_select" name="division_select"  placeholder="Seleccione una division" class="form-control" style="width:100%; height:35px;">
                                            <option value="">-- Seleccione una division --</option>

                                          </select>
                                        </div>   
                                      </div>
                                      <div class="col-md-6" id="distributor_content">
                                        <div class="form-group">
                                          <label>Distribuidor</label>
                                          <select id="distributor_select" name="distributor_select"  placeholder="Seleccione un Distribuidor" class="form-control " style="width:100%; height:35px;">
                                            <option value="">-- Seleccione un distribuidor --</option>
                                          </select>
                                        </div>   
                                      </div>
                                      <div class="col-md-6" id="region_content">
                                        <div class="form-group">
                                          <label>Region</label>
                                          <select id="region_select" name="region_select" placeholder="Seleccione una region" class="form-control " style="width:100%; height:35px;">
                                            <option value=""> -- Seleccione una region --</option>
                                          </select>
                                        </div>   
                                      </div>
                                      <div class="col-md-6" id="coordinacion_content">
                                        <div class="form-group">
                                          <label>Coordinacion</label>
                                          <select id="coordinacion_select" name="coordinacion_select" placeholder="Seleccione una coordinacion" loaded="false" class="form-control " style="width:100%; height:35px;">
                                            <option value=""> -- Seleccione una coordinacion --</option>
                                          </select>
                                        </div>   
                                      </div>
                                      <div class="col-md-6" id="distributor_name_content">
                                        <div class="form-group">
                                          <label>Distribuidor</label>
                                          <input type="text" id="distributor_name" name="distributor_name" class="form-control" disabled>
                                        </div>   
                                      </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Nombre</label>
                                                <input type="text" id="name" name="name" class="form-control" placeholder="Ingresar Nombre">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Apellido</label>
                                                <input type="text" id="last_name" name="last_name" class="form-control" placeholder="Ingresar Apellido">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Email</label>
                                                <input type="text" id="email" name="email" class="form-control" placeholder="Ingresar Email">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">I.N.E</label>
                                                <input type="text" id="dni" name="dni" class="form-control" placeholder="Numero I.N.E">
                                            </div>
                                        </div>
                                        <!--div class="col-md-4"> Comentado debido a Telmovpay
                                            <div class="form-group">
                                                <label class="control-label">CURP</label>
                                                <input type="text" id="curp" name="curp" class="form-control" placeholder="Numero CURP">
                                            </div>
                                        </div-->                                        
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Telefono</label>
                                                <input type="text" id="phone" name="phone" class="form-control" placeholder="99 9999 9999">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Telefono oficina</label>
                                                <input type="text" id="phone_job" name="phone_job" class="form-control" placeholder="99 9999 9999">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                      <div id="chclass1" class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Profesión</label>
                                                <input type="text" id="profession" name="profession" class="form-control" placeholder="Ingrese Profesión">
                                            </div>
                                        </div>
                                        <div id="chclass2" class="col-md-6">
                                            <div class="form-group">
                                                <label class="control-label">Cargo</label>
                                                <input type="text" id="position" name="position" class="form-control" placeholder="Ingrese Cargo">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12 ">
                                            <div class="form-group">
                                                <label>Direccion completa</label>
                                                <input type="text" id="address" name="address" class="form-control" placeholder=" Ingrese direccion completa">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="delivery-content" hidden="true">
                                        <div class="col-md-12">
                                          <label>Dirección para el envío de inventario</label>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Calle</label>
                                                <input type="text" id="street" name="street" class="form-control" placeholder="Ingrese la calle">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Colonia</label>
                                                <input type="text" id="colony" name="colony" class="form-control" placeholder="Ingrese la colonia">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Municipio</label>
                                                <input type="text" id="municipality" name="municipality" class="form-control" placeholder="Ingrese el municipio">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Estado</label>
                                                <input type="text" id="state" name="state" class="form-control" placeholder="Ingrese el estado">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>C&oacute;digo postal</label>
                                                <input type="text" id="pc" name="pc" class="form-control" placeholder="Ingrese el código postal">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Número externo</label>
                                                <input type="text" id="ext_number" name="ext_number" class="form-control" placeholder="Ingrese el número externo">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Número interno</label>
                                                <input type="text" id="int_number" name="int_number" class="form-control" placeholder="Ingrese el número interno">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Referencia</label>
                                                <input type="text" id="reference" name="reference" class="form-control" placeholder="Ingrese la referencia">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row" id="password_row">
                                      <div class="pass_cont col-md-6">
                                        <div class="form-group">
                                            <label class="control-label">Contraseña</label>
                                            <input type="password" id="password" name="password" class="form-control" placeholder="Ingrese Contraseña">
                                        </div>
                                      </div>
                                      <div class="pass_cont col-md-6">
                                        <div class="form-group">
                                          <label class="control-label">Re. Contraseña</label>
                                          <input type="password" id="re_password" name='re_password' class="form-control" placeholder="Repetir Contraseña">
                                        </div>
                                      </div>

                                      <div class="col-md-4" id="second_pass_area" style="display: none;">
                                        <div class="form-group">
                                          <label>Clave para asignar saldo</label>
                                          <input type="password" name="secondPass" id="secondPass" class="form-control">
                                        </div>
                                      </div>

                                    </div>

                                    <div class="row">
                                      <div id="chclass" class="col-md-4" hidden>
                                          <div class="form-group">
                                            <label class="control-label">Comisión</label>
                                            <select id="commission" name="commission" class="form-control" placeholder="Seleccionar Comisión...">
                                              <option value="0">0.00</option>
                                              <option value="0.05">0.05</option>
                                              <option value="0.08">0.08</option>
                                            </select>
                                          </div>
                                      </div>

                                      <div class="col-md-4" id="organization_area">
                                        <div class="form-group">
                                          <label>Organización</label>
                                          <select id="organization" name="organization" class="form-control">

                                            @if(count($org) > 1)
                                              <option value="">seleccione una organización</option>
                                            @endif
                                            @foreach($org as $organization)
                                              <option value="{{$organization->id}}" data-type="{{$organization->type}}">{{$organization->business_name}}</option>
                                            @endforeach
                                          </select>
                                        </div>
                                      </div>
                                      <div class="col-md-4" id="ware_org_cont" hidden>
                                        <div class="form-group">
                                            <label class="control-label">Bodegas</label>
                                            <select id="ware_org" name="ware_org[]" class="form-control" multiple>
                                              <option value="">Selecciona la(s) bodega(s)</option>
                                            </select>
                                        </div>
                                      </div>
                                       <div id="chclass3" class="col-md-4">
                                            <div class="form-group">
                                                <label class="control-label">Estado</label>
                                                <select id="status" name="status" class="form-control">
                                                    <option value="A">Activo</option>
                                                    <option value="S">Suspendido</option>
                                                </select>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="row">
                                      <div class="col-md-12">
                                        <div class="form-group">
                                          <label class="control-label">Supervisor</label>
                                          <select id="parent_email" name="parent_email" class="form-control" placeholder="Seleccionar usuario(s)...">
                                            <option value="">Seleccione un coordinador</option>
                                            @if(!empty($users_scc))
                                            @foreach ($users_scc as $user)
                                              <option class="parent_user" data-type="{{ $user->platform }}" value="{{ $user->email }}">{{ $user->name }} {{ $user->last_name }}
                                              </option>
                                            @endforeach
                                            @endif
                                          </select>
                                        </div>
                                      </div>
                                    </div>

                                    <div class="row">
                                      <div class="col-md-12">
                                        <div class="form-group">
                                          <label class="control-label">Tomar el puesto de:</label>
                                          <select id="replacement" name="replacement" class="form-control" placeholder="Seleccionar usuario(s)...">
                                            <option value="">Seleccione un usuario</option>
                                          </select>
                                        </div>
                                      </div>
                                    </div>

                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              
              @include("pages.ajax.politics.partials.assign_politics")
                
              <div id="codDepContent" class="tab-pane fade tab-cod" hidden="true">
                <div class="row">
                  <div class="col-md-12">
                    <div class="panel panel-info">
                      <div class="panel-wrapper collapse in" aria-expanded="true">
                        <div class="panel-body">
                          @if(count($bbva))
                          <h3 class="box-title">Banco BBVA</h3>
                          <hr>

                          <div class="row">
                            <input type="hidden" name="bbvaid" id="bbvaid" value="{{ $bbva[0]->id }}">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="control-label">C&oacute;digo de dep&oacute;sito</label>
                                <input type="text" id="codBV" name="codBV" class="form-control" placeholder="XX1234" style="text-transform: uppercase;">
                              </div>
                            </div>

                            <div class="col-md-6" id="delcod-bv-content" hidden="true">
                              <div class="form-group">
                                <label class="control-label">
                                  Eliminar asignaci&oacute;n del c&oacute;digo de dep&oacute;sito
                                </label>
                                <div>
                                  <button type="button" class="btn btn-danger btn-del-cod" id="btn-del-bv">
                                    Eliminar c&oacute;digo <span></span>
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                          @endif

                          @if(count($azteca))
                          <h3 class="box-title">Banco Azteca</h3>
                          <hr>

                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="control-label">Cuenta</label>

                                <select id="bankAccount" name="bankAccount" class="form-control">
                                  @foreach($azteca as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }} - {{ substr($d->numAcount, (strlen($d->numAcount) - 4)) }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label class="control-label">
                                  C&oacute;digo de dep&oacute;sito
                                </label>

                                <select id="codBA" name="codBA" class="form-control">
                                  <option value="">Seleccione un c&oacute;digo</option>
                                  @foreach($codes as $code)
                                    <option value="{{ $code }}">{{ $code }}</option>
                                  @endforeach
                                </select>
                              </div>
                            </div>

                            <div class="col-md-6" id="delcod-az-content" hidden="true">
                              <div class="form-group">
                                <label class="control-label">
                                  Eliminar asignaci&oacute;n del c&oacute;digo de dep&oacute;sito
                                </label>
                                <div>
                                  <button type="button" class="btn btn-danger btn-del-cod" id="btn-del-az">
                                    Eliminar c&oacute;digo <span></span>
                                  </button>
                                </div>
                              </div>
                            </div>
                          </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-actions modal-footer">
                  <button type="submit" class="btn btn-success" onclick="save();"> <i class="fa fa-check"></i> Guardar</button>
                  <button type="button" id="modal_close_btn" class="modal_close_btn btn btn-default" data-modal="#myModal">Close</button>
              </div>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<div class="modal modalAnimate" id="chpassword" role="dialog">
  <div class="modal-dialog" id="modal02">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#chpassword">&times;</button>
        <h4 class="modal-title">Cambiar contraseña</h4>
      </div>
      <div class="modal-body">
        <form id="chpass_form" action="" method="PUT">
          <input type="hidden" id="id" name="id" class="form-control">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <div class="row" id="password_row">
                        <div class="col-md-6">
                          <div class="form-group">
                              <label class="control-label">Contraseña</label>
                              <input type="password" id="ch_password" name="ch_password" class="form-control" placeholder="Ingrese Contraseña">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Re. Contraseña</label>
                            <input type="password" id="ch_re_password" name='ch_re_password' class="form-control" placeholder="Repetir Contraseña">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
                <button type="submit" class="btn btn-success" onclick="savechpass();">Guardar</button>
                <button type="button" id="chpass_close_btn" class="modal_close_btn btn btn-default" data-modal="#chpassword">Close</button>
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
            <h4 class="page-title">Usuarios</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/islim/">Dashboard</a></li>
                <li class="active">usuarios</li>
            </ol>
        </div>
    </div>
</div>
<!--mostrar usuarios-->
{{-- <button hidden type="button" id="open_chpass_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#chpassword">Agregar</button> --}}

<div class="container">
  <div class="row">
    <div class="col-md-12">
      <form id="uporrecharge_form" method="GET" action="view/reports/ur/detail/">
        <div class="container">
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Organizaciones</label>
                  <select id="orgS" name="orgS" class="form-control">
                    @if(count($org) > 1)
                      <option value="">Todas</option>
                    @endif
                    @foreach($org as $organization)
                      <option value="{{$organization->id}}">{{$organization->business_name}}</option>
                    @endforeach
                  </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Perfiles</label>
                  <select id="profileS" name="profileS" class="form-control">
                    <option value="">Todos</option>
                    @if(!empty($profiles))
                      @foreach($profiles as $profile)
                        <option value="{{$profile->id}}">{{$profile->name}}</option>
                      @endforeach
                    @endif
                  </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Supervisor</label>
                  <select id="supervisorS" name="supervisorS" class="form-control">
                    <option value="">Todos</option>
                    @foreach($supervisors as $supervisor)
                      <option value="{{$supervisor->email}}">{{ strtoupper($supervisor->name) }} {{strtoupper($supervisor->last_name)}}</option>
                    @endforeach
                  </select>
              </div>
            </div>
          </div>

          <div class="row">

            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Estatus</label>
                  <select id="statusS" name="statusS" class="form-control">
                    <option value="">Todos</option>
                    <option value="A">Activo</option>
                    {{-- <option value="I">Inactivo</option> --}}
                    <option value="S">Suspendido</option>
                  </select>
              </div>
            </div>

            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Tipo Usuario</label>
                  <select id="userTypeS" name="userTypeS" class="form-control">
                    <option value="">Todos</option>
                    <option value="admin">Administrador</option>
                    <option value="vendor">Vendedor</option>
                    <option value="call">Call Center</option>
                    <option value="promotor">Promotor</option>
                    <option value="coordinador">Coordinador</option>
                  </select>
              </div>
            </div>

            <div class="col-md-4" id="distributor-content">
              <div class="form-group">
                  <label class="control-label">Distribuidor</label>
                  <select id="distributor" name="distributor" class="form-control">
                    <option value="">Todos</option>
                    @foreach($distributors as $distributor)
                      <option value="{{$distributor->id}}">
                        {{ strtoupper($distributor->description)}}
                        </option>
                    @endforeach
                  </select>
              </div>
            </div>

            <input type="hidden" name="myUser" id="myUser" value="{{session('user')->email}}">

            <div class="col-md-12 p-t-25 d-flex flex-row-reverse">
              <button type="button" class="btn btn-success" onclick="drawTable();">
                <i class="fa fa-check"></i> Buscar
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>

  <div class="row">
    <div class="col-md-12">
      @foreach (session('user')->policies as $policy)
        @if ($policy->code == 'AMU-CUS')
          @if ($policy->value > 0)
            <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-modal="#myModal">Agregar</button>
          @else
            <button hidden type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-modal="#myModal">Agregar</button>
          @endif
        @endif
      @endforeach
      <hr>
      <div class="row white-box" id="content-table-users" style="display: none;">
        <div class="col-md-12" style="padding-bottom: 20px;">
          <a class="btn btn-success" id="downloadCSV" href="{{route('csvUsers')}}" target="_blank">Exportar en CSV</a>
        </div>
        <div class="col-md-12">
          <div class="table-responsive">
            <table id="myTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Acciones</th>
                  <th>Usuario</th>
                  <th>Email</th>
                  <th>Tipo de usuario</th>
                  <th>I.N.E.</th>
                  <th>Teléfono</th>
                  <th>Teléfono de oficina</th>
                  <th>Organizaci&oacute;n</th>
                  <th>Profesión</th>
                  <th>Cargo</th>
                  <th>Dirección</th>
                  <th>Distribuidor</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  var user_org;
  @if(session('user.id_org')!=null)
    user_org = {{session('user.id_org')}};
  @endif
</script>


<script src="js/select2.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput-jquery.min.js"></script>
<script src="js/users/main.js?{{time()}}" defer="defer"></script>
<script src="js/common-modals.js"></script>