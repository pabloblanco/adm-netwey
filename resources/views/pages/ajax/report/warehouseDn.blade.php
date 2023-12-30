<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Ubicar DN</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Ubicar DN</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h3>Ubicar DN</h3>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <div class="container">
        <div class="row">
          <div class="col-md-5">
            <div class="form-group">
                <label class="control-label">MSISDN</label>
                <input class="typeahead form-control" id="findDn"  name="findDn" type="text">
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">&nbsp;</label>
              <div class="input-group">
              <button type="button" class="btn btn-success" id="findDnB">
                <i class="fa fa-check"></i> Buscar Dn
              </button>
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
                <header>Ubicación del DN</header>
              </h3>
            </div>

            <div class="col-md-12">
              <h4>Datos de la bodega</h4>
              <div class="p-b-10"><b>DN: </b> <span id="dni"></span> </div>
              <div class="p-b-10"><b>Tipo: </b> <span id="type"></span> </div>
              <div class="p-b-10"><b>Bodega: </b> <span id="whi"></span> </div>
              <div class="p-b-10"><b>Teléfono de bodega: </b> <span id="pwh"></span> </div>

              <div id="dataSeller">
                <h4>Datos del Vendedor/Coordinador</h4>
                <div class="p-b-10"><b>Nombre: </b> <span id="usrni"></span> </div>
                <div class="p-b-10"><b>email: </b> <span id="usri"></span> </div>
                <div class="p-b-10"><b>Teléfono: </b> <span id="psell"></span> </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/reports/warehouseDn.js?v=2.0"></script>