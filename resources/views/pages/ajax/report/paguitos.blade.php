<style type="text/css">
  .selectize-input:after{
        content: none !important;
    }
</style>
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Reporte de financiamientos Paguitos
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Reporte
          </a>
        </li>
        <li class="active">
          Reporte de financiamientos Paguitos
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12 col-sm-12">
      <form class=" text-left" id="filterConc" method="POST" name="filterConc">
        <div class="row">
          <div class="col-md-4 col-sm-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Coordinador
              </label>
              <select class="form-control" id="coord" name="coord">
                <option value="">
                  Seleccione un Coordinador
                </option>
                <span class="input-group-addon">
                  <i aria-hidden="true" class="fa fa-search">
                  </i>
                </span>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Vendedor
              </label>
              <select class="form-control" id="seller" name="seller">
                <option value="">
                  Seleccione un vendedor
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Estatus
              </label>
              <select class="form-control" id="status" name="status">
                <option value="">
                  Seleccione un status
                </option>
                <option value="A">
                  Notificado
                </option>
                <option value="P">
                  Asociado
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Fecha Inicio de venta
              </label>
              <div class="input-group">
                <input class="form-control" data-date-format="dd-mm-yyyy" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('-30 days', time())) }}">
                  <span class="input-group-addon">
                    <i class="icon-calender">
                    </i>
                  </span>
                </input>
              </div>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Fecha Fin de venta
              </label>
              <div class="input-group">
                <input class="form-control" data-date-format="dd-mm-yyyy" id="dateEnd" name="dateEnd" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y') }}">
                  <span class="input-group-addon">
                    <i class="icon-calender">
                    </i>
                  </span>
                </input>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-12 p-t-20 text-center">
          <div class="form-group">
            <button class="btn btn-success" id="search" type="button">
              Buscar
            </button>
          </div>
        </div>
      </form>
    </div>
    <div class="col-md-12 col-sm-12" hidden="" id="rep-si">
      <div class="row white-box">
        <div class="col-md-12">
          <h3 class="text-center">
            Reporte de Financiamientos con Paguitos
          </h3>
        </div>
        <div class="col-md-12">
          <button class="btn btn-success m-b-20" id="download" type="button">
            Exportar Excel
          </button>
        </div>
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-paguitos">
              <thead>
                <tr>
                  <th>
                    MSISDN
                  </th>
                  <th>
                    Coordinador
                  </th>
                  <th>
                    Vendedor
                  </th>
                  <th>
                    Cliente
                  </th>
                  <th>
                    Monto inicial
                  </th>
                  <th>
                    Monto financiado
                  </th>
                  <th>
                    Monto total
                  </th>
                  <th>
                    Fecha financiamiento
                  </th>
                  <th>
                    Fecha asociación
                  </th>
                  <th>
                    Estatus
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/reports/rangePicker.js">
</script>
<script src="js/reports/paguitos.js">
</script>
