<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte Estatus del vendedor</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li> 
                <li class="active">Estus del vendedor</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h3>Configuración del reporte</h3>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
      <form id="seller_status_form" method="GET" action="view/reports/seller/status">
        <div class="container">
          <div class="row">
            <div class="col-md-12">
              <div class="form-group">
                  <label class="control-label">Vendedores</label>
                  <select id="seller" name="seller" class="form-control">
                    <option value="">Seleccione un vendedor...</option>
                    @foreach ($users as $user)
                      <option value="{{$user->email}}" parent="{$user->parent_email}">{{$user->name}} {{$user->last_name}}</option>
                    @endforeach
                  </select>
                  <label class="control-label" id="error_seller"></label>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">Fecha desde</label>
                <div class="input-group">
                  <input type="text" id="date_ini" name="date_ini" class="form-control" placeholder="Fecha de recepción">
                  <span class="input-group-addon"><i class="icon-calender"></i></span>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">Fecha hasta</label>
                <div class="input-group">
                  <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Fecha de recepción">
                  <span class="input-group-addon"><i class="icon-calender"></i></span>
                </div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="form-group">
                <label class="control-label">&nbsp;</label>
                <div class="input-group">
                <button type="submit" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Generar reporte</button>
                </div>
              </div>
          </div>
        </div>
      </form>
      <hr>
      <div class="container" id="report_container">
      </div>
    </div>
  </div>
</div>
<script src="js/reports/seller_status.js"></script>