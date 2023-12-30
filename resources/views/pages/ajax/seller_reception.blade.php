@if (count($users) > 0)
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Recepción de dinero</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="/islim/">Dashboard</a></li>
          <li class="active">Recepción de dinero</li>
        </ol>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-4">
        <div class="form-group">
          <label class="control-label">
            @if (session('user')->platform == 'admin')
              Coordinadores
            @else
              Vendedores
            @endif
          </label>
          <input type="hidden" id="sellers_list" name="sellers_list">
            @if (session('user')->platform == 'admin')
              <select id="sellers" name="sellers" class="form-control" placeholder="Seleccionar coordinador(es)...">
                <option value="">Seleccionar coordinador(es)...</option>
                @foreach ($users as $seller)
                  <option value="{{ $seller->email }}">{{$seller->name}} {{$seller->last_name}}</option>
                @endforeach
              </select>
            @else
              <select id="sellers" name="sellers" class="form-control" placeholder="Seleccionar vendedor(es)...">
                <option value="">Seleccionar vendedor(es)...</option>
                @foreach ($users as $seller)
                  <option value="{{ $seller->email }}">{{$seller->name}} {{$seller->last_name}}</option>
                @endforeach
              </select>
            @endif
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12" id="sales_table_area">
      </div>
    </div>
  </div>
@else
  @if (session('user')->platform == 'admin')
    <h3>No tienes coordinadores asignados</h3>
  @else
    <h3>No tienes vendedores asignados</h3>
  @endif
@endif
<script src="js/sellerreception/main.js"></script>