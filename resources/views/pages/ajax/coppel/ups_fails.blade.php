<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Coppel - Altas Fallidas</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/islim/">Dashboard</a></li>
                <li class="active">Coppel - Altas Fallidas</li>
            </ol>
        </div>
    </div>
  <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
        <hr>
        {{-- @if (count($object['ups_fails']) > 0) --}}
          <div class="table-responsive">
            <table id="myTable" class="table table-striped">
              <thead>
                <tr>
                  <th id="actionCol">Acción</th>
                  <th>id</th>
                  <th>msisdn</th>
                  <th>Cliente</th>
                  <th>Vendedor</th>
                  <th>Articulo</th>
                  <th>Paquete</th>
                  <th>Servicio</th>
                  <th>Error</th>
                  <th>Fecha</th>
                </tr>
              </thead>
              {{-- <tbody>
                @foreach ($object['ups_fails'] as $up_fail)
                  <tr>
                    <th>
                      <button type="button" class="btn btn-success btn-md button" onclick="">Asociar</button>
                    </th>
                    <th>{{ $up_fail->id }}</th>
                    <th>{{ $up_fail->msisdn }}</th>
                    <th>{{ $up_fail->client }}</th>
                    <th>{{ $up_fail->seller }}</th>
                    <th>{{ $up_fail->article }}</th>
                    <th>{{ $up_fail->pack }}</th>
                    <th>{{ $up_fail->service }}</th>
                    <th>{{ $up_fail->error }}</th>
                    <th>{{ $up_fail->date_register }}</th>
                  </tr>
                @endforeach
              </tbody> --}}
            </table>
          </div>
       {{--  @else
        <h3>No hay registros disponibles</h3>
        @endif --}}
      </div>
    </div>
  </div>
</div>




<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" id="modal_close_x">&times;</button>
        <h4 class="modal-title">Corregir Alta Fallida de Coppel con nuevo MSISDN</h4>
      </div>
      <div class="modal-body">
       {{--  <form id="product_form" action="api/products/store" method="POST">
          <div class="form-body"> --}}

            <div class="panel panel-info">
              <div class="panel-wrapper collapse in" aria-expanded="true">
                <div class="panel-body pb-0">


                  <div class="row">
                    <div class=" col-12 col-md-6 px-md-5" style="background:#ff000008">
                      <h3 class="box-title">Informacion de Alta Fallida</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">id</label>
                            <input type="text" id="fail_id" name="fail_id" class="form-control" readonly="true">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">MSISDN</label>
                            <input type="text" id="fail_dn" name="fail_dn" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Cliente</label>
                            <input type="text" id="fail_client" name="fail_client" class="form-control" readonly="true">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Vendedor</label>
                            <input type="text" id="fail_seller" name="fail_seller" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Articulo</label>
                            <input type="text" id="fail_article" name="fail_article" class="form-control" readonly="true">
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Paquete</label>
                            <input type="text" id="fail_pack" name="fail_pack" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Servicio</label>
                            <input type="text" id="fail_service" name="fail_service" class="form-control" readonly="true">
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Registro</label>
                            <input type="text" id="fail_date" name="fail_date" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>
                    </div>


                    <div class=" col-12 col-md-6 px-md-5" style="background:#00800008">
                      <h3 class="box-title">Informacion de Alta Sustituto</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">MSISDN</label>
                            <input type="text" id="new_msisdn" name="new_msisdn" class="form-control" placeholder="Ingresar MSISDN">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">&nbsp;</label><br>
                            <button type="button" class="btn btn-success" id="valid-btn">Validar</button>
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Cliente</label>
                            <input type="text" id="new_client" name="new_client" class="form-control" readonly="true">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Vendedor</label>
                            <input type="text" id="new_seller" name="new_seller" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Articulo</label>
                            <input type="text" id="new_article" name="new_article" class="form-control" readonly="true">
                          </div>
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Paquete</label>
                            <input type="text" id="new_pack" name="new_pack" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>

                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Servicio</label>
                            <input type="text" id="new_service" name="new_service" class="form-control" readonly="true">
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Registro</label>
                            <input type="text" id="new_date" name="new_date" class="form-control" readonly="true">
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>

            <input type="hidden" id="is_valid" name="is_valid" value="false">

            <div class="form-actions modal-footer">
                <button type="button" class="btn btn-success" id="associate-btn" disabled="true" >Procesar</button>
            </div>
          {{-- </div> --}}
        {{-- </form> --}}
      </div>
    </div>
  </div>
</div>


<script src="js/coppel/main.js?v=2.0"></script>
<script src="js/common-modals.js"></script>
