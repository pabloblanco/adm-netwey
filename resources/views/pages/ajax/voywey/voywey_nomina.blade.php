{{--/*
Autor: Ing. LuisJ 
Contact: luis@gdalab.com
Marzo 2021
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Reporte de ordenes de entrega Jelou-Voywey
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Reportes
          </a>
        </li>
        <li class="active">
          Reporte de ordenes de entrega Jelou-Voywey
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container report-conciliacion">
  <div class="row justify-content-center">
    <div class="col-12 pb-5">
      <h3>
        Configuración del reporte
      </h3>
      <form action="" class="" id="report_tb_form" method="POST" name="report_tb_form">
        {{ csrf_field() }}
        <div class="row justify-content-center align-items-center">
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Fecha Inicio de Entrega
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
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Fecha Fin de Entrega
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
          <div class="col-12 text-center">
            <button class="btn btn-success" id="search" name="search" type="button">
              <i class="fa fa-check">
              </i>
              Mostrar Reporte
            </button>
          </div>
        </div>
      </form>
    </div>
    <div class="col-12" hidden="" id="rep-sc">
      <div class="row white-box">
        <div class="col-md-12">
          <h3 class="text-center">
            Reporte de ordenes de entrega Jelou-Voywey
          </h3>
        </div>
        <div class="col-md-12">
          <button class="btn btn-success m-b-20" id="download" type="button">
            Exportar reporte
          </button>
        </div>
        <div class="col-md-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-com">
              <thead>
                <tr>
                  <th>
                    Folio
                  </th>
                  <th>
                    Nombre del vendedor
                  </th>
                  <th>
                    Apellido del vendedor
                  </th>
                  <th>
                    Correo del vendedor
                  </th>
                  <th>
                    Nombre del repartidor
                  </th>
                  <th>
                    Apellido del repartidor
                  </th>
                  <th>
                    Email del repartidor
                  </th>
                  <th>
                    Telefono del repartidor
                  </th>
                  <th>
                    DNI del repartidor
                  </th>
                  <th>
                    Direccion de entrega
                  </th>
                  <th>
                    Direccion de activacion
                  </th>
                  <th>
                    Precio
                  </th>
                  <th>
                    Forma de pago
                  </th>
                  <th>
                    Numero de transacion
                  </th>
                  <th>
                    Nombre del cliente
                  </th>
                  <th>
                    Apellido del cliente
                  </th>
                  <th>
                    Email del cliente
                  </th>
                  <th>
                    Telefono del cliente
                  </th>
                  <th>
                    Fecha de registro
                  </th>
                  <th>
                    Fecha de entrega
                  </th>
                  <th>
                    MSISDN activado
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
<script src="js/voywey/voywey_nomina.js">
</script>
