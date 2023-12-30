@php

  $canC = session('user')->policies->search(
      function ($item, $key){
        return $item->code == 'A2P-CPR';
      }
  );

  $canU = session('user')->policies->search(
      function ($item, $key){
        return $item->code == 'A2P-UPR';
      }
  );

  $canD = session('user')->policies->search(
      function ($item, $key){
        return $item->code == 'A2P-DPR';
      }
  );

@endphp
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Productos
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li>
          <a href="/islim/">
            Dashboard
          </a>
        </li>
        <li class="active">
          Productos
        </li>
      </ol>
    </div>
  </div>
  <div class="row">
    <div class="col-sm-12">
      <div class="white-box">
        @if ($canC)
        <button class="btn btn-info btn-lg" id="open_modal_btn" type="button">
          Agregar
        </button>
        {{-- @else
        <button class="btn btn-info btn-lg" data-target="#myModal" data-toggle="modal" hidden="" id="open_modal_btn" type="button">
          Agregar
        </button>
        --}}
        @endif
        <hr>
          @if (count($object['products']) > 0)
          <div class="table-responsive">
            <table class="table table-striped" id="myTable">
              <thead>
                <tr>
                  <th>
                    Acciones
                  </th>
                  <th>
                    id
                  </th>
                  <th>
                    Proveedor
                  </th>
                  <th>
                    Categoría
                  </th>
                  <th>
                    Título
                  </th>
                  <th>
                    Descripción
                  </th>
                  <th>
                    Marca
                  </th>
                  <th>
                    Modelo
                  </th>
                  <th>
                    SKU
                  </th>
                  <th>
                    Tipo
                  </th>
                  <th>
                    Status
                  </th>
                </tr>
              </thead>
              <tbody>
                @foreach ($object['products'] as $product)
                <tr>
                  <th>
                    @if($canU)
                    <button class="btn btn-warning btn-md button" onclick="update('{{ $product }}')" type="button">
                      Editar
                    </button>
                    @endif

                    @if($canD)
                    <button class="btn btn-danger btn-md button" onclick="deleteData('{{ $product->id }}', '{{ $product->title }}')" type="button">
                      Eliminar
                    </button>
                    @endif
                  </th>
                  <th>
                    {{ $product->id }}
                  </th>
                  <th>
                    {{ $product->provider_name }}
                  </th>
                  <th>
                    {{ $product->category_name }}
                  </th>
                  <th>
                    {{ $product->title }}
                  </th>
                  <th>
                    {{ $product->description }}
                  </th>
                  <th>
                    {{ $product->brand }}
                  </th>
                  <th>
                    {{ $product->model }}
                  </th>
                  <th>
                    {{ !empty($product->sku) ? $product->sku : 'N/A' }}
                  </th>
                  <th>
                    @switch($product->artic_type)
                          @case('H') Internet Hogar @break
                          @case('M') MIFI @break
                          @case('T') Telefonía @break
                          @case('F') Fibra @break
                      @endswitch
                  </th>
                  <th>
                    {{ $product->status }}
                  </th>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          @endif
        </hr>
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
        <button class="close" id="modal_close_x" type="button">
          ×
        </button>
        <h4 class="modal-title">
          Crear Producto
        </h4>
        <input type="hidden" id="artic_id" value="0">
      </div>
      <div class="modal-body">
        <form action="api/products/store" id="product_form" method="POST">
          <div class="form-body">
            <div class="row">
              <div class="col-md-12">
                <div class="panel panel-info">
                  <div aria-expanded="true" class="panel-wrapper collapse in">
                    <div class="panel-body">
                      <h3 class="box-title">
                        Informacion general
                      </h3>
                      <hr/>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Proveedores
                            </label>
                            <select class="form-control" id="provider_dni" name="provider_dni">
                              @foreach ($object['providers'] as $provider)
                              <option value="{{ $provider->dni }}">
                                {{ $provider->name }}, DNI: {{ $provider->dni }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Categorías
                            </label>
                            <select class="form-control" id="category_id" name="category_id">
                              @foreach ($object['categories'] as $category)
                              <option value="{{ $category->id }}">
                                {{ $category->title }}
                              </option>
                              @endforeach
                            </select>
                          </div>
                        </div>
                      </div>
                      <hr/>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Tipo de producto
                            </label>
                            <select class="form-control" id="artic_type" name="artic_type">
                              <option value="H">
                                Internet Hogar
                              </option>
                              <option value="T">
                                Telefonia
                              </option>
                              <option value="M">
                                MIFI
                              </option>
                              <option value="F">
                                Fibra
                              </option>
                            </select>
                          </div>
                        </div>

                        <div class="col-md-12 d-none mt-5" id="list_fiber_zones">
                          <label class="control-label">
                            Relacion de Articulos por Zona de Fibra
                          </label>
                          <hr class="mt-2 mb-3">
                          <div class="row">
                            <div class="col-md-11">
                              <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="control-label">
                                      Zona de Fibra
                                    </label>
                                    <select class="form-control" id="fiber_zone" name="fiber_zone">
                                      @if(!is_null($object['fiberzones']))
                                      <option value="">
                                        Selecciona una zona de Fibra
                                      </option>
                                      @foreach ($object['fiberzones'] as $fiberzone)
                                      <option value="{{ $fiberzone->id }}">
                                        {{ trim($fiberzone->name) }}
                                      </option>
                                      @endforeach
                                      @else
                                      <option value="">
                                        * Hubo un problema al listar las zonas de fibra, vuelve ingresar a productos
                                      </option>
                                      @endif
                                    </select>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label class="control-label">
                                      Articulo de la Zona de Fibra
                                    </label>
                                    <select class="form-control" id="fiber_article" name="fiber_article">
                                      <option value="">
                                        Selecciona un articulo de la zona de fibra
                                      </option>
                                    </select>
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="col-md-1">
                              <div class="form-group">
                                <label class="control-label d-none d-md-block">
                                  &nbsp;
                                </label>
                                <button type="button" class="btn btn-success w-100" id="addArtBtn" data-val="0">Agregar</button>
                              </div>
                            </div>
                          </div>

                          <div class="row" id="prod-fiber-zone-container">
                          </div>
                          <input type="hidden" id="prod-fiber-zone" name="prod_fiber_zone" value="">
                          <hr class="mt-0 mb-5">
                        </div>

                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Título
                            </label>
                            <input class="form-control" id="title" name="title" placeholder="Ingresar Título" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Descripción
                            </label>
                            <input class="form-control" id="description" name="description" placeholder="Ingresar Descripción" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Marca
                            </label>
                            <input class="form-control" id="brand" name="brand" placeholder="Ingresar Marca" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Modelo
                            </label>
                            <input class="form-control" id="model" name="model" placeholder="Ingresar Modelo" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              SKU
                            </label>
                            <input class="form-control" id="sku" name="sku" placeholder="Ingresar SKU del producto" type="text">
                            </input>
                          </div>
                        </div>
                        {{--
                        <div class="col-md-6" hidden="">
                          <div class="form-group">
                            <label class="control-label">
                              Tipo de código de barras
                            </label>
                            <input class="form-control" id="type_barcode" name="type_barcode" placeholder="Ingresar Tipo de código de barras" type="text">
                            </input>
                          </div>
                        </div>
                        --}}
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Precio referencial
                            </label>
                            <input class="form-control" id="price_ref" name="price_ref" placeholder="Ingresar Precio Referencial" type="text">
                            </input>
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                            <label class="control-label">
                              Estatus
                            </label>
                            <select class="form-control" id="status" name="status">
                              <option value="A">
                                Activo
                              </option>
                              <option value="I">
                                Inactivo
                              </option>
                              {{--
                              <option value="Trash">
                                Inhabilitado
                              </option>
                              --}}
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
              @if ($canU || $canC)
              <button class="btn btn-success" onclick="save();" type="submit">
                Guardar
              </button>
              @endif
              <button class="btn btn-default" id="modal_close_btn" type="button">
                Close
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script src="js/product/main.js?v=2.0">
</script>
<script src="js/common-modals.js">
</script>
