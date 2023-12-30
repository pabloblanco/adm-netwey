{{--/*
Autor: Ing. LuisJ
Contact: luis@gdalab.com
Septiembre 2021
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Reporte de ventas Jelou
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Reportes
          </a>
        </li>
        <li class="active">
          Reporte de ventas Jelou
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
      <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
        <div class="row justify-content-center align-items-center">
          {{ csrf_field() }}
          <div class="col-md-4 col-12 px-4">
            <div class="form-group">
              <label class="control-label">
                Tipo de Fecha
              </label>
              <select class="form-control" id="typeDate" name="typeDate">
                <option value="high">
                  Fecha de activación
                </option>
                <option value="send">
                  Fecha de entrega
                </option>
                <option value="init">
                  Fecha de creación
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12">
            <div class="form-group">
              <label class="control-label" id="date_reg">
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
          <div class="col-md-4 col-sm-6 col-12 pr-md-5 pl-sm-5">
            <div class="form-group">
              <label class="control-label" id="date_end">
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
          <div class="col-md-4 col-12 px-4">
            <div class="form-group">
              <label class="control-label">
                Operador logistico
              </label>
              <select class="form-control" id="operador" name="operador">
                <option value="">
                  Seleccione un operador
                </option>
                <option value="voy">
                  VoyWey
                </option>
                <option value="99">
                  99 Minutos
                </option>
                <option value="prova">
                  Prova
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12 px-4 d-none" id="blockCurrier">
            <div class="form-group">
              <label class="control-label">
                Courier
              </label>
              <select class="form-control" id="optionCurrier" name="optionCurrier">
                <option value="">
                  Seleccione un Courier
                </option>
                <option value="IN">
                  Voywey Interno
                </option>
                <option value="EX">
                  Voywey Externo
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12 px-4" id="optionConciliado">
            <div class="form-group">
              <label class="control-label">
                Pago conciliado
              </label>
              <select class="form-control" id="optionPago" name="optionPago">
                <option value="">
                  Seleccione una opcion
                </option>
                <option value="SI">
                  SI
                </option>
                <option value="NO">
                  NO
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12 px-4">
            <div class="form-group">
              <label class="control-label">
                Tipo de entrega
              </label>
              <select class="form-control" id="optionDeliveryComplete" name="optionDeliveryComplete">
                <option value="SI">
                  Completada
                </option>
                <option value="NO">
                  En proceso
                </option>
              </select>
            </div>
          </div>
          <div class="col-md-4 col-sm-6 col-12 px-4" id="BlockViewFail">
            <div class="form-group">
              <input id="optionFailActive" type="checkbox">
                <label for="optionFailActive">
                  Mostrar ordenes con problemas de activación
                </label>
              </input>
            </div>
          </div>
          <div class="col-md-12 text-center">
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
            Reporte de ventas Jelou
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
                    Courier
                  </th>
                  <th>
                    Nombre del Cliente
                  </th>
                  <th>
                    Telefono del Cliente
                  </th>
                  <th>
                    DNI del cliente
                  </th>
                  <th>
                    Status
                  </th>
                  <th class="Lastsales">
                    Dias transcurridos
                  </th>
                  <th>
                    MSISDN
                  </th>
                  <th>
                    Status del DN
                  </th>
                  <th>
                    Tipo de DN
                  </th>
                  <th>
                    SKU
                  </th>
                  <th>
                    Operador logistico
                  </th>
                  <th>
                    Fecha de creación
                  </th>
                  <th>
                    Estado de la entrega
                  </th>
                  <th>
                    Direccion de entrega
                  </th>
                  <th>
                    Fecha de entrega
                  </th>
                  <th>
                    Monto pagado
                  </th>
                  <th>
                    Forma de pago
                  </th>
                  <th>
                    ¿Dinero en netwey?
                  </th>
                  <th>
                    Fecha del deposito
                  </th>
                  <th>
                    Fecha de Alta
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
<script src="js/reports/sales_jelou.js">
</script>
