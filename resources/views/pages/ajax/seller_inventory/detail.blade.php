@php
  $retP = false;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'A1V-R1V'){
      $retP = $policy->value;
    }
  }
@endphp

@if (count($inventory))
  @if($retP)
    <div class="row" style="padding-bottom: 15px;">
      <div class="col-md-4">
        <button type="button" class="btn btn-danger btn-md button" onclick="deleteItems();">
          Eliminar
        </button>
      </div>
    </div>  
  @endif

  <div class="table-responsive">
    <table id="myTable" class="table table-striped">
      <thead>
        <tr>
          <th></th>
          <th>Id</th>
          <th>Producto</th>
          <th>MSISDN</th>
          <th>IMEI / MAC</th>
          <th>Precio</th>
          <th>Estatus de <br>Asignación</th>
          @if (session('admin'))
            <th>Estado</th>
          @endif
          @if($retP)
          <th></th>
          @endif
        </tr>
      </thead>
      <tbody>
        @foreach ($inventory as $product)
          <tr>
            <th>
              @if($retP && (empty($product->date_red) || $product->date_red == 'N'))
                <input type="checkbox" id="{{$product->id}}" name="{{$product->id}}" class="form-check-input" value="{{$product->id}}">
              @else
                <input type="checkbox" class="form-check-input" disabled="true">
              @endif
            </th>
            <th>{{ $product->id}}</th>
            <th>{{ $product->title }}</th>
            <th>{{ $product->msisdn }}</th>
            <th>{{ $product->imei }}</th>
            <th>{{ $product->price }}</th>
            <th>
                @if ($product->type == 'P')
                  Pre-Asignado
                @else
                  Asignado
                @endif
              </th>
            @if (session('admin'))
              <th>
                @if ($product->status == 'A')
                  Activo
                @else
                  Inactivo
                @endif
              </th>
            @endif
            @if($retP && (empty($product->date_red) || $product->date_red == 'N'))
            <th>
              <button type="button" class="btn btn-danger btn-md button" onclick="deleteItem('api/seller_inventories/{{$product->users_email}}/{{$product->type}}/{{$product->id}}', '{{$product->msisdn}} - {{$product->title}}', '{{$product->users_email}}');">Eliminar</button>
            </th>
            @endif
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
@else
  <p>No hay registro de inventario</p>
@endif