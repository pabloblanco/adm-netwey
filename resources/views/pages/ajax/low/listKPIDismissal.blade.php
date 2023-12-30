<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">KPI de Descuentos por equipos perdidos</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li> 
                <li class="active">KPI de Descuentos por equipos perdidos</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    {{-- <div class="col-md-12">
      <h3>Configuración del reporte</h3>
    </div> --}}
  </div>
  <div class="row">
    <div class="col-md-12">
      <form id="kpi_dismissal_form" method="POST" action="">
        <div class="container">
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="form-group mb-0">
                  <label class="control-label">Año</label>
                  <select required id="year_list" name="year_list" class="form-control">
                    <option value=''>Seleccione año del periodo</option>
                    @foreach ($years as $year)
                      <option value="{{$year->year}}">{{$year->year}}</option>
                    @endforeach
                  </select>
              </div>
               <label id="year_list-selectized-error" class="myErrorClass" for="year_list-selectized" style=""></label>
            </div>
            <div class="col-md-6 mb-3">
              <div class="form-group mb-0">
                  <label class="control-label">Mes</label>
                  <select required id="month_list" name="month_list" class="form-control" disabled>
                    <option value=''>Seleccione mes del periodo</option>
                  </select>
              </div>
               <label id="month_list-selectized-error" class="myErrorClass" for="month_list-selectized" style=""></label>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">&nbsp;</label>
                <div class="input-group">
                <button type="submit" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Consultar</button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <hr>
      <div class="col-md-12 col-sm-12" id="rep-sc" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center" id='title-rep'>
                        KPI Articulos Perdidos
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-com" class="table table-striped">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th>Regional</th>
                                    <th>Coordinador</th>
                                    <th>Equipos Viejos</th>
                                    <th>Merma</th>
                                    <th>Asignados</th>
                                    <th>KPI</th>
                                    <th>Costo de Equipos Perdidos</th>
                                    <th>Descuento(%)</th>
                                    <th>Descuento($)</th>
                                    <th>Desc. Regional(%)</th>
                                    <th>Desc. Regional($)</th>
                                    <th>Desc. Coordinador(%)</th>
                                    <th>Desc. Coordinador($)</th>
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
<script src="js/reports/kpi_dismissal.js"></script>