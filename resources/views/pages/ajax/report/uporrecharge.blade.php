<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de @if ($view == 'ups') Altas @else Recargas @endif</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">@if ($view == 'ups') Altas @else Recargas @endif</li>
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
      <form id="uporrecharge_form" method="GET" action="view/reports/ur/detail/{{$view}}">
        <input type="hidden" name="view" id="view" value="{{$view}}">

        <div class="row">
          <div class="col-md-4 col-sm-6">
              <div class="form-group">
                  <label class="control-label">Organizaci&oacute;n</label>
                  <select id="org" name="org" class="form-control">
                      <option value="">Seleccione una organizaci&oacute;n</option>
                      @foreach($orgs as $org)
                          <option value="{{ $org->id }}" @if($orgs->count() == 1) selected @endif>{{ $org->business_name }}</option>
                      @endforeach
                  </select>
              </div>
          </div>

          <div class="col-md-4 col-sm-6" @if (session('user')->platform != 'admin') style="display: none;" @endif>
              <div class="form-group">
                  <label class="control-label">Coordinador</label>
                  <select id="coord" name="coord" class="form-control">
                      <option value="">Seleccione un Coordinador</option>
                  </select>
              </div>
          </div>

          <div class="col-md-4 col-sm-6">
              <div class="form-group">
                  <label class="control-label">Vendedor</label>
                  <select id="seller" name="seller" class="form-control">
                      <option value="">Seleccione un vendedor</option>
                  </select>
              </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Fecha desde</label>
              <div class="input-group">
                <input type="text" name="date_ini" id="date_ini" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}" readonly="true">
                <span class="input-group-addon"><i class="icon-calender"></i></span>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Fecha hasta</label>
              <div class="input-group">
                <input type="text" name="date_end" id="date_end" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}" readonly="true">
                <span class="input-group-addon"><i class="icon-calender"></i></span>
              </div>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Plan</label>
              <select id="service" name="service" class="form-control">
                <option value="">Seleccione un estatus</option>
                @foreach($services as $service)
                    <option value="{{ $service->id }}">
                        {{ $service->description }}
                    </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Producto</label>
              <select id="product" name="product" class="form-control">
                <option value="">Seleccione un producto</option>
                @foreach($products as $product)
                  <option value="{{ $product->id }}">
                      {{ $product->title }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Tipo</label>
              <select id="type_buy" name="type_buy" class="form-control">
                <option value="">Todos</option>
                <option value="CO">Contado</option>
                <option value="CR">Crédito</option>
              </select>
            </div>
          </div>

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Conciliadas</label>
              <select id="conciliation" name="conciliation" class="form-control">
                <option value="">Todos</option>
                <option value="Y">Si</option>
                <option value="N">No</option>
              </select>
            </div>
          </div>

          @if ($view != 'ups')
          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Serviciabilidad</label>
              <select id="serviceability" name="serviceability" class="form-control">
                <option value="">Todos</option>
                @foreach ($serviceabilities as $serviceability)
                  <option value="{{$serviceability->broadband}}">{{$serviceability->broadband}}</option>
                @endforeach
              </select>
            </div>
          </div>
          @endif

          <div class="col-md-3 col-sm-6">
            <div class="form-group">
              <label class="control-label">Tipo linea</label>
              <select id="type_line" name="type_line" class="form-control">
                <option value="">Todos</option>
                <option value="H">Internet Hogar</option>
                <option value="T">Telefon&iacute;a</option>
                <option value="M">MIFI Nacional</option>
                <option value="MH">MIFI Altan</option>
                <option value="F">Fibra</option>
              </select>
            </div>
          </div>
          
          <div class="col-md-3 col-sm-4" id="coverage-content">
              <div class="form-group">
                  <label class="control-label">Zona de Cobertura</label>
                  <select id="coverage_area" name="coverage_area" class="form-control">
                      <option value="" selected>Todas</option>
                      @foreach($coverage as $zone)
                        <option value="{{ $zone->id }}">
                            {{ $zone->name }}
                        </option>
                      @endforeach
                  </select>
              </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 p-t-20 text-center">
              <div class="input-group">
                <button type="submit" class="btn btn-success" onclick="getReport();">
                  <i class="fa fa-check"></i> Generar reporte
                </button>
              </div>
              <hr>
          </div>
        </div>
      </form>
      <div class="container" id="report_container">
      </div>
    </div>
  </div>
</div>
<script src="js/reports/uporrecharge.js"></script>