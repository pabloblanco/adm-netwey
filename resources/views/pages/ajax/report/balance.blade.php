<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Asignación de saldo</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">Asignación de saldo</li>
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
      <form id="report_form" method="GET" action="view/reports/balance/detail/vendor">
        <div class="container">
          <div class="row">
            <div class="col-md-3">
              <div class="form-group">
                <label class="control-label">Coordinador</label>
                <div class="input-group">
                  <select id="user_email" name="user_email" class="form-control" placeholder="Seleccionar usuario(s)...">
                    <option value="">Seleccione un Coordinador</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->email }}">{{ $user->name }} {{ $user->last_name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="control-label">Banco</label>
                <div class="input-group">
                  <select id="bank" name="bank" class="form-control" placeholder="Seleccionar usuario(s)...">
                    <option value="">Seleccione un Banco</option>
                    @foreach ($banks as $bank)
                        <option value="{{ $bank->bank_id }}">{{ $bank->name }}, cuenta N°: {{ $bank->numAcount }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="control-label">Fecha desde</label>
                <div class="input-group">
                  <input readonly type="text" id="date_ini" name="date_ini" class="form-control" placeholder="Fecha de recepción">
                  <span class="input-group-addon"><i class="icon-calender"></i></span>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="form-group">
                <label class="control-label">Fecha hasta</label>
                <div class="input-group">
                  <input readonly type="text" id="date_end" name="date_end" class="form-control" placeholder="Fecha de recepción">
                  <span class="input-group-addon"><i class="icon-calender"></i></span>
                </div>
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
    </div>
  </div>
  <div id="report_container">
  </div>
</div>
<script src="js/reports/balance.js"></script>