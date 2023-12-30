<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Categorías de productos</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Categorías de productos</li>
      </ol>
    </div>
  </div>  
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal">Agregar</button>
      <hr>
      @foreach ($categories as $category)
          <div class="row white-box">
            <div class="col-md-12">
              <div class="card card-outline-primary text-center text-dark">
                <div class="card-block">
                  <header>Categoría n° {{ $category->id }}</header>
                  <hr>
                  <div class="row">
                    <div class="col-md-10">
                      <div class="row">
                        <div class="col-md-12">
                          <label class="control-label">Título: {{ $category->title }}</label>
                        </div>
                      </div>
                      <hr>
                      <div class="row">
                        <div class="col-md-6">
                          <label class="control-label">Descripción: {{ $category->description }}</label>
                        </div>
                        <div class="col-md-6">
                          <label class="control-label">Status: {{ $category->status }}</label>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-2">
                      <div class="row">
                        <div class="col-md-12">
                          <button type="button" class="btn btn-warning btn-lg button" onclick="update('{{ $category }}')">Editar</button>
                        </div>
                      </div>
                      <br>
                      <div class="row">
                        <div class="col-md-12">
                          <button type="button" class="btn btn-danger btn-lg button" onclick="deleteData('{{ $category->id }}', '{{ $category->title }}')">Eliminar</button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

      @endforeach
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
        <h4 class="modal-title">Crear Categoría de producto</h4>
      </div>
      <div class="modal-body">
        <form id="category_form" action="api/categories/store" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div class="panel-wrapper collapse in" aria-expanded="true">
                    <div class="panel-body">
                      <h3 class="box-title">Informacion general</h3>
                      <hr>
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Título</label>
                            <input type="text" id="title" name="title" class="form-control" placeholder="Ingresar Título">
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label class="control-label">Descripción</label>
                            <input type="text" id="description" name="description" class="form-control" placeholder="Ingresar Descripción">
                          </div>
                        </div>
                        <div class="col-md-4">
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
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="form-actions modal-footer">
                <button type="submit" class="btn btn-success" onclick="save();"> <i class="fa fa-check"></i> Guardar</button>
                <button type="button" id="modal_close_btn" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="js/productscategories/main.js"></script>