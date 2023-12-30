<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Reporte de Bodegas</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="#">Reportes</a></li>
        <li class="active">Bodegas</li>
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
      <form id="report_form" method="GET" action="view/reports/warehouses/detail">
        <div class="container">
          <div class="row">
            @php
              $size = "col-md-6";
            @endphp
            {{-- @if (session('user')->profile->type == 'master' && !empty($orgs)) --}}
            @if($orgs->count() > 1)
              @php
                $size = "col-md-4";
              @endphp
              <div class="{{$size}}">
                <div class="form-group">
                  <label class="control-label">Organizaciones</label>
                  <select id="org" name="org" class="form-control">
                    <option value="">Todas</option>
                    @foreach ($orgs as $org)
                      <option value="{{$org->id}}">{{$org->business_name}}</option>
                    @endforeach
                  </select>
                  </div>
              </div>
            @endif
            <div class="{{$size}}">
              <div class="form-group">
                  <label class="control-label">Bodegas</label>
                  <select id="warehouse" name="warehouse" class="form-control">
                    <option value="">Todos</option>
                      @foreach ($warehouses as $warehouse)
                        <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                      @endforeach
                  </select>
              </div>
            </div>
            <div class="{{$size}}">
              <div class="form-group">
                  <label class="control-label">Productos</label>
                  <select id="product" name="product" class="form-control">
                    <option value="">Todos</option>
                      @foreach ($products as $product)
                        <option value="{{$product->id}}">{{$product->title}}</option>
                      @endforeach
                  </select>
              </div>
            </div>
            <div class="col-md-12 text-center">
              <button type="submit" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Generar reporte</button>
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
<script src="js/reports/warehouses.js"></script>
