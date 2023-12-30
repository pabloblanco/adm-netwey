<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Concentradores</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">Concentrador</li>
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
      <form id="report_form" method="GET" action="view/reports/concentrators/detail">
        <div class="row">
          <div class="col-md-8 col-sm-6">
              <div class="form-group">
                  <label class="control-label">Concentradores</label>
                  <select id="concentrator" name="concentrator" class="form-control">
                  <option value="">Todos</option>
                  @foreach ($concentrators as $concentrator)
                    <option value="{{$concentrator->id}}">{{$concentrator->name}}</option>
                  @endforeach
                </select>
              </div> 
          </div>

          <div class="col-md-4 col-sm-6">
              <div class="form-group">
                <label class="control-label">Tipo de servicios</label>
                <select id="type" name="type" class="form-control">
                  <option value="">Todos</option>
                  <option value="recharges">Recargas</option>
                  <option value="ups">Altas</option>
                </select>
              </div>
          </div>

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

      <div class="container" id="report_container"></div>
    </div>
  </div>
</div>
<script src="js/reports/concentrators.js?v=2.2"></script>