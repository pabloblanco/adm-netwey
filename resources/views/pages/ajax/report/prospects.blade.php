<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Prospectos</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">Prospectos</li>
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
      @if (count($sellers) > 0)
        <form id="report_form" method="GET" action="view/reports/prospects/detail">
          <div class="container">
            <div class="row">
              {{-- @if(!empty($orgs) && count($orgs) && session('user')->profile->type == "master") --}}
              @if($orgs->count() > 1)
                <div class="col-md-3">
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
              <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Vendedores/Coordinadores</label>
                    <select id="seller" name="seller" class="form-control">
                      <option value="">Todos</option>
                      @foreach ($sellers as $seller)
                        <option value="{{$seller->email}}">{{$seller->name}} {{$seller->last_name}}</option>
                      @endforeach
                    </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label class="control-label">Fecha desde</label>
                  <div class="input-group">
                    <input type="text" id="date_ini" name="date_ini" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}">
                    <span class="input-group-addon"><i class="icon-calender"></i></span>
                  </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label class="control-label">Fecha hasta</label>
                  <div class="input-group">
                    <input type="text" id="date_end" name="date_end" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}">
                    <span class="input-group-addon"><i class="icon-calender"></i></span>
                  </div>
                </div>
              </div>
              {{-- @if(!empty($orgs) && count($orgs) && session('user')->profile->type == "master") --}}
              @if($orgs->count() > 1)
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success" onclick="getReport();">
                      <i class="fa fa-check"></i> Generar reporte
                    </button>
                </div>
              @else
                <div class="col-md-3">
                  <div class="form-group text-center">
                    <label class="control-label">&nbsp;</label>
                    <div class="input-group">
                    <button type="submit" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Generar reporte</button>
                    </div>
                  </div>
                </div>
              @endif
            </div>
          </div>
        </form>
        <hr>
        <div class="container" id="report_container">
        </div>
      @else
        <h3>No hay vendedores activos</h3>
      @endif
    </div>
  </div>
</div>
<script src="js/reports/prospects.js"></script>