<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Estructura administrativa</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Estructura administrativa</li>
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
      <form id="seller_status_form" method="GET" action="view/reports/seller/status">
      </form>
      <div class="container">
        <div class="row">
          <div class="col-md-5">
            <div class="form-group">
                <label class="control-label">Tipo</label>
                <select id="org" name="org" class="form-control" multiple>
                  <option value="">Seleccione una organizaci&oacute;n...</option>
                  @foreach ($organizations as $organization)
                    <option value="{{$organization['id']}}" @if(!empty(session('user')->id_org)) selected @endif>{{$organization['business_name']}}</option>
                  @endforeach
                </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">&nbsp;</label>
              <div class="input-group">
              <button type="button" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Generar reporte</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <hr>
      <div class="container" id="report_container" style="display: none;">
        <div class="white-box">
          <div class="row">
            <div class="col-md-12">
              <h3>
                <header>Reporte de estructura organizativa</header>
              </h3>
            </div>

            <div class="col-md-12" style="padding-bottom: 15px;">
              <button type="button" class="btn btn-success" id="exportCsv">Exportar en CSV</button>
              <a href="#". style="display: none;" id="downloadfile"></a>
            </div>

            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table table-bordered" id="users-table">
                  <thead>
                      <tr>
                          <th>Jerarqu&iacute;a</th>
                          <th>Organizaci&oacute;n</th>
                          <th>Nombre</th>
                          <th>Tel&eacute;fono</th>
                          <th>Cargo</th>
                          <th>Perfil</th>
                      </tr>
                  </thead>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/reports/organizationsEstruct.js"></script>