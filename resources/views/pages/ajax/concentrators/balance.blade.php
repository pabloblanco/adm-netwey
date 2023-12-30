<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="modal_close_x">&times;</button>
        <h4 class="modal-title">Reportar depósito</h4>
      </div>
      <div class="modal-body">
        <form id="concentrator_form" action="" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <label class="control-label">Concentrador: <span id="modal_name"></span></label>
                  </div>
                  <div class="col-md-12">
                    <label class="control-label">Email: <span id="modal_email"></span></label>
                  </div>
                  <div class="col-md-12">
                    <label class="control-label">Teléfono: <span id="modal_phone"></span></label>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <label class="control-label">Dirección: <span id="modal_address"></span></label>
                  </div>
                  <div class="col-md-12">
                    <label class="control-label">Saldo para recargas: <span id="modal_balance"></span></label>
                  </div>
                </div>
              </div>
            </div>
            <hr>
            <div class="row">
              <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Número de depósito/transferencia</label>
                    <input type="number" id="nro_deposit" name="nro_deposit" class="form-control" placeholder="N° de depósito/transferencia">
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Fecha de depósito/transferencia</label>
                    <div class="input-group">
                      <input type="text" id="date_deposit" name="date_deposit" class="form-control" placeholder="yyyy/mm/dd">
                      <span class="input-group-addon"><i class="icon-calender"></i></span>
                    </div>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                  <label class="control-label">Banco</label>
                  <select id="bank" name="bank" class="form-control" placeholder="Seleccionar Banco...">
                    <option value="">Seleccionar Banco...</option>
                    @foreach ($banks as $bank)
                      <option value="{{ $bank->id }}">{{ $bank->name }}, Cuenta {{ $bank->numAcount }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-group">
                    <label class="control-label">Monto depositado</label>
                    <input type="text" id="amount" name="amount" class="form-control" placeholder="Monto">
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group">
                  <label class="control-label">Imagen</label>
                  <input type="file" id="image" name="image" class="form-control" class="form-control">
                </div>
              </div>
              <div class="col-md-6">
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Monto a asignar: <span id="assigned_amount">0</span></label>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Comisión: <span id="commissions_amount">0</span>%</label>
                    </div>
                  </div>
                  <div class="col-md-12">
                    <div class="form-group">
                        <label class="control-label">Total para recargas: <span id="total_amount">0</span></label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
                <button type="submit" class="btn btn-success" onclick="save();">Guardar</button>
                <button type="button" id="modal_close_btn" class="btn btn-default">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<button hidden type="button" id="modal_open_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal"></button>
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Asignación de saldo a concentradores</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/islim/">Dashboard</a></li>
                <li class="active">Asignación de saldo a concentradores</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <div class="row white-box">
        <div class="col-md-12">
          <div class="table-responsive">
            <table id="concentratorBalanceTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Acciones</th>
                  <th>Concentrador</th>
                  <th>Email</th>
                  <th>Teléfono</th>
                  <th>Saldo para recargas</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/concentrators/balance.js"></script>
 <script src="js/common-modals.js"></script>