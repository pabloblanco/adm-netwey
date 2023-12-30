<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Paquetes del vendedor</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#" onclick="getview('seller_inventories');">Inventario por vendedores</a></li>
                <li class="active">{{$users->email}}</li>
            </ol>
        </div>
    </div>
  <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
        <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Asignar</button>
        <hr>
          <div class="table-responsive">
            <table id="myTable" class="table table-striped">
              <thead>
                <tr>
                  <th>Titulo</th>
                  <th>Descripción</th>
                  <th>Precio</th>
                  <th>Fecha de inicio</th>
                  <th>Fecha final</th>
                  <th>Fecha de registro</th>
                  <th>Estado</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($users->packs as $pack)
                  <tr>
                    <th>{{ $pack->title}}</th>
                    <th>{{ $pack->description }}</th>
                    <th>{{ $pack->price_arti }}</th>
                    <th>{{ $pack->date_ini }}</th>
                    <th>{{ $pack->date_end }}</th>
                    <th>{{ $pack->date_reg }}</th>
                    <th>{{ $pack->status }}</th>
                    <th>
                      <button type="button" class="btn btn-success btn-md" onclick="update('{{ $pack}}')">Editar</button>
                      <button type="button" class="btn btn-danger btn-md" onclick="deletepack('{{ $pack->id }}', '{{ $users->email }}')">Eliminar</button>
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
        <h4 class="modal-title">Asignar paquete</h4>
      </div>
      <div class="modal-body">
        <form id="sellerinv_form" action="api/seller_inventories/store" method="POST">
          <input type="hidden" id="user_email" name="user_email" value={{$users->email}}>
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3 class="box-title">Informacion general</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">Paquetes</label>
                            <select id="pack_id" name="pack_id" class="form-control">
                              @foreach ($opacks as $pack)
                                <option value="{{ $pack->id }}">{{ $pack->title }}</option>
                              @endforeach
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
                      <div class="row">
                        <div class="col-md-12">
                          <div class="form-group">
                            <label class="control-label">Productos</label>
                            <input type="hidden" id="article_list" name="article_list">
                            <select id="article" name="article" multiple class="form-control" placeholder="Seleccionar artículo(s)...">
                              @foreach ($articles as $article)
                                <option value="{{ $article->id }}">{{ $article->title}}, SERIAL: {{$article->serial}}</option>
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
