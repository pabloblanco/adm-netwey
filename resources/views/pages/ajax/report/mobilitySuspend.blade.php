<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Clientes suspendidos por movilidad</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Clientes suspendidos (mov)</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="container" id="report_container">
        <div class="white-box">
          <div class="row">
            <div class="col-md-12">
              <h3>
                <header>Reporte de clientes suspendidos por movilidad</header>
              </h3>
            </div>

            <div class="col-md-12" style="padding-bottom: 15px;">
              <button type="button" class="btn btn-success" id="exportCsv">Exportar en CSV</button>
            </div>

            <div class="col-md-12">
              <div class="table-responsive">
                <table class="table table-bordered" id="clients-table">
                  <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Tel&eacute;fono</th>
                        <th>MSISDN</th>
                        <th>Latitud</th>
                        <th>Longitud</th>
                        <th>Fecha</th>
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
<script src="js/reports/mobilitySuspend.js"></script>