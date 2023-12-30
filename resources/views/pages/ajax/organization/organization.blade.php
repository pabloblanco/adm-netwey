@php
  $police = session('user')->policies->where('code', 'ORG-ADD')->first(); //permiso para ver dashboard
  $addOrgPermission = (!empty($police)  && $police->value > 0);
@endphp

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Organización</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/islim/">Dashboard</a></li>
                <li class="active">Organización</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">

      @if($addOrgPermission)
        <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">
          Agregar
        </button>
      @endif
      <hr>
      <div class="row white-box">
        <div class="col-md-12">
          <div class="table-responsive">
            <table id="myTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Acción</th>
                  <th>Nombre</th>
                  <th>Dirección</th>
                  <th>R.F.C.</th>
                  <th>Estado</th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!--modal agregar/editar-->
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="modal_close_x">&times;</button>
        <h4 class="modal-title">Organizaci&oacute;n</h4>
      </div>
      <div class="modal-body">
        <form id="organization_form" action="api/organization/store" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  {{--Datos organizacion--}}
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3>Datos de la organizaci&oacute;n</h3>
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                              <label class="control-label">R.F.C</label>
                              <input type="text" id="rfc" name="rfc" class="form-control" placeholder="Ingrese R.F.C">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Razon social</label>
                            <input type="text" id="business_name" name='business_name' class="form-control" placeholder="Ingrese Razon social">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                              <label class="control-label">Direccion</label>
                              <input type="text" id="address" name="address" class="form-control" placeholder="Ingrese Dirección">
                          </div>
                        </div>
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Estado</label>
                              <select name="status" id="status" class="form-control">
                                <option value="A">Activo</option>
                                <option value="I">Inactivo</option>
                              </select>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Tipo</label>
                              <select name="type" id="type" class="form-control">
                                <option value="N" selected>Distribuidor</option>
                                <option value="R">Retail</option>
                              </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {{--Datos de contacto--}}
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3>Datos de contacto</h3>
                      <div class="row">
                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Nombre</label>
                            <input type="text" id="contact_name" name='contact_name' class="form-control" placeholder="Nombre y apellido">
                          </div>
                        </div>

                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Teléfono</label>
                            <input type="text" id="contact_phone" name='contact_phone' class="form-control" placeholder="Teléfono">
                          </div>
                        </div>

                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Email</label>
                            <input type="text" id="contact_email" name='contact_email' class="form-control" placeholder="email">
                          </div>
                        </div>

                        <div class="col-md-3">
                          <div class="form-group">
                            <label class="control-label">Direcci&oacute;n</label>
                            <input type="text" id="contact_address" name='contact_address' class="form-control" placeholder="Dirección">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  {{--Responsable--}}
                  <div class="panel-wrapper collapse in" aria-expanded="true" id="panel-responsable" style="display: none">
                    <div class="panel-body">
                      <h3>Responsable</h3>
                      <div class="row">
                        <div class="col-md-6" id="responNow" style="display: none;">
                          <div class="form-group">
                              <label class="control-label">Responsable Actual</label>
                              <p></p>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                              <label class="control-label">Responsable</label>
                              <select id="responsible" name="responsible" class="form-control">
                              </select>
                          </div>
                        </div>
                        {{--<div hidden class="col-md-6">
                          <div class="form-group">
                              <input id="rfcr" name="rfc" class="form-control">
                          </div>
                        </div>--}}
                      </div>
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
{{--<button hidden type="button" id="open_responsible_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#responsibleModal"></button>
<div class="modal fade" id="responsibleModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Responsable</h4>
      </div>
      <div class="modal-body">
        <form id="responsible_form" action="api/organization/responsible" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <div class="row">
                        <div class="col-md-12" id="responNow" style="display: none;">
                          <div class="form-group">
                              <label class="control-label">Responsable Actual</label>
                              <p></p>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                              <label class="control-label">Responsable</label>
                              <select id="responsible" name="responsible" class="form-control">
                              </select>
                          </div>
                        </div>
                        <div hidden class="col-md-6">
                          <div class="form-group">
                              <input id="rfcr" name="rfc" class="form-control">
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
                <button type="submit" class="btn btn-success" onclick="saveResponsible();">Guardar</button>
                <button type="button" id="responsible_close_btn" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>--}}
<script src="js/organization/main.js?{{time()}}" defer="defer"></script>
<script src="js/common-modals.js"></script>