<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte depositos de venta</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">depositos de venta</li>
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
      <form id="report_form" method="GET" action="view/reports/selldeposit/detail">
        <div class="container">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                  <label class="control-label">Supervisores</label>
                  <select id="parent_email" name="parent_email" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($coordinator as $user)
                      <option value="{{$user->email}}">{{$user->name}} {{$user->last_name}}</option>
                    @endforeach
                  </select>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                  <label class="control-label">Vendedores</label>
                  <select id="user_email" name="user_email" class="form-control">
                    <option value="">Todos</option>
                    @foreach ($vendor as $user)
                      <option value="{{$user->email}}">{{$user->name}} {{$user->last_name}}</option>
                    @endforeach
                  </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">Procesado</label>
                <select name="process" id="process" class="form-control">
                  <option value="">Todos</option>
                  <option value="A">Procesados</option>
                  <option value="P">Por rocesar</option>
                </select>
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">N° transferencia</label>
                <input type="text" class="form-control" name="n_tranfer" id="n_tranfer" placeholder="N° de transferencia">
              </div>
            </div>
            <div class="col-md-2">
              <div class="form-group">
                <label class="control-label">&nbsp;</label>
                <div class="input-group">
                  <button type="submit" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Generar reporte</button>
                </div>
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
<script src="js/reports/sellDeposit.js"></script>
