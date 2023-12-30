<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Inventario por vendedores/coordinadores</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">Inventario por vendedores/coordinadores</li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h3>Configuración del reporte</h3>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <form id="report_form" method="GET" action="view/reports/seller/inventories/detail">
        <div class="container">
          <div class="row">
            @if ($orgs->count() > 1)
              <div class="col-md-4">
                <div class="form-group">
                  <label class="control-label">Organizaciones</label>
                  <select id="org" name="org" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($orgs as $org)
                      <option value="{{$org->id}}">{{$org->business_name}}</option>
                    @endforeach
                  </select>
                  </div>
              </div>
            @endif
            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Regionales</label>
                  <select id="reg" name="reg" class="form-control">
                    <option value="">Todos</option>
                    {{--@foreach ($users as $user)
                      @if($user->platform == 'admin')
                        <option value="{{$user->email}}">
                          {{$user->name}} {{$user->last_name}}
                        </option>
                      @endif
                    @endforeach--}}
                  </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Coordinadores</label>
                  <select id="user" name="user" class="form-control">
                    <option value="">Todos</option>
                    {{--@foreach ($users as $user)
                      @if($user->platform == 'coordinador')
                        <option value="{{$user->email}}">
                          {{$user->name}} {{$user->last_name}}
                        </option>
                      @endif
                    @endforeach--}}
                  </select>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                  <label class="control-label">Vendedores</label>
                  <select id="seller" name="seller" class="form-control">
                    <option value="">Todos</option>
                    {{--@foreach ($users as $user)
                      @if($user->platform == 'vendor')
                        <option value="{{$user->email}}">
                          {{$user->name}} {{$user->last_name}}
                        </option>
                      @endif
                    @endforeach--}}
                  </select>
              </div>
            </div>
            <div class="col-md-12 text-center">
              <button type="submit" class="btn btn-success" onclick="getReport();">
                <i class="fa fa-check"></i>
                Generar reporte
              </button>
            </div>
          </div>
        </div>
      </form>
      <hr>
      <div class="container" id="report_container">
      </div>
    </div>
  </div>
</div>
<script src="js/reports/sellerInventory.js?v=1.1"></script>