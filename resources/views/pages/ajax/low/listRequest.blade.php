{{--/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Febrero 2022
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Listado de solicitud de bajas de usuarios
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Bajas
          </a>
        </li>
        <li class="active">
          Solicitud de bajas
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
                  Vendedor solicitando baja
                </label>
                <select class="form-control" id="seller" name="seller">
                  <option value="">
                    Buscar un usuario
                  </option>
                </select>
              </div>
            </div>
            <div class="col-12 text-left">
              <p>
                Solo se verán datos con un rango de 6 meses
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
            Listado de solicitud de bajas
          </h3>
        </div>
        <div class="col-12">
          <button class="btn btn-success m-b-20" id="download" type="button">
            Exportar listado
          </button>
        </div>
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-com">
              <thead>
                <tr>
                  <th class="text-center align-middle">
                    Acciones
                  </th>
                  <th class="text-center align-middle">
                    Solicitante
                  </th>
                  <th class="text-center align-middle">
                    Nombre del solicitante
                  </th>
                  <th class="text-center align-middle">
                    Usuario a dar de baja
                  </th>
                  <th class="text-center align-middle">
                    Nombre del usuario a dar de baja
                  </th>
                  <th class="text-center align-middle">
                    Motivo de la baja
                  </th>
                  <th class="text-center align-middle">
                    Fecha de solicitud
                  </th>
                  <th class="text-center align-middle">
                    Deuda en efectivo al dia de solicitud
                  </th>
                  <th class="text-center align-middle">
                    Dias en deuda con efectivo
                  </th>
                  <th class="text-center align-middle">
                    Deuda en inventario
                  </th>
                  <th class="text-center align-middle">
                    Deuda en Abono
                  </th>
                  <th class="text-center align-middle">
                    Ventas en Abono
                  </th>
                  <th class="text-center align-middle">
                    Deuda total
                  </th>
                  <th class="text-center align-middle">
                    Evidencia de la solicitud
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
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button class="modal_close_btn close" data-modal="#myModal" id="modal_close_x" type="button">
          ×
        </button>
        <h4 class="modal-title" id="modal-title">
          Evidencia adjuntada
          <p class="mb-0">
            <strong>
              Fecha de la solicitud:
            </strong>
            <span id="date_Low">
            </span>
          </p>
          <p class="mb-0">
            <strong>
              Solicitante:
            </strong>
            <span id="email_req">
            </span>
          </p>
          <p class="mb-0">
            <strong>
              Usuario a dar de baja:
            </strong>
            <span id="email_Low">
            </span>
          </p>
          <p class="mb-0">
            <strong>
              Motivo:
            </strong>
            <span id="reason_low">
            </span>
          </p>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped display nowrap" id="myTableDetailEvidence">
                <thead>
                  <tr>
                    <th class="align-middle">
                      Archivo
                    </th>
                    <th class="align-middle">
                      Fecha de la evidencia
                    </th>
                    <th class="align-middle">
                      Acciones
                    </th>
                  </tr>
                </thead>
                <tbody id="detailEvidence">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/reports/rangePicker.js">
</script>
{{--
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js">
</script>
--}}
<script src="js/low/axios.min.js?v=0.19.2">
</script>
<script src="js/low/main.js">
</script>
<script src="js/common-modals.js">
</script>