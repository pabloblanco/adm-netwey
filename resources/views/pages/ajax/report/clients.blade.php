<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Clientes</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">Clientes</li>
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
      @if (count($services) > 0)
        <form id="report_form" method="GET" action="view/reports/clients/detail">
        
        <div class="container">
          <div class="row">
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-check">
                      <label class="form-check-label bt-switch">
                        <input type="checkbox" id="client_manual_check" class="form-check-input"> Ingresar MSISDN manualmente
                      </label>  
                    </div>
                  </div>
                  <div class="col-md-12" id="msisdn_file_container">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label class="control-label">Archivo con MSISDN</label>
                        <input type="file" id="msisdn_file" name="msisdn_file" class="form-control-file">
                      </div>
                    </div>
                  </div>
                  <div class="col-md-12" id="msisdn_select_container">
                    <div class="form-group">
                      <label class="control-label">MSISDN</label>
                      <select id="msisdn_select" name="msisdn_select" class="form-control" multiple>
                        <option value="">Seleccione el(los) msisdn(s)</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
            <div class="col-md-6">
              <div class="form-group">
                  <label class="control-label">Servicios</label>
                  <select id="service" name="service" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($services as $service)
                      <option value="{{$service->id}}">{{$service->title}}</option>
                    @endforeach
                  </select>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4 col-sm-6">
              <div class="form-group">
                <label class="control-label">Fecha desde</label>
                <div class="input-group">
                  <input type="text" name="date_ini" id="date_ini" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}">
                  <span class="input-group-addon"><i class="icon-calender"></i></span>
                </div>
              </div>
            </div>
                    
            <div class="col-md-4 col-sm-6">
              <div class="form-group">
                <label class="control-label">Fecha hasta</label>
                <div class="input-group">
                  <input type="text" name="date_end" id="date_end" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}">
                  <span class="input-group-addon"><i class="icon-calender"></i></span>
                </div>
              </div>
            </div>

            <div class="col-md-4 col-sm-6">
              <div class="form-group">
                <label class="control-label">Tipo linea</label>
                <select id="type_line" name="type_line" class="form-control">
                  <option value="">Todos</option>
                  <option value="H">Internet Hogar</option>
                  <option value="T">Telefon&iacute;a</option>
                  <option value="M">MIFI</option>
                  <option value="F">Fibra</option>
                </select>
              </div>
            </div>
          </div>
        </div>

        <div class="row">
          <div class="col-md-12 p-t-20 text-center">
              <div class="input-group">
                <button type="button" class="btn btn-success" onclick="getReport();"> 
                  <i class="fa fa-check"></i> Generar reporte
                </button>
              </div>

              <hr>
          </div>
        </div>
        </form>
        <div class="container" id="report_container">
        </div>
      @else
        <h3>No hay servicios activos</h3>
      @endif
    </div>
  </div>
</div>
<script src="js/reports/clients.js?v=2.0"></script>