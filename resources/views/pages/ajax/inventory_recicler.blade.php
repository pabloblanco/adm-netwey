{{--/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Marzo 2022
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Listado de reciclaje de msisdn
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Inventario
          </a>
        </li>
        <li class="active">
          Reciclaje de msisdn
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container report-retention">
  <div class="row justify-content-center">
    <div class="col-12 pb-5">
      <h3>
        Configuración del listado
      </h3>
      <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
        {{ csrf_field() }}
        <div class="container">
          <div class="row justify-content-center">
            <div class="pr-md-4 col-md-4 col-12">
              <div class="form-group">
                <label class="control-label">
                  Fecha Inicio
                </label>
                <div class="input-group">
                  <input class="form-control" data-date-format="dd-mm-yyyy" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 30 days', time())) }}">
                    <span class="input-group-addon">
                      <i class="icon-calender">
                      </i>
                    </span>
                  </input>
                </div>
              </div>
            </div>
            <div class="px-md-4 col-md-4 col-12">
              <div class="form-group">
                <label class="control-label">
                  Fecha Fin
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
            <div class="pl-md-4 col-md-4 col-12">
              <div class="form-group">
                <label class="control-label">
                  Status
                </label>
                <select class="form-control" id="status" name="status">
                  <option value="">
                    Seleccione un status
                  </option>
                  <option value="C">
                    Solicitado
                  </option>
                  <option value="P">
                    Procesado
                  </option>
                  <option value="E">
                    Error
                  </option>
                  <option value="R">
                    Rechazados
                  </option>
                </select>
              </div>
            </div>
            <div class="col-12 text-left">
              <p>
                * Solo se verán datos con rangos de 6 meses.
                <span>
                  Para verificar los casos por procesar debes consultar Altan
                </span>
              </p>
              <p>
                <strong>
                  Ofertas por defecto registradas:
                </strong>
                <br/>
                HBB: {{ (!empty($codeHBB))? $codeHBB : 'S/N'}}
                <br/>
                Mifi: {{ (!empty($codeMifi))?  $codeMifi :  'S/N' }}
                <br/>
                Telefonia: {{ (!empty($codTelf))?  $codTelf :  'S/N' }}
                <br/>
              </p>
            </div>
            <div class="col-12 text-center">
              <button class="btn btn-success" id="search" name="search" type="button">
                <i class="fa fa-check">
                </i>
                Mostrar listado
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
    <div class="col-12" hidden="" id="rep-sc">
      <div class="row white-box">
        <div class="col-12">
          <h3 class="text-center">
            Listado de reciclaje de msisdn
          </h3>
        </div>
        <div class="col-12">
          <button class="btn btn-success m-b-20" id="download" type="button">
            Exportar listado
          </button>
        </div>
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-com" name="list-com">
              <thead>
                <tr>
                  <th class="text-center align-middle">
                    Acciones
                  </th>
                  <th class="text-center align-middle">
                    MSISDN
                  </th>
                  <th class="text-center align-middle">
                    Origen de la solicitud
                  </th>
                  <th class="text-center align-middle">
                    Responsable de la solicitud
                  </th>
                  <th class="text-center align-middle">
                    Fecha de la solicitud
                  </th>
                  <th class="text-center align-middle">
                    Codigo de oferta Altan
                  </th>
                  <th class="text-center align-middle">
                    Observacion
                  </th>
                  <th class="text-center align-middle">
                    Cliente en Netwey
                  </th>
                  <th class="text-center align-middle">
                    Dias sin recargar
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
<script src="js/inventories/recicler.js">
</script>