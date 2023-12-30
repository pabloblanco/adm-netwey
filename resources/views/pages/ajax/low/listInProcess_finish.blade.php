{{--/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Marzo 2022
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Listado de {{$title}}
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
          {{$title}}
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container report-retention">
  <div class="row justify-content-center">
    @if(isset($viewFile))
    <div class="col-12" id="finiquite_file_container">
      <div class=" form-group">
        <div class="row justify-content-center align-items-center">
          <label class="col-12 control-label">
            Cargar archivo csv con los finiquitos de forma masiva
          </label>
          <div class="col-md-8 col-12">
            <input class="form-control-file" id="file_csv" name="file_csv" type="file">
              <label class="control-label" id="error_status_file">
              </label>
            </input>
          </div>
          <div class="col-md-4 col-12 text-center">
            <button class="btn btn-success" onclick="pushFile()">
              <i class="fa fa-check">
              </i>
              Cargar archivo
            </button>
          </div>
        </div>
      </div>
      <hr/>
    </div>
    @endif
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
                  Nombre del Usuario de Baja
                </label>
                <div class="input-group">
                  <input class="form-control" id="user_dismissal" name="user_dismissal" type="text">
                  </input>
                </div>
              </div>
            </div>
            <div class="pr-md-4 col-md-4 col-12">
              <div class="form-group">
                <label class="control-label">
                  Fecha Inicio (Solicitud)
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
                  Fecha Fin (Solicitud)
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
                  Status de deuda
                </label>
                <select class="form-control" id="statusCash" name="statusCash">
                  <option value="">
                    Seleccione un status
                  </option>
                  <option value="Ycash">
                    Con adeudo
                  </option>
                  <option value="Ncash">
                    Libre de deuda
                  </option>
                </select>
              </div>
            </div>
            @if(isset($showPlus))
            <div class="pl-md-4 col-md-4 col-12">
              <div class="form-group">
                <label class="control-label">
                  Status del proceso
                </label>
                <select class="form-control" id="statusLow" name="statusLow">
                  <option value="">
                    Seleccione un status
                  </option>
                  @foreach ($status as $status)
                  <option value="{{$status['code']}}">
                    {{$status['description']}}
                  </option>
                  @endforeach
                </select>
              </div>
            </div>
            @endif
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
            Listado de {{$title}}
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
                    Usuario a dar de baja
                  </th>
                  <th class="text-center align-middle">
                    Nombre del usuario a dar de baja
                  </th>
                  <th class="text-center align-middle">
                    Distribuidor
                  </th>
                  <th class="text-center align-middle">
                    Fecha de solicitud
                  </th>
                  @if(isset($showPlus))
                  <th class="text-center align-middle">
                    Deuda en inventario
                  </th>
                  <th class="text-center align-middle">
                    Deuda en efectivo
                  </th>
                  <th class="text-center align-middle">
                    Deuda en abonos
                  </th>
                  @endif
                  <th class="text-center align-middle">
                    Deuda acumulada
                  </th>
                  <th class="text-center align-middle">
                    Saldo a favor
                  </th>
                  <th class="text-center align-middle">
                    Total a descontar
                  </th>
                  <th class="text-center align-middle">
                    Fecha de Aprobacion{{isset($showPlus)? '/Rechazo':''}} de solicitud
                  </th>
                  @if(isset($showPlus))
                  <th class="text-center align-middle">
                    Fecha de finalizacion
                  </th>
                  <th class="text-center align-middle">
                    Status
                  </th>
                  <th class="text-center align-middle">
                    Motivo del rechazo
                  </th>
                  <th class="text-center align-middle">
                    Monto descontado
                  </th>
                  <th class="text-center align-middle">
                    Monto del finiquito
                  </th>
                  <th class="text-center align-middle">
                    Fecha del finiquito
                  </th>
                  @endif
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
@if(isset($showPlus))
<script src="js/low/reportLow.js">
</script>
@endif
@if(isset($viewFile))
<script src="js/low/requestProcess.js">
</script>
<script src="js/low/finiquite.js">
</script>
@endif
<script src="js/common-modals.js">
</script>