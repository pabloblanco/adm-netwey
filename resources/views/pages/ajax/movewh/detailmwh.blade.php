<div class="col-md-8">
  <input type="hidden" id="ids" name="ids" class="form-check-input" value="">
  <div class="table-responsive">
    <table id="myTable" class="table table-striped">
      <thead>
        <tr>
          <th>Id</th>
          <th>Serial</th>
          <th>MSISDN</th>
          <th>IMEI</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @foreach ($detailinwh as $inventory)
          <tr>
            <th>{{ $inventory->id }}</th>
            <th>{{ $inventory->serial }}</th>
            <th>{{ $inventory->msisdn }}</th>
            <th>{{ $inventory->imei }}</th>
            <th><input type="checkbox" id="id_{{$inventory->id}}" name="id_{{$inventory->id}}" class="form-check-input" value="{{$inventory->id}}"></th>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
<div class="col-md-4">
  <center>
    <button type="submit" id="movewh" onClick="save();" class="btn btn-info btn-lg">Mover</button>
  </center>
</div>
<script src="js/warehouses/detail.js"></script>