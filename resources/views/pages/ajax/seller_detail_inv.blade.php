<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Inventario por vendedores</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#" onclick="getview('seller_inventories');">Inventario por vendedores</a></li>
                <li class="active">{{$user->email}}</li>
            </ol>
        </div>
    </div>
  <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
        @foreach (session('user')->policies as $policy)
          @if ($policy->code == 'ADP-CDP') 
            @if ($policy->value > 0)
              <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Asignar</button>
            @else
              <button hidden type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Asignar</button>
            @endif
          @endif
        @endforeach
        <hr>
          <div class="table-responsive">
            <table id="myTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Titulo</th>
                  <th>MSISDN</th>
                  <th>ICCID</th>
                  <th>Fecha de recepción</th>
                  <th>Fecha de envio</th>
                  <th>Precio</th>
                  <th>status</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($user->products as $product)
                  <tr>
                    <th>{{ $product->title}}</th>
                    <th>{{ $product->msisdn }}</th>
                    <th>{{ $product->iccid }}</th>
                    <th>{{ $product->date_reception }}</th>
                    <th>{{ $product->date_sending }}</th>
                    <th>{{ $product->price_pay }}</th>
                    <th>{{ $product->status }}</th>
                    <th>
                        @foreach (session('user')->policies as $policy)
                          @if (($policy->code == 'ADP-UDP') && ($policy->value > 0))
                            <button type="button" class="btn btn-success btn-md button" onclick="update('{{ $product }}')">Editar</button>
                          @endif
                        @endforeach
                        @foreach (session('user')->policies as $policy)
                          @if (($policy->code == 'ADP-DDP') && ($policy->value > 0))
                            <button type="button" class="btn btn-danger btn-md button" onclick="deletearti('{{ $product->id }}','{{$user->email}}')">Eliminar</button>
                          @endif
                        @endforeach
                    </th>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Asignar inventario</h4>
      </div>
      <div class="modal-body">
        <form id="sellerinv_form" action="api/seller_inventories/store" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3 class="box-title">Información general</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Vendedor</label>
                              <select id="user_email" name="user_email" class="form-control">
                                <option value={{$user->email}}>{{$user->email}}</option>
                                @if(session('user.platform') == 'admin')
                                  @foreach($users as $usr)
                                    <option value="{{$usr->email}}">{{$usr->email}}</option>
                                  @endforeach
                                @endif
                              </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Status</label>
                            <select id="status" name="status" class="form-control">
                              <option value="A">Activo</option>
                              <option value="I">Inactivo</option>
                              <option value="Trash">Inhabilitado</option>
                            </select>
                          </div>
                        </div>
                      </div>
                      <hr>
                      <div class="row" id="article_container">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="control-label">Productos</label>
                            <input type="hidden" id="article_list" name="article_list">
                              <select id="article" name="article" multiple class="form-control" placeholder="Seleccionar artículo(s)...">
                                @foreach ($otherproducts as $article)
                                  <option value="{{ $article->id }}">{{ $article->title}}, MSISDN: {{$article->msisdn}}</option>
                                @endforeach
                              </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
                <button type="submit" class="btn btn-success" onclick="save();">Guardar</button>
                <button type="button" id="modal_close_btn" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="js/sellerinventories/main.js"></script>
