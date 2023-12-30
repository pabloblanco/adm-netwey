@php
$asignPermision = 0;
foreach (session('user')->policies as $policy) {
  if ($policy->code == 'A2W-MIW'){
    $asignPermision = $policy->value;
  }
}
@endphp
@if ($asignPermision > 0)
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Movimientos entre bodegas</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Movimientos entre bodegas</li>
      </ol>
    </div>
  </div>
</div>
<div class="container white-box">
  <label class="control-label">Movimiento entre bodegas por archivos CSV</label>
  <br><br>
  <form id="file_form">
    <div class="row">
      <div class="col-md-12">
        <div class="form-group">
          <label>
            <input class="form-check-input" id="remove-inv-file" type="checkbox">
            Retirar inventario a usuarios
          </label>
        </div>
      </div>
      <div class="col-md-4"><input type="file" name="csv" id="csv"></div>
      {{--<div class="col-md-3">
        <div class="form-group">
          <select id="whinifile" name="whinifile" class="form-control" placeholder="Seleccionar bodega inicial...">
            <option value="">Seleccionar bodega inicial...</option>
            @foreach($warehouses as $warehouse)
            <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
            @endforeach
          </select>
        </div>
      </div>--}}
      <div class="col-md-3">
        <div class="form-group">
          <select id="whendfile" name="whendfile" class="form-control" placeholder="Seleccionar bodega destino...">
            <option value="">Seleccionar bodega destino...</option>
            @foreach($warehouses as $warehouse)
            <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="col-md-2"><button id="fileup" class="btn btn-success"><i class="fa fa-check"></i>Guardar</button></div>
    </div>
  </form>
  <hr>
  <form id="movewh_form" action="api/movewh/update" method="POST">
    <label class="control-label">Movimiento entre bodegas</label>
    <br><br>
    <div class="row ">
      <div class="col-lg-12">
        <div class="row">
          {{--<div class="col-md-12">
            <div class="form-group">
              <label>
                <input class="form-check-input" id="remove-inv" name="removeInv" type="checkbox" value="Y">
                Retirar inventario a usuarios
              </label>
            </div>
          </div>--}}
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">Bodega inicial</label>
              <select id="whini" name="whini" class="form-control" placeholder="Seleccionar bodega inicial...">
                <option value="">Seleccionar bodega inicial...</option>
                @foreach($warehouses as $warehouse)
                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">Bodega destino</label>
              <select id="whend" name="whend" class="form-control" placeholder="Seleccionar bodega destino...">
                <option value="">Seleccionar bodega destino...</option>
                @foreach($warehouses as $warehouse)
                <option value="{{$warehouse->id}}">{{$warehouse->name}}</option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div class="row" id="products">
        </div>
      </div>
    </div>
  </form>
</div>
<script src="js/warehouses/movewh.js"></script>
@else
<h3>Lo sentimos, usteed no posee permisos suficientes para acceder a este módulo</h3>
@endif
