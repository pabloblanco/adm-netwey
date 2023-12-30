@php
  $ids = array();
  $objects = array();
@endphp
@if (count($sales) > 0)
  <script type="text/javascript">
    var objects = new Array();
  </script>
    @foreach ($sales as $sale)
      @php
        $ids[] = $sale->id;
      @endphp
      <div class="row white-box">
        <div class="col-md-12">
          <div class="card card-outline-primary text-center text-dark">
            <div class="card-block">
              <header>Registro de recepción de deposito n° {{$sale->id}}</header>
              <hr>
              <div class="row">
                <div class="col-md-10">
                  <div class="row">
                    <div class="col-md-4">
                      <label class="control-label">Vendedor: <span> {{ $sale->user }}</span></label>
                    </div>
                    <div class="col-md-4">
                      <label class="control-label">Email: <span> {{ $sale->users_email }}</span></label>
                    </div>
                    <div class="col-md-4">
                      <label class="control-label">Fecha: <span> {{ $sale->date_reg }}</span></label>
                    </div>
                  </div>
                  <hr>
                  <form id="report_deposit_form_{{ $sale->id }}" action="api/seller_reception/deposit/report/{{ $sale->id }}" method="POST">
                    <div class="row">
                      <div class="col-md-3">
                        <div class="form-group">
                          <label class="control-label">Nro Depósito</label>
                          <input type="number" id="deposit{{ $sale->id }}" name='deposit{{ $sale->id }}' class="form-control" placeholder="Nro Depósito">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label class="control-label">Banco</label>
                          <select id="bank{{ $sale->id }}" name="bank{{ $sale->id }}" class="form-control" placeholder="Seleccionar Banco...">
                            <option value="">Seleccionar Banco...</option>
                            @foreach ($banks as $bank)
                              <option value="{{ $bank->id }}">{{ $bank->name }}, Cuenta {{ $bank->numAcount }}</option>
                            @endforeach
                          </select>
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label class="control-label">Monto</label>
                          <input disabled type="number" id="amount{{ $sale->id }}" name='amount{{ $sale->id }}' class="form-control" placeholder="Monto depositado" value="{{$sale->amount}}">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="form-group">
                          <label class="control-label">Imagen</label>
                          <input type="file" id="image{{ $sale->id }}" name="image{{ $sale->id }}" class="form-control"  class="form-control">
                        </div>
                      </div>
                      <input type="submit" id="submit_form_btn{{ $sale->id }}" onClick="save('{{ $sale->id }}');" hidden>
                    </div>
                  </form>
                </div>
                {{--<div class="col-md-2">
                  <div class="container">
                    <div class="row">
                      <div class="col-md-12">
                        <button type="submit" class="btn btn-warning btn-md button" onClick="getDetail('{{ $sale->id }}');">Ver detalle</button>
                      </div>
                    </div>
                  </div>
                  <br>
                  <div class="container">
                    <div class="row">
                      <div class="col-md-12">
                        <button class="btn btn-success btn-md button" onClick="$('#submit_form_btn{{ $sale->id }}').click();">Reportar</button>
                      </div>
                    </div>
                  </div>
                </div>--}}
              </div>
            </div>
          </div>
        </div>
      </div>
      <script type="text/javascript">
        objects.push({rules: {deposit{{$sale->id}}: {required: true},bank{{$sale->id}}: {required: true},amount{{$sale->id}}: {required: true}}, messages: {deposit{{$sale->id}}: {required: 'Debe indicar el número de depósito'},bank{{$sale->id}}: {required: 'Debe indicar un banco'},amount{{$sale->id}}: {required: 'Debe indicar un monto'}}});
        console.log(objects);
      </script>
    @endforeach
  <button hidden type="button" id="open_detail_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#detail_deposit">detail</button>
    <div class="modal fade" id="detail_deposit" role="dialog">
    <div class="modal-dialog" id="modal01">
      <!-- Modal content-->
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 id="modal-title" class="modal-title">Editar</h4>
        </div>
        <div class="modal-body">
          <div id ="body_detail">
          </div>
        </div>
      </div>
    </div>
  </div>
@else
  <h3>El coordinador no tiene depósitos por realizar</h3>
@endif
@php
  $ids = json_encode($ids);
@endphp
<script type="text/javascript">
  var sales = {{$ids}};
</script>
<script src="js/sellerreception/deposittable.js"></script>