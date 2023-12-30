<div class="col-md-4">
  <div class="form-group">
    <label class="control-label">Productos</label>
    <select id="product" name="product" class="form-control" placeholder="Seleccionar bodega destino...">
      <option value="">Seleccionar un producto...</option>
      @foreach($proinwh as $product)
        <option value="{{$product->id}}">{{$product->title}}</option>
      @endforeach
    </select>
  </div>
</div>
<script src="js/warehouses/prod.js"></script>