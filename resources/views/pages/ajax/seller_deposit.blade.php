<!-- EDITADO-->
@if (count($users) > 0)
  <div class="container-fluid">
    <div class="row bg-title">
      <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
        <h4 class="page-title">Conciliación Depositos Efectivo</h4>
      </div>
      <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
        <ol class="breadcrumb">
          <li><a href="/islim/">Dashboard</a></li>
          <li class="active">Depositos de ventas</li>
        </ol>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-4">
        <div class="form-group">
          <label class="control-label">Coordinadores</label>
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
  <h3>No hay coordinadores registrados activos</h3>
@endif
<script src="js/sellerreception/deposit.js"></script>