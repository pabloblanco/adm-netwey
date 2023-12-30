{{--/*
/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Febrero 2022
 */
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Listado de exportación
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Portabilidad
          </a>
        </li>
        <li class="active">
          Listado de exportación
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
                  Tipo
                </label>
                <select class="form-control" id="typePort" name="typePort">
                  <option value="">
                    Seleccione un tipo
                  </option>
                  @foreach ($typePort as $typePort)
                  <option value="{{$typePort['code']}}">
                    {{$typePort['description']}}
                  </option>
                  @endforeach
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
            Listado de exportación
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
                  <!--<th class="text-center align-middle">
                    Acciones
                  </th>-->
                  <th class="text-center align-middle">
                    Msisdn exportado
                  </th>
                  <th class="text-center align-middle">
                    ID venta
                  </th>
                  <th class="text-center align-middle">
                    Fecha de alta
                  </th>
                  <th class="text-center align-middle">
                    Fecha de exportación
                  </th>
                  <th class="text-center align-middle">
                    PortID
                  </th>
                  <th class="text-center align-middle">
                    DNI del cliente
                  </th>
                  <th class="text-center align-middle">
                    Nombre del cliente
                  </th>
                  <th class="text-center align-middle">
                    Status
                  </th>
                  <th class="text-center align-middle">
                    Tipo
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
          Detalle del SOAP
          <p class="mb-0">
            <strong>
              Msisdn Exportado:
            </strong>
            <span id="dn_portar">
            </span>
          </p>
          <p class="mb-0">
            <strong>
              Fecha de solicitud:
            </strong>
            <span id="date_solicitud">
            </span>
          </p>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped display nowrap" id="myTableDetailSoap">
                <thead>
                  <tr>
                    <th class="align-middle">
                      Detalle del mensaje
                    </th>
                    <th class="align-middle">
                      Tipo de mensaje
                    </th>
                    <th class="align-middle">
                      Fecha de solicitud
                    </th>
                  </tr>
                </thead>
                <tbody id="detailSoap">
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
<script src="js/portabilidad/exportacion.js">
</script>
<script src="js/common-modals.js">
</script>