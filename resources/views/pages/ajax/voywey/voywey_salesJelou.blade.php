{{--/*
Autor: Ing. LuisJ 
Contact: luis@gdalab.com
Marzo 2021
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Reporte de ventas Jelou
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
      <form action="" class="" id="report_tb_form" method="POST" name="report_tb_form">
        {{ csrf_field() }}
        <div class="row justify-content-center align-items-center">
          <div class="col-md-4 col-12">
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
          <div class="col-md-4 px-md-4 col-12">
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
          <div class="col-md-4 col-12">
            <div class="form-group">
              <label class="control-label">
                Status de pago
              </label>
              <select class="form-control" id="status" name="status">
                <option value="">
                  Seleccione el status
                </option>
                @foreach ($status as $status)
                <option value="{{$status['code']}}">
                  {{$status['description']}}
                </option>
                {{--@if($status['code']=='C')
                <option selected="true" value="{{$status['code']}}">
                  {{$status['description']}}
                </option>
                @else
                <option value="{{$status['code']}}">
                  {{$status['description']}}
                </option>
                @endif --}}
                            @endforeach
              </select>
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
                    Orden
                  </th>
                  <th>
                    Orden Voywey
                  </th>
                  <th>
                    Status
                  </th>
                  <th>
                    Fecha de creacion
                  </th>
                  <th>
                    Dias transcurridos en Activar
                  </th>
                  <th>
                    Fecha de Activacion
                  </th>
                  <th>
                    Monto pagado
                  </th>
                  <th>
                    Codigo promocional
                  </th>
                  <th>
                    Forma de Pago
                  </th>
                  <th>
                    Email del vendedor
                  </th>
                  <th>
                    Nombre del vendedor
                  </th>
                  <th>
                    Apellido del vendedor
                  </th>
                  <th>
                    Telefono del vendedor
                  </th>
                  <th>
                    INE del Repartidor
                  </th>
                  <th>
                    Nombre del Repartidor
                  </th>
                  <th>
                    Apellido del Repartidor
                  </th>
                  <th>
                    Correo del Repartidor
                  </th>
                  <th>
                    Telefono del Repartidor
                  </th>
                  <th>
                    DNI del cliente
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
                    MSISDN
                  </th>
                  <th>
                    Modelo de equipo
                  </th>
                  <th>
                    Plan adquirido
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
<script src="js/voywey/voywey_salesJelou.js">
</script>
